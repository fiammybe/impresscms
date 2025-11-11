# ImpressCMS Image Select Form Element Documentation

## Overview

The `icms_form_elements_select_Image` class is a specialized form element that allows users to select images from the ImpressCMS image library. It extends the base `icms_form_elements_Select` class and provides integrated image management features including live preview, category organization, and image browser integration.

## When to Use

Use the Image Select element when you need to:

- Allow users to select images from the ImpressCMS image library
- Display images organized by category
- Provide a live preview of the selected image
- Integrate with module preferences or configuration forms
- Respect user permissions for image access
- Support both database-stored and filesystem-stored images

## Class Hierarchy

```
icms_form_elements_Select
    └── icms_form_elements_select_Image
```

The Image Select class inherits all functionality from the base Select class while adding specialized image selection capabilities.

## Constructor

### Signature

```php
public function __construct(
    string $caption,
    string $name,
    mixed $value = NULL,
    mixed $cat = NULL
)
```

### Parameters

| Parameter | Type | Description | Example |
|-----------|------|-------------|---------|
| `$caption` | string | The label/caption displayed for this form element | `"Select Logo"` |
| `$name` | string | The HTML "name" attribute for the select element | `"logo_image"` |
| `$value` | mixed | Pre-selected image path or ID (optional) | `"/images/icons/default.png"` or `42` |
| `$cat` | mixed | Filter images by category (optional) | `5` or `array(1, 3, 5)` |

### Constructor Examples

```php
// Basic image selection with all categories
$imageSelect = new icms_form_elements_select_Image('Select Logo', 'logo_image');
$form->addElement($imageSelect);

// Image selection for specific category
$imageSelect = new icms_form_elements_select_Image('Select Banner', 'banner_image', null, 5);
$form->addElement($imageSelect);

// Image selection with pre-selected value
$imageSelect = new icms_form_elements_select_Image('Select Icon', 'icon_image', '/images/icons/default.png');
$form->addElement($imageSelect);

// Image selection filtered to multiple categories
$imageSelect = new icms_form_elements_select_Image('Select Image', 'image', null, array(1, 3, 5));
$form->addElement($imageSelect);
```

## Public Methods

### addOptGroup()

Adds a single optgroup (option group) to the select element for organizing images by category.

```php
public function addOptGroup(array $value = array(), string $name = "&nbsp;"): void
```

**Parameters:**
- `$value` (array): Array of image options where keys are image paths/IDs and values are display names
- `$name` (string): The label for the optgroup (category name)

**Example:**
```php
$select = new icms_form_elements_select_Image('Image', 'image');
$icons = array(
    '/images/icons/star.png' => 'Star Icon',
    '/images/icons/heart.png' => 'Heart Icon'
);
$select->addOptGroup($icons, 'Icons');
```

### addOptGroupArray()

Adds multiple optgroups at once. This is typically called automatically during construction.

```php
public function addOptGroupArray(array $options): void
```

**Parameters:**
- `$options` (array): Associative array where keys are category names and values are image option arrays

**Example:**
```php
$select = new icms_form_elements_select_Image('Image', 'image');
$categories = array(
    'Icons' => array(
        '/images/icons/star.png' => 'Star',
        '/images/icons/heart.png' => 'Heart'
    ),
    'Banners' => array(
        '/images/banners/header.jpg' => 'Header Banner'
    )
);
$select->addOptGroupArray($categories);
```

### getImageList()

Retrieves images from the image library organized by category, respecting user permissions.

```php
public function getImageList(mixed $cat = NULL): array
```

**Parameters:**
- `$cat` (mixed): Filter by category - null (all), int (single), or array (multiple)

**Returns:**
- Array organized by category name, containing image paths and display names

**Permission Handling:**
- Respects user group permissions
- Only shows images with 'imgcat_read' permission
- Only includes images with 'image_display' flag set to 1
- Handles both database and filesystem storage types

**Example:**
```php
// Get all images from all categories
$images = $select->getImageList();

// Get images from specific category
$images = $select->getImageList(5);

// Get images from multiple categories
$images = $select->getImageList(array(1, 3, 5));
```

### getOptGroups()

Returns the array of optgroups that have been added to this select element.

```php
public function getOptGroups(): array
```

**Returns:**
- Associative array where keys are category names and values are image option arrays

**Example:**
```php
$select = new icms_form_elements_select_Image('Image', 'image');
$optgroups = $select->getOptGroups();
foreach ($optgroups as $categoryName => $images) {
    echo "Category: $categoryName\n";
    foreach ($images as $path => $name) {
        echo "  - $name ($path)\n";
    }
}
```

### getOptGroupsID()

Returns the mapping of category names to their database IDs.

```php
public function getOptGroupsID(): array
```

**Returns:**
- Associative array where keys are category names and values are category database IDs

**Example:**
```php
$select = new icms_form_elements_select_Image('Image', 'image');
$optgroupIds = $select->getOptGroupsID();
foreach ($optgroupIds as $categoryName => $categoryId) {
    echo "Category '$categoryName' has ID: $categoryId\n";
}
```

### render()

Generates the complete HTML markup for the image select element.

```php
public function render(): string
```

**Returns:**
- Complete HTML markup including select dropdown, preview image, and add button

**Generated HTML Includes:**
1. Select dropdown with optgroups for each category
2. JavaScript onchange event for live preview
3. Image preview display area
4. "Add Image" button (if user has write permissions)

**Example:**
```php
$select = new icms_form_elements_select_Image('Logo', 'logo');
echo $select->render();
// Outputs: <select...>...</select><input type="button"...><img...>
```

## Common ImpressCMS Patterns

### Pattern 1: Module Preferences

```php
// In module preferences form
$form = new icms_form_Theme('Module Settings', 'settings', 'admin.php', 'post');

$logoSelect = new icms_form_elements_select_Image(
    'Module Logo',
    'module_logo',
    $config['module_logo']
);
$form->addElement($logoSelect, true);

$form->display();
```

### Pattern 2: Category-Specific Images

```php
// Select banner for specific category
$bannerSelect = new icms_form_elements_select_Image(
    'Category Banner',
    'category_banner',
    null,
    3  // Only images from category ID 3
);
$form->addElement($bannerSelect);
```

### Pattern 3: Multiple Category Selection

```php
// Select from multiple categories
$imageSelect = new icms_form_elements_select_Image(
    'Featured Image',
    'featured_image',
    null,
    array(1, 2, 3)  // Images from categories 1, 2, and 3
);
$form->addElement($imageSelect);
```

### Pattern 4: Pre-Selected Image

```php
// Edit form with existing image
$existingImage = $object->getVar('image_path');
$imageSelect = new icms_form_elements_select_Image(
    'Select Image',
    'image_path',
    $existingImage  // Pre-select existing image
);
$form->addElement($imageSelect);
```

## Image Storage Types

### Database Storage

Images stored in the database are accessed via:
```
/image.php?id={image_id}
```

### Filesystem Storage

Images stored on the filesystem are accessed via relative paths:
```
/images/category_name/image_name.jpg
```

The class automatically handles both storage types based on the category's storage type setting.

## Permission System

The Image Select element respects ImpressCMS permission system:

- **Read Permission**: `imgcat_read` - Controls which images are visible
- **Write Permission**: `imgcat_write` - Controls whether "Add Image" button appears
- **User Groups**: Permissions are checked against user's group memberships
- **Anonymous Users**: See only images accessible to anonymous group

## HTML Output Example

```html
<select onchange='if(this.options[this.selectedIndex].value != ""){
    document.getElementById("logo_image_img").src="http://site.com/"+this.options[this.selectedIndex].value;
}else{
    document.getElementById("logo_image_img").src="http://site.com/images/blank.gif";
}' size='1' name='logo_image' id='logo_image'>
    <option value=''>-- Select --</option>
    <optgroup id="img_cat_1" label="Icons">
        <option value='/images/icons/star.png'>Star Icon</option>
        <option value='/images/icons/heart.png'>Heart Icon</option>
    </optgroup>
    <optgroup id="img_cat_2" label="Banners">
        <option value='/images/banners/header.jpg'>Header Banner</option>
    </optgroup>
</select>
<input type='button' value='Add Image' onclick="window.open(...)">
<br />
<img id='logo_image_img' src='http://site.com/images/blank.gif'>
```

## Best Practices

1. **Always provide a caption**: Use descriptive captions for better UX
2. **Use category filtering**: Filter to relevant categories to reduce clutter
3. **Set pre-selected values**: When editing, always pre-select existing images
4. **Check permissions**: Verify user has appropriate permissions before rendering
5. **Validate on submit**: Always validate the selected image path on form submission
6. **Handle missing images**: Gracefully handle cases where selected image no longer exists

## Related Classes

- `icms_form_elements_Select` - Base select element class
- `icms_image_Handler` - Manages image objects
- `icms_image_category_Handler` - Manages image categories
- `icms_form_Theme` - Form container class

## Security Considerations

- Image paths are HTML-escaped in the select options
- User permissions are always checked before displaying images
- The image browser popup is only available to users with write permissions
- Image paths are validated before being used in HTML output

