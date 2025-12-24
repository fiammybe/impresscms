<?php
/**
 * Class Aliases for Backward Compatibility
 *
 * This file creates aliases for all legacy underscore-based class names
 * to their new PSR-4 namespaced equivalents.
 *
 * Include this file after Composer autoloader to enable backward compatibility.
 *
 * @package ImpressCMS
 * @generated
 */

// icms_Event => Icms\Event
if (!class_exists('icms_Event', false) && !interface_exists('icms_Event', false) && !trait_exists('icms_Event', false)) {
    class_alias('Icms\Event', 'icms_Event');
    // Mark as deprecated
    if (class_exists('icms_core_Debug', false)) {
        // Deprecation will be triggered when legacy class is used
    }
}

// icms_Utils => Icms\Utils
if (!class_exists('icms_Utils', false) && !interface_exists('icms_Utils', false) && !trait_exists('icms_Utils', false)) {
    class_alias('Icms\Utils', 'icms_Utils');
    // Mark as deprecated
    if (class_exists('icms_core_Debug', false)) {
        // Deprecation will be triggered when legacy class is used
    }
}

// icms_config_Handler => Icms\Config\Handler
if (!class_exists('icms_config_Handler', false) && !interface_exists('icms_config_Handler', false) && !trait_exists('icms_config_Handler', false)) {
    class_alias('Icms\Config\Handler', 'icms_config_Handler');
    // Mark as deprecated
    if (class_exists('icms_core_Debug', false)) {
        // Deprecation will be triggered when legacy class is used
    }
}

// icms_config_Item_Handler => Icms\Config\Item\Handler
if (!class_exists('icms_config_Item_Handler', false) && !interface_exists('icms_config_Item_Handler', false) && !trait_exists('icms_config_Item_Handler', false)) {
    class_alias('Icms\Config\Item\Handler', 'icms_config_Item_Handler');
    // Mark as deprecated
    if (class_exists('icms_core_Debug', false)) {
        // Deprecation will be triggered when legacy class is used
    }
}

// icms_config_Item_Object => Icms\Config\Item\Object
if (!class_exists('icms_config_Item_Object', false) && !interface_exists('icms_config_Item_Object', false) && !trait_exists('icms_config_Item_Object', false)) {
    class_alias('Icms\Config\Item\Object', 'icms_config_Item_Object');
    // Mark as deprecated
    if (class_exists('icms_core_Debug', false)) {
        // Deprecation will be triggered when legacy class is used
    }
}

// icms_config_category_Handler => Icms\Config\Category\Handler
if (!class_exists('icms_config_category_Handler', false) && !interface_exists('icms_config_category_Handler', false) && !trait_exists('icms_config_category_Handler', false)) {
    class_alias('Icms\Config\Category\Handler', 'icms_config_category_Handler');
    // Mark as deprecated
    if (class_exists('icms_core_Debug', false)) {
        // Deprecation will be triggered when legacy class is used
    }
}

// icms_config_category_Object => Icms\Config\Category\Object
if (!class_exists('icms_config_category_Object', false) && !interface_exists('icms_config_category_Object', false) && !trait_exists('icms_config_category_Object', false)) {
    class_alias('Icms\Config\Category\Object', 'icms_config_category_Object');
    // Mark as deprecated
    if (class_exists('icms_core_Debug', false)) {
        // Deprecation will be triggered when legacy class is used
    }
}

// icms_config_option_Handler => Icms\Config\Option\Handler
if (!class_exists('icms_config_option_Handler', false) && !interface_exists('icms_config_option_Handler', false) && !trait_exists('icms_config_option_Handler', false)) {
    class_alias('Icms\Config\Option\Handler', 'icms_config_option_Handler');
    // Mark as deprecated
    if (class_exists('icms_core_Debug', false)) {
        // Deprecation will be triggered when legacy class is used
    }
}

// icms_config_option_Object => Icms\Config\Option\Object
if (!class_exists('icms_config_option_Object', false) && !interface_exists('icms_config_option_Object', false) && !trait_exists('icms_config_option_Object', false)) {
    class_alias('Icms\Config\Option\Object', 'icms_config_option_Object');
    // Mark as deprecated
    if (class_exists('icms_core_Debug', false)) {
        // Deprecation will be triggered when legacy class is used
    }
}

// icms_core_DataFilter => Icms\Core\DataFilter
if (!class_exists('icms_core_DataFilter', false) && !interface_exists('icms_core_DataFilter', false) && !trait_exists('icms_core_DataFilter', false)) {
    class_alias('Icms\Core\DataFilter', 'icms_core_DataFilter');
    // Mark as deprecated
    if (class_exists('icms_core_Debug', false)) {
        // Deprecation will be triggered when legacy class is used
    }
}

// icms_core_Debug => Icms\Core\Debug
if (!class_exists('icms_core_Debug', false) && !interface_exists('icms_core_Debug', false) && !trait_exists('icms_core_Debug', false)) {
    class_alias('Icms\Core\Debug', 'icms_core_Debug');
    // Mark as deprecated
    if (class_exists('icms_core_Debug', false)) {
        // Deprecation will be triggered when legacy class is used
    }
}

// icms_core_Filesystem => Icms\Core\Filesystem
if (!class_exists('icms_core_Filesystem', false) && !interface_exists('icms_core_Filesystem', false) && !trait_exists('icms_core_Filesystem', false)) {
    class_alias('Icms\Core\Filesystem', 'icms_core_Filesystem');
    // Mark as deprecated
    if (class_exists('icms_core_Debug', false)) {
        // Deprecation will be triggered when legacy class is used
    }
}

// icms_core_HTMLFilter => Icms\Core\HTMLFilter
if (!class_exists('icms_core_HTMLFilter', false) && !interface_exists('icms_core_HTMLFilter', false) && !trait_exists('icms_core_HTMLFilter', false)) {
    class_alias('Icms\Core\HTMLFilter', 'icms_core_HTMLFilter');
    // Mark as deprecated
    if (class_exists('icms_core_Debug', false)) {
        // Deprecation will be triggered when legacy class is used
    }
}

// icms_core_Logger => Icms\Core\Logger
if (!class_exists('icms_core_Logger', false) && !interface_exists('icms_core_Logger', false) && !trait_exists('icms_core_Logger', false)) {
    class_alias('Icms\Core\Logger', 'icms_core_Logger');
    // Mark as deprecated
    if (class_exists('icms_core_Debug', false)) {
        // Deprecation will be triggered when legacy class is used
    }
}

// icms_core_Message => Icms\Core\Message
if (!class_exists('icms_core_Message', false) && !interface_exists('icms_core_Message', false) && !trait_exists('icms_core_Message', false)) {
    class_alias('Icms\Core\Message', 'icms_core_Message');
    // Mark as deprecated
    if (class_exists('icms_core_Debug', false)) {
        // Deprecation will be triggered when legacy class is used
    }
}

// icms_core_Object => Icms\Core\Object
if (!class_exists('icms_core_Object', false) && !interface_exists('icms_core_Object', false) && !trait_exists('icms_core_Object', false)) {
    class_alias('Icms\Core\Object', 'icms_core_Object');
    // Mark as deprecated
    if (class_exists('icms_core_Debug', false)) {
        // Deprecation will be triggered when legacy class is used
    }
}

// icms_core_ObjectHandler => Icms\Core\ObjectHandler
if (!class_exists('icms_core_ObjectHandler', false) && !interface_exists('icms_core_ObjectHandler', false) && !trait_exists('icms_core_ObjectHandler', false)) {
    class_alias('Icms\Core\ObjectHandler', 'icms_core_ObjectHandler');
    // Mark as deprecated
    if (class_exists('icms_core_Debug', false)) {
        // Deprecation will be triggered when legacy class is used
    }
}

// icms_core_OnlineHandler => Icms\Core\OnlineHandler
if (!class_exists('icms_core_OnlineHandler', false) && !interface_exists('icms_core_OnlineHandler', false) && !trait_exists('icms_core_OnlineHandler', false)) {
    class_alias('Icms\Core\OnlineHandler', 'icms_core_OnlineHandler');
    // Mark as deprecated
    if (class_exists('icms_core_Debug', false)) {
        // Deprecation will be triggered when legacy class is used
    }
}

// icms_core_Security => Icms\Core\Security
if (!class_exists('icms_core_Security', false) && !interface_exists('icms_core_Security', false) && !trait_exists('icms_core_Security', false)) {
    class_alias('Icms\Core\Security', 'icms_core_Security');
    // Mark as deprecated
    if (class_exists('icms_core_Debug', false)) {
        // Deprecation will be triggered when legacy class is used
    }
}

// icms_core_Session => Icms\Core\Session
if (!class_exists('icms_core_Session', false) && !interface_exists('icms_core_Session', false) && !trait_exists('icms_core_Session', false)) {
    class_alias('Icms\Core\Session', 'icms_core_Session');
    // Mark as deprecated
    if (class_exists('icms_core_Debug', false)) {
        // Deprecation will be triggered when legacy class is used
    }
}

// icms_core_StopSpammer => Icms\Core\StopSpammer
if (!class_exists('icms_core_StopSpammer', false) && !interface_exists('icms_core_StopSpammer', false) && !trait_exists('icms_core_StopSpammer', false)) {
    class_alias('Icms\Core\StopSpammer', 'icms_core_StopSpammer');
    // Mark as deprecated
    if (class_exists('icms_core_Debug', false)) {
        // Deprecation will be triggered when legacy class is used
    }
}

// icms_core_Textsanitizer => Icms\Core\Textsanitizer
if (!class_exists('icms_core_Textsanitizer', false) && !interface_exists('icms_core_Textsanitizer', false) && !trait_exists('icms_core_Textsanitizer', false)) {
    class_alias('Icms\Core\Textsanitizer', 'icms_core_Textsanitizer');
    // Mark as deprecated
    if (class_exists('icms_core_Debug', false)) {
        // Deprecation will be triggered when legacy class is used
    }
}

// icms_core_Versionchecker => Icms\Core\Versionchecker
if (!class_exists('icms_core_Versionchecker', false) && !interface_exists('icms_core_Versionchecker', false) && !trait_exists('icms_core_Versionchecker', false)) {
    class_alias('Icms\Core\Versionchecker', 'icms_core_Versionchecker');
    // Mark as deprecated
    if (class_exists('icms_core_Debug', false)) {
        // Deprecation will be triggered when legacy class is used
    }
}

// icms_core_VersioncheckerInterface => Icms\Core\VersioncheckerInterface
if (!class_exists('icms_core_VersioncheckerInterface', false) && !interface_exists('icms_core_VersioncheckerInterface', false) && !trait_exists('icms_core_VersioncheckerInterface', false)) {
    class_alias('Icms\Core\VersioncheckerInterface', 'icms_core_VersioncheckerInterface');
    // Mark as deprecated
    if (class_exists('icms_core_Debug', false)) {
        // Deprecation will be triggered when legacy class is used
    }
}

// icms_core_Versionchecker_RSS => Icms\Core\Versionchecker\RSS
if (!class_exists('icms_core_Versionchecker_RSS', false) && !interface_exists('icms_core_Versionchecker_RSS', false) && !trait_exists('icms_core_Versionchecker_RSS', false)) {
    class_alias('Icms\Core\Versionchecker\RSS', 'icms_core_Versionchecker_RSS');
    // Mark as deprecated
    if (class_exists('icms_core_Debug', false)) {
        // Deprecation will be triggered when legacy class is used
    }
}

// icms_core_Versioncheckergithub => Icms\Core\Versioncheckergithub
if (!class_exists('icms_core_Versioncheckergithub', false) && !interface_exists('icms_core_Versioncheckergithub', false) && !trait_exists('icms_core_Versioncheckergithub', false)) {
    class_alias('Icms\Core\Versioncheckergithub', 'icms_core_Versioncheckergithub');
    // Mark as deprecated
    if (class_exists('icms_core_Debug', false)) {
        // Deprecation will be triggered when legacy class is used
    }
}

// icms_data_notification_Handler => Icms\Data\Notification\Handler
if (!class_exists('icms_data_notification_Handler', false) && !interface_exists('icms_data_notification_Handler', false) && !trait_exists('icms_data_notification_Handler', false)) {
    class_alias('Icms\Data\Notification\Handler', 'icms_data_notification_Handler');
    // Mark as deprecated
    if (class_exists('icms_core_Debug', false)) {
        // Deprecation will be triggered when legacy class is used
    }
}

// icms_image_Handler => Icms\Image\Handler
if (!class_exists('icms_image_Handler', false) && !interface_exists('icms_image_Handler', false) && !trait_exists('icms_image_Handler', false)) {
    class_alias('Icms\Image\Handler', 'icms_image_Handler');
    // Mark as deprecated
    if (class_exists('icms_core_Debug', false)) {
        // Deprecation will be triggered when legacy class is used
    }
}

// icms_image_Object => Icms\Image\Object
if (!class_exists('icms_image_Object', false) && !interface_exists('icms_image_Object', false) && !trait_exists('icms_image_Object', false)) {
    class_alias('Icms\Image\Object', 'icms_image_Object');
    // Mark as deprecated
    if (class_exists('icms_core_Debug', false)) {
        // Deprecation will be triggered when legacy class is used
    }
}

// icms_image_category_Handler => Icms\Image\Category\Handler
if (!class_exists('icms_image_category_Handler', false) && !interface_exists('icms_image_category_Handler', false) && !trait_exists('icms_image_category_Handler', false)) {
    class_alias('Icms\Image\Category\Handler', 'icms_image_category_Handler');
    // Mark as deprecated
    if (class_exists('icms_core_Debug', false)) {
        // Deprecation will be triggered when legacy class is used
    }
}

// icms_image_category_Object => Icms\Image\Category\Object
if (!class_exists('icms_image_category_Object', false) && !interface_exists('icms_image_category_Object', false) && !trait_exists('icms_image_category_Object', false)) {
    class_alias('Icms\Image\Category\Object', 'icms_image_category_Object');
    // Mark as deprecated
    if (class_exists('icms_core_Debug', false)) {
        // Deprecation will be triggered when legacy class is used
    }
}

// icms_image_set_Handler => Icms\Image\Set\Handler
if (!class_exists('icms_image_set_Handler', false) && !interface_exists('icms_image_set_Handler', false) && !trait_exists('icms_image_set_Handler', false)) {
    class_alias('Icms\Image\Set\Handler', 'icms_image_set_Handler');
    // Mark as deprecated
    if (class_exists('icms_core_Debug', false)) {
        // Deprecation will be triggered when legacy class is used
    }
}

// icms_image_set_Object => Icms\Image\Set\Object
if (!class_exists('icms_image_set_Object', false) && !interface_exists('icms_image_set_Object', false) && !trait_exists('icms_image_set_Object', false)) {
    class_alias('Icms\Image\Set\Object', 'icms_image_set_Object');
    // Mark as deprecated
    if (class_exists('icms_core_Debug', false)) {
        // Deprecation will be triggered when legacy class is used
    }
}

// icms_ipf_About => Icms\Ipf\About
if (!class_exists('icms_ipf_About', false) && !interface_exists('icms_ipf_About', false) && !trait_exists('icms_ipf_About', false)) {
    class_alias('Icms\Ipf\About', 'icms_ipf_About');
    // Mark as deprecated
    if (class_exists('icms_core_Debug', false)) {
        // Deprecation will be triggered when legacy class is used
    }
}

// icms_ipf_Controller => Icms\Ipf\Controller
if (!class_exists('icms_ipf_Controller', false) && !interface_exists('icms_ipf_Controller', false) && !trait_exists('icms_ipf_Controller', false)) {
    class_alias('Icms\Ipf\Controller', 'icms_ipf_Controller');
    // Mark as deprecated
    if (class_exists('icms_core_Debug', false)) {
        // Deprecation will be triggered when legacy class is used
    }
}

// icms_ipf_Handler => Icms\Ipf\Handler
if (!class_exists('icms_ipf_Handler', false) && !interface_exists('icms_ipf_Handler', false) && !trait_exists('icms_ipf_Handler', false)) {
    class_alias('Icms\Ipf\Handler', 'icms_ipf_Handler');
    // Mark as deprecated
    if (class_exists('icms_core_Debug', false)) {
        // Deprecation will be triggered when legacy class is used
    }
}

// icms_ipf_Highlighter => Icms\Ipf\Highlighter
if (!class_exists('icms_ipf_Highlighter', false) && !interface_exists('icms_ipf_Highlighter', false) && !trait_exists('icms_ipf_Highlighter', false)) {
    class_alias('Icms\Ipf\Highlighter', 'icms_ipf_Highlighter');
    // Mark as deprecated
    if (class_exists('icms_core_Debug', false)) {
        // Deprecation will be triggered when legacy class is used
    }
}

// icms_ipf_Metagen => Icms\Ipf\Metagen
if (!class_exists('icms_ipf_Metagen', false) && !interface_exists('icms_ipf_Metagen', false) && !trait_exists('icms_ipf_Metagen', false)) {
    class_alias('Icms\Ipf\Metagen', 'icms_ipf_Metagen');
    // Mark as deprecated
    if (class_exists('icms_core_Debug', false)) {
        // Deprecation will be triggered when legacy class is used
    }
}

// icms_ipf_Object => Icms\Ipf\Object
if (!class_exists('icms_ipf_Object', false) && !interface_exists('icms_ipf_Object', false) && !trait_exists('icms_ipf_Object', false)) {
    class_alias('Icms\Ipf\Object', 'icms_ipf_Object');
    // Mark as deprecated
    if (class_exists('icms_core_Debug', false)) {
        // Deprecation will be triggered when legacy class is used
    }
}

// icms_ipf_Tree => Icms\Ipf\Tree
if (!class_exists('icms_ipf_Tree', false) && !interface_exists('icms_ipf_Tree', false) && !trait_exists('icms_ipf_Tree', false)) {
    class_alias('Icms\Ipf\Tree', 'icms_ipf_Tree');
    // Mark as deprecated
    if (class_exists('icms_core_Debug', false)) {
        // Deprecation will be triggered when legacy class is used
    }
}

// icms_ipf_category_Handler => Icms\Ipf\Category\Handler
if (!class_exists('icms_ipf_category_Handler', false) && !interface_exists('icms_ipf_category_Handler', false) && !trait_exists('icms_ipf_category_Handler', false)) {
    class_alias('Icms\Ipf\Category\Handler', 'icms_ipf_category_Handler');
    // Mark as deprecated
    if (class_exists('icms_core_Debug', false)) {
        // Deprecation will be triggered when legacy class is used
    }
}

// icms_ipf_category_Object => Icms\Ipf\Category\Object
if (!class_exists('icms_ipf_category_Object', false) && !interface_exists('icms_ipf_category_Object', false) && !trait_exists('icms_ipf_category_Object', false)) {
    class_alias('Icms\Ipf\Category\Object', 'icms_ipf_category_Object');
    // Mark as deprecated
    if (class_exists('icms_core_Debug', false)) {
        // Deprecation will be triggered when legacy class is used
    }
}

// icms_ipf_export_Handler => Icms\Ipf\Export\Handler
if (!class_exists('icms_ipf_export_Handler', false) && !interface_exists('icms_ipf_export_Handler', false) && !trait_exists('icms_ipf_export_Handler', false)) {
    class_alias('Icms\Ipf\Export\Handler', 'icms_ipf_export_Handler');
    // Mark as deprecated
    if (class_exists('icms_core_Debug', false)) {
        // Deprecation will be triggered when legacy class is used
    }
}

// icms_ipf_export_Renderer => Icms\Ipf\Export\Renderer
if (!class_exists('icms_ipf_export_Renderer', false) && !interface_exists('icms_ipf_export_Renderer', false) && !trait_exists('icms_ipf_export_Renderer', false)) {
    class_alias('Icms\Ipf\Export\Renderer', 'icms_ipf_export_Renderer');
    // Mark as deprecated
    if (class_exists('icms_core_Debug', false)) {
        // Deprecation will be triggered when legacy class is used
    }
}

// icms_ipf_form_Base => Icms\Ipf\Form\Base
if (!class_exists('icms_ipf_form_Base', false) && !interface_exists('icms_ipf_form_Base', false) && !trait_exists('icms_ipf_form_Base', false)) {
    class_alias('Icms\Ipf\Form\Base', 'icms_ipf_form_Base');
    // Mark as deprecated
    if (class_exists('icms_core_Debug', false)) {
        // Deprecation will be triggered when legacy class is used
    }
}

// icms_ipf_form_Secure => Icms\Ipf\Form\Secure
if (!class_exists('icms_ipf_form_Secure', false) && !interface_exists('icms_ipf_form_Secure', false) && !trait_exists('icms_ipf_form_Secure', false)) {
    class_alias('Icms\Ipf\Form\Secure', 'icms_ipf_form_Secure');
    // Mark as deprecated
    if (class_exists('icms_core_Debug', false)) {
        // Deprecation will be triggered when legacy class is used
    }
}

// icms_ipf_form_elements_Autocomplete => Icms\Ipf\Form\Elements\Autocomplete
if (!class_exists('icms_ipf_form_elements_Autocomplete', false) && !interface_exists('icms_ipf_form_elements_Autocomplete', false) && !trait_exists('icms_ipf_form_elements_Autocomplete', false)) {
    class_alias('Icms\Ipf\Form\Elements\Autocomplete', 'icms_ipf_form_elements_Autocomplete');
    // Mark as deprecated
    if (class_exists('icms_core_Debug', false)) {
        // Deprecation will be triggered when legacy class is used
    }
}

// icms_ipf_form_elements_Blockoptions => Icms\Ipf\Form\Elements\Blockoptions
if (!class_exists('icms_ipf_form_elements_Blockoptions', false) && !interface_exists('icms_ipf_form_elements_Blockoptions', false) && !trait_exists('icms_ipf_form_elements_Blockoptions', false)) {
    class_alias('Icms\Ipf\Form\Elements\Blockoptions', 'icms_ipf_form_elements_Blockoptions');
    // Mark as deprecated
    if (class_exists('icms_core_Debug', false)) {
        // Deprecation will be triggered when legacy class is used
    }
}

// icms_ipf_form_elements_Checkbox => Icms\Ipf\Form\Elements\Checkbox
if (!class_exists('icms_ipf_form_elements_Checkbox', false) && !interface_exists('icms_ipf_form_elements_Checkbox', false) && !trait_exists('icms_ipf_form_elements_Checkbox', false)) {
    class_alias('Icms\Ipf\Form\Elements\Checkbox', 'icms_ipf_form_elements_Checkbox');
    // Mark as deprecated
    if (class_exists('icms_core_Debug', false)) {
        // Deprecation will be triggered when legacy class is used
    }
}

// icms_ipf_form_elements_Date => Icms\Ipf\Form\Elements\Date
if (!class_exists('icms_ipf_form_elements_Date', false) && !interface_exists('icms_ipf_form_elements_Date', false) && !trait_exists('icms_ipf_form_elements_Date', false)) {
    class_alias('Icms\Ipf\Form\Elements\Date', 'icms_ipf_form_elements_Date');
    // Mark as deprecated
    if (class_exists('icms_core_Debug', false)) {
        // Deprecation will be triggered when legacy class is used
    }
}

// icms_ipf_form_elements_Datetime => Icms\Ipf\Form\Elements\Datetime
if (!class_exists('icms_ipf_form_elements_Datetime', false) && !interface_exists('icms_ipf_form_elements_Datetime', false) && !trait_exists('icms_ipf_form_elements_Datetime', false)) {
    class_alias('Icms\Ipf\Form\Elements\Datetime', 'icms_ipf_form_elements_Datetime');
    // Mark as deprecated
    if (class_exists('icms_core_Debug', false)) {
        // Deprecation will be triggered when legacy class is used
    }
}

// icms_ipf_form_elements_File => Icms\Ipf\Form\Elements\File
if (!class_exists('icms_ipf_form_elements_File', false) && !interface_exists('icms_ipf_form_elements_File', false) && !trait_exists('icms_ipf_form_elements_File', false)) {
    class_alias('Icms\Ipf\Form\Elements\File', 'icms_ipf_form_elements_File');
    // Mark as deprecated
    if (class_exists('icms_core_Debug', false)) {
        // Deprecation will be triggered when legacy class is used
    }
}

// icms_ipf_form_elements_Fileupload => Icms\Ipf\Form\Elements\Fileupload
if (!class_exists('icms_ipf_form_elements_Fileupload', false) && !interface_exists('icms_ipf_form_elements_Fileupload', false) && !trait_exists('icms_ipf_form_elements_Fileupload', false)) {
    class_alias('Icms\Ipf\Form\Elements\Fileupload', 'icms_ipf_form_elements_Fileupload');
    // Mark as deprecated
    if (class_exists('icms_core_Debug', false)) {
        // Deprecation will be triggered when legacy class is used
    }
}

// icms_ipf_form_elements_Image => Icms\Ipf\Form\Elements\Image
if (!class_exists('icms_ipf_form_elements_Image', false) && !interface_exists('icms_ipf_form_elements_Image', false) && !trait_exists('icms_ipf_form_elements_Image', false)) {
    class_alias('Icms\Ipf\Form\Elements\Image', 'icms_ipf_form_elements_Image');
    // Mark as deprecated
    if (class_exists('icms_core_Debug', false)) {
        // Deprecation will be triggered when legacy class is used
    }
}

// icms_ipf_form_elements_Imageupload => Icms\Ipf\Form\Elements\Imageupload
if (!class_exists('icms_ipf_form_elements_Imageupload', false) && !interface_exists('icms_ipf_form_elements_Imageupload', false) && !trait_exists('icms_ipf_form_elements_Imageupload', false)) {
    class_alias('Icms\Ipf\Form\Elements\Imageupload', 'icms_ipf_form_elements_Imageupload');
    // Mark as deprecated
    if (class_exists('icms_core_Debug', false)) {
        // Deprecation will be triggered when legacy class is used
    }
}

// icms_ipf_form_elements_Language => Icms\Ipf\Form\Elements\Language
if (!class_exists('icms_ipf_form_elements_Language', false) && !interface_exists('icms_ipf_form_elements_Language', false) && !trait_exists('icms_ipf_form_elements_Language', false)) {
    class_alias('Icms\Ipf\Form\Elements\Language', 'icms_ipf_form_elements_Language');
    // Mark as deprecated
    if (class_exists('icms_core_Debug', false)) {
        // Deprecation will be triggered when legacy class is used
    }
}

// icms_ipf_form_elements_Page => Icms\Ipf\Form\Elements\Page
if (!class_exists('icms_ipf_form_elements_Page', false) && !interface_exists('icms_ipf_form_elements_Page', false) && !trait_exists('icms_ipf_form_elements_Page', false)) {
    class_alias('Icms\Ipf\Form\Elements\Page', 'icms_ipf_form_elements_Page');
    // Mark as deprecated
    if (class_exists('icms_core_Debug', false)) {
        // Deprecation will be triggered when legacy class is used
    }
}

// icms_ipf_form_elements_Parentcategory => Icms\Ipf\Form\Elements\Parentcategory
if (!class_exists('icms_ipf_form_elements_Parentcategory', false) && !interface_exists('icms_ipf_form_elements_Parentcategory', false) && !trait_exists('icms_ipf_form_elements_Parentcategory', false)) {
    class_alias('Icms\Ipf\Form\Elements\Parentcategory', 'icms_ipf_form_elements_Parentcategory');
    // Mark as deprecated
    if (class_exists('icms_core_Debug', false)) {
        // Deprecation will be triggered when legacy class is used
    }
}

// icms_ipf_form_elements_Passwordtray => Icms\Ipf\Form\Elements\Passwordtray
if (!class_exists('icms_ipf_form_elements_Passwordtray', false) && !interface_exists('icms_ipf_form_elements_Passwordtray', false) && !trait_exists('icms_ipf_form_elements_Passwordtray', false)) {
    class_alias('Icms\Ipf\Form\Elements\Passwordtray', 'icms_ipf_form_elements_Passwordtray');
    // Mark as deprecated
    if (class_exists('icms_core_Debug', false)) {
        // Deprecation will be triggered when legacy class is used
    }
}

// icms_ipf_form_elements_Radio => Icms\Ipf\Form\Elements\Radio
if (!class_exists('icms_ipf_form_elements_Radio', false) && !interface_exists('icms_ipf_form_elements_Radio', false) && !trait_exists('icms_ipf_form_elements_Radio', false)) {
    class_alias('Icms\Ipf\Form\Elements\Radio', 'icms_ipf_form_elements_Radio');
    // Mark as deprecated
    if (class_exists('icms_core_Debug', false)) {
        // Deprecation will be triggered when legacy class is used
    }
}

// icms_ipf_form_elements_Richfile => Icms\Ipf\Form\Elements\Richfile
if (!class_exists('icms_ipf_form_elements_Richfile', false) && !interface_exists('icms_ipf_form_elements_Richfile', false) && !trait_exists('icms_ipf_form_elements_Richfile', false)) {
    class_alias('Icms\Ipf\Form\Elements\Richfile', 'icms_ipf_form_elements_Richfile');
    // Mark as deprecated
    if (class_exists('icms_core_Debug', false)) {
        // Deprecation will be triggered when legacy class is used
    }
}

// icms_ipf_form_elements_Section => Icms\Ipf\Form\Elements\Section
if (!class_exists('icms_ipf_form_elements_Section', false) && !interface_exists('icms_ipf_form_elements_Section', false) && !trait_exists('icms_ipf_form_elements_Section', false)) {
    class_alias('Icms\Ipf\Form\Elements\Section', 'icms_ipf_form_elements_Section');
    // Mark as deprecated
    if (class_exists('icms_core_Debug', false)) {
        // Deprecation will be triggered when legacy class is used
    }
}

// icms_ipf_form_elements_Select => Icms\Ipf\Form\Elements\Select
if (!class_exists('icms_ipf_form_elements_Select', false) && !interface_exists('icms_ipf_form_elements_Select', false) && !trait_exists('icms_ipf_form_elements_Select', false)) {
    class_alias('Icms\Ipf\Form\Elements\Select', 'icms_ipf_form_elements_Select');
    // Mark as deprecated
    if (class_exists('icms_core_Debug', false)) {
        // Deprecation will be triggered when legacy class is used
    }
}

// icms_ipf_form_elements_Selectmulti => Icms\Ipf\Form\Elements\Selectmulti
if (!class_exists('icms_ipf_form_elements_Selectmulti', false) && !interface_exists('icms_ipf_form_elements_Selectmulti', false) && !trait_exists('icms_ipf_form_elements_Selectmulti', false)) {
    class_alias('Icms\Ipf\Form\Elements\Selectmulti', 'icms_ipf_form_elements_Selectmulti');
    // Mark as deprecated
    if (class_exists('icms_core_Debug', false)) {
        // Deprecation will be triggered when legacy class is used
    }
}

// icms_ipf_form_elements_Signature => Icms\Ipf\Form\Elements\Signature
if (!class_exists('icms_ipf_form_elements_Signature', false) && !interface_exists('icms_ipf_form_elements_Signature', false) && !trait_exists('icms_ipf_form_elements_Signature', false)) {
    class_alias('Icms\Ipf\Form\Elements\Signature', 'icms_ipf_form_elements_Signature');
    // Mark as deprecated
    if (class_exists('icms_core_Debug', false)) {
        // Deprecation will be triggered when legacy class is used
    }
}

// icms_ipf_form_elements_Source => Icms\Ipf\Form\Elements\Source
if (!class_exists('icms_ipf_form_elements_Source', false) && !interface_exists('icms_ipf_form_elements_Source', false) && !trait_exists('icms_ipf_form_elements_Source', false)) {
    class_alias('Icms\Ipf\Form\Elements\Source', 'icms_ipf_form_elements_Source');
    // Mark as deprecated
    if (class_exists('icms_core_Debug', false)) {
        // Deprecation will be triggered when legacy class is used
    }
}

// icms_ipf_form_elements_Text => Icms\Ipf\Form\Elements\Text
if (!class_exists('icms_ipf_form_elements_Text', false) && !interface_exists('icms_ipf_form_elements_Text', false) && !trait_exists('icms_ipf_form_elements_Text', false)) {
    class_alias('Icms\Ipf\Form\Elements\Text', 'icms_ipf_form_elements_Text');
    // Mark as deprecated
    if (class_exists('icms_core_Debug', false)) {
        // Deprecation will be triggered when legacy class is used
    }
}

// icms_ipf_form_elements_Time => Icms\Ipf\Form\Elements\Time
if (!class_exists('icms_ipf_form_elements_Time', false) && !interface_exists('icms_ipf_form_elements_Time', false) && !trait_exists('icms_ipf_form_elements_Time', false)) {
    class_alias('Icms\Ipf\Form\Elements\Time', 'icms_ipf_form_elements_Time');
    // Mark as deprecated
    if (class_exists('icms_core_Debug', false)) {
        // Deprecation will be triggered when legacy class is used
    }
}

// icms_ipf_form_elements_Upload => Icms\Ipf\Form\Elements\Upload
if (!class_exists('icms_ipf_form_elements_Upload', false) && !interface_exists('icms_ipf_form_elements_Upload', false) && !trait_exists('icms_ipf_form_elements_Upload', false)) {
    class_alias('Icms\Ipf\Form\Elements\Upload', 'icms_ipf_form_elements_Upload');
    // Mark as deprecated
    if (class_exists('icms_core_Debug', false)) {
        // Deprecation will be triggered when legacy class is used
    }
}

// icms_ipf_form_elements_Urllink => Icms\Ipf\Form\Elements\Urllink
if (!class_exists('icms_ipf_form_elements_Urllink', false) && !interface_exists('icms_ipf_form_elements_Urllink', false) && !trait_exists('icms_ipf_form_elements_Urllink', false)) {
    class_alias('Icms\Ipf\Form\Elements\Urllink', 'icms_ipf_form_elements_Urllink');
    // Mark as deprecated
    if (class_exists('icms_core_Debug', false)) {
        // Deprecation will be triggered when legacy class is used
    }
}

// icms_ipf_form_elements_User => Icms\Ipf\Form\Elements\User
if (!class_exists('icms_ipf_form_elements_User', false) && !interface_exists('icms_ipf_form_elements_User', false) && !trait_exists('icms_ipf_form_elements_User', false)) {
    class_alias('Icms\Ipf\Form\Elements\User', 'icms_ipf_form_elements_User');
    // Mark as deprecated
    if (class_exists('icms_core_Debug', false)) {
        // Deprecation will be triggered when legacy class is used
    }
}

// icms_ipf_form_elements_Yesno => Icms\Ipf\Form\Elements\Yesno
if (!class_exists('icms_ipf_form_elements_Yesno', false) && !interface_exists('icms_ipf_form_elements_Yesno', false) && !trait_exists('icms_ipf_form_elements_Yesno', false)) {
    class_alias('Icms\Ipf\Form\Elements\Yesno', 'icms_ipf_form_elements_Yesno');
    // Mark as deprecated
    if (class_exists('icms_core_Debug', false)) {
        // Deprecation will be triggered when legacy class is used
    }
}

// icms_ipf_member_Handler => Icms\Ipf\Member\Handler
if (!class_exists('icms_ipf_member_Handler', false) && !interface_exists('icms_ipf_member_Handler', false) && !trait_exists('icms_ipf_member_Handler', false)) {
    class_alias('Icms\Ipf\Member\Handler', 'icms_ipf_member_Handler');
    // Mark as deprecated
    if (class_exists('icms_core_Debug', false)) {
        // Deprecation will be triggered when legacy class is used
    }
}

// icms_ipf_permission_Handler => Icms\Ipf\Permission\Handler
if (!class_exists('icms_ipf_permission_Handler', false) && !interface_exists('icms_ipf_permission_Handler', false) && !trait_exists('icms_ipf_permission_Handler', false)) {
    class_alias('Icms\Ipf\Permission\Handler', 'icms_ipf_permission_Handler');
    // Mark as deprecated
    if (class_exists('icms_core_Debug', false)) {
        // Deprecation will be triggered when legacy class is used
    }
}

// icms_ipf_registry_Handler => Icms\Ipf\Registry\Handler
if (!class_exists('icms_ipf_registry_Handler', false) && !interface_exists('icms_ipf_registry_Handler', false) && !trait_exists('icms_ipf_registry_Handler', false)) {
    class_alias('Icms\Ipf\Registry\Handler', 'icms_ipf_registry_Handler');
    // Mark as deprecated
    if (class_exists('icms_core_Debug', false)) {
        // Deprecation will be triggered when legacy class is used
    }
}

// icms_ipf_seo_Object => Icms\Ipf\Seo\Object
if (!class_exists('icms_ipf_seo_Object', false) && !interface_exists('icms_ipf_seo_Object', false) && !trait_exists('icms_ipf_seo_Object', false)) {
    class_alias('Icms\Ipf\Seo\Object', 'icms_ipf_seo_Object');
    // Mark as deprecated
    if (class_exists('icms_core_Debug', false)) {
        // Deprecation will be triggered when legacy class is used
    }
}

// icms_ipf_view_Column => Icms\Ipf\View\Column
if (!class_exists('icms_ipf_view_Column', false) && !interface_exists('icms_ipf_view_Column', false) && !trait_exists('icms_ipf_view_Column', false)) {
    class_alias('Icms\Ipf\View\Column', 'icms_ipf_view_Column');
    // Mark as deprecated
    if (class_exists('icms_core_Debug', false)) {
        // Deprecation will be triggered when legacy class is used
    }
}

// icms_ipf_view_Row => Icms\Ipf\View\Row
if (!class_exists('icms_ipf_view_Row', false) && !interface_exists('icms_ipf_view_Row', false) && !trait_exists('icms_ipf_view_Row', false)) {
    class_alias('Icms\Ipf\View\Row', 'icms_ipf_view_Row');
    // Mark as deprecated
    if (class_exists('icms_core_Debug', false)) {
        // Deprecation will be triggered when legacy class is used
    }
}

// icms_ipf_view_Single => Icms\Ipf\View\Single
if (!class_exists('icms_ipf_view_Single', false) && !interface_exists('icms_ipf_view_Single', false) && !trait_exists('icms_ipf_view_Single', false)) {
    class_alias('Icms\Ipf\View\Single', 'icms_ipf_view_Single');
    // Mark as deprecated
    if (class_exists('icms_core_Debug', false)) {
        // Deprecation will be triggered when legacy class is used
    }
}

// icms_ipf_view_Table => Icms\Ipf\View\Table
if (!class_exists('icms_ipf_view_Table', false) && !interface_exists('icms_ipf_view_Table', false) && !trait_exists('icms_ipf_view_Table', false)) {
    class_alias('Icms\Ipf\View\Table', 'icms_ipf_view_Table');
    // Mark as deprecated
    if (class_exists('icms_core_Debug', false)) {
        // Deprecation will be triggered when legacy class is used
    }
}

// icms_ipf_view_Tree => Icms\Ipf\View\Tree
if (!class_exists('icms_ipf_view_Tree', false) && !interface_exists('icms_ipf_view_Tree', false) && !trait_exists('icms_ipf_view_Tree', false)) {
    class_alias('Icms\Ipf\View\Tree', 'icms_ipf_view_Tree');
    // Mark as deprecated
    if (class_exists('icms_core_Debug', false)) {
        // Deprecation will be triggered when legacy class is used
    }
}

// icms_member_Handler => Icms\Member\Handler
if (!class_exists('icms_member_Handler', false) && !interface_exists('icms_member_Handler', false) && !trait_exists('icms_member_Handler', false)) {
    class_alias('Icms\Member\Handler', 'icms_member_Handler');
    // Mark as deprecated
    if (class_exists('icms_core_Debug', false)) {
        // Deprecation will be triggered when legacy class is used
    }
}

// icms_member_group_Handler => Icms\Member\Group\Handler
if (!class_exists('icms_member_group_Handler', false) && !interface_exists('icms_member_group_Handler', false) && !trait_exists('icms_member_group_Handler', false)) {
    class_alias('Icms\Member\Group\Handler', 'icms_member_group_Handler');
    // Mark as deprecated
    if (class_exists('icms_core_Debug', false)) {
        // Deprecation will be triggered when legacy class is used
    }
}

// icms_member_group_Object => Icms\Member\Group\Object
if (!class_exists('icms_member_group_Object', false) && !interface_exists('icms_member_group_Object', false) && !trait_exists('icms_member_group_Object', false)) {
    class_alias('Icms\Member\Group\Object', 'icms_member_group_Object');
    // Mark as deprecated
    if (class_exists('icms_core_Debug', false)) {
        // Deprecation will be triggered when legacy class is used
    }
}

// icms_member_group_membership_Handler => Icms\Member\Group\Membership\Handler
if (!class_exists('icms_member_group_membership_Handler', false) && !interface_exists('icms_member_group_membership_Handler', false) && !trait_exists('icms_member_group_membership_Handler', false)) {
    class_alias('Icms\Member\Group\Membership\Handler', 'icms_member_group_membership_Handler');
    // Mark as deprecated
    if (class_exists('icms_core_Debug', false)) {
        // Deprecation will be triggered when legacy class is used
    }
}

// icms_member_group_membership_Object => Icms\Member\Group\Membership\Object
if (!class_exists('icms_member_group_membership_Object', false) && !interface_exists('icms_member_group_membership_Object', false) && !trait_exists('icms_member_group_membership_Object', false)) {
    class_alias('Icms\Member\Group\Membership\Object', 'icms_member_group_membership_Object');
    // Mark as deprecated
    if (class_exists('icms_core_Debug', false)) {
        // Deprecation will be triggered when legacy class is used
    }
}

// icms_member_groupperm_Handler => Icms\Member\Groupperm\Handler
if (!class_exists('icms_member_groupperm_Handler', false) && !interface_exists('icms_member_groupperm_Handler', false) && !trait_exists('icms_member_groupperm_Handler', false)) {
    class_alias('Icms\Member\Groupperm\Handler', 'icms_member_groupperm_Handler');
    // Mark as deprecated
    if (class_exists('icms_core_Debug', false)) {
        // Deprecation will be triggered when legacy class is used
    }
}

// icms_member_groupperm_Object => Icms\Member\Groupperm\Object
if (!class_exists('icms_member_groupperm_Object', false) && !interface_exists('icms_member_groupperm_Object', false) && !trait_exists('icms_member_groupperm_Object', false)) {
    class_alias('Icms\Member\Groupperm\Object', 'icms_member_groupperm_Object');
    // Mark as deprecated
    if (class_exists('icms_core_Debug', false)) {
        // Deprecation will be triggered when legacy class is used
    }
}

// icms_member_user_Handler => Icms\Member\User\Handler
if (!class_exists('icms_member_user_Handler', false) && !interface_exists('icms_member_user_Handler', false) && !trait_exists('icms_member_user_Handler', false)) {
    class_alias('Icms\Member\User\Handler', 'icms_member_user_Handler');
    // Mark as deprecated
    if (class_exists('icms_core_Debug', false)) {
        // Deprecation will be triggered when legacy class is used
    }
}

// icms_member_user_Object => Icms\Member\User\Object
if (!class_exists('icms_member_user_Object', false) && !interface_exists('icms_member_user_Object', false) && !trait_exists('icms_member_user_Object', false)) {
    class_alias('Icms\Member\User\Object', 'icms_member_user_Object');
    // Mark as deprecated
    if (class_exists('icms_core_Debug', false)) {
        // Deprecation will be triggered when legacy class is used
    }
}

// icms_plugins_EditorHandler => Icms\Plugins\EditorHandler
if (!class_exists('icms_plugins_EditorHandler', false) && !interface_exists('icms_plugins_EditorHandler', false) && !trait_exists('icms_plugins_EditorHandler', false)) {
    class_alias('Icms\Plugins\EditorHandler', 'icms_plugins_EditorHandler');
    // Mark as deprecated
    if (class_exists('icms_core_Debug', false)) {
        // Deprecation will be triggered when legacy class is used
    }
}

// icms_plugins_Handler => Icms\Plugins\Handler
if (!class_exists('icms_plugins_Handler', false) && !interface_exists('icms_plugins_Handler', false) && !trait_exists('icms_plugins_Handler', false)) {
    class_alias('Icms\Plugins\Handler', 'icms_plugins_Handler');
    // Mark as deprecated
    if (class_exists('icms_core_Debug', false)) {
        // Deprecation will be triggered when legacy class is used
    }
}

// icms_plugins_Object => Icms\Plugins\Object
if (!class_exists('icms_plugins_Object', false) && !interface_exists('icms_plugins_Object', false) && !trait_exists('icms_plugins_Object', false)) {
    class_alias('Icms\Plugins\Object', 'icms_plugins_Object');
    // Mark as deprecated
    if (class_exists('icms_core_Debug', false)) {
        // Deprecation will be triggered when legacy class is used
    }
}

// icms_preload_Handler => Icms\Preload\Handler
if (!class_exists('icms_preload_Handler', false) && !interface_exists('icms_preload_Handler', false) && !trait_exists('icms_preload_Handler', false)) {
    class_alias('Icms\Preload\Handler', 'icms_preload_Handler');
    // Mark as deprecated
    if (class_exists('icms_core_Debug', false)) {
        // Deprecation will be triggered when legacy class is used
    }
}

// icms_preload_Item => Icms\Preload\Item
if (!class_exists('icms_preload_Item', false) && !interface_exists('icms_preload_Item', false) && !trait_exists('icms_preload_Item', false)) {
    class_alias('Icms\Preload\Item', 'icms_preload_Item');
    // Mark as deprecated
    if (class_exists('icms_core_Debug', false)) {
        // Deprecation will be triggered when legacy class is used
    }
}

// icms_preload_LibrariesHandler => Icms\Preload\LibrariesHandler
if (!class_exists('icms_preload_LibrariesHandler', false) && !interface_exists('icms_preload_LibrariesHandler', false) && !trait_exists('icms_preload_LibrariesHandler', false)) {
    class_alias('Icms\Preload\LibrariesHandler', 'icms_preload_LibrariesHandler');
    // Mark as deprecated
    if (class_exists('icms_core_Debug', false)) {
        // Deprecation will be triggered when legacy class is used
    }
}

// icms_view_Breadcrumb => Icms\View\Breadcrumb
if (!class_exists('icms_view_Breadcrumb', false) && !interface_exists('icms_view_Breadcrumb', false) && !trait_exists('icms_view_Breadcrumb', false)) {
    class_alias('Icms\View\Breadcrumb', 'icms_view_Breadcrumb');
    // Mark as deprecated
    if (class_exists('icms_core_Debug', false)) {
        // Deprecation will be triggered when legacy class is used
    }
}

// icms_view_PageBuilder => Icms\View\PageBuilder
if (!class_exists('icms_view_PageBuilder', false) && !interface_exists('icms_view_PageBuilder', false) && !trait_exists('icms_view_PageBuilder', false)) {
    class_alias('Icms\View\PageBuilder', 'icms_view_PageBuilder');
    // Mark as deprecated
    if (class_exists('icms_core_Debug', false)) {
        // Deprecation will be triggered when legacy class is used
    }
}

// icms_view_PageNav => Icms\View\PageNav
if (!class_exists('icms_view_PageNav', false) && !interface_exists('icms_view_PageNav', false) && !trait_exists('icms_view_PageNav', false)) {
    class_alias('Icms\View\PageNav', 'icms_view_PageNav');
    // Mark as deprecated
    if (class_exists('icms_core_Debug', false)) {
        // Deprecation will be triggered when legacy class is used
    }
}

// icms_view_Printerfriendly => Icms\View\Printerfriendly
if (!class_exists('icms_view_Printerfriendly', false) && !interface_exists('icms_view_Printerfriendly', false) && !trait_exists('icms_view_Printerfriendly', false)) {
    class_alias('Icms\View\Printerfriendly', 'icms_view_Printerfriendly');
    // Mark as deprecated
    if (class_exists('icms_core_Debug', false)) {
        // Deprecation will be triggered when legacy class is used
    }
}

// icms_view_Tpl => Icms\View\Tpl
if (!class_exists('icms_view_Tpl', false) && !interface_exists('icms_view_Tpl', false) && !trait_exists('icms_view_Tpl', false)) {
    class_alias('Icms\View\Tpl', 'icms_view_Tpl');
    // Mark as deprecated
    if (class_exists('icms_core_Debug', false)) {
        // Deprecation will be triggered when legacy class is used
    }
}

// icms_view_Tree => Icms\View\Tree
if (!class_exists('icms_view_Tree', false) && !interface_exists('icms_view_Tree', false) && !trait_exists('icms_view_Tree', false)) {
    class_alias('Icms\View\Tree', 'icms_view_Tree');
    // Mark as deprecated
    if (class_exists('icms_core_Debug', false)) {
        // Deprecation will be triggered when legacy class is used
    }
}

// icms_view_block_Handler => Icms\View\Block\Handler
if (!class_exists('icms_view_block_Handler', false) && !interface_exists('icms_view_block_Handler', false) && !trait_exists('icms_view_block_Handler', false)) {
    class_alias('Icms\View\Block\Handler', 'icms_view_block_Handler');
    // Mark as deprecated
    if (class_exists('icms_core_Debug', false)) {
        // Deprecation will be triggered when legacy class is used
    }
}

// icms_view_block_Object => Icms\View\Block\Object
if (!class_exists('icms_view_block_Object', false) && !interface_exists('icms_view_block_Object', false) && !trait_exists('icms_view_block_Object', false)) {
    class_alias('Icms\View\Block\Object', 'icms_view_block_Object');
    // Mark as deprecated
    if (class_exists('icms_core_Debug', false)) {
        // Deprecation will be triggered when legacy class is used
    }
}

// icms_view_block_position_Handler => Icms\View\Block\Position\Handler
if (!class_exists('icms_view_block_position_Handler', false) && !interface_exists('icms_view_block_position_Handler', false) && !trait_exists('icms_view_block_position_Handler', false)) {
    class_alias('Icms\View\Block\Position\Handler', 'icms_view_block_position_Handler');
    // Mark as deprecated
    if (class_exists('icms_core_Debug', false)) {
        // Deprecation will be triggered when legacy class is used
    }
}

// icms_view_block_position_Object => Icms\View\Block\Position\Object
if (!class_exists('icms_view_block_position_Object', false) && !interface_exists('icms_view_block_position_Object', false) && !trait_exists('icms_view_block_position_Object', false)) {
    class_alias('Icms\View\Block\Position\Object', 'icms_view_block_position_Object');
    // Mark as deprecated
    if (class_exists('icms_core_Debug', false)) {
        // Deprecation will be triggered when legacy class is used
    }
}

// icms_view_template_file_Handler => Icms\View\Template\File\Handler
if (!class_exists('icms_view_template_file_Handler', false) && !interface_exists('icms_view_template_file_Handler', false) && !trait_exists('icms_view_template_file_Handler', false)) {
    class_alias('Icms\View\Template\File\Handler', 'icms_view_template_file_Handler');
    // Mark as deprecated
    if (class_exists('icms_core_Debug', false)) {
        // Deprecation will be triggered when legacy class is used
    }
}

// icms_view_template_file_Object => Icms\View\Template\File\Object
if (!class_exists('icms_view_template_file_Object', false) && !interface_exists('icms_view_template_file_Object', false) && !trait_exists('icms_view_template_file_Object', false)) {
    class_alias('Icms\View\Template\File\Object', 'icms_view_template_file_Object');
    // Mark as deprecated
    if (class_exists('icms_core_Debug', false)) {
        // Deprecation will be triggered when legacy class is used
    }
}

// icms_view_template_set_Handler => Icms\View\Template\Set\Handler
if (!class_exists('icms_view_template_set_Handler', false) && !interface_exists('icms_view_template_set_Handler', false) && !trait_exists('icms_view_template_set_Handler', false)) {
    class_alias('Icms\View\Template\Set\Handler', 'icms_view_template_set_Handler');
    // Mark as deprecated
    if (class_exists('icms_core_Debug', false)) {
        // Deprecation will be triggered when legacy class is used
    }
}

// icms_view_template_set_Object => Icms\View\Template\Set\Object
if (!class_exists('icms_view_template_set_Object', false) && !interface_exists('icms_view_template_set_Object', false) && !trait_exists('icms_view_template_set_Object', false)) {
    class_alias('Icms\View\Template\Set\Object', 'icms_view_template_set_Object');
    // Mark as deprecated
    if (class_exists('icms_core_Debug', false)) {
        // Deprecation will be triggered when legacy class is used
    }
}

// icms_view_theme_Factory => Icms\View\Theme\Factory
if (!class_exists('icms_view_theme_Factory', false) && !interface_exists('icms_view_theme_Factory', false) && !trait_exists('icms_view_theme_Factory', false)) {
    class_alias('Icms\View\Theme\Factory', 'icms_view_theme_Factory');
    // Mark as deprecated
    if (class_exists('icms_core_Debug', false)) {
        // Deprecation will be triggered when legacy class is used
    }
}

// icms_view_theme_Object => Icms\View\Theme\Object
if (!class_exists('icms_view_theme_Object', false) && !interface_exists('icms_view_theme_Object', false) && !trait_exists('icms_view_theme_Object', false)) {
    class_alias('Icms\View\Theme\Object', 'icms_view_theme_Object');
    // Mark as deprecated
    if (class_exists('icms_core_Debug', false)) {
        // Deprecation will be triggered when legacy class is used
    }
}

