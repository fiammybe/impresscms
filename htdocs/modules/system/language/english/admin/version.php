<?php
// $Id: version.php 12227 2013-07-19 08:07:21Z fiammy $
//%%%%%%	Admin Module Name  Version 	%%%%%
if (!defined('_AM_DBUPDATED')) {define("_AM_DBUPDATED","Database Updated Successfully!");}
define("_AM_VERSION_TITLE", 'ImpressCMS Version Checker');
define("_AM_VERSION_NO_UPDATE", 'You are running the latest version of ImpressCMS !');
define("_AM_VERSION_UPDATE_NEEDED", 'There is a new version of ImpressCMS ! The ImpressCMS Project strongly recommends always using the latest release.');
define("_AM_VERSION_MOREINFO", 'Click on the following link to get more information on the latest release: ');

define("_AM_VERSION_CHECK_RSSDATA_EMPTY", 'No information was available to check for an updated release.');
define("_AM_VERSION_CHANGELOG", 'Changelog');
define("_AM_VERSION_WARNING", 'Warning');
define("_AM_VERSION_WARNING_NOT_A_FINAL", 'Please note that you are currently running a "Final" version of ImpressCMS and the proposed updated release is not a "Final" release. All releases other then "Final" should not be used on a production environment. If your install of ImpressCMS is running on a production environment, we recommend you wait for a final release. More info can be found: <a href="https://www.impresscms.org/modules/simplywiki/index.php?page=Final_release">here</a>.');

define("_AM_VERSION_YOUR_VERSION", 'Your ImpressCMS Version:');
define("_AM_VERSION_LATEST_VERSION", 'Latest ImpressCMS Version:');
// Added in ImpressCMS 1.2
define("_AM_VERSION_OP_SYSTEM", 'Server\'s Operating System:');
define("_AM_VERSION_MYSQL_SYSTEM", 'Your Database Version:');
define("_AM_VERSION_PHP_SYSTEM", 'Your PHP Version:');
define("_AM_VERSION_API_SYSTEM", 'Your API name:');
define("_AM_VERSION_SYSTEM_INFO", 'Click here to view your System information');

// Update functionality constants
define("_AM_VERSION_UPDATE_DOWNLOAD", 'Download Update');
define("_AM_VERSION_UPDATE_INSTALL", 'Install Update');
define("_AM_VERSION_UPDATE_BACKUP", 'Create Backup');
define("_AM_VERSION_UPDATE_VERIFY", 'Verify Download');
define("_AM_VERSION_UPDATE_PROGRESS", 'Update Progress');
define("_AM_VERSION_UPDATE_SUCCESS", 'Update completed successfully!');
define("_AM_VERSION_UPDATE_FAILED", 'Update failed. Please check the error messages below.');
define("_AM_VERSION_UPDATE_BACKUP_CREATED", 'Backup created successfully');
define("_AM_VERSION_UPDATE_DOWNLOAD_COMPLETE", 'Download completed');
define("_AM_VERSION_UPDATE_INSTALL_COMPLETE", 'Installation completed');
define("_AM_VERSION_UPDATE_HASH_VERIFY", 'Hash verification');
define("_AM_VERSION_UPDATE_CONFIRM", 'Are you sure you want to update ImpressCMS? This will overwrite your current installation.');
define("_AM_VERSION_UPDATE_PERMISSION_DENIED", 'You do not have permission to perform updates.');
define("_AM_VERSION_UPDATE_NO_UPDATE", 'No update available to install.');
define("_AM_VERSION_UPDATE_HASH_OPTIONAL", 'Hash verification (optional)');
define("_AM_VERSION_UPDATE_HASH_PLACEHOLDER", 'Enter SHA256 hash to verify download (optional)');
define("_AM_VERSION_UPDATE_START", 'Start Update Process');
define("_AM_VERSION_UPDATE_STEP_BACKUP", 'Step 1: Creating backup...');
define("_AM_VERSION_UPDATE_STEP_DOWNLOAD", 'Step 2: Downloading update...');
define("_AM_VERSION_UPDATE_STEP_VERIFY", 'Step 3: Verifying download...');
define("_AM_VERSION_UPDATE_STEP_INSTALL", 'Step 4: Installing update...');
define("_AM_VERSION_UPDATE_STEP_CLEANUP", 'Step 5: Cleaning up...');
?>