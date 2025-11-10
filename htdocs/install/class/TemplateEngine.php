<?php

/**
 * Bootstrap-compatible template engine for ImpressCMS installer
 *
 * This class provides a lightweight template rendering system that works
 * before the full ImpressCMS system is initialized. It can use Smarty if
 * available, or fall back to simple PHP template rendering.
 *
 * @copyright The ImpressCMS Project http://www.impresscms.org/
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
 * @package installer
 * @since 1.5
 */
class InstallerTemplateEngine
{
    /**
     * Template variables
     * @var array
     */
    private $variables = [];

    /**
     * Template directory path
     * @var string
     */
    private $templateDir = '';

    /**
     * Smarty instance (if available)
     * @var object|null
     */
    private $smarty = null;

    /**
     * Whether to use Smarty or PHP templates
     * @var bool
     */
    private $useSmarty = false;

    /**
     * Constructor
     *
     * @param string $templateDir Path to templates directory
     */
    public function __construct($templateDir = '')
    {
        if (empty($templateDir)) {
            $templateDir = dirname(__FILE__) . '/../templates';
        }

        $this->templateDir = rtrim($templateDir, '/');

        // Try to initialize Smarty if available
        $this->initSmarty();
    }

    /**
     * Initialize Smarty if available
     *
     * @return void
     */
    private function initSmarty()
    {
        // Try to load Smarty if not already loaded
        if (!class_exists('Smarty', false)) {
            $this->loadSmarty();
        }

        // Check if Smarty is available
        if (class_exists('Smarty', false)) {
            try {
                // Ensure compile and cache directories exist
                $compileDir = dirname(__FILE__) . '/../compile';
                $cacheDir = dirname(__FILE__) . '/../cache';

                if (!is_dir($compileDir)) {
                    @mkdir($compileDir, 0777, true);
                }
                if (!is_dir($cacheDir)) {
                    @mkdir($cacheDir, 0777, true);
                }

                $this->smarty = new Smarty();
                // Use property assignment for Smarty 2.x compatibility
                $this->smarty->template_dir = $this->templateDir;
                $this->smarty->compile_dir = $compileDir;
                $this->smarty->cache_dir = $cacheDir;
                $this->smarty->left_delimiter = '<{';
                $this->smarty->right_delimiter = '}>';
                $this->useSmarty = true;
            } catch (Exception $e) {
                // Fall back to PHP templates
                $this->useSmarty = false;
            }
        }
    }

    /**
     * Load Smarty library
     *
     * @return void
     */
    private function loadSmarty()
    {
        // Try multiple possible locations for Smarty
        $possiblePaths = [
            dirname(__FILE__) . '/../libraries/smarty/Smarty.class.php',
            dirname(__FILE__) . '/../../libraries/smarty/Smarty.class.php',
            dirname(__FILE__) . '/../../../libraries/smarty/Smarty.class.php',
        ];

        foreach ($possiblePaths as $path) {
            if (file_exists($path)) {
                require_once $path;
                return;
            }
        }
    }

    /**
     * Assign a variable to the template
     *
     * @param string|array $name Variable name or array of variables
     * @param mixed $value Variable value (ignored if $name is array)
     * @return void
     */
    public function assign($name, $value = null)
    {
        if (is_array($name)) {
            $this->variables = array_merge($this->variables, $name);
        } else {
            $this->variables[$name] = $value;
        }

        // Also assign to Smarty if available
        if ($this->useSmarty && $this->smarty) {
            if (is_array($name)) {
                $this->smarty->assign($name);
            } else {
                $this->smarty->assign($name, $value);
            }
        }
    }

    /**
     * Get a template variable
     *
     * @param string $name Variable name
     * @return mixed Variable value or null if not set
     */
    public function get($name)
    {
        return isset($this->variables[$name]) ? $this->variables[$name] : null;
    }

    /**
     * Render a template
     *
     * @param string $templateName Template name (without .html extension)
     * @return string Rendered template output
     */
    public function render($templateName)
    {
        if ($this->useSmarty && $this->smarty) {
            return $this->renderWithSmarty($templateName);
        }

        return $this->renderWithPhp($templateName);
    }

    /**
     * Render template using Smarty
     *
     * @param string $templateName Template name
     * @return string Rendered output
     */
    private function renderWithSmarty($templateName)
    {
        try {
            $templateFile = $templateName . '.html.tpl';
            return $this->smarty->fetch($templateFile);
        } catch (Exception $e) {
            // Fall back to PHP rendering
            return $this->renderWithPhp($templateName);
        }
    }

    /**
     * Render template using PHP
     *
     * @param string $templateName Template name
     * @return string Rendered output
     */
    private function renderWithPhp($templateName)
    {
        $templateFile = $this->templateDir . '/' . $templateName . '.html.tpl';

        if (!file_exists($templateFile)) {
            return "<!-- Template not found: $templateName -->";
        }

        // Extract variables into local scope
        extract($this->variables, EXTR_SKIP);

        // Start output buffering
        ob_start();

        try {
            include $templateFile;
            $output = ob_get_contents();
        } finally {
            ob_end_clean();
        }

        return $output;
    }

    /**
     * Display a template (output directly)
     *
     * @param string $templateName Template name
     * @return void
     */
    public function display($templateName)
    {
        echo $this->render($templateName);
    }

    /**
     * Get template directory
     *
     * @return string
     */
    public function getTemplateDir()
    {
        return $this->templateDir;
    }

    /**
     * Check if using Smarty
     *
     * @return bool
     */
    public function isSmartyEnabled()
    {
        return $this->useSmarty;
    }
}

