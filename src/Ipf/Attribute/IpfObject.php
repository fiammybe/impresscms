<?php

declare(strict_types=1);

namespace App\Ipf\Attribute;

/**
 * Marks an ORM-mapped class as an object of a module (design D7), so that it
 * gets lists, filters and the API like an IPF object:
 *
 *     #[ORM\Entity]
 *     #[ORM\Table(name: 'blog_post')]    // without the ImpressCMS table prefix
 *     #[IpfObject(module: 'blog', item: 'post', identifier: 'title')]
 *     class Post { ... }
 *
 * Captions come from language constants, as for IPF objects:
 * `_CO_<MODULE>_<ITEM>_<FIELD>` and `..._DSC`, or the given prefix followed by
 * the field name.
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
final class IpfObject
{
    /**
     * @param string|null $identifier    the field that names an object; default: the key
     * @param string|null $summary       the field that summarises an object
     * @param string|null $captionPrefix the start of the caption constants, for example "_CO_BLOG_POST_"
     * @param string|null $language      a language file of the module that defines the captions; default: "common"
     * @param bool        $adminLanguage whether that file is in the module's admin language directory
     */
    public function __construct(
        public readonly string $module,
        public readonly string $item,
        public readonly ?string $identifier = null,
        public readonly ?string $summary = null,
        public readonly ?string $captionPrefix = null,
        public readonly ?string $language = null,
        public readonly bool $adminLanguage = false,
    ) {
    }
}
