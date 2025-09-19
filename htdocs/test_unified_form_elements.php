<!DOCTYPE html>
<html>
<head>
    <title>Unified Form Elements Template Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .test-section { margin: 20px 0; padding: 15px; border: 1px solid #ccc; }
        .icms_checkboxoption { margin: 5px 0; }
        .password-tray { margin: 10px 0; }
        pre { background: #f5f5f5; padding: 10px; border: 1px solid #ddd; }
        .grouped { border: 1px solid #999; padding: 10px; background: #fff; }
        .form-element { margin: 10px 0; padding: 10px; border: 1px solid #ddd; background: #f9f9f9; }
    </style>
</head>
<body>
    <h1>Unified Form Elements Template Test</h1>
    <p>Testing standard and IPF form elements with unified templates</p>
    
    <?php
    // Include ImpressCMS bootstrap
    define('ICMS_ROOT_PATH', dirname(__FILE__));
    require_once ICMS_ROOT_PATH . '/mainfile.php';
    require_once ICMS_ROOT_PATH . '/libraries/icms/form/elements/Text.php';
    require_once ICMS_ROOT_PATH . '/libraries/icms/form/elements/Password.php';
    require_once ICMS_ROOT_PATH . '/libraries/icms/form/elements/Button.php';
    require_once ICMS_ROOT_PATH . '/libraries/icms/form/elements/Checkbox.php';
    require_once ICMS_ROOT_PATH . '/libraries/icms/ipf/form/elements/Text.php';
    require_once ICMS_ROOT_PATH . '/libraries/icms/ipf/form/elements/Passwordtray.php';
    require_once ICMS_ROOT_PATH . '/libraries/icms/ipf/form/elements/Checkbox.php';
    
    // Mock IPF Object for testing
    class MockIPFObject {
        public $vars = array();
        public $controls = array();
        public $handler;
        
        public function __construct() {
            $this->vars = array(
                'username' => array(
                    'form_caption' => 'Username',
                    'value' => 'john_doe'
                ),
                'password' => array(
                    'form_caption' => 'Password',
                    'value' => ''
                ),
                'interests' => array(
                    'form_caption' => 'Your Interests',
                    'value' => array('sports', 'music')
                )
            );
            
            $this->controls = array(
                'username' => array(
                    'size' => 30,
                    'maxlength' => 50
                ),
                'password' => array(),
                'interests' => array(
                    'options' => array(
                        'sports' => 'Sports',
                        'music' => 'Music',
                        'movies' => 'Movies',
                        'books' => 'Books'
                    ),
                    'delimeter' => '<br>'
                )
            );
        }
        
        public function getVar($key, $format = 's') {
            return isset($this->vars[$key]['value']) ? $this->vars[$key]['value'] : null;
        }
        
        public function getControl($key) {
            return isset($this->controls[$key]) ? $this->controls[$key] : array();
        }
    }
    
    $mockObject = new MockIPFObject();
    ?>
    
    <!-- Text Elements Test -->
    <div class="test-section">
        <h2>Text Elements</h2>
        
        <h3>Standard Text Element</h3>
        <div class="form-element">
            <?php
            $standardText = new icms_form_elements_Text('Full Name', 'fullname', 30, 100, 'John Smith');
            echo $standardText->render();
            ?>
        </div>
        
        <h3>IPF Text Element</h3>
        <div class="form-element">
            <?php
            $ipfText = new icms_ipf_form_elements_Text($mockObject, 'username');
            echo $ipfText->render();
            ?>
        </div>
        
        <p><strong>Expected:</strong> Both should render as text input fields with correct values</p>
    </div>
    
    <!-- Password Elements Test -->
    <div class="test-section">
        <h2>Password Elements</h2>
        
        <h3>Standard Password Element</h3>
        <div class="form-element">
            <?php
            $standardPassword = new icms_form_elements_Password('Password', 'password', 20, 50);
            echo $standardPassword->render();
            ?>
        </div>
        
        <h3>IPF Password Tray Element</h3>
        <div class="form-element">
            <?php
            $ipfPasswordTray = new icms_ipf_form_elements_Passwordtray($mockObject, 'password');
            echo $ipfPasswordTray->render();
            ?>
        </div>
        
        <p><strong>Expected:</strong> Standard shows single password field, IPF shows dual password fields for confirmation</p>
    </div>
    
    <!-- Button Elements Test -->
    <div class="test-section">
        <h2>Button Elements</h2>
        
        <h3>Standard Button Element</h3>
        <div class="form-element">
            <?php
            $standardButton = new icms_form_elements_Button('Submit', 'submit_btn', 'Submit Form', 'submit');
            echo $standardButton->render();
            ?>
        </div>
        
        <p><strong>Expected:</strong> Button should render correctly (no IPF version exists)</p>
    </div>
    
    <!-- Checkbox Elements Test -->
    <div class="test-section">
        <h2>Checkbox Elements</h2>
        
        <h3>Standard Checkbox Element</h3>
        <div class="form-element">
            <?php
            $standardCheckbox = new icms_form_elements_Checkbox('Hobbies', 'hobbies[]', array('reading', 'gaming'));
            $standardCheckbox->addOptionArray(array(
                'reading' => 'Reading',
                'gaming' => 'Gaming',
                'cooking' => 'Cooking',
                'gardening' => 'Gardening'
            ));
            echo $standardCheckbox->render();
            ?>
        </div>
        
        <h3>IPF Checkbox Element</h3>
        <div class="form-element">
            <?php
            $ipfCheckbox = new icms_ipf_form_elements_Checkbox($mockObject, 'interests');
            echo $ipfCheckbox->render();
            ?>
        </div>
        
        <p><strong>Expected:</strong> Both should show checkboxes with correct checked states and "Check All" functionality</p>
    </div>
    
    <!-- Summary -->
    <div class="test-section">
        <h2>Test Summary</h2>
        <h3>Unified Template Benefits:</h3>
        <ul>
            <li><strong>Text Elements:</strong> IPF Text uses parent's template (already unified)</li>
            <li><strong>Password Elements:</strong> IPF Passwordtray now uses template instead of direct HTML</li>
            <li><strong>Button Elements:</strong> Only standard version exists (already uses template)</li>
            <li><strong>Checkbox Elements:</strong> Both use same unified template with enhanced features</li>
        </ul>
        
        <h3>Preserved Functionality:</h3>
        <ul>
            <li>✅ IPF object integration maintained</li>
            <li>✅ Dynamic value loading from object properties</li>
            <li>✅ Validation JavaScript (where applicable)</li>
            <li>✅ All existing public API methods</li>
            <li>✅ Backward compatibility</li>
        </ul>
    </div>
</body>
</html>
