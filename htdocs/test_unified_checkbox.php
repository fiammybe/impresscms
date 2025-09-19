<!DOCTYPE html>
<html>
<head>
    <title>Unified Checkbox Template Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .test-section { margin: 20px 0; padding: 15px; border: 1px solid #ccc; }
        .icms_checkboxoption { margin: 5px 0; }
        pre { background: #f5f5f5; padding: 10px; border: 1px solid #ddd; }
        .grouped { border: 1px solid #999; padding: 10px; background: #fff; }
    </style>
</head>
<body>
    <h1>Unified Checkbox Template Test</h1>
    <p>Testing both standard and IPF checkbox classes with the unified template</p>
    
    <?php
    // Include ImpressCMS bootstrap
    define('ICMS_ROOT_PATH', dirname(__FILE__));
    require_once ICMS_ROOT_PATH . '/mainfile.php';
    require_once ICMS_ROOT_PATH . '/libraries/icms/form/elements/Checkbox.php';
    require_once ICMS_ROOT_PATH . '/libraries/icms/ipf/form/elements/Checkbox.php';
    
    // Mock IPF Object for testing
    class MockIPFObject {
        public $vars = array();
        public $handler;
        
        public function __construct() {
            $this->vars = array(
                'interests' => array(
                    'form_caption' => 'Your Interests',
                    'value' => array('sports', 'music')
                )
            );
        }
        
        public function getVar($key) {
            return isset($this->vars[$key]['value']) ? $this->vars[$key]['value'] : null;
        }
        
        public function getControl($key) {
            return array(
                'options' => array(
                    'sports' => 'Sports',
                    'music' => 'Music',
                    'movies' => 'Movies',
                    'books' => 'Books',
                    'travel' => 'Travel'
                ),
                'delimeter' => '<br>'
            );
        }
    }
    
    echo '<div class="test-section">';
    echo '<h2>Test 1: Standard Checkbox Class</h2>';
    
    $standardCheckbox = new icms_form_elements_Checkbox('Hobbies', 'hobbies[]', array('reading', 'gaming'));
    $standardCheckbox->addOptionArray(array(
        'reading' => 'Reading',
        'gaming' => 'Gaming',
        'cooking' => 'Cooking',
        'gardening' => 'Gardening'
    ));
    
    echo '<h3>Data Structure (Standard):</h3>';
    echo '<pre>' . print_r($standardCheckbox->getCheckboxOptions(), true) . '</pre>';
    
    echo '<h3>Rendered HTML (Standard):</h3>';
    echo $standardCheckbox->render();
    echo '</div>';
    
    echo '<div class="test-section">';
    echo '<h2>Test 2: IPF Checkbox Class</h2>';
    
    $mockObject = new MockIPFObject();
    $ipfCheckbox = new icms_ipf_form_elements_Checkbox($mockObject, 'interests');
    
    echo '<h3>Data Structure (IPF):</h3>';
    echo '<pre>' . print_r($ipfCheckbox->getCheckboxOptions(), true) . '</pre>';
    
    echo '<h3>Rendered HTML (IPF):</h3>';
    echo $ipfCheckbox->render();
    
    echo '<h3>Validation JavaScript (IPF):</h3>';
    echo '<pre>' . htmlspecialchars($ipfCheckbox->renderValidationJS()) . '</pre>';
    echo '</div>';
    
    echo '<div class="test-section">';
    echo '<h2>Test 3: Template Compatibility Check</h2>';
    echo '<h3>Both classes should:</h3>';
    echo '<ul>';
    echo '<li>Use the same template structure (grouped div, icms_checkboxoption spans)</li>';
    echo '<li>Show correct checked states</li>';
    echo '<li>Include "Check All" functionality for multiple options</li>';
    echo '<li>Use proper delimiters between options</li>';
    echo '</ul>';
    echo '</div>';
    ?>
    
    <div class="test-section">
        <h2>Expected Results:</h2>
        <ul>
            <li><strong>Standard Class:</strong> Reading and Gaming should be CHECKED</li>
            <li><strong>IPF Class:</strong> Sports and Music should be CHECKED</li>
            <li><strong>Both:</strong> Should show "Check All" option</li>
            <li><strong>Both:</strong> Should use the same HTML structure</li>
            <li><strong>IPF:</strong> Should generate validation JavaScript</li>
        </ul>
    </div>
</body>
</html>
