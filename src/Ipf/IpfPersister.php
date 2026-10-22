<?php

declare(strict_types=1);

namespace App\Ipf;

use Symfony\Component\Form\FormInterface;

/**
 * Stores and deletes IPF entities bound by an IpfEntityType form, as
 * Icms\Ipf\Controller does, without its redirects (design D8):
 *
 * - save(): the handler's insert(), which runs its events (beforeSave,
 *   beforeInsert/beforeUpdate, afterSave, …), then each permission of the
 *   handler with the groups the form bound, replacing the previous ones
 * - delete(): the handler's delete(), which also removes the permissions
 *
 * The upload branch of storeFromDefaultForm() has no counterpart: forms with
 * upload controls are not built (ControlMap).
 */
final class IpfPersister
{
    /**
     * @return list<string> errors; empty when the entity and its permissions were stored
     */
    public function save(FormInterface $form): array
    {
        $entity = $form->getData();
        if (!is_object($entity) || !$form->isSubmitted()) {
            throw new \LogicException('Only a submitted IPF entity form can be saved.');
        }
        if (!$form->isValid()) {
            $errors = [];
            foreach ($form->getErrors(true) as $error) {
                $errors[] = $error->getMessage();
            }

            return array_values(array_unique($errors));
        }

        $handler = $entity->handler;
        // the providers of enabled capabilities store the bound capability group, not $_POST (design D14)
        $integration = \Icms\Capability\Integration::instance();
        $integration->withSubmission(self::capabilityValues($form));
        try {
            $stored = $handler->insert($entity);
        } finally {
            $integration->withSubmission(null);
        }
        if (!$stored) {
            return array_values(array_map('strval', $entity->getErrors())) ?: ['The handler did not store the item.'];
        }

        if ($handler->getPermissions()) {
            $permissions = new \Icms\Ipf\Permission\Handler($handler);
            foreach ($handler->getPermissions() as $permission) {
                $name = (string) $permission['perm_name'];
                // as in legacy, a selection the browser did not send (none) removes every group
                $field = $form->has($name) ? $form->get($name) : null;
                $groups = $field !== null && $field->isSubmitted() ? array_values(array_map('intval', (array) $field->getData())) : [];
                $permissions->saveItem_Permissions($groups, $entity->id(), $name);
            }
        }

        return [];
    }

    /**
     * The values of the capability group of the form (CapabilityFormExtension),
     * by capability; empty when the form has none.
     *
     * @return array<string, array<string, mixed>>
     */
    private static function capabilityValues(FormInterface $form): array
    {
        $field = \Icms\Capability\Integration::FIELD;
        if (!$form->has($field) || !$form->get($field)->isSubmitted()) {
            return [];
        }

        $values = $form->get($field)->getData();

        return is_array($values) ? array_filter($values, 'is_array') : [];
    }

    /**
     * @return list<string> errors; empty when the entity was deleted
     */
    public function delete(object $entity): array
    {
        if (!$entity->handler->delete($entity)) {
            return array_values(array_map('strval', $entity->getErrors())) ?: ['The handler did not delete the item.'];
        }

        return [];
    }
}
