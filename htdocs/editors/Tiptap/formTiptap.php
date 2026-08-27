<?php
/**
 * Tiptap adapter for ImpressCMS
 */
defined('ICMS_ROOT_PATH') || die('Root path not defined');

class icmsFormTiptap extends icms_form_elements_Textarea
{
    public $rootpath = '';
    public $_language = _LANGCODE;
    public $_width = '100%';
    public $_height = '28rem';
    public $config = array();

    /**
     * Constructor
     *
     * @param array $configs Editor options
     * @param bool  $checkCompatible Return false on failure
     */
    public function __construct($configs, $checkCompatible = false)
    {
        $this->rootpath = '/editors/Tiptap';

        if (is_array($configs)) {
            $vars = array_keys(get_object_vars($this));
            foreach ($configs as $key => $value) {
                if (in_array('_' . $key, $vars, true)) {
                    $this->{'_' . $key} = $value;
                } else {
                    $this->config[$key] = $value;
                }
            }
        }

        if ($checkCompatible && !$this->isCompatible()) {
            return false;
        }

        parent::__construct(@$this->_caption, @$this->_name, @$this->_value);
        parent::setExtra("style='width: " . $this->_width . "; height: " . $this->_height . ";'");
    }

    /**
     * Prepare HTML for output
     *
     * @return string
     */
    public function render()
    {
        global $xoTheme;

        $prepared = $this->prepareContent();
        $this->setValue($prepared['html']);

        $textareaId = $this->getName() . '_tarea';
        $editorId = $textareaId . '_editor';
        $toolbarId = $textareaId . '_toolbar';

        $xoTheme->addStylesheet($this->rootpath . '/assets/css/editor.css', array('media' => 'screen'));
        $xoTheme->addScript($this->rootpath . '/assets/js/editor.js', array('type' => 'text/javascript'));

        $xoTheme->addScript(
            '',
            array('type' => 'text/javascript'),
            'window.ImpressCmsTiptap = window.ImpressCmsTiptap || {queue: [], create: function () {}, enqueue: function (config) { this.queue.push(config); }, flush: function () {}};'
            . 'window.ImpressCmsTiptap.enqueue('
            . $this->encodeConfiguration(
                array(
                    'textareaId' => $textareaId,
                    'editorId' => $editorId,
                    'toolbarId' => $toolbarId,
                    'height' => $this->_height,
                    'content' => $prepared['html'],
                    'document' => $prepared['document'],
                )
            )
            . ');'
            . 'window.ImpressCmsTiptap.flush();'
        );

        $ret = '<div class="icms-tiptap">';
        $ret .= '<div class="icms-tiptap-toolbar" id="' . htmlspecialchars($toolbarId, ENT_QUOTES) . '">';
        $ret .= $this->renderToolbarButton('paragraph', 'P');
        $ret .= $this->renderToolbarButton('bold', 'B');
        $ret .= $this->renderToolbarButton('italic', 'I');
        $ret .= $this->renderToolbarButton('strike', 'S');
        $ret .= $this->renderToolbarButton('code', '{}');
        $ret .= $this->renderToolbarButton('heading1', 'H1');
        $ret .= $this->renderToolbarButton('heading2', 'H2');
        $ret .= $this->renderToolbarButton('heading3', 'H3');
        $ret .= $this->renderToolbarButton('bulletList', 'UL');
        $ret .= $this->renderToolbarButton('orderedList', 'OL');
        $ret .= $this->renderToolbarButton('blockquote', '&ldquo;');
        $ret .= $this->renderToolbarButton('codeBlock', '&lt;/&gt;');
        $ret .= $this->renderToolbarButton('horizontalRule', 'HR');
        $ret .= $this->renderToolbarButton('undo', '&larr;');
        $ret .= $this->renderToolbarButton('redo', '&rarr;');
        $ret .= '</div>';
        $ret .= '<div class="icms-tiptap-surface" id="' . htmlspecialchars($editorId, ENT_QUOTES) . '"></div>';
        $ret .= parent::render();
        $ret .= '</div>';

        return $ret;
    }

    /**
     * Check compatibility
     *
     * @return bool
     */
    protected function isCompatible()
    {
        return is_readable(__DIR__ . '/assets/js/editor.js');
    }

    /**
     * @return array
     */
    protected function prepareContent()
    {
        $content = (string) $this->getValue(false);
        $document = null;

        if (!$this->canUsePhpBridge() || $content === '') {
            return array(
                'html' => $content,
                'document' => $document,
            );
        }

        require_once __DIR__ . '/vendor/autoload.php';

        try {
            $tiptap = new \Tiptap\Editor();
            $content = $tiptap->sanitize($content);
            $document = (new \Tiptap\Editor())
                ->setContent($content)
                ->getDocument();
        } catch (\Throwable $exception) {
            $document = null;
        }

        return array(
            'html' => $content,
            'document' => $document,
        );
    }

    /**
     * @return bool
     */
    protected function canUsePhpBridge()
    {
        return is_readable(__DIR__ . '/vendor/autoload.php');
    }

    /**
     * @param array $configuration
     *
     * @return string
     */
    protected function encodeConfiguration($configuration)
    {
        $json = json_encode($configuration, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);

        if ($json === false) {
            return '{}';
        }

        return $json;
    }

    /**
     * @param string $action
     * @param string $label
     *
     * @return string
     */
    protected function renderToolbarButton($action, $label)
    {
        return '<button class="icms-tiptap-button" type="button" data-action="'
            . htmlspecialchars($action, ENT_QUOTES)
            . '">' . $label . '</button>';
    }
}
