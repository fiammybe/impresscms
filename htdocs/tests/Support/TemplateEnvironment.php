<?php
/**
 * Renders library templates for real, without a database.
 *
 * Icms\View\Tpl and the `db:` resource run unchanged; only the template file
 * handler they ask is a fake that finds every template in the system module's
 * templates directory, as the default template set does once the templates
 * are imported. Also defines the language constants and globals the form
 * classes read.
 *
 * Self-contained (no Icms\Form\Definition classes), so that the same
 * environment renders the legacy output of an older checkout for the
 * characterization snapshots (see FormOutputScenarios).
 *
 * @package icms\tests
 */

declare(strict_types=1);

namespace Icms\Tests\Support;

final class TemplateEnvironment
{
    private static bool $booted = false;

    public static function boot(): void
    {
        if (self::$booted) {
            return;
        }
        self::$booted = true;

        require_once dirname(__DIR__) . '/environment.php';

        $compile = sys_get_temp_dir() . '/icms-test-templates_c-' . getmypid();
        if (!is_dir($compile)) {
            mkdir($compile, 0777, true);
        }

        $constants = [
            'ICMS_COMPILE_PATH' => $compile,
            'ICMS_MODULES_PATH' => ICMS_ROOT_PATH . '/modules',
            'ICMS_MODULES_URL' => ICMS_URL . '/modules',
            'ICMS_UPLOAD_URL' => ICMS_URL . '/uploads',
            'ICMS_IMAGES_URL' => ICMS_URL . '/images',
            'ICMS_IMAGES_SET_URL' => ICMS_URL . '/images/kfaenza',
            'ICMS_VERSION_NAME' => 'ImpressCMS test',
            'ICMS_LIBRARIES_URL' => ICMS_URL . '/libraries',
            '_GO' => 'Go',
            '_PREV' => '&laquo;',
            '_NEXT' => '&raquo;',
            '_LANGCODE' => 'en',
            '_CHARSET' => 'utf-8',
            '_GLOBAL_LEFT' => 'left',
            '_FORM_ENTER' => 'Please enter %s',
            '_ALL' => 'All',
            '_CHECKALL' => 'Check all',
            '_REQUIRED' => 'Required',
            '_CORE_CLASSNOTINSTANIATED' => 'This class cannot be instantiated!',
            '_CO_ICMS_CURRENT_FILE' => 'Current file: ',
            '_CO_ICMS_URL_FILE_DSC' => 'Alternatively you can use an URL.',
            '_CO_ICMS_URL_FILE' => 'URL:',
            '_CO_ICMS_DELETE' => 'Delete',
            '_CO_ICMS_CREATE' => 'Create',
            '_CO_ICMS_MODIFY' => 'Modify',
            '_CO_ICMS_CANCEL' => 'Cancel',
            '_AM_VISIBLEIN' => 'Visible in',
            '_AM_TOPPAGE' => 'Top page',
            '_AM_ALLPAGES' => 'All pages',
            'ICMS_CAPTCHA_REFRESH' => 'Click the image to refresh',
            'ICMS_CAPTCHA_MAXATTEMPTS' => 'Maximum attempts: %d',
            'ICMS_CAPTCHA_RULE_CASESENSITIVE' => 'Code is case sensitive',
            'ICMS_CAPTCHA_RULE_CASEINSENSITIVE' => 'Code is case insensitive',
            'ICMS_CAPTCHA_RULE_TEXT' => 'Enter the result of the expression',
            'XOBJ_DTYPE_TXTBOX' => 1,
            'XOBJ_DTYPE_TXTAREA' => 2,
            'XOBJ_DTYPE_INT' => 3,
            'XOBJ_DTYPE_URL' => 4,
            'XOBJ_DTYPE_EMAIL' => 5,
            'XOBJ_DTYPE_ARRAY' => 6,
            'XOBJ_DTYPE_OTHER' => 7,
            'XOBJ_DTYPE_SOURCE' => 8,
            'XOBJ_DTYPE_STIME' => 9,
            'XOBJ_DTYPE_MTIME' => 10,
            'XOBJ_DTYPE_LTIME' => 11,
            'XOBJ_DTYPE_SIMPLE_ARRAY' => 101,
            'XOBJ_DTYPE_CURRENCY' => 200,
            'XOBJ_DTYPE_FLOAT' => 201,
            'XOBJ_DTYPE_TIME_ONLY' => 202,
            'XOBJ_DTYPE_URLLINK' => 203,
            'XOBJ_DTYPE_FILE' => 204,
            'XOBJ_DTYPE_IMAGE' => 205,
            'XOBJ_DTYPE_FORM_SECTION' => 210,
            'XOBJ_DTYPE_FORM_SECTION_CLOSE' => 211,
        ];
        foreach ($constants as $name => $value) {
            if (!defined($name)) {
                define($name, $value);
            }
        }

        $GLOBALS['icmsConfig'] = ($GLOBALS['icmsConfig'] ?? []) + [
            'template_set' => 'default',
            'theme_set' => 'iTheme',
            'theme_fromfile' => 1,
            'debug_mode' => 0,
            'language' => 'english',
            'anonymous' => 'Anonymous',
        ];
        $GLOBALS['icmsConfigPersona'] = ($GLOBALS['icmsConfigPersona'] ?? []) + ['pagstyle' => 'default'];
        $_SERVER['PHP_SELF'] = '/modules/news/index.php';
        $GLOBALS['icmsConfigCaptcha'] = ($GLOBALS['icmsConfigCaptcha'] ?? []) + [
            'captcha_num_chars' => 4,
            'captcha_casesensitive' => 0,
            'captcha_maxattempt' => 8,
        ];

        if (!function_exists('icms_conv_nr2local')) {
            eval('function icms_conv_nr2local($string) { return $string; }');
        }
        if (!function_exists('icms_get_page_before_form')) {
            eval('function icms_get_page_before_form() { return "http://localhost/before.php?a=1&b=2"; }');
        }

        self::setHandler('icms_view_template_file', new class {
            /** @return list<object> */
            public function getPrefetchedBlock(string $set, string $name): array
            {
                if (!is_file(ICMS_ROOT_PATH . '/modules/system/templates/' . $name)) {
                    return [];
                }

                return [new class ($name) {
                    public function __construct(private string $name)
                    {
                    }

                    public function getVar(string $key, string $format = 's'): string
                    {
                        return match ($key) {
                            'tpl_module' => 'system',
                            'tpl_type' => 'module',
                            'tpl_source' => (string) file_get_contents(ICMS_ROOT_PATH . '/modules/system/templates/' . $this->name),
                            'tpl_lastmodified' => (string) filemtime(ICMS_ROOT_PATH . '/modules/system/templates/' . $this->name),
                            default => '',
                        };
                    }
                }];
            }
        });
    }

    /**
     * Register a fake handler for \icms::handler($name)
     */
    public static function setHandler(string $name, object $handler): void
    {
        $property = new \ReflectionProperty(\icms::class, 'handlers');
        $handlers = (array) $property->getValue();
        $handlers[$name] = $handler;
        $property->setValue(null, $handlers);
    }
}
