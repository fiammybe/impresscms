# ImpressCMS PSR-4 Namespace Migration Guide

## Overview

This document describes the migration from legacy underscore-based class names to modern PSR-4 namespaced class names in the ImpressCMS library (`htdocs/libraries/icms/`).

**Migration Date**: December 2024  
**Affected Version**: 2.0+  
**PHP Requirements**: PHP 7.4+ (tested with PHP 8.2)

## What Changed?

### Old Naming Convention (Legacy)
```php
// Underscore-separated class names
$security = new icms_core_Security();
$handler = new icms_db_Handler();
$object = new icms_core_Object();
```

### New Naming Convention (Modern)
```php
// PSR-4 Namespaced class names
use Icms\Core\Security;
use Icms\Db\Handler;
use Icms\Core\BaseObject;

$security = new Security();
$handler = new Handler();
$object = new BaseObject();
```

## Backward Compatibility

**100% backward compatibility is maintained!** All legacy class names continue to work through class aliases.

```php
// This still works!
$security = new icms_core_Security();

// This also works!
$security = new \Icms\Core\Security();

// Both create the same object
```

## Complete Class Mapping

### Core Classes

| Legacy Name | Modern Name | Notes |
|------------|-------------|-------|
| `icms_core_Security` | `Icms\Core\Security` | Security and tokens |
| `icms_core_Debug` | `Icms\Core\Debug` | Debug utilities |
| `icms_core_DataFilter` | `Icms\Core\DataFilter` | Data filtering |
| `icms_core_Object` | `Icms\Core\BaseObject` | ⚠️ Renamed to BaseObject |
| `icms_core_ObjectHandler` | `Icms\Core\ObjectHandler` | Object handler |
| `icms_core_Session` | `Icms\Core\Session` | Session management |
| `icms_core_Filesystem` | `Icms\Core\Filesystem` | File operations |
| `icms_core_HTMLFilter` | `Icms\Core\HTMLFilter` | HTML filtering |
| `icms_core_Logger` | `Icms\Core\Logger` | Logging |
| `icms_core_Message` | `Icms\Core\Message` | Messages |
| `icms_core_OnlineHandler` | `Icms\Core\OnlineHandler` | Online users |
| `icms_core_StopSpammer` | `Icms\Core\StopSpammer` | Anti-spam |
| `icms_core_Textsanitizer` | `Icms\Core\Textsanitizer` | Text sanitization |
| `icms_core_Versionchecker` | `Icms\Core\Versionchecker` | Version checking |
| `icms_core_VersioncheckerInterface` | `Icms\Core\VersioncheckerInterface` | Interface |
| `icms_core_Versionchecker_RSS` | `Icms\Core\Versionchecker_RSS` | RSS version checker |
| `icms_core_Versioncheckergithub` | `Icms\Core\Versioncheckergithub` | GitHub checker |

### Database Classes

| Legacy Name | Modern Name |
|------------|-------------|
| `icms_db_Connection` | `Icms\Db\Connection` |
| `icms_db_Factory` | `Icms\Db\Factory` |
| `icms_db_IConnection` | `Icms\Db\IConnection` |
| `icms_db_IUtility` | `Icms\Db\IUtility` |
| `icms_db_criteria_Compo` | `Icms\Db\Criteria\Compo` |
| `icms_db_criteria_Element` | `Icms\Db\Criteria\Element` |
| `icms_db_criteria_Item` | `Icms\Db\Criteria\Item` |
| `icms_db_mysql_Connection` | `Icms\Db\Mysql\Connection` |
| `icms_db_mysql_Utility` | `Icms\Db\Mysql\Utility` |
| `icms_db_legacy_*` | `Icms\Db\Legacy\*` |

### Form Classes

| Legacy Name | Modern Name |
|------------|-------------|
| `icms_form_Base` | `Icms\Form\Base` |
| `icms_form_Element` | `Icms\Form\Element` |
| `icms_form_Groupperm` | `Icms\Form\Groupperm` |
| `icms_form_Simple` | `Icms\Form\Simple` |
| `icms_form_Table` | `Icms\Form\Table` |
| `icms_form_Theme` | `Icms\Form\Theme` |
| `icms_form_elements_*` | `Icms\Form\Elements\*` |

### IPF (ImpressCMS Persistable Framework) Classes

| Legacy Name | Modern Name | Notes |
|------------|-------------|-------|
| `icms_ipf_Object` | `Icms\Ipf\BaseObject` | ⚠️ Renamed to BaseObject |
| `icms_ipf_Handler` | `Icms\Ipf\Handler` | |
| `icms_ipf_Controller` | `Icms\Ipf\Controller` | |
| `icms_ipf_Tree` | `Icms\Ipf\Tree` | |
| `icms_ipf_About` | `Icms\Ipf\About` | |
| `icms_ipf_Metagen` | `Icms\Ipf\Metagen` | |
| `icms_ipf_form_*` | `Icms\Ipf\Form\*` | |
| `icms_ipf_view_*` | `Icms\Ipf\View\*` | |

### Member Classes

| Legacy Name | Modern Name | Notes |
|------------|-------------|-------|
| `icms_member_Handler` | `Icms\Member\Handler` | |
| `icms_member_user_Object` | `Icms\Member\User\BaseObject` | ⚠️ Renamed |
| `icms_member_user_Handler` | `Icms\Member\User\Handler` | |
| `icms_member_group_Object` | `Icms\Member\Group\BaseObject` | ⚠️ Renamed |
| `icms_member_group_Handler` | `Icms\Member\Group\Handler` | |
| `icms_member_groupperm_Object` | `Icms\Member\Groupperm\BaseObject` | ⚠️ Renamed |
| `icms_member_groupperm_Handler` | `Icms\Member\Groupperm\Handler` | |

### View Classes

| Legacy Name | Modern Name | Notes |
|------------|-------------|-------|
| `icms_view_PageBuilder` | `Icms\View\PageBuilder` | |
| `icms_view_Tpl` | `Icms\View\Tpl` | |
| `icms_view_Tree` | `Icms\View\Tree` | |
| `icms_view_Breadcrumb` | `Icms\View\Breadcrumb` | |
| `icms_view_PageNav` | `Icms\View\PageNav` | |
| `icms_view_Printerfriendly` | `Icms\View\Printerfriendly` | |
| `icms_view_block_*` | `Icms\View\Block\*` | |
| `icms_view_template_*` | `Icms\View\Template\*` | |
| `icms_view_theme_*` | `Icms\View\Theme\*` | |

### Other Classes

| Legacy Name | Modern Name |
|------------|-------------|
| `icms_Event` | `Icms\Event` |
| `icms_Utils` | `Icms\Utils` |
| `icms_auth_*` | `Icms\Auth\*` |
| `icms_config_*` | `Icms\Config\*` |
| `icms_data_*` | `Icms\Data\*` |
| `icms_feeds_*` | `Icms\Feeds\*` |
| `icms_file_*` | `Icms\File\*` |
| `icms_image_*` | `Icms\Image\*` |
| `icms_messaging_*` | `Icms\Messaging\*` |
| `icms_module_*` | `Icms\Module\*` |
| `icms_plugins_*` | `Icms\Plugins\*` |
| `icms_preload_*` | `Icms\Preload\*` |
| `icms_sys_*` | `Icms\Sys\*` |

## Special Notes

### "Object" → "BaseObject" Rename

Due to `Object` being a reserved word in PHP 7.2+, all classes named `Object` have been renamed to `BaseObject`:

```php
// OLD
class icms_core_Object { }

// NEW
namespace Icms\Core;
class BaseObject { }

// LEGACY ALIAS (still works!)
class_alias('Icms\Core\BaseObject', 'icms_core_Object');
```

**Affected Classes**: 
- `icms_core_Object` → `Icms\Core\BaseObject`
- `icms_ipf_Object` → `Icms\Ipf\BaseObject`
- `icms_module_Object` → `Icms\Module\BaseObject`
- And 27 more (see complete mapping above)

## Migration Strategies

### Strategy 1: Do Nothing (Recommended for External Code)

If your code is **outside** `htdocs/libraries/icms/`, you don't need to change anything! The legacy class names continue to work via aliases.

```php
// This continues to work indefinitely
$security = new icms_core_Security();
```

### Strategy 2: Gradual Migration (Recommended for New Code)

Use modern names in new code, keep legacy names in existing code:

```php
// New code - use modern names
use Icms\Core\Security;
$security = new Security();

// Existing code - keep legacy names
$object = new icms_core_Object(); // Still works!
```

### Strategy 3: Full Migration (Optional)

Migrate all code to use modern namespaced classes:

1. Add `use` statements at the top of your files
2. Update class instantiations
3. Update type hints and docblocks
4. Test thoroughly

```php
<?php
// Add use statements
use Icms\Core\Security;
use Icms\Core\BaseObject;
use Icms\Db\Handler;

// Use short class names
$security = new Security();
$object = new BaseObject();
$handler = new Handler();

// Update type hints
function processObject(BaseObject $obj): void {
    // ...
}
```

## Deprecation Timeline

- **Current (2.0.x)**: Both legacy and modern names supported
- **Future (3.0.x)**: Legacy names may trigger deprecation warnings
- **Future (4.0.x)**: Legacy names may be removed (TBD)

## Technical Details

### Autoloading

The refactoring uses Composer's autoloading:

```json
{
    "autoload": {
        "psr-4": {
            "Icms\\": "libraries/icms/"
        },
        "psr-0": {
            "icms_": "libraries/"
        },
        "files": [
            "libraries/icms_aliases.php"
        ]
    }
}
```

### Class Aliases

Backward compatibility is achieved through `class_alias()`:

```php
// In htdocs/libraries/icms_aliases.php
class_alias('Icms\Core\Security', 'icms_core_Security');
class_alias('Icms\Core\BaseObject', 'icms_core_Object');
// ... 112 more aliases
```

### File Structure

Files are organized to match PSR-4 naming:

```
htdocs/libraries/icms/
├── Core/
│   ├── Security.php        (namespace Icms\Core; class Security)
│   ├── BaseObject.php      (namespace Icms\Core; class BaseObject)
│   └── Debug.php           (namespace Icms\Core; class Debug)
├── Db/
│   ├── Handler.php         (namespace Icms\Db; class Handler)
│   └── Criteria/
│       ├── Item.php        (namespace Icms\Db\Criteria; class Item)
│       └── Compo.php       (namespace Icms\Db\Criteria; class Compo)
└── ...
```

## Examples

### Example 1: Basic Usage

```php
// Legacy way (still works)
$security = new icms_core_Security();
$token = $security->createToken();

// Modern way
use Icms\Core\Security;
$security = new Security();
$token = $security->createToken();
```

### Example 2: Class Extends

```php
// Legacy
class MyHandler extends icms_core_ObjectHandler {
    // ...
}

// Modern
use Icms\Core\ObjectHandler;
class MyHandler extends ObjectHandler {
    // ...
}
```

### Example 3: Type Hints

```php
// Legacy
function process(icms_core_Object $object) {
    // ...
}

// Modern
use Icms\Core\BaseObject;
function process(BaseObject $object): void {
    // ...
}
```

### Example 4: Static Methods

```php
// Legacy
icms_core_Debug::message("Debug info");

// Modern
use Icms\Core\Debug;
Debug::message("Debug info");

// Or with FQCN
\Icms\Core\Debug::message("Debug info");
```

## FAQ

**Q: Do I need to update my existing modules?**  
A: No! Legacy class names continue to work. Update only when convenient.

**Q: What about performance?**  
A: Class aliases have minimal performance impact. PSR-4 autoloading is actually more efficient than the old PSR-0 approach.

**Q: Can I mix legacy and modern names?**  
A: Yes! Both work seamlessly together since they reference the same classes.

**Q: Why was `Object` renamed to `BaseObject`?**  
A: `Object` became a reserved word in PHP 7.2+. Using it as a class name in a namespace causes a fatal error.

**Q: How do I know which class to use?**  
A: Use this simple conversion:
- Replace `icms_` with `Icms\`
- Replace remaining `_` with `\`
- Capitalize each word
- Exception: `Object` → `BaseObject`

**Q: Are there any breaking changes?**  
A: No! This is a fully backward-compatible refactoring.

## Testing

After migration, test your code thoroughly:

1. **Unit Tests**: Run existing test suites
2. **Integration Tests**: Test module functionality
3. **Manual Testing**: Verify all features work correctly

## Support

For questions or issues:
- **Forum**: https://www.impresscms.org/modules/newbb/
- **GitHub**: https://github.com/fiammybe/impresscms
- **Documentation**: https://wiki.impresscms.org/

## See Also

- [PSR-4: Autoloader](https://www.php-fig.org/psr/psr-4/)
- [PSR-12: Coding Style](https://www.php-fig.org/psr/psr-12/)
- [PHP Reserved Words](https://www.php.net/manual/en/reserved.php)

---

**Last Updated**: December 2024  
**Document Version**: 1.0
