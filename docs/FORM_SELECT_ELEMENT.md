# ImpressCMS Form Select Element Documentation

## Overview

The `icms_form_elements_Select` class creates HTML select dropdown or list elements for use in ImpressCMS forms. It supports both single and multiple selections, and provides flexible option management.

**Location:** `htdocs/libraries/icms/form/elements/Select.php`

**Extends:** `icms_form_Element`

## When to Use

Use the Select class when you need to:
- Create a dropdown list for selecting a single value
- Create a multi-select list for selecting multiple values
- Build custom select elements with dynamic options
- Extend it to create specialized select elements

## Class Hierarchy

The Select class is the base for several specialized select elements:

- **icms_form_elements_select_Group** - Select user groups
- **icms_form_elements_select_User** - Select users
- **icms_form_elements_select_Lang** - Select languages
- **icms_form_elements_select_Theme** - Select themes
- **icms_form_elements_select_Editor** - Select editors
- **icms_form_elements_select_Timezone** - Select timezones

## Constructor

```php
public function __construct(
    string $caption,
    string $name,
    mixed $value = null,
    int $size = 1,
    bool $multiple = false
)
```

### Parameters

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `$caption` | string | Required | The label/caption displayed for this form element |
| `$name` | string | Required | The HTML "name" attribute for the select element |
| `$value` | mixed | null | Pre-selected value(s): null, string/int, or array |
| `$size` | int | 1 | Number of rows: 1 for dropdown, >1 for multi-row list |
| `$multiple` | bool | false | Whether to allow multiple selections |

## Public Methods

### isMultiple()

```php
public function isMultiple(): bool
```

Checks if multiple selections are allowed.

**Returns:** `bool` - True if multiple selections allowed, false otherwise

### getSize()

```php
public function getSize(): int
```

Gets the number of visible rows in the select element.

**Returns:** `int` - Number of rows (1 = dropdown, >1 = list)

### getValue($encode = false)

```php
public function getValue(bool $encode = false): array
```

Gets the pre-selected values.

**Parameters:**
- `$encode` (bool) - Whether to HTML-encode values (default: false)

**Returns:** `array` - Array of pre-selected values

### setValue($value)

```php
public function setValue(mixed $value): void
```

Sets pre-selected values. Accepts single value or array.

**Parameters:**
- `$value` (mixed) - Single value or array of values to pre-select

### addOption($value, $name = "")

```php
public function addOption(string $value, string $name = ""): void
```

Adds a single option to the select element.

**Parameters:**
- `$value` (string) - The option value (submitted with form)
- `$name` (string) - Display text (uses value if empty)

### addOptionArray($options)

```php
public function addOptionArray(array $options): void
```

Adds multiple options at once.

**Parameters:**
- `$options` (array) - Associative array of value=>name pairs

### getOptions($encode = false)

```php
public function getOptions(int $encode = false): array
```

Gets all options in the select element.

**Parameters:**
- `$encode` (int) - Encoding level: 0=none, 1=values only, 2=values and names

**Returns:** `array` - Associative array of value=>name pairs

### render()

```php
public function render(): string
```

Renders the select element as HTML. Called automatically by the form system.

**Returns:** `string` - Complete HTML markup for the select element

## Practical Code Examples

### Basic Dropdown List

```php
$select = new icms_form_elements_Select('Choose a Status', 'status');
$select->addOption('active', 'Active');
$select->addOption('inactive', 'Inactive');
$select->addOption('pending', 'Pending Review');
$form->addElement($select);
```

### Dropdown with Pre-selected Value

```php
$select = new icms_form_elements_Select('Post Status', 'post_status', 'published');
$options = array(
    'draft' => 'Draft',
    'published' => 'Published',
    'archived' => 'Archived'
);
$select->addOptionArray($options);
$form->addElement($select);
```

### Multi-select List

```php
$select = new icms_form_elements_Select(
    'Select Categories',
    'categories',
    array(1, 3),  // Pre-select categories 1 and 3
    5,            // Show 5 rows
    true          // Allow multiple selections
);
$select->addOptionArray(array(
    1 => 'News',
    2 => 'Events',
    3 => 'Blog',
    4 => 'Gallery'
));
$form->addElement($select);
```

### Populate from Database

```php
$select = new icms_form_elements_Select('Select User', 'user_id');

// Get users from database
$member_handler = icms::handler('icms_member');
$users = $member_handler->getUserList();

$select->addOptionArray($users);
$form->addElement($select);
```

### With Custom HTML Attributes

```php
$select = new icms_form_elements_Select('Priority', 'priority', 'medium');
$select->addOptionArray(array(
    'low' => 'Low',
    'medium' => 'Medium',
    'high' => 'High'
));

// Add onchange event
$select->setExtra('onchange="alert(\'Priority changed!\')"');

// Add CSS class
$select->setClass('form-control');

$form->addElement($select);
```

## Common ImpressCMS Patterns

### Pattern 1: Configuration Select

```php
// In module admin preferences
$select = new icms_form_elements_Select(
    'Default Category',
    'default_category'
);
$select->addOptionArray($category_handler->getList());
$form->addElement($select);
```

### Pattern 2: Specialized Select Elements

```php
// Use specialized select for groups
$group_select = new icms_form_elements_select_Group(
    'User Groups',
    'groups',
    false,  // Don't include anonymous
    null,   // No pre-selected value
    5,      // Show 5 rows
    true    // Allow multiple selections
);
$form->addElement($group_select);

// Use specialized select for languages
$lang_select = new icms_form_elements_select_Lang(
    'Language',
    'language',
    'english'
);
$form->addElement($lang_select);
```

### Pattern 3: Dynamic Option Loading

```php
$select = new icms_form_elements_Select('Category', 'category_id');

// Load options based on module
$category_handler = icms_getModuleHandler('category', 'mymodule');
$categories = $category_handler->getList();

$select->addOptionArray($categories);
$form->addElement($select);
```

### Pattern 4: Conditional Options

```php
$select = new icms_form_elements_Select('Status', 'status');

// Add options based on user permissions
if (icms::$user->isAdmin()) {
    $select->addOption('draft', 'Draft');
    $select->addOption('published', 'Published');
    $select->addOption('archived', 'Archived');
} else {
    $select->addOption('draft', 'Draft');
    $select->addOption('published', 'Published');
}

$form->addElement($select);
```

## Inherited Methods from icms_form_Element

The Select class inherits useful methods from its parent class:

- `setCaption($caption)` - Set the element label
- `getCaption($encode = false)` - Get the element label
- `setName($name)` - Set the HTML name attribute
- `getName($encode = true)` - Get the HTML name attribute
- `setDescription($description)` - Set element description
- `getDescription($encode = false)` - Get element description
- `setExtra($extra)` - Set extra HTML attributes
- `getExtra()` - Get extra HTML attributes
- `setClass($class)` - Add CSS class
- `getClass()` - Get CSS classes
- `setRequired()` - Mark as required field
- `isRequired()` - Check if required

## HTML Output Examples

### Single Select Dropdown

```html
<select size='1' name='status' id='status'>
<option value='active' selected='selected'>Active</option>
<option value='inactive'>Inactive</option>
<option value='pending'>Pending</option>
</select>
```

### Multi-select List

```html
<select size='5' name='categories[]' id='categories' multiple='multiple'>
<option value='1' selected='selected'>News</option>
<option value='2'>Events</option>
<option value='3' selected='selected'>Blog</option>
<option value='4'>Gallery</option>
</select>
```

## Best Practices

1. **Always add options before rendering** - Add all options before the form is rendered
2. **Use meaningful values** - Use descriptive values that make sense in your code
3. **Pre-select defaults** - Set sensible default values for better UX
4. **Validate on submission** - Always validate selected values on the server side
5. **Use specialized classes** - Use icms_form_elements_select_* classes when available
6. **Escape output** - Use getValue(true) or getOptions(2) when outputting values
7. **Consider accessibility** - Add descriptions and use clear labels

## Security Considerations

- Option values are HTML-escaped in render() using htmlspecialchars()
- Always validate selected values on the server side
- Use getValue(true) to get encoded values for safe output
- Never trust user input from select elements

## Related Classes

- `icms_form_Element` - Base class for all form elements
- `icms_form_Base` - Base class for forms
- `icms_form_Theme` - Theme-based form rendering
- `icms_form_elements_Checkbox` - Checkbox form element
- `icms_form_elements_Radio` - Radio button form element

