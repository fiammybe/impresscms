# ImpressCMS Core Update System

## Overview

The `icms_core_Update` class provides functionality to automatically download, verify, and install ImpressCMS core updates from GitHub releases.

## Features

- **Automatic Download**: Downloads the latest release from GitHub
- **Hash Verification**: Optional SHA256 hash verification for security
- **Backup Creation**: Creates a backup before updating
- **File List Installation**: Installs the release file by file, never touching the site configuration or the installer
- **Permission Checks**: Ensures only administrators can perform updates
- **Error Handling**: Comprehensive error reporting and logging

## Usage

### Basic Usage

```php
// Create update instance
$updater = new icms_core_Update();

// Check if update is available
if ($updater->isUpdateAvailable()) {
    // Perform complete update process
    if ($updater->performUpdate()) {
        echo "Update completed successfully!";
    } else {
        echo "Update failed: " . $updater->getErrors(true);
    }
}
```

### Step-by-Step Usage

```php
$updater = new icms_core_Update();

// Step 1: Create backup
if ($updater->createBackup()) {
    echo "Backup created successfully\n";
}

// Step 2: Download update
if ($updater->downloadUpdate($optionalHash)) {
    echo "Download completed\n";
}

// Step 3: Install update
if ($updater->installUpdate()) {
    echo "Installation completed\n";
}

// Step 4: Cleanup
$updater->cleanup();
```

### Backup Class Usage

```php
// Create backup instance
$backup = new icms_core_Backup();

// Create a backup
$backupPath = $backup->createBackup('my-backup', true);

// List available backups
$backups = $backup->listBackups();

// Delete a backup
$backup->deleteBackup('backup-2023-12-01.tar.gz');

// Configure exclusions
$backup->addExcludeDir('logs');
$backup->addExcludeFile('*.cache');
$backup->setMaxFileSize(100 * 1024 * 1024); // 100MB

// Generate checksum file for integrity verification
$backup->generateChecksum();
```

## What the updater installs

Release packages (built by `bin/build-package`) hold the contents of `htdocs/` in the root of the zip. The updater finds the package root by looking for `include/version.php` (in the zip root, in `htdocs/`, or in a single wrapper directory) and checks that the core files are present.

The files are then installed one by one from a list:

- **Never written or deleted**: `mainfile.php` (the site configuration), `install/` (the installer is removed after installation and must not come back), `cache/`, `templates_c/` and `uploads/`.
- **Only installed when the site does not have them yet**: `robots.txt`, `favicon.ico` and `htaccess.txt`.
- **Deleted**: files listed in `DELETED-FILES.txt`, which packages built with `bin/build-package --from` contain, except protected paths and paths outside the web root.

Before anything is changed, every target file is checked for write access; if one is not writable, the update stops without changing the site. Files that fail to copy or delete are reported.

Bundled themes and modules are overwritten with the release version, so changes made directly in those files are lost.

## System Requirements

- **PHP Extensions**: zip (ZipArchive)
- **PHP Functions**: file_get_contents
- **PHP Settings**: allow_url_fopen enabled
- **File Permissions**: Write access to ICMS_ROOT_PATH and ICMS_CACHE_PATH
- **User Permissions**: Administrator access required

## Security Features

1. **Permission Checks**: Only administrators can perform updates
2. **Hash Verification**: Optional SHA256 hash verification
3. **Backup Creation**: Automatic backup before installation
4. **Safe Extraction**: Archive entries that would end up outside the extraction directory are rejected, files are extracted to a temporary directory first, and temporary files are removed also when an update fails
5. **Security Tokens**: The update form and the backup manager actions only run with a valid security token
5. **Error Handling**: Comprehensive error checking throughout process

## File Structure

- `htdocs/libraries/icms/core/Update.php` - Main update class
- `htdocs/libraries/icms/core/Backup.php` - Backup management class
- `htdocs/modules/system/admin/version/main.php` - Admin interface integration
- `htdocs/modules/system/admin/version/backup_manager.php` - Backup management interface
- `htdocs/modules/system/templates/system_adm_version.html` - Template with update UI
- `htdocs/modules/system/language/english/admin/version.php` - Language constants

## Configuration

The update system uses the following directories:

- **Temporary Directory**: `ICMS_CACHE_PATH/updates`
- **Backup Directory**: `ICMS_CACHE_PATH/backups`
- **Extract Directory**: `ICMS_CACHE_PATH/updates/extract`

## Error Handling

The class provides detailed error messages for common issues:

- Network connectivity problems
- File permission issues
- Archive extraction failures
- Hash verification failures
- System requirement failures

## Testing

Use the test script at `htdocs/modules/system/admin/version/update_test.php` to verify:

- Version checker functionality
- Update class instantiation
- System requirements
- File permissions
- Directory creation

## Integration with ImpressCMS Core Classes

### Version Checker Integration

The update system integrates with the existing `icms_core_Versioncheckergithub` class to:

- Check for available updates
- Get download URLs
- Retrieve version information
- Access release notes

### Filesystem Class Integration

Both Update and Backup classes leverage `icms_core_Filesystem` for file operations:

**Directory Operations:**
- `icms_core_Filesystem::mkdir()` - Create directories with proper permissions
- `icms_core_Filesystem::deleteRecursive()` - Recursively delete directories

**File Operations:**
- `icms_core_Filesystem::deleteFile()` - Safe file deletion
- `icms_core_Filesystem::copyRecursive()` - Recursive file/directory copying

**Iteration and Checksums:**
- Uses same `RecursiveDirectoryIterator` patterns as `generateChecksum()`
- Consistent exclusion logic for cache and templates_c directories
- Compatible with existing ImpressCMS file integrity checking

## Best Practices

1. **Always test updates** on a staging environment first
2. **Verify hash values** when provided by the ImpressCMS team
3. **Check system requirements** before attempting updates
4. **Ensure adequate disk space** for backups and temporary files
5. **Monitor update process** for any error messages

## Troubleshooting

### Common Issues

1. **ZipArchive not available**: Install the php-zip extension
2. **Permission denied**: Ensure web server has write access to required directories
3. **Download fails**: Check allow_url_fopen setting and network connectivity
4. **Hash verification fails**: Verify the provided hash is correct

### Debug Information

Enable error reporting and check the following:

- PHP error logs
- Web server error logs
- File permissions on ICMS_ROOT_PATH and ICMS_CACHE_PATH
- Available disk space
- Network connectivity to GitHub

## Security Considerations

- Only administrators can access update functionality
- Hash verification helps ensure file integrity
- Backups are created before any changes
- Temporary files are cleaned up after installation
- All file operations use secure path handling

## Future Enhancements

Potential improvements for future versions:

- Progress indicators for long operations
- Rollback functionality using created backups
- Update scheduling and automation
- Multi-step update process with user confirmation
- Integration with module and theme updates
