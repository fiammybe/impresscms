<?php

declare(strict_types=1);

namespace App\Tests\Integration\Capability;

use App\Ipf\Form\IpfFormFactory;
use App\Ipf\IpfPersister;
use App\Tests\Support\CapabilityLegacyTestCase;
use App\Tests\Support\CapabilitySite;
use Icms\Capability\Contract\Tagging\CapabilityInterface as Tagging;
use Icms\Capability\ObjectRef;
use Icms\Capability\Registry;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Request;

/**
 * Framework-handled IPF forms get the same capability fields and storage as
 * the legacy IPF forms (design D14).
 */
#[RunTestsInSeparateProcesses]
#[Group('spec:capability-ipf-integration')]
final class SymfonyBridgeTest extends CapabilityLegacyTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        CapabilitySite::select('tagging', 'smarttags');
        CapabilitySite::assign(['news/article' => ['tagging' => true]]);
        self::reload();
    }

    public function testTheRegistryAndTheContractsAreServices(): void
    {
        $container = static::getContainer();

        self::assertSame(Registry::instance(), $container->get(Registry::class));
        $tagging = $container->get(Tagging::class);
        self::assertInstanceOf(Tagging::class, $tagging);
        self::assertSame('smarttags', $tagging->providerModule());
    }

    /**
     * @scenario Symfony-handled form
     */
    public function testAFrameworkFormHasTheSectionAndStoresLikeTheLegacyPage(): void
    {
        $article = self::article('Framework article');
        Registry::capability('tagging')->setTags(ObjectRef::fromEntity($article), ['before']);
        $forms = static::getContainer()->get(IpfFormFactory::class);

        $form = $forms->create($article);
        self::assertTrue($form->has('capability'));
        self::assertSame('before', $form->get('capability')->get('tagging')->get('tags')->getData(), 'prefilled like the legacy field');

        // the legacy IPF form's HTML posts the same field names
        $html = '<html><body>' . $article->getForm('caption', 'addarticle')->render() . '</body></html>';
        $legacyForm = (new Crawler($html, 'http://127.0.0.1/modules/news/admin/article.php'))->filterXPath('//form[@id="addarticle"]')->form();
        $legacyForm['capability[tagging][tags]'] = 'symfony, form';
        $values = $legacyForm->getPhpValues();
        unset($values['capability_REQUEST']);

        $form = $forms->submit($forms->create($article), Request::create('/', 'POST', $values));
        self::assertTrue($form->isValid(), (string) $form->getErrors(true));
        self::assertSame([], static::getContainer()->get(IpfPersister::class)->save($form));

        $labels = array_map(static fn ($tag): string => $tag->label, Registry::capability('tagging')->getTags(ObjectRef::fromEntity($article)));
        sort($labels);
        self::assertSame(['form', 'symfony'], $labels);
    }

    public function testAFormOfATypeWithoutCapabilitiesIsUnchanged(): void
    {
        $mimetype = icms_getModuleHandler('mimetype', 'system')->get(1);

        self::assertFalse(static::getContainer()->get(IpfFormFactory::class)->create($mimetype)->has('capability'));
    }
}
