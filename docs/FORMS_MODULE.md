# ImpressCMS Forms Module Documentation

## Table of Contents

1. [Overview](#1-overview)
2. [Features & Capabilities](#2-features--capabilities)
3. [Architecture & Workflow](#3-architecture--workflow)
4. [User Roles & Permissions](#4-user-roles--permissions)
5. [Customization & Extensibility](#5-customization--extensibility)
6. [Examples & Use Cases](#6-examples--use-cases)
7. [Best Practices](#7-best-practices)
8. [Technical References](#8-technical-references)

---

## 1. Overview

### Purpose

The ImpressCMS Forms module provides a comprehensive, object-oriented framework for creating, rendering, validating, and processing HTML forms throughout the content management system. It serves as the foundation for user input across the entire platform, from user registration and login to module configuration and content management.

### Role Within the System

The Forms module is a core component of ImpressCMS that:

- Powers all administrative interfaces for module configuration
- Enables user registration, profile editing, and authentication
- Provides form-building capabilities for custom modules
- Integrates with the IPF (ImpressCMS Persistence Framework) for automated form generation
- Supports theme-based rendering for consistent visual presentation

### Key Concepts

#### Form Creation

Forms in ImpressCMS are created by instantiating form container classes and adding form elements:

```php
$form = new icms_form_Theme('Form Title', 'form_name', 'action.php', 'post');
$form->addElement(new icms_form_elements_Text('Name', 'name', 50, 255));
$form->addElement(new icms_form_elements_Button('', 'submit', 'Submit', 'submit'));
$form->display();
```

#### Form Submission

Forms submit data via HTTP POST or GET methods. The form system handles:

- Data collection from multiple element types
- Required field validation (client-side and server-side)
- Security token verification for CSRF protection
- File upload handling

#### Validation

The validation system operates at two levels:

1. **Client-side**: JavaScript validation generated automatically based on element configuration
2. **Server-side**: PHP validation using element-specific rules and custom validation code

#### Storage

Form data is processed by handler classes that:

- Sanitize and validate input data
- Map form fields to database columns
- Execute database operations (INSERT, UPDATE, DELETE)

---

## 2. Features & Capabilities

### 2.1 Supported Input Types

#### Basic Input Elements

| Element Class | Description | HTML Output |
|---------------|-------------|-------------|
| `icms_form_elements_Text` | Single-line text input | `<input type="text">` |
| `icms_form_elements_Password` | Password input (masked) | `<input type="password">` |
| `icms_form_elements_Textarea` | Multi-line text area | `<textarea>` |
| `icms_form_elements_Hidden` | Hidden field | `<input type="hidden">` |
| `icms_form_elements_Label` | Display-only text | Plain text/HTML |

#### Selection Elements

| Element Class | Description | HTML Output |
|---------------|-------------|-------------|
| `icms_form_elements_Select` | Dropdown/list selection | `<select>` |
| `icms_form_elements_Checkbox` | Multiple choice checkboxes | `<input type="checkbox">` |
| `icms_form_elements_Radio` | Single choice radio buttons | `<input type="radio">` |
| `icms_form_elements_Radioyn` | Yes/No radio selection | Yes/No radio pair |

#### Specialized Select Elements

| Element Class | Description |
|---------------|-------------|
| `icms_form_elements_select_Country` | Country selection dropdown |
| `icms_form_elements_select_Group` | User group selection |
| `icms_form_elements_select_User` | User selection |
| `icms_form_elements_select_Lang` | Language selection |
| `icms_form_elements_select_Theme` | Theme selection |
| `icms_form_elements_select_Editor` | Editor selection |
| `icms_form_elements_select_Timezone` | Timezone selection |
| `icms_form_elements_select_Image` | Image library selection |
| `icms_form_elements_select_Matchoption` | Match option selection |

#### Date and Time Elements

| Element Class | Description |
|---------------|-------------|
| `icms_form_elements_Date` | Date picker with calendar popup |
| `icms_form_elements_Datetime` | Combined date and time picker |

#### File Upload Elements

| Element Class | Description |
|---------------|-------------|
| `icms_form_elements_File` | Basic file upload |
| `icms_ipf_form_elements_Fileupload` | Enhanced file upload with preview |
| `icms_ipf_form_elements_Image` | Image upload with preview |
| `icms_ipf_form_elements_Imageupload` | Enhanced image upload |
| `icms_ipf_form_elements_Richfile` | Rich file upload with metadata |

#### Rich Text Elements

| Element Class | Description |
|---------------|-------------|
| `icms_form_elements_Dhtmltextarea` | DHTML-enhanced textarea |
| `icms_form_elements_Editor` | Configurable text editor |
| `icms_ipf_form_elements_Source` | Source code editor |

#### Advanced Elements

| Element Class | Description |
|---------------|-------------|
| `icms_form_elements_Button` | Form buttons (submit, reset, button) |
| `icms_form_elements_Tray` | Container for grouping elements |
| `icms_form_elements_Captcha` | CAPTCHA verification |
| `icms_form_elements_Colorpicker` | Color selection picker |
| `icms_form_elements_Hiddentoken` | Security token element |
| `icms_form_elements_Groupperm` | Group permission selector |

#### IPF (Intelligent Persistence Framework) Elements

| Element Class | Description |
|---------------|-------------|
| `icms_ipf_form_elements_Autocomplete` | Auto-complete text input |
| `icms_ipf_form_elements_Blockoptions` | Block configuration options |
| `icms_ipf_form_elements_Page` | Page selection |
| `icms_ipf_form_elements_Parentcategory` | Parent category selection |
| `icms_ipf_form_elements_Passwordtray` | Password input with confirmation |
| `icms_ipf_form_elements_Section` | Form section divider |
| `icms_ipf_form_elements_Selectmulti` | Enhanced multi-select |
| `icms_ipf_form_elements_Signature` | Digital signature field |
| `icms_ipf_form_elements_Time` | Time-only picker |
| `icms_ipf_form_elements_Upload` | Generic upload handler |
| `icms_ipf_form_elements_Urllink` | URL link input |
| `icms_ipf_form_elements_User` | User selection with avatar |
| `icms_ipf_form_elements_Yesno` | Enhanced Yes/No selection |

### 2.2 Validation Rules and Error Handling

#### Required Field Validation

Mark elements as required during form construction:

```php
// Method 1: During addElement()
$form->addElement(new icms_form_elements_Text('Username', 'username', 50, 255), true);

// Method 2: Using setRequired()
$element = new icms_form_elements_Text('Email', 'email', 50, 255);
$element->setRequired();
$form->addElement($element);
```

#### Client-Side Validation

The form system automatically generates JavaScript validation:

```php
// Generated JavaScript checks:
// - Required fields are not empty
// - Custom validation rules per element type
// - Radio button groups have a selection
// - Checkbox groups have selections (when required)
```

#### Custom Validation Code

Add custom JavaScript validation to any element:

```php
$element = new icms_form_elements_Text('Email', 'email', 50, 255);
$element->customValidationCode[] = "
    if (myform.email.value.indexOf('@') == -1) {
        window.alert('Please enter a valid email address');
        myform.email.focus();
        return false;
    }
";
$form->addElement($element, true);
```

#### Server-Side Validation

Implement server-side validation in your form processing:

```php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify security token
    if (!icms::$security->check()) {
        redirect_header('form.php', 3, implode('<br />', icms::$security->getErrors()));
    }
    
    // Validate required fields
    $username = trim($_POST['username']);
    if (empty($username)) {
        $errors[] = 'Username is required';
    }
    
    // Validate email format
    $email = trim($_POST['email']);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email address';
    }
    
    // Handle errors
    if (!empty($errors)) {
        // Display errors and form
    }
}
```

### 2.3 Security Features

#### CSRF Protection

Protect forms with security tokens:

```php
// Add token to form (method 1: in constructor)
$form = new icms_form_Theme('My Form', 'myform', 'submit.php', 'post', true);

// Add token to form (method 2: manually)
$form->addElement(new icms_form_elements_Hiddentoken());

// Verify token on submission
if (!icms::$security->check()) {
    die('Security token verification failed');
}
```

#### Input Sanitization

All form elements provide encoding methods:

```php
// Get encoded values for safe output
$value = $element->getValue(true);  // HTML-encoded
$caption = $element->getCaption(true);  // HTML-encoded

// Select options with encoding levels
$options = $select->getOptions(0);  // No encoding
$options = $select->getOptions(1);  // Values encoded
$options = $select->getOptions(2);  // Values and names encoded
```

#### CAPTCHA Integration

Protect forms from automated submissions:

```php
// Add CAPTCHA to form
$form->addElement(new icms_form_elements_Captcha('Verify You Are Human'));

// Verify CAPTCHA on submission
$captcha = icms_form_elements_captcha_Object::instance();
if (!$captcha->verify()) {
    echo $captcha->getMessage();
    // Handle verification failure
}
```

---

## 3. Architecture & Workflow

### 3.1 Class Hierarchy

```
icms_form_Base (abstract)
├── icms_form_Theme
│   └── icms_ipf_form_Base
│       └── icms_ipf_form_Secure
├── icms_form_Simple
├── icms_form_Table
└── icms_form_Groupperm

icms_form_Element (abstract)
├── icms_form_elements_Text
│   └── icms_form_elements_Date
│       └── icms_form_elements_Datetime
├── icms_form_elements_Textarea
│   └── icms_form_elements_Dhtmltextarea
├── icms_form_elements_Select
│   ├── icms_form_elements_select_Country
│   ├── icms_form_elements_select_Group
│   ├── icms_form_elements_select_User
│   ├── icms_form_elements_select_Lang
│   ├── icms_form_elements_select_Theme
│   ├── icms_form_elements_select_Editor
│   ├── icms_form_elements_select_Timezone
│   ├── icms_form_elements_select_Image
│   └── icms_form_elements_select_Matchoption
├── icms_form_elements_Checkbox
├── icms_form_elements_Radio
│   └── icms_form_elements_Radioyn
├── icms_form_elements_Button
├── icms_form_elements_File
├── icms_form_elements_Hidden
│   └── icms_form_elements_Hiddentoken
├── icms_form_elements_Label
├── icms_form_elements_Password
├── icms_form_elements_Tray (container)
├── icms_form_elements_Captcha
├── icms_form_elements_Colorpicker
├── icms_form_elements_Editor
└── icms_form_elements_Groupperm
```

### 3.2 Form Lifecycle

```
┌─────────────────────────────────────────────────────────────────────┐
│                       FORM CREATION PHASE                            │
├─────────────────────────────────────────────────────────────────────┤
│  1. Instantiate Form Container                                       │
│     └── new icms_form_Theme($title, $name, $action, $method)        │
│                                                                      │
│  2. Create Form Elements                                             │
│     └── new icms_form_elements_*($caption, $name, ...)              │
│                                                                      │
│  3. Add Elements to Form                                             │
│     └── $form->addElement($element, $required)                       │
│                                                                      │
│  4. Configure Options (for select elements)                          │
│     └── $element->addOptionArray($options)                           │
└─────────────────────────────────────────────────────────────────────┘
                                   │
                                   ▼
┌─────────────────────────────────────────────────────────────────────┐
│                       FORM RENDERING PHASE                           │
├─────────────────────────────────────────────────────────────────────┤
│  1. Generate HTML Markup                                             │
│     └── $form->render() or $form->display()                         │
│                                                                      │
│  2. Render Each Element                                              │
│     └── $element->render() called for each element                   │
│                                                                      │
│  3. Generate Validation JavaScript                                   │
│     └── $form->renderValidationJS()                                  │
│                                                                      │
│  4. Output to Browser                                                │
│     └── HTML form displayed to user                                  │
└─────────────────────────────────────────────────────────────────────┘
                                   │
                                   ▼
┌─────────────────────────────────────────────────────────────────────┐
│                       USER INTERACTION PHASE                         │
├─────────────────────────────────────────────────────────────────────┤
│  1. User Fills Form Fields                                           │
│                                                                      │
│  2. Client-Side Validation (on submit)                               │
│     └── JavaScript validates required fields                         │
│     └── Custom validation code executed                              │
│                                                                      │
│  3. Form Submission                                                  │
│     └── POST/GET request sent to action URL                          │
└─────────────────────────────────────────────────────────────────────┘
                                   │
                                   ▼
┌─────────────────────────────────────────────────────────────────────┐
│                       FORM PROCESSING PHASE                          │
├─────────────────────────────────────────────────────────────────────┤
│  1. Receive Form Data                                                │
│     └── $_POST or $_GET array                                        │
│                                                                      │
│  2. Security Verification                                            │
│     └── icms::$security->check() for token validation                │
│                                                                      │
│  3. Server-Side Validation                                           │
│     └── Validate required fields                                     │
│     └── Validate data formats                                        │
│     └── Verify CAPTCHA if used                                       │
│                                                                      │
│  4. Data Processing                                                  │
│     └── Sanitize input                                               │
│     └── Process file uploads                                         │
│     └── Store data in database                                       │
│                                                                      │
│  5. Response                                                         │
│     └── Redirect on success                                          │
│     └── Display errors on failure                                    │
└─────────────────────────────────────────────────────────────────────┘
```

### 3.3 Data Flow

```
User Input → Form Element → $_POST/$_GET → Handler → Database
     ↑              │              │           │          │
     │              │              │           │          │
     │    Client-side      Server-side    Sanitize    Prepared
     │    Validation       Validation     + Escape   Statements
     │              │              │           │          │
     └──────────────┴──────────────┴───────────┴──────────┘
              Error handling at each stage
```

### 3.4 File Locations

```
htdocs/libraries/icms/form/
├── Base.php              # Abstract base class for forms
├── Element.php           # Abstract base class for elements
├── Theme.php             # Theme-enabled form rendering
├── Simple.php            # Simple HTML form rendering
├── Table.php             # Table-based form rendering
├── Groupperm.php         # Group permission form
└── elements/             # Form element classes
    ├── Button.php
    ├── Captcha.php
    ├── Checkbox.php
    ├── Colorpicker.php
    ├── Date.php
    ├── Datetime.php
    ├── Dhtmltextarea.php
    ├── Editor.php
    ├── File.php
    ├── Groupperm.php
    ├── Hidden.php
    ├── Hiddentoken.php
    ├── Label.php
    ├── Password.php
    ├── Radio.php
    ├── Radioyn.php
    ├── Select.php
    ├── Text.php
    ├── Textarea.php
    ├── Tray.php
    └── select/           # Specialized select elements
        ├── Country.php
        ├── Editor.php
        ├── Group.php
        ├── Image.php
        ├── Lang.php
        ├── Matchoption.php
        ├── Theme.php
        ├── Timezone.php
        └── User.php

htdocs/libraries/icms/ipf/form/
├── Base.php              # IPF form base class
├── Secure.php            # Secure IPF form with token
└── elements/             # IPF-specific elements
    ├── Autocomplete.php
    ├── Blockoptions.php
    ├── Checkbox.php
    ├── Date.php
    ├── Datetime.php
    ├── File.php
    ├── Fileupload.php
    ├── Image.php
    ├── Imageupload.php
    ├── Language.php
    ├── Page.php
    ├── Parentcategory.php
    ├── Passwordtray.php
    ├── Radio.php
    ├── Richfile.php
    ├── Section.php
    ├── Select.php
    ├── Selectmulti.php
    ├── Signature.php
    ├── Source.php
    ├── Text.php
    ├── Time.php
    ├── Upload.php
    ├── Urllink.php
    ├── User.php
    └── Yesno.php
```

---

## 4. User Roles & Permissions

### 4.1 Role-Based Form Access

#### Administrator

Administrators have full access to all form functionality:

- Create, modify, and delete forms in all modules
- Access to all administrative configuration forms
- Manage user permissions for form access
- View and manage all form submissions

#### Editor/Moderator

Editors typically have:

- Access to content editing forms
- Limited administrative form access
- Cannot modify system-level configurations

#### Registered User

Registered users can:

- Access profile editing forms
- Submit forms in modules they have permission to use
- View their own form submission history

#### Anonymous User (Guest)

Anonymous users may:

- Access public forms (registration, contact, etc.)
- Submit forms protected by CAPTCHA
- Cannot access user-specific forms

### 4.2 Implementing Permission Checks

```php
// Check if user can access a form
if (!icms::$user || !icms::$user->isAdmin()) {
    // Redirect non-admins
    redirect_header('index.php', 3, 'Access denied');
}

// Group-based permission check
$gperm_handler = icms::handler('icms_member_groupperm');
if (!$gperm_handler->checkRight('form_access', $formId, $user_groups, $moduleId)) {
    redirect_header('index.php', 3, 'You do not have permission to access this form');
}
```

### 4.3 Group Permission Forms

Create forms for managing group permissions:

```php
$form = new icms_form_Groupperm(
    'Module Permissions',    // Title
    $moduleId,               // Module ID
    'access_form',           // Permission name
    'Select which groups can access this form'  // Description
);

// Add items (e.g., form IDs) to manage permissions for
$form->addItem(1, 'Contact Form');
$form->addItem(2, 'Registration Form');
$form->addItem(3, 'Survey Form');

$form->display();
```

### 4.4 Dynamic Field Visibility

Show/hide fields based on user role:

```php
$form = new icms_form_Theme('Edit Content', 'content_form', 'submit.php', 'post');

// Basic fields for all users
$form->addElement(new icms_form_elements_Text('Title', 'title', 50, 255), true);
$form->addElement(new icms_form_elements_Textarea('Content', 'content', 10, 60), true);

// Admin-only fields
if (icms::$user && icms::$user->isAdmin()) {
    $form->addElement(new icms_form_elements_Radioyn('Published', 'published', 1));
    $form->addElement(new icms_form_elements_select_User('Author', 'author'));
}

$form->display();
```

---

## 5. Customization & Extensibility

### 5.1 Creating Custom Form Elements

Create a new form element by extending `icms_form_Element`:

```php
class MyModule_Form_Elements_Rating extends icms_form_Element {
    private $_value;
    private $_maxRating;
    
    public function __construct($caption, $name, $value = 0, $maxRating = 5) {
        $this->setCaption($caption);
        $this->setName($name);
        $this->_value = (int) $value;
        $this->_maxRating = (int) $maxRating;
    }
    
    public function getValue($encode = false) {
        return $this->_value;
    }
    
    public function setValue($value) {
        $this->_value = (int) $value;
    }
    
    public function render() {
        $name = $this->getName();
        $html = "<div class='rating-input'>";
        for ($i = 1; $i <= $this->_maxRating; $i++) {
            $checked = ($i == $this->_value) ? " checked='checked'" : "";
            $html .= "<input type='radio' name='{$name}' value='{$i}'{$checked} /> {$i} ";
        }
        $html .= "</div>";
        return $html;
    }
    
    public function renderValidationJS() {
        if ($this->isRequired()) {
            $name = $this->getName();
            $caption = $this->getCaption();
            return "
                var hasRating = false;
                for (var i = 0; i < myform.{$name}.length; i++) {
                    if (myform.{$name}[i].checked) hasRating = true;
                }
                if (!hasRating) {
                    window.alert('Please select a {$caption}');
                    return false;
                }
            ";
        }
        return '';
    }
}
```

### 5.2 Custom Form Containers

Create a specialized form container:

```php
class MyModule_Form_Wizard extends icms_form_Theme {
    private $_steps = array();
    private $_currentStep = 1;
    
    public function __construct($title, $name, $action) {
        parent::__construct($title, $name, $action, 'post', true);
    }
    
    public function addStep($stepNumber, $title) {
        $this->_steps[$stepNumber] = array(
            'title' => $title,
            'elements' => array()
        );
    }
    
    public function addElementToStep($stepNumber, &$element, $required = false) {
        $this->_steps[$stepNumber]['elements'][] = &$element;
        parent::addElement($element, $required);
    }
    
    public function setCurrentStep($step) {
        $this->_currentStep = $step;
    }
    
    public function render() {
        // Custom multi-step rendering logic
        $html = "<div class='wizard-form'>";
        $html .= $this->renderProgressBar();
        $html .= parent::render();
        $html .= "</div>";
        return $html;
    }
    
    private function renderProgressBar() {
        $html = "<div class='wizard-progress'>";
        foreach ($this->_steps as $num => $step) {
            $active = ($num == $this->_currentStep) ? 'active' : '';
            $html .= "<span class='step {$active}'>{$step['title']}</span>";
        }
        $html .= "</div>";
        return $html;
    }
}
```

### 5.3 Template Customization

Some elements support custom templates via Smarty:

```php
// Text element with custom template
$text = new icms_form_elements_Text('Name', 'name', 50, 255);
$text->customTemplate = 'my_custom_text_template.html';
$form->addElement($text);
```

Custom template example (`my_custom_text_template.html`):

```html
<div class="custom-text-wrapper">
    <input type="text" 
           name="{$ele_name}" 
           id="{$ele_id}" 
           size="{$ele_size}" 
           maxlength="{$ele_maxlength}" 
           value="{$ele_value|escape:'htmlall'}"
           class="form-control"
           {$ele_extra} />
</div>
```

### 5.4 Smarty Integration

Assign forms to Smarty templates:

```php
// In PHP
$form = new icms_form_Theme('Contact Us', 'contact', 'submit.php', 'post');
// ... add elements ...

global $xoopsTpl;
$form->assign($xoopsTpl);
```

```html
<!-- In Smarty template -->
<form name="{$contact.name}" action="{$contact.action}" method="{$contact.method}" {$contact.extra}>
    {$contact.javascript}
    
    {foreach from=$contact.elements key=name item=element}
        {if !$element.hidden}
            <div class="form-group">
                <label>{$element.caption}</label>
                {$element.body}
                {if $element.description}
                    <small class="help-text">{$element.description}</small>
                {/if}
            </div>
        {else}
            {$element.body}
        {/if}
    {/foreach}
</form>
```

### 5.5 CSS Styling

Target form elements with CSS:

```css
/* Form container */
.xo-theme-form {
    max-width: 800px;
    margin: 0 auto;
}

/* Form table */
.xo-theme-form table.outer {
    border-collapse: collapse;
    width: 100%;
}

/* Form header */
.xo-theme-form th {
    background-color: #f5f5f5;
    padding: 10px;
    text-align: left;
}

/* Label column */
.xo-theme-form td.head {
    width: 30%;
    vertical-align: top;
    padding: 10px;
}

/* Input column */
.xo-theme-form td.even {
    padding: 10px;
}

/* Required field indicator */
.xoops-form-element-caption-required .caption-marker {
    color: #ff0000;
    font-weight: bold;
}

/* Help text */
.xoops-form-element-help {
    font-size: 0.9em;
    color: #666;
    margin-top: 5px;
}
```

### 5.6 JavaScript Hooks

Add custom JavaScript behavior:

```php
// Add onchange event to select
$select = new icms_form_elements_Select('Category', 'category');
$select->setExtra('onchange="handleCategoryChange(this.value)"');

// Add CSS class for jQuery selection
$text = new icms_form_elements_Text('Email', 'email', 50, 255);
$text->setClass('validate-email');
```

```javascript
// Custom JavaScript
function handleCategoryChange(categoryId) {
    // AJAX request to load sub-categories
    $.ajax({
        url: 'ajax.php',
        data: { action: 'get_subcategories', category: categoryId },
        success: function(response) {
            $('#subcategory').html(response);
        }
    });
}

// jQuery validation
$(document).ready(function() {
    $('.validate-email').on('blur', function() {
        var email = $(this).val();
        if (email && !isValidEmail(email)) {
            $(this).addClass('error');
        } else {
            $(this).removeClass('error');
        }
    });
});
```

---

## 6. Examples & Use Cases

### 6.1 Contact Form

```php
<?php
// contact_form.php
include_once 'mainfile.php';

// Create form
$form = new icms_form_Theme('Contact Us', 'contact_form', 'contact_submit.php', 'post', true);

// Name field
$name = new icms_form_elements_Text('Your Name', 'name', 50, 100);
$form->addElement($name, true);

// Email field
$email = new icms_form_elements_Text('Email Address', 'email', 50, 100);
$email->customValidationCode[] = "
    if (myform.email.value.indexOf('@') == -1) {
        window.alert('Please enter a valid email address');
        myform.email.focus();
        return false;
    }
";
$form->addElement($email, true);

// Subject dropdown
$subject = new icms_form_elements_Select('Subject', 'subject');
$subject->addOptionArray(array(
    'general' => 'General Inquiry',
    'support' => 'Technical Support',
    'sales' => 'Sales Question',
    'other' => 'Other'
));
$form->addElement($subject, true);

// Message textarea
$message = new icms_form_elements_Textarea('Message', 'message', 10, 60);
$form->addElement($message, true);

// CAPTCHA for spam protection
$form->addElement(new icms_form_elements_Captcha('Verification'));

// Submit button
$form->addElement(new icms_form_elements_Button('', 'submit', 'Send Message', 'submit'));

// Display form
$xoopsOption['template_main'] = 'contact_form.tpl';
include ICMS_ROOT_PATH . '/header.php';
$xoopsTpl->assign('form', $form->render());
include ICMS_ROOT_PATH . '/footer.php';
```

### 6.2 User Registration Form

```php
<?php
// registration_form.php
$form = new icms_form_Theme('Register New Account', 'register', 'register.php', 'post', true);

// Username
$username = new icms_form_elements_Text('Username', 'uname', 25, 25);
$username->setDescription('Letters, numbers, and underscores only');
$form->addElement($username, true);

// Email
$email = new icms_form_elements_Text('Email', 'email', 50, 60);
$form->addElement($email, true);

// Password with confirmation
$pass_tray = new icms_form_elements_Tray('Password', '<br />');
$pass_tray->addElement(new icms_form_elements_Password('', 'pass', 15, 50), true);
$pass_tray->addElement(new icms_form_elements_Password('Confirm:', 'pass_confirm', 15, 50), true);
$form->addElement($pass_tray);

// Country
$country = new icms_form_elements_select_Country('Country', 'user_country', 'US');
$form->addElement($country);

// Timezone
$timezone = new icms_form_elements_select_Timezone('Timezone', 'timezone_offset', 0);
$form->addElement($timezone);

// Terms agreement
$terms = new icms_form_elements_Checkbox('', 'agree_terms');
$terms->addOption(1, 'I agree to the Terms of Service');
$form->addElement($terms, true);

// CAPTCHA
$form->addElement(new icms_form_elements_Captcha('Security Check'));

// Submit
$form->addElement(new icms_form_elements_Button('', 'submit', 'Create Account', 'submit'));

$form->display();
```

### 6.3 Survey Form with Multiple Question Types

```php
<?php
// survey_form.php
$form = new icms_form_Theme('Customer Satisfaction Survey', 'survey', 'survey_submit.php', 'post', true);

// Rating question
$satisfaction = new icms_form_elements_Radio('How satisfied are you with our service?', 'satisfaction');
$satisfaction->addOptionArray(array(
    5 => 'Very Satisfied',
    4 => 'Satisfied',
    3 => 'Neutral',
    2 => 'Dissatisfied',
    1 => 'Very Dissatisfied'
));
$form->addElement($satisfaction, true);

// Multiple choice question
$features = new icms_form_elements_Checkbox('Which features do you use most?', 'features');
$features->addOptionArray(array(
    'search' => 'Search',
    'categories' => 'Categories',
    'user_profiles' => 'User Profiles',
    'messaging' => 'Private Messaging',
    'forums' => 'Forums'
));
$form->addElement($features);

// Dropdown question
$frequency = new icms_form_elements_Select('How often do you visit our site?', 'visit_frequency');
$frequency->addOptionArray(array(
    'daily' => 'Daily',
    'weekly' => 'Several times a week',
    'monthly' => 'Several times a month',
    'rarely' => 'Rarely'
));
$form->addElement($frequency, true);

// Open-ended question
$comments = new icms_form_elements_Textarea('Additional Comments', 'comments', 5, 60);
$comments->setDescription('Please share any additional feedback');
$form->addElement($comments);

// Submit
$form->addElement(new icms_form_elements_Button('', 'submit', 'Submit Survey', 'submit'));

$form->display();
```

### 6.4 File Upload Form

```php
<?php
// upload_form.php
$form = new icms_form_Theme('Upload Document', 'upload_form', 'upload.php', 'post', true);
$form->setExtra('enctype="multipart/form-data"');

// Title
$form->addElement(new icms_form_elements_Text('Document Title', 'title', 50, 100), true);

// Description
$form->addElement(new icms_form_elements_Textarea('Description', 'description', 5, 60));

// File upload
$file = new icms_form_elements_File('Select File', 'upload_file', 5242880); // 5MB max
$file->setDescription('Maximum file size: 5MB. Allowed types: PDF, DOC, DOCX');
$form->addElement($file, true);

// Category
$category = new icms_form_elements_Select('Category', 'category');
$category->addOptionArray(array(
    'reports' => 'Reports',
    'presentations' => 'Presentations',
    'forms' => 'Forms',
    'other' => 'Other'
));
$form->addElement($category);

// Submit
$form->addElement(new icms_form_elements_Button('', 'submit', 'Upload', 'submit'));

$form->display();
```

### 6.5 Admin Configuration Form

```php
<?php
// admin/preferences.php
$form = new icms_form_Theme('Module Configuration', 'config_form', 'preferences.php', 'post', true);

// Section: General Settings
$form->insertBreak('General Settings', 'head');

// Enable module
$form->addElement(new icms_form_elements_Radioyn('Enable Module', 'mod_enabled', 1));

// Items per page
$perpage = new icms_form_elements_Text('Items Per Page', 'items_perpage', 5, 5, '10');
$form->addElement($perpage);

// Section: Display Settings
$form->insertBreak('Display Settings', 'head');

// Theme selection
$theme = new icms_form_elements_select_Theme('Module Theme', 'mod_theme');
$form->addElement($theme);

// Editor selection
$editor = new icms_form_elements_select_Editor('Default Editor', 'default_editor');
$form->addElement($editor);

// Section: Access Control
$form->insertBreak('Access Control', 'head');

// Allowed groups
$groups = new icms_form_elements_select_Group('Allowed Groups', 'allowed_groups', true, null, 5, true);
$form->addElement($groups);

// Buttons
$button_tray = new icms_form_elements_Tray('', '&nbsp;');
$button_tray->addElement(new icms_form_elements_Button('', 'submit', 'Save Changes', 'submit'));
$button_tray->addElement(new icms_form_elements_Button('', 'reset', 'Reset', 'reset'));
$form->addElement($button_tray);

$form->display();
```

### 6.6 IPF Object Form (Automatic Form Generation)

```php
<?php
// Using IPF for automatic form generation
class MyModule_Article extends icms_ipf_Object {
    public function __construct(&$handler) {
        parent::__construct($handler);
        
        // Define fields with form controls
        $this->quickInitVar('article_id', XOBJ_DTYPE_INT, true);
        $this->quickInitVar('title', XOBJ_DTYPE_TXTBOX, true);
        $this->quickInitVar('content', XOBJ_DTYPE_TXTAREA, true);
        $this->quickInitVar('category_id', XOBJ_DTYPE_INT, true);
        $this->quickInitVar('author', XOBJ_DTYPE_INT, true);
        $this->quickInitVar('published', XOBJ_DTYPE_INT, false, 0);
        $this->quickInitVar('created_date', XOBJ_DTYPE_LTIME, false);
        
        // Set form captions
        $this->setVarInfo('title', 'form_caption', 'Article Title');
        $this->setVarInfo('content', 'form_caption', 'Article Content');
        $this->setVarInfo('category_id', 'form_caption', 'Category');
        
        // Set custom controls
        $this->setControl('category_id', array(
            'name' => 'select',
            'itemHandler' => 'category',
            'module' => 'mymodule'
        ));
        $this->setControl('author', array('name' => 'user'));
        $this->setControl('published', array('name' => 'yesno'));
    }
}

// Create form automatically from object
$articleHandler = icms_getModuleHandler('article', 'mymodule');
$articleObj = $articleHandler->create();

$form = new icms_ipf_form_Base($articleObj, 'article_form', 'Article', 'submit.php');
$form->display();
```

---

## 7. Best Practices

### 7.1 Security

1. **Always use security tokens** for forms that modify data:
   ```php
   $form = new icms_form_Theme($title, $name, $action, 'post', true);
   // OR
   $form->addElement(new icms_form_elements_Hiddentoken());
   ```

2. **Verify security tokens** on form submission:
   ```php
   if (!icms::$security->check()) {
       redirect_header('form.php', 3, implode('<br />', icms::$security->getErrors()));
   }
   ```

3. **Use CAPTCHA** for public-facing forms:
   ```php
   $form->addElement(new icms_form_elements_Captcha());
   ```

4. **Sanitize all input** before database operations:
   ```php
   $title = $db->quoteValue(trim($_POST['title']));
   // Or use prepared statements
   ```

5. **Validate file uploads** thoroughly:
   ```php
   $allowed_types = array('image/jpeg', 'image/png', 'image/gif');
   if (!in_array($_FILES['upload']['type'], $allowed_types)) {
       // Reject file
   }
   ```

### 7.2 Usability

1. **Use descriptive captions** and help text:
   ```php
   $element = new icms_form_elements_Text('Email Address', 'email', 50, 100);
   $element->setDescription('We will never share your email with third parties');
   ```

2. **Group related fields** using Tray elements:
   ```php
   $address_tray = new icms_form_elements_Tray('Address', '<br />');
   $address_tray->addElement(new icms_form_elements_Text('Street:', 'street', 50, 100));
   $address_tray->addElement(new icms_form_elements_Text('City:', 'city', 30, 50));
   ```

3. **Set sensible defaults**:
   ```php
   $country = new icms_form_elements_select_Country('Country', 'country', 'US');
   $timezone = new icms_form_elements_select_Timezone('Timezone', 'tz', 0);
   ```

4. **Mark required fields clearly**:
   ```php
   $form->addElement($element, true); // Adds visual indicator and validation
   ```

5. **Provide clear error messages**:
   ```php
   $element->customValidationCode[] = "
       if (myform.email.value.indexOf('@') == -1) {
           window.alert('Please enter a valid email address (example: user@domain.com)');
           myform.email.focus();
           return false;
       }
   ";
   ```

### 7.3 Accessibility

1. **Use proper labels** (captions are automatically rendered as labels):
   ```php
   $form->addElement(new icms_form_elements_Text('First Name', 'firstname', 50, 100));
   ```

2. **Add descriptions for complex fields**:
   ```php
   $password->setDescription('Minimum 8 characters, including at least one number');
   ```

3. **Ensure keyboard navigation** works properly with tab order

4. **Use accesskeys** for important fields:
   ```php
   $element->setAccessKey('e'); // Alt+E focuses this field
   ```

### 7.4 Performance

1. **Load options efficiently**:
   ```php
   // Good: Load once, reuse
   $userList = $member_handler->getUserList();
   $select->addOptionArray($userList);
   
   // Avoid: Multiple database queries
   ```

2. **Use appropriate field sizes**:
   ```php
   // Match field size to expected input
   new icms_form_elements_Text('ZIP Code', 'zip', 10, 10); // Small field
   new icms_form_elements_Textarea('Content', 'content', 20, 80); // Large field
   ```

3. **Minimize client-side validation code** for better page load times

### 7.5 Maintainability

1. **Use consistent naming conventions**:
   ```php
   // Form names: lowercase with underscores
   $form = new icms_form_Theme('Title', 'module_action_form', ...);
   
   // Element names: lowercase, match database columns
   new icms_form_elements_Text('Title', 'article_title', ...);
   ```

2. **Separate form creation from display**:
   ```php
   function createArticleForm($article = null) {
       $form = new icms_form_Theme(...);
       // Add elements
       return $form;
   }
   
   // Usage
   $form = createArticleForm($existingArticle);
   $form->display();
   ```

3. **Document custom validation rules**:
   ```php
   // Validate: Email must be from company domain
   $email->customValidationCode[] = "/* Company email validation */...";
   ```

### 7.6 Common Pitfalls to Avoid

1. **Don't trust client-side validation alone** - Always validate on server
2. **Don't forget file upload encoding**:
   ```php
   $form->setExtra('enctype="multipart/form-data"');
   ```
3. **Don't use same name for multiple elements** (except arrays)
4. **Don't expose sensitive data** in hidden fields
5. **Don't skip security token verification** for any form that modifies data

---

## 8. Technical References

### 8.1 Form Base Class API

#### icms_form_Base

| Method | Parameters | Returns | Description |
|--------|------------|---------|-------------|
| `__construct` | `$title, $name, $action, $method='post', $addtoken=false` | void | Create new form |
| `getTitle` | `$encode=false` | string | Get form title |
| `getName` | `$encode=true` | string | Get form name attribute |
| `getAction` | `$encode=true` | string | Get form action URL |
| `getMethod` | none | string | Get form method (get/post) |
| `addElement` | `&$formElement, $required=false` | void | Add element to form |
| `getElements` | `$recurse=false` | array | Get all form elements |
| `getElementNames` | none | array | Get element name attributes |
| `getElementByName` | `$name` | mixed | Get element by name |
| `setElementValue` | `$name, $value` | void | Set element value |
| `setElementValues` | `$values` | void | Set multiple values |
| `getElementValue` | `$name, $encode=false` | string | Get element value |
| `getElementValues` | `$encode=false` | array | Get all element values |
| `setExtra` | `$extra` | void | Set extra form attributes |
| `getExtra` | none | string | Get extra attributes |
| `setRequired` | `&$formElement` | void | Mark element required |
| `getRequired` | none | array | Get required elements |
| `insertBreak` | `$extra=null` | void | Insert visual break |
| `render` | none | string | Render form HTML |
| `display` | none | void | Output form HTML |
| `renderValidationJS` | `$withtags=true` | string | Get validation JavaScript |
| `assign` | `&$tpl` | void | Assign to Smarty template |

### 8.2 Element Base Class API

#### icms_form_Element

| Method | Parameters | Returns | Description |
|--------|------------|---------|-------------|
| `setName` | `$name` | void | Set element name |
| `getName` | `$encode=true` | string | Get element name |
| `setCaption` | `$caption` | void | Set element caption |
| `getCaption` | `$encode=false` | string | Get element caption |
| `setDescription` | `$description` | void | Set help text |
| `getDescription` | `$encode=false` | string | Get help text |
| `setAccessKey` | `$key` | void | Set keyboard shortcut |
| `getAccessKey` | none | string | Get keyboard shortcut |
| `setClass` | `$class` | void | Add CSS class |
| `getClass` | none | string | Get CSS classes |
| `setExtra` | `$extra, $replace=false` | array | Set extra HTML attributes |
| `getExtra` | `$encode=false` | string | Get extra attributes |
| `setHidden` | none | void | Mark as hidden element |
| `isHidden` | none | bool | Check if hidden |
| `setRequired` | none | void | Mark as required |
| `isRequired` | none | bool | Check if required |
| `isContainer` | none | bool | Check if container element |
| `render` | none | string | Render element HTML |
| `renderValidationJS` | none | string | Get validation JS |

### 8.3 Data Types (for IPF)

| Constant | Value | Description | Default Control |
|----------|-------|-------------|-----------------|
| `XOBJ_DTYPE_TXTBOX` | 1 | Single-line text | text |
| `XOBJ_DTYPE_TXTAREA` | 2 | Multi-line text | textarea |
| `XOBJ_DTYPE_INT` | 3 | Integer | text (small) |
| `XOBJ_DTYPE_URL` | 4 | URL | text |
| `XOBJ_DTYPE_EMAIL` | 5 | Email address | text |
| `XOBJ_DTYPE_ARRAY` | 6 | Serialized array | - |
| `XOBJ_DTYPE_OTHER` | 7 | Other/custom | - |
| `XOBJ_DTYPE_SOURCE` | 8 | Source code | source editor |
| `XOBJ_DTYPE_STIME` | 9 | Short time (date only) | date |
| `XOBJ_DTYPE_MTIME` | 10 | Medium time | datetime |
| `XOBJ_DTYPE_LTIME` | 11 | Long time (timestamp) | datetime |
| `XOBJ_DTYPE_FLOAT` | 13 | Float/decimal | text (small) |
| `XOBJ_DTYPE_CURRENCY` | 200 | Currency value | text |
| `XOBJ_DTYPE_URLLINK` | 201 | URL link object | urllink |
| `XOBJ_DTYPE_FILE` | 202 | File object | richfile |
| `XOBJ_DTYPE_IMAGE` | 203 | Image | image |
| `XOBJ_DTYPE_FORM_SECTION` | 210 | Form section start | section |
| `XOBJ_DTYPE_FORM_SECTION_CLOSE` | 211 | Form section end | section close |
| `XOBJ_DTYPE_SIMPLE_ARRAY` | 212 | Simple array | - |
| `XOBJ_DTYPE_TIME_ONLY` | 213 | Time only | time |

### 8.4 Error Codes and Troubleshooting

| Issue | Possible Cause | Solution |
|-------|---------------|----------|
| Form not displaying | Missing `display()` call | Add `$form->display()` or `echo $form->render()` |
| Security token error | Token expired or missing | Add token to form, ensure session is active |
| File upload fails | Missing enctype | Add `$form->setExtra('enctype="multipart/form-data"')` |
| Validation not working | JavaScript disabled | Add server-side validation |
| Select options empty | Options added after render | Add options before `display()` |
| Required indicator missing | Element not marked required | Use `$form->addElement($el, true)` |
| Form submits to wrong URL | Incorrect action parameter | Check `$action` in constructor |
| Hidden fields not submitted | Rendered outside form tags | Ensure elements added with `addElement()` |
| Multiple selection not working | Missing array brackets | Element name should end with `[]` |
| CAPTCHA always fails | Session issues | Check PHP session configuration |

### 8.5 Configuration Options

#### CAPTCHA Configuration

| Option | Type | Default | Description |
|--------|------|---------|-------------|
| `mode` | string | 'text' | CAPTCHA type: 'text' or 'image' |
| `skipmember` | bool | true | Skip for logged-in users |
| `numchar` | int | 5 | Number of characters |
| `minfontsize` | int | 20 | Minimum font size (image mode) |
| `maxfontsize` | int | 25 | Maximum font size (image mode) |
| `backgroundtype` | int | 0 | Background type (image mode) |
| `backgroundnum` | int | 50 | Number of background elements |

#### Editor Configuration

Available editors depend on installation:

- `dhtmltextarea` - Built-in DHTML editor
- `textarea` - Plain textarea
- `tinymce` - TinyMCE (if installed)
- `ckeditor` - CKEditor (if installed)

### 8.6 Related Documentation

- [FORM_SELECT_ELEMENT.md](FORM_SELECT_ELEMENT.md) - Select element documentation
- [FORM_SELECT_COUNTRY_ELEMENT.md](FORM_SELECT_COUNTRY_ELEMENT.md) - Country select documentation
- [FORM_SELECT_GROUP_ELEMENT.md](FORM_SELECT_GROUP_ELEMENT.md) - Group select documentation
- [FORM_SELECT_IMAGE_ELEMENT.md](FORM_SELECT_IMAGE_ELEMENT.md) - Image select documentation

---

## Appendix: Quick Reference Card

### Creating a Basic Form

```php
// 1. Create form
$form = new icms_form_Theme('Title', 'name', 'action.php', 'post', true);

// 2. Add elements
$form->addElement(new icms_form_elements_Text('Caption', 'name', 50, 255), true);
$form->addElement(new icms_form_elements_Textarea('Content', 'content', 10, 60));
$form->addElement(new icms_form_elements_Button('', 'submit', 'Submit', 'submit'));

// 3. Display
$form->display();
```

### Processing Form Submission

```php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify security
    if (!icms::$security->check()) {
        die('Security error');
    }
    
    // Get and validate data
    $name = trim($_POST['name']);
    if (empty($name)) {
        $errors[] = 'Name is required';
    }
    
    // Process or display errors
    if (empty($errors)) {
        // Save data
        redirect_header('success.php', 3, 'Saved successfully');
    }
}
```

### Common Element Examples

```php
// Text input
new icms_form_elements_Text('Label', 'name', $size, $maxlength, $default);

// Select dropdown
$select = new icms_form_elements_Select('Label', 'name', $default, $size, $multiple);
$select->addOptionArray(array('key1' => 'Value 1', 'key2' => 'Value 2'));

// Checkbox group
$checkbox = new icms_form_elements_Checkbox('Label', 'name', $defaults);
$checkbox->addOptionArray(array('opt1' => 'Option 1', 'opt2' => 'Option 2'));

// Radio buttons
$radio = new icms_form_elements_Radio('Label', 'name', $default);
$radio->addOptionArray(array('yes' => 'Yes', 'no' => 'No'));

// File upload
new icms_form_elements_File('Label', 'name', $maxFileSize);

// Hidden field
new icms_form_elements_Hidden('name', 'value');

// Button
new icms_form_elements_Button('Label', 'name', 'Button Text', 'submit|reset|button');
```

---

*Documentation generated for ImpressCMS Forms Module*
*Last updated: 2024*
