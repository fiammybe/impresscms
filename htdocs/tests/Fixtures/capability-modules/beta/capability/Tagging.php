<?php

// a second tagging provider that only contributes form fields
class mod_beta_TaggingCapability extends \Icms\Tests\Fixtures\Capability\TaggingStub implements \Icms\Capability\Ui\FormContributorInterface
{
    public function formElements(string $objectType, ?\Icms\Capability\ObjectRef $ref, string $fieldPrefix): array
    {
        return [];
    }
}
