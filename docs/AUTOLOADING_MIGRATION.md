# ImpressCMS Autoloading Migration Guide

## Overview

ImpressCMS has been successfully migrated from its custom autoloader to Composer's PSR-4 autoloading standard while maintaining full backward compatibility. This modernization improves performance, maintainability, and aligns with modern PHP standards.

## What Changed

### Before (Legacy System)
- Custom `icms_Autoloader` class handled all class loading
- Manual `include_once` and `require_once` statements throughout the codebase
- PSR-0 style class naming with underscores (`icms_core_Security`)
- Module-specific autoloader registration

### After (Modern System)
- Composer's optimized PSR-4 autoloader as primary system
- Backward compatibility bridge (`icms_ComposerAutoloadBridge`)
- Legacy autoloader maintained as fallback
- Performance monitoring and optimization tools
- Seamless integration with existing code

## Key Benefits

1. **Performance**: Composer's optimized autoloader with APCu caching support
2. **Standards Compliance**: PSR-4 autoloading standard
3. **Maintainability**: Reduced manual include statements
4. **Backward Compatibility**: Existing code continues to work unchanged
5. **Future-Proof**: Easy integration with modern PHP packages
6. **Deployment Ready**: Only htdocs/ folder needs to be deployed to web server

## File Structure

```
/
├── htdocs/                                 # Web-accessible deployment directory
│   ├── composer.json                      # Composer configuration with PSR-4 mapping
│   ├── vendor/                            # Composer dependencies and autoloader
│   ├── libraries/icms/
│   │   ├── ComposerAutoloadBridge.php     # Compatibility bridge
│   │   ├── AutoloadPerformanceMonitor.php # Performance monitoring
│   │   └── [existing class files]        # Unchanged class structure
│   ├── install/                           # Installation wizard (updated for new autoloading)
│   └── test_autoloading.php               # Integration test script
└── [other project files]                  # Non-web-accessible files
```

## Configuration Details

### Composer Configuration (htdocs/composer.json)

```json
{
    "autoload": {
        "psr-4": {
            "Icms\\": "libraries/icms/"
        },
        "psr-0": {
            "icms_": "libraries/"
        },
        "classmap": [
            "libraries/icms.php"
        ],
        "files": [
            "include/functions.php"
        ]
    }
}
```

### Autoloader Priority

1. **Composer PSR-4 Autoloader** (Primary)
2. **Composer PSR-0 Autoloader** (For icms_ classes)
3. **Compatibility Bridge** (Class aliasing and conversion)
4. **Legacy icms_Autoloader** (Fallback)

## Usage Examples

### Loading Core Classes (No Changes Required)

```php
// These work exactly as before
$security = new icms_core_Security();
$logger = new icms_core_Logger();
$auth = icms_auth_Factory::getAuthConnection();
```

### New PSR-4 Style (Optional)

```php
// Future PSR-4 style (when fully migrated)
use Icms\Core\Security;
use Icms\Core\Logger;

$security = new Security();
$logger = new Logger();
```

### Module Registration

Modules are automatically registered with both autoloaders:

```php
// In module Object.php - this happens automatically
$module->registerClasses(); // Registers with both systems
```

## Performance Optimization

### Development Environment

```bash
cd htdocs
composer optimize-development
```

### Production Environment

```bash
cd htdocs
composer optimize-production
```

This enables:
- Optimized class maps
- APCu caching (if available)
- Authoritative class maps for maximum performance

### Performance Monitoring

```php
// Enable performance monitoring
icms_AutoloadPerformanceMonitor::enable();

// Your application code here...

// Generate performance report
echo icms_AutoloadPerformanceMonitor::generateReport();
```

## Migration Checklist for Developers

### ✅ Completed Automatically
- [x] Composer configuration setup
- [x] Backward compatibility bridge
- [x] Legacy autoloader integration
- [x] Performance optimization
- [x] Testing framework

### 📋 For Module Developers
- [ ] Test your modules with the new system
- [ ] Consider migrating to PSR-4 naming (optional)
- [ ] Update any manual includes to rely on autoloading
- [ ] Run performance tests

### 🔧 For Core Developers
- [ ] Gradually replace manual includes with autoloading
- [ ] Consider PSR-4 namespace migration for new classes
- [ ] Monitor performance metrics
- [ ] Update documentation

## Testing

### Automated Testing

Run the integration test:

```bash
cd htdocs
php test_autoloading.php
```

Expected output:
```
Testing ImpressCMS Composer Autoloading Integration
==================================================

1. Loading Composer autoloader...
   ✓ Composer autoloader loaded successfully

2. Loading ImpressCMS core...
   ✓ ImpressCMS core loaded successfully

3. Initializing ImpressCMS...
   ✓ ImpressCMS initialized successfully

4. Testing class autoloading...
   ✓ icms_core_Security loaded successfully
   ✓ icms_core_Logger loaded successfully
   [... more classes ...]

   Summary: 8/8 classes loaded successfully

5. Testing bridge functionality...
   ✓ Bridge class loaded successfully
   ✓ Bridge detects Composer availability
   ✓ Legacy autoloader properly detected

6. Testing autoloader registration...
   Found 3 registered autoloaders:
   1. Composer\Autoload\ClassLoader::loadClass
   2. icms_ComposerAutoloadBridge::autoload
   3. icms_Autoloader::autoload

   Status:
   ✓ Composer autoloader registered
   ✓ Bridge autoloader registered
   ✓ Legacy autoloader registered

7. Performance test...
   Loaded 30 class instances in 2.15ms
   Average: 0.07ms per class
   ✓ Performance is acceptable

==================================================
Autoloading integration test completed successfully!
```

### Manual Testing

1. **Class Loading**: Verify all existing classes load correctly
2. **Module Functionality**: Test module registration and class loading
3. **Performance**: Monitor load times and memory usage
4. **Error Handling**: Test fallback mechanisms

## Troubleshooting

### Common Issues

#### Classes Not Found
```php
// Check if autoloaders are registered
$autoloaders = spl_autoload_functions();
var_dump($autoloaders);

// Check if Composer is available
if (icms_ComposerAutoloadBridge::isComposerAvailable()) {
    echo "Composer autoloader is available";
}
```

#### Performance Issues
```php
// Enable performance monitoring
icms_AutoloadPerformanceMonitor::enable();

// Check optimization status
$optimizations = icms_AutoloadPerformanceMonitor::checkOptimizations();
var_dump($optimizations);
```

#### Module Classes Not Loading
```php
// Verify module registration
$module = icms_getModuleInfo('your_module');
if ($module) {
    // Check if classes are registered
    // Module classes should be automatically registered
}
```

### Debug Mode

Enable debug output by setting:

```php
define('ICMS_AUTOLOAD_DEBUG', true);
```

## Best Practices

### For New Development
1. Use autoloading instead of manual includes
2. Follow PSR-4 naming conventions for new classes
3. Test with both development and production autoloader configurations
4. Monitor performance impact

### For Existing Code
1. Gradually replace manual includes
2. Test thoroughly after changes
3. Maintain backward compatibility
4. Document any breaking changes

## Future Roadmap

1. **Phase 1** (Completed): Composer integration with backward compatibility
2. **Phase 2** (Future): Gradual PSR-4 namespace migration
3. **Phase 3** (Future): Legacy autoloader deprecation
4. **Phase 4** (Future): Full PSR-4 compliance

## Deployment

### Production Deployment

The new structure makes deployment simpler and more secure:

1. **Deploy only htdocs/ folder**: Copy the entire `htdocs/` directory to your web server's document root
2. **Run production optimization**:
   ```bash
   cd /path/to/your/webroot
   composer optimize-production
   ```
3. **Set proper permissions**: Ensure web server can read all files and write to cache directories
4. **Test installation**: Access `/install/` to run the installation wizard

### Development Setup

1. **Clone repository**: Get the full repository for development
2. **Install dependencies**:
   ```bash
   cd htdocs
   composer install --dev
   ```
3. **Run tests**:
   ```bash
   php test_autoloading.php
   ```

### Security Benefits

- **Reduced attack surface**: Only web-accessible files are in the document root
- **Protected dependencies**: Composer vendor directory is within the web root but can be protected via .htaccess
- **Clean separation**: Development files and tools remain outside the deployment directory

## Support

For issues or questions:
1. Check the troubleshooting section above
2. Run the integration test script
3. Enable debug mode for detailed information
4. Consult the ImpressCMS development team

---

**Note**: This migration maintains 100% backward compatibility. Existing code will continue to work without any changes required.
