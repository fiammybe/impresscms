<?php
// module administration
define('_MD_AM_CAPABILITIES_REBUILD_FAIL', 'The capability registry could not be rebuilt: %s');
define('_MD_AM_CAPABILITY_REJECTED', 'Capability provider %s ignored: %s');
define('_MD_AM_CAPABILITY_UNINSTALL_WARNING', 'This module is the active provider of these capabilities. After the uninstall they are no longer available for these object types, until another provider is chosen:');

// capabilities page
define('_MD_AM_CAPABILITIES_PROVIDERS', 'Providers');
define('_MD_AM_CAPABILITIES_ASSIGNMENTS', 'Assignments');
define('_MD_AM_CAPABILITIES_ASSIGNMENTS_DSC', 'Choose which capabilities are enabled for each type of object. Capabilities that a module does not support for a type, and capabilities without a provider, cannot be enabled.');
define('_MD_AM_CAPABILITIES_REBUILD', 'Rebuild registry');
define('_MD_AM_CAPABILITIES_REBUILT', 'The capability registry was rebuilt; %d provider files were ignored.');
define('_MD_AM_CAPABILITIES_REJECTED', 'Ignored provider files');
define('_MD_AM_CAPABILITIES_SAVE', 'Save assignments');
define('_MD_AM_CAPABILITIES_SAVED', 'The assignments were saved.');
define('_MD_AM_CAPABILITY_STATE_ACTIVE', 'active');
define('_MD_AM_CAPABILITY_STATE_UNRESOLVED', 'no provider chosen');
define('_MD_AM_CAPABILITY_STATE_UNAVAILABLE', 'unavailable');
define('_MD_AM_CAPABILITY_CONTRACT', 'contract %s');
define('_MD_AM_CAPABILITY_PROVIDER', 'Provider');
define('_MD_AM_CAPABILITY_MODULE_VERSION', 'Module version');
define('_MD_AM_CAPABILITY_CONTRACT_VERSION', 'Contract');
define('_MD_AM_CAPABILITY_COMPATIBLE', 'compatible');
define('_MD_AM_CAPABILITY_INCOMPATIBLE', 'incompatible (%s)');
define('_MD_AM_CAPABILITY_MANAGE', 'Manage');
define('_MD_AM_CAPABILITY_SAVE_SELECTION', 'Save selection');
define('_MD_AM_CAPABILITY_NO_PROVIDER', 'No installed module provides this capability.');
define('_MD_AM_CAPABILITY_SELECTED_GONE', 'The chosen provider "%s" is not installed, not active or not compatible anymore. Choose a provider.');
define('_MD_AM_CAPABILITY_PROVIDER_INVALID', 'This module is not a compatible provider of the capability.');
define('_MD_AM_CAPABILITY_SELECTED', '%s is now the provider of %s.');
define('_MD_AM_CAPABILITY_SWITCH', 'Switch provider');
define('_MD_AM_CAPABILITY_SWITCH_CONFIRM', 'Switch %1$s from %2$s to %3$s? It is enabled for these object types:');
define('_MD_AM_CAPABILITY_SWITCH_DATA', 'The data stored by %s is not moved and will not be visible through the new provider. It is kept, so switching back shows it again.');
define('_MD_AM_CAPABILITY_TOKEN_INVALID', 'The security token is not valid; nothing was changed.');
define('_MD_AM_CAPABILITY_OBJECT_TYPE', 'Object type');
define('_MD_AM_CAPABILITY_NOT_SUPPORTED', 'Not supported by this module');
define('_MD_AM_CAPABILITY_UNSUPPORTED', '%2$s cannot be enabled for %1$s: the module does not support it. Nothing was saved.');
