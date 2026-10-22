<?php

/**
 * Dispatches the IPF extension points to the active capability providers
 *
 * @copyright	The ImpressCMS Project https://www.impresscms.org/
 * @license		https://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU GPL v2
 * @package		ICMS\Capability
 */

declare(strict_types=1);

namespace Icms\Capability;

use Icms\Capability\Exception\UnsupportedObjectException;
use Icms\Capability\Ui\FormContributorInterface;
use Icms\Capability\Ui\PersistenceContributorInterface;
use Icms\Capability\Ui\TableFilterContributorInterface;
use Icms\Capability\Ui\TemplateRendererInterface;
use Icms\Capability\Ui\ViewContributorInterface;
use Icms\Db\Criteria\Element as CriteriaElement;
use Icms\Db\Criteria\Item;
use Icms\Form\Elements\Hiddentoken;
use Icms\Ipf\Entity;
use Icms\Ipf\Form\Base;
use Icms\Ipf\Handler;
use Throwable;

/**
 * The only class the IPF hooks call (design D10, D11). Every method is inert
 * for object types without an enabled capability: it reads the assignment
 * matrix (once per request) and returns.
 */
final class Integration
{
    /** The request key of the capability fields and the name of their token. */
    public const FIELD = 'capability';

    private static ?self $instance = null;

    /** @var array<string, true> references whose submission was stored in this request */
    private array $processed = [];

    /** @var array<string, list<string>> the providers' errors by reference */
    private array $errors = [];

    /** @var array<string, array<string, mixed>>|null capability values set by a host application */
    private ?array $submission = null;

    private ?bool $tokenValid = null;

    public static function instance(): self
    {
        return self::$instance ??= new self();
    }

    public static function reset(): void
    {
        self::$instance = null;
    }

    /**
     * Use these capability values for the next saves instead of the request,
     * for forms a host application bound itself (design D14). The caller has
     * already checked the request's token. Null goes back to the request.
     *
     * @param array<string, array<string, mixed>>|null $values capability => fields
     */
    public function withSubmission(?array $values): void
    {
        $this->submission = $values;
    }

    /** Add a section per enabled capability to the form of the object (Icms\Ipf\Form\Base). */
    public function contributeToForm(Base $form, object $object): void
    {
        $sections = $this->formSections($object);

        foreach ($sections as $capability => $elements) {
            $form->addElement(new FormSection($capability, Definitions::title($capability)));

            foreach ($elements as $element) {
                $form->addElement($element);
            }
        }

        if ($sections !== []) {
            $form->addElement(new Hiddentoken(self::FIELD));
        }
    }

    /**
     * The fields of every enabled capability with a form contributor, for a
     * form of the object; the fields are named under prefix().
     *
     * @return array<string, list<\Icms\Form\Element>> by capability; empty capabilities are left out
     */
    public function formSections(object $object): array
    {
        if (! $object instanceof Entity) {
            return [];
        }

        $objectType = ObjectRef::typeOf($object);
        $ref = $this->refOrNull($object);
        $sections = [];

        foreach ($this->enabledFor($objectType) as $capability) {
            $contributor = Registry::instance()->contributor($capability, FormContributorInterface::class);

            if ($contributor === null) {
                continue;
            }

            try {
                $elements = $contributor->formElements($objectType, $ref, self::prefix($capability));
            } catch (Throwable $exception) {
                Registry::log("The {$capability} form fields of {$objectType} failed: {$exception->getMessage()}");

                continue;
            }

            if ($elements !== []) {
                $sections[$capability] = $elements;
            }
        }

        return $sections;
    }

    /**
     * The object was stored by its handler: let the providers store the
     * capability fields submitted for it. Only for a submitted form with a
     * valid capability token, once per object per request.
     */
    public function afterStored(object $object): void
    {
        if (! $object instanceof Entity) {
            return;
        }

        $values = $this->submittedValues();

        if ($values === []) {
            return;
        }

        $ref = $this->refOrNull($object);

        if ($ref === null || isset($this->processed[$ref->toString()])) {
            return;
        }

        if ($this->submission === null && ! $this->isTokenValid()) {
            Registry::log("The capability fields of {$ref->toString()} were ignored: no valid security token.");

            return;
        }

        $this->processed[$ref->toString()] = true;

        foreach ($this->enabledFor($ref->objectType()) as $capability) {
            if (! isset($values[$capability]) || ! is_array($values[$capability])) {
                continue;
            }

            $contributor = Registry::instance()->contributor($capability, PersistenceContributorInterface::class);

            if ($contributor === null) {
                continue;
            }

            try {
                $errors = $contributor->store($ref, new Submission($capability, $values[$capability]));
            } catch (Throwable $exception) {
                $errors = [$exception->getMessage()];
            }

            foreach ($errors as $error) {
                $message = Definitions::title($capability) . ': ' . $error;
                $object->setErrors($message);
                $this->errors[$ref->toString()][] = $message;
            }
        }
    }

    /**
     * The errors the providers reported while storing the capability fields of
     * the object in this request; the object itself is stored.
     *
     * @return list<string>
     */
    public function errorsFor(object $object): array
    {
        $ref = $object instanceof Entity ? $this->refOrNull($object) : null;

        return $ref === null ? [] : $this->errors[$ref->toString()] ?? [];
    }

    /** The object was deleted by its handler: the providers drop their data for it. */
    public function afterDeleted(object $object): void
    {
        if (! $object instanceof Entity) {
            return;
        }

        $ref = $this->refOrNull($object);

        if ($ref === null) {
            return;
        }

        foreach ($this->enabledFor($ref->objectType()) as $capability) {
            try {
                Registry::capability($capability)->clear($ref);
            } catch (Throwable $exception) {
                Registry::log("{$capability} could not remove the data of {$ref->toString()}: {$exception->getMessage()}");
            }
        }
    }

    /**
     * The rows the providers add to the generic single view of the object.
     *
     * @return array<string, array{
        caption: string,
        value: string
    }> by row key
     */
    public function viewRows(object $object): array
    {
        if (! $object instanceof Entity) {
            return [];
        }

        $ref = $this->refOrNull($object);

        if ($ref === null) {
            return [];
        }

        $rows = [];

        foreach ($this->viewContributors($ref->objectType()) as $capability => $contributor) {
            try {
                $contributor->prefetch([$ref]);
                $row = $contributor->viewRow($ref);
            } catch (Throwable $exception) {
                Registry::log("The {$capability} view row of {$ref->toString()} failed: {$exception->getMessage()}");

                continue;
            }

            if ($row === null) {
                continue;
            }

            $rows[self::FIELD . "_{$capability}"] = ['caption' => $row['caption'], 'value' => $row['html']];
        }

        return $rows;
    }

    /**
     * Load the view data of these objects at once (a page of a table).
     *
     * @param iterable<Entity> $objects
     */
    public function prefetch(iterable $objects): void
    {
        $refsByType = [];

        foreach ($objects as $object) {
            $ref = $object instanceof Entity ? $this->refOrNull($object) : null;

            if ($ref !== null) {
                $refsByType[$ref->objectType()][] = $ref;
            }
        }

        foreach ($refsByType as $objectType => $refs) {
            foreach ($this->viewContributors($objectType) as $capability => $contributor) {
                try {
                    $contributor->prefetch($refs);
                } catch (Throwable $exception) {
                    Registry::log("Prefetching {$capability} data of {$objectType} failed: {$exception->getMessage()}");
                }
            }
        }
    }

    /**
     * The filters the providers offer for a table of the handler's objects.
     *
     * @return array<string, array{
        capability: string,
        caption: string,
        options: array<string, string>
    }> by filter key
     */
    public function tableFilters(Handler $handler): array
    {
        $objectType = "{$handler->_moduleName}/{$handler->_itemname}";
        $filters = [];

        foreach ($this->enabledFor($objectType) as $capability) {
            $contributor = Registry::instance()->contributor($capability, TableFilterContributorInterface::class);

            if ($contributor === null) {
                continue;
            }

            try {
                $options = $contributor->filterOptions($objectType);
            } catch (Throwable $exception) {
                Registry::log("The {$capability} filter of {$objectType} failed: {$exception->getMessage()}");

                continue;
            }

            if ($options === []) {
                continue;
            }

            $filters[self::FIELD . "_{$capability}"] = [
                'capability' => $capability,
                'caption' => Definitions::title($capability),
                'options' => $options,
            ];
        }

        return $filters;
    }

    /** The criterion that restricts the handler's objects to those matching a capability filter value. */
    public function tableCriteria(Handler $handler, string $capability, string $value): CriteriaElement
    {
        $objectType = "{$handler->_moduleName}/{$handler->_itemname}";
        $contributor = Registry::instance()->contributor($capability, TableFilterContributorInterface::class);
        $ids = [];

        if ($contributor !== null && Registry::isEnabledFor($objectType, $capability)) {
            $ids = array_values(array_unique(array_filter(array_map('intval', $contributor->matchingIds($objectType, $value)))));
        }

        // no id is 0: an empty result matches nothing
        $ids = $ids === [] ? [0] : $ids;

        return new Item($handler->keyName, '(' . implode(',', $ids) . ')', 'IN');
    }

    /**
     * A capability's output for an object in a template, or '' when the
     * capability is not enabled for the object's type.
     *
     * @param array<string, mixed> $params
     */
    public function render(string $capability, ObjectRef $ref, string $variant, array $params = []): string
    {
        if (! Registry::isEnabledFor($ref->objectType(), $capability)) {
            return '';
        }

        $renderer = Registry::instance()->contributor($capability, TemplateRendererInterface::class);

        if ($renderer === null) {
            return '';
        }

        try {
            return $renderer->render($ref, $variant, $params);
        } catch (Throwable $exception) {
            Registry::log("Rendering {$capability} ({$variant}) for {$ref->toString()} failed: {$exception->getMessage()}");

            return '';
        }
    }

    public static function prefix(string $capability): string
    {
        return self::FIELD . "[{$capability}]";
    }

    /** @return list<string> */
    private function enabledFor(string $objectType): array
    {
        try {
            return Registry::instance()->assignments()->enabledFor($objectType);
        } catch (Throwable $exception) {
            Registry::log("The capabilities of {$objectType} could not be determined: {$exception->getMessage()}");

            return [];
        }
    }

    /** @return array<string, ViewContributorInterface> */
    private function viewContributors(string $objectType): array
    {
        $contributors = [];

        foreach ($this->enabledFor($objectType) as $capability) {
            $contributor = Registry::instance()->contributor($capability, ViewContributorInterface::class);

            if ($contributor !== null) {
                $contributors[$capability] = $contributor;
            }
        }

        return $contributors;
    }

    /** @return array<string, array<string, mixed>> */
    private function submittedValues(): array
    {
        $values = $this->submission ?? ($_POST[self::FIELD] ?? []);

        return is_array($values) ? $values : [];
    }

    private function isTokenValid(): bool
    {
        if ($this->tokenValid !== null) {
            return $this->tokenValid;
        }

        $security = class_exists('icms', false) ? \icms::$security : null;
        $token = $_POST[self::FIELD . '_REQUEST'] ?? '';

        return $this->tokenValid = is_object($security) && is_string($token) && $security->check(true, $token, self::FIELD);
    }

    private function refOrNull(Entity $object): ?ObjectRef
    {
        try {
            return ObjectRef::fromEntity($object);
        } catch (UnsupportedObjectException) {
            return null;
        }
    }
}
