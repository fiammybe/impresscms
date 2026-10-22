<?php

// two capabilities from one module, with every UI contribution
class mod_alpha_TaggingCapability extends \Icms\Tests\Fixtures\Capability\TaggingStub implements
    \Icms\Capability\Ui\FormContributorInterface,
    \Icms\Capability\Ui\PersistenceContributorInterface,
    \Icms\Capability\Ui\ViewContributorInterface,
    \Icms\Capability\Ui\TableFilterContributorInterface,
    \Icms\Capability\Ui\TemplateRendererInterface
{
    public function formElements(string $objectType, ?\Icms\Capability\ObjectRef $ref, string $fieldPrefix): array
    {
        return [];
    }

    public function store(\Icms\Capability\ObjectRef $ref, \Icms\Capability\Submission $submission): array
    {
        return [];
    }

    public function prefetch(array $refs): void
    {
    }

    public function viewRow(\Icms\Capability\ObjectRef $ref): ?array
    {
        return null;
    }

    public function filterOptions(string $objectType): array
    {
        return [];
    }

    public function matchingIds(string $objectType, string $filterValue): array
    {
        return [];
    }

    public function render(\Icms\Capability\ObjectRef $ref, string $variant, array $params): string
    {
        return '';
    }
}
