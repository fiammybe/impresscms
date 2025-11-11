# ImpressCMS Group Select Form Element Documentation

## Overview

The `icms_form_elements_select_Group` class is a specialized form element that allows users to select one or more user groups from the ImpressCMS system. It extends the base `icms_form_elements_Select` class and automatically populates with all available groups from the member system.

## When to Use

Use the Group Select element when you need to:

- Allow users to select user groups for permission assignment
- Configure group-based access control
- Assign groups to image categories (read/write permissions)
- Set up group-based module restrictions
- Filter users by group membership
- Manage group assignments in admin interfaces
- Configure module preferences by group

## Class Hierarchy

```
icms_form_elements_Select
    └── icms_form_elements_select_Group
```

The Group Select class inherits all functionality from the base Select class while adding specialized group selection capabilities.

## Constructor

### Signature

```php
public function __construct(
    string $caption,
    string $name,
    bool $include_anon = false,
    mixed $value = null,
    int $size = 1,
    bool $multiple = false
)
```

### Parameters

| Parameter | Type | Default | Description | Example |
|-----------|------|---------|-------------|---------|
| `$caption` | string | - | The label/caption displayed for this form element | `"Select Group"` |
| `$name` | string | - | The HTML "name" attribute for the select element | `"group_id"` |
| `$include_anon` | bool | false | Include the anonymous group in the list | `true` or `false` |
| `$value` | mixed | null | Pre-selected group ID(s) | `1` or `array(1, 2)` |
| `$size` | int | 1 | Number of visible rows (1 = dropdown) | `1` or `5` |
| `$multiple` | bool | false | Allow multiple group selections | `true` or `false` |

### Constructor Examples

```php
// Basic dropdown (single selection, no anonymous)
$groupSelect = new icms_form_elements_select_Group('Select Group', 'group_id');
$form->addElement($groupSelect);

// Multi-select list with anonymous group
$groupSelect = new icms_form_elements_select_Group(
    'Select Groups',
    'group_ids',
    true,   // Include anonymous
    null,   // No pre-selected
    5,      // Show 5 rows
    true    // Allow multiple
);
$form->addElement($groupSelect);

// Pre-selected groups for permissions
$groupSelect = new icms_form_elements_select_Group(
    'Read Permissions',
    'read_groups',
    true,
    array(1, 2),  // Pre-select admin and registered
    5,
    true
);
$form->addElement($groupSelect);

// Single group selection with anonymous
$groupSelect = new icms_form_elements_select_Group(
    'Default Group',
    'default_group',
    true,   // Include anonymous
    ICMS_GROUP_ADMIN  // Pre-select admin group
);
$form->addElement($groupSelect);
```

## Inherited Methods

The Group Select element inherits all methods from `icms_form_elements_Select`, including:

- `addOption()` - Add a single option
- `addOptionArray()` - Add multiple options
- `getValue()` - Get the selected value(s)
- `setValue()` - Set the selected value(s)
- `setSize()` - Set the number of visible rows
- `setMultiple()` - Enable/disable multiple selection
- `render()` - Generate HTML markup

See the Select element documentation for details on these inherited methods.

## Common ImpressCMS Patterns

### Pattern 1: Image Category Permissions

```php
// Set read permissions for image category
$readGroupSelect = new icms_form_elements_select_Group(
    'Read Permissions',
    'readgroup',
    true,  // Include anonymous
    array(ICMS_GROUP_ADMIN),  // Pre-select admin
    5,
    true   // Allow multiple
);
$form->addElement($readGroupSelect);

// Set write permissions for image category
$writeGroupSelect = new icms_form_elements_select_Group(
    'Write Permissions',
    'writegroup',
    true,
    array(ICMS_GROUP_ADMIN),
    5,
    true
);
$form->addElement($writeGroupSelect);
```

### Pattern 2: Module Preferences

```php
// Single group selection for module default group
$groupSelect = new icms_form_elements_select_Group(
    'Default User Group',
    'default_group',
    false,  // Don't include anonymous
    $config['default_group']
);
$form->addElement($groupSelect);

// Multiple group selection for module access
$accessSelect = new icms_form_elements_select_Group(
    'Groups with Access',
    'access_groups',
    false,
    $config['access_groups'],
    5,
    true
);
$form->addElement($accessSelect);
```

### Pattern 3: User Search/Filter

```php
// Find users in specific groups
$groupSelect = new icms_form_elements_select_Group(
    'Search by Group',
    'search_groups',
    false,  // Don't include anonymous
    null,
    5,
    true    // Allow multiple groups
);
$form->addElement($groupSelect);
```

### Pattern 4: User Management

```php
// Assign groups to user
$groupSelect = new icms_form_elements_select_Group(
    'User Groups',
    'groups',
    false,  // Don't include anonymous
    $user->getGroups(),  // Pre-select user's current groups
    5,
    true    // Allow multiple
);
$form->addElement($groupSelect);
```

## Group Constants

ImpressCMS provides predefined group constants:

```php
ICMS_GROUP_ANONYMOUS  // Anonymous/guest group (typically ID 1)
ICMS_GROUP_ADMIN      // Administrator group (typically ID 2)
ICMS_GROUP_REGISTERED // Registered users group (typically ID 3)
```

## Anonymous Group Behavior

### When to Include Anonymous Group

Include the anonymous group (`$include_anon = true`) when:
- Setting permissions for image categories
- Configuring public access levels
- Setting up guest-accessible content
- Managing read permissions

### When to Exclude Anonymous Group

Exclude the anonymous group (`$include_anon = false`) when:
- Assigning groups to users
- Filtering active users
- Managing user-specific permissions
- Searching for registered users

## HTML Output Example

### Single Selection (Dropdown)

```html
<select name='group_id' id='group_id'>
    <option value='2'>Administrator</option>
    <option value='3'>Registered Users</option>
    <option value='4'>Moderators</option>
</select>
```

### Multiple Selection (List Box)

```html
<select name='group_ids[]' id='group_ids[]' multiple='multiple' size='5'>
    <option value='1'>Anonymous</option>
    <option value='2'>Administrator</option>
    <option value='3'>Registered Users</option>
    <option value='4'>Moderators</option>
</select>
```

## Best Practices

1. **Use Appropriate Inclusion**: Only include anonymous group when necessary for permissions
2. **Pre-select Current Values**: When editing, always pre-select existing group assignments
3. **Use Descriptive Captions**: Use clear labels like "Read Permissions" or "Write Permissions"
4. **Validate on Submit**: Always validate selected groups on form submission
5. **Check Permissions**: Verify user has permission to manage groups before rendering
6. **Use Constants**: Use `ICMS_GROUP_ADMIN` instead of hardcoded group IDs
7. **Handle Multiple Selections**: When `$multiple = true`, remember values are arrays

## Form Submission Handling

### Single Selection

```php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $groupId = (int) $_POST['group_id'];
    // Process single group ID
}
```

### Multiple Selection

```php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $groupIds = isset($_POST['group_ids']) ? (array) $_POST['group_ids'] : array();
    $groupIds = array_map('intval', $groupIds);
    // Process multiple group IDs
}
```

## Related Classes

- `icms_form_elements_Select` - Base select element class
- `icms_member_Handler` - Manages user groups
- `icms_member_groupperm_Handler` - Manages group permissions
- `icms_form_Theme` - Form container class

## Security Considerations

- Group IDs are validated as integers
- User permissions are checked before displaying groups
- Group assignments are validated on form submission
- Always sanitize and validate group IDs from user input
- Use prepared statements when storing group assignments

## Common Issues and Solutions

### Issue: Anonymous group not appearing

**Solution**: Set `$include_anon = true` in the constructor

```php
$groupSelect = new icms_form_elements_select_Group(
    'Groups',
    'groups',
    true  // Include anonymous
);
```

### Issue: Multiple selections not working

**Solution**: Set `$multiple = true` and use array for pre-selected values

```php
$groupSelect = new icms_form_elements_select_Group(
    'Groups',
    'groups',
    false,
    array(1, 2),  // Array of group IDs
    5,
    true  // Enable multiple
);
```

### Issue: Pre-selected values not showing

**Solution**: Ensure values match actual group IDs in the database

```php
// Get current group IDs from database
$currentGroups = $user->getGroups();

$groupSelect = new icms_form_elements_select_Group(
    'Groups',
    'groups',
    false,
    $currentGroups  // Use actual group IDs
);
```

