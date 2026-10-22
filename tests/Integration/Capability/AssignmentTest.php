<?php

declare(strict_types=1);

namespace App\Tests\Integration\Capability;

use App\Tests\Support\CapabilityLegacyTestCase;
use App\Tests\Support\CapabilitySite;
use Icms\Capability\Registry;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

/**
 * Capabilities per IPF object type (design D9).
 */
#[RunTestsInSeparateProcesses]
#[Group('spec:capability-assignment')]
final class AssignmentTest extends CapabilityLegacyTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        CapabilitySite::select('tagging', 'smarttags');
        CapabilitySite::select('taxonomy', 'taxonomy');
        self::reload();
    }

    /**
     * @scenario Listing object types
     */
    public function testTheObjectTypesAreTheIpfItemsOfTheInstalledModules(): void
    {
        $types = Registry::instance()->snapshot()->objectTypes();

        self::assertContains('news/article', $types);
        self::assertContains('news/category', $types);
        self::assertContains('system/mimetype', $types);
        self::assertNotContains('system/capabilityassignment', $types, 'the framework\'s own configuration is not assignable');
    }

    /**
     * @scenario Enabling tagging for articles
     * @scenario Enabled check
     */
    public function testACapabilityIsEnabledOnlyForTheTypesItIsAssignedTo(): void
    {
        CapabilitySite::assign(['news/article' => ['tagging' => true], 'system/mimetype' => ['tagging' => false]]);
        self::reload();

        self::assertTrue(Registry::isEnabledFor('news/article', 'tagging'));
        self::assertTrue(Registry::isEnabledFor(self::article('Enabled'), 'tagging'));
        self::assertFalse(Registry::isEnabledFor(icms_getModuleHandler('mimetype', 'system')->get(1), 'tagging'));
        self::assertFalse(Registry::isEnabledFor('news/article', 'taxonomy'), 'not assigned');
    }

    /**
     * @scenario Unsupported capability
     */
    public function testAConsumerCanDeclareACapabilityUnsupported(): void
    {
        CapabilitySite::assign(['news/category' => ['tagging' => true]]);
        self::reload();

        self::assertTrue(Registry::instance()->snapshot()->isUnsupported('news/category', 'tagging'));
        self::assertFalse(Registry::isEnabledFor('news/category', 'tagging'), 'even when a cell says enabled');
    }

    /**
     * @scenario Provider missing
     */
    public function testAnAssignedCapabilityWithoutActiveProviderIsNotEnabled(): void
    {
        CapabilitySite::assign(['news/article' => ['tagging' => true]]);
        CapabilitySite::select('tagging', 'nolongerinstalled');
        self::reload();

        self::assertFalse(Registry::isEnabledFor('news/article', 'tagging'));
        self::assertTrue(Registry::instance()->assignments()->isAssigned('news/article', 'tagging'), 'the assignment is kept');
    }

    public function testTheMatrixIsReadOncePerRequest(): void
    {
        CapabilitySite::assign(['news/article' => ['tagging' => true]]);
        self::reload();

        $queries = $this->queriesOf(static function (): void {
            foreach (['news/article', 'news/category', 'system/mimetype', 'news/article'] as $objectType) {
                Registry::isEnabledFor($objectType, 'tagging');
                Registry::isEnabledFor($objectType, 'taxonomy');
            }
        });

        self::assertSame([], array_values(array_filter($queries, static fn (string $sql): bool => str_contains($sql, 'capabilityassignment'))));
    }
}
