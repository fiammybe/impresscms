# ImpressCMS Core Update System

## Overview

The `icms_core_Update` class provides functionality to automatically download, verify, and install ImpressCMS core updates from GitHub releases.

## Features

- **Automatic Download**: Downloads the latest release from GitHub
- **Hash Verification**: Optional SHA256 hash verification for security
- **Backup Creation**: Creates a backup before updating
- **Safe Installation**: Extracts and installs updates safely
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

## System Requirements

- **PHP Extensions**: ZipArchive (preferred) or system unzip command
- **PHP Functions**: file_get_contents, exec (optional)
- **PHP Settings**: allow_url_fopen enabled
- **File Permissions**: Write access to ICMS_ROOT_PATH and ICMS_CACHE_PATH
- **User Permissions**: Administrator access required

## Security Features

1. **Permission Checks**: Only administrators can perform updates
2. **Hash Verification**: Optional SHA256 hash verification
3. **Backup Creation**: Automatic backup before installation
4. **Safe Extraction**: Files extracted to temporary directory first
5. **Error Handling**: Comprehensive error checking throughout process

## File Structure

- `htdocs/libraries/icms/core/Update.php` - Main update class
- `htdocs/modules/system/admin/version/main.php` - Admin interface integration
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

## Integration with Version Checker

The update system integrates with the existing `icms_core_Versioncheckergithub` class to:

- Check for available updates
- Get download URLs
- Retrieve version information
- Access release notes

## Best Practices

1. **Always test updates** on a staging environment first
2. **Verify hash values** when provided by the ImpressCMS team
3. **Check system requirements** before attempting updates
4. **Ensure adequate disk space** for backups and temporary files
5. **Monitor update process** for any error messages

## Troubleshooting

### Common Issues

1. **ZipArchive not available**: Install php-zip extension or ensure system unzip command is available
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
