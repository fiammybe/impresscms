# ImpressCMS Country Select Form Element Documentation

## Overview

The `icms_form_elements_select_Country` class is a specialized form element that allows users to select a country from a comprehensive list of all ISO 3166 countries. It extends the base `icms_form_elements_Select` class and automatically populates with localized country names based on the current language.

## When to Use

Use the Country Select element when you need to:

- Allow users to select their country
- Collect geographic information in user profiles
- Set country-based preferences or restrictions
- Configure location-based settings
- Integrate with address forms
- Support international user registration
- Store country information in databases
- Implement country-based shipping or billing

## Class Hierarchy

```
icms_form_elements_Select
    └── icms_form_elements_select_Country
```

The Country Select class inherits all functionality from the base Select class while adding specialized country selection capabilities.

## Constructor

### Signature

```php
public function __construct(
    string $caption,
    string $name,
    mixed $value = null,
    int $size = 1
)
```

### Parameters

| Parameter | Type | Default | Description | Example |
|-----------|------|---------|-------------|---------|
| `$caption` | string | - | The label/caption displayed for this form element | `"Select Country"` |
| `$name` | string | - | The HTML "name" attribute for the select element | `"country"` |
| `$value` | mixed | null | Pre-selected country code(s) | `"US"` or `array("US", "CA")` |
| `$size` | int | 1 | Number of visible rows (1 = dropdown) | `1` or `5` |

### Constructor Examples

```php
// Basic country selection (dropdown)
$countrySelect = new icms_form_elements_select_Country('Select Country', 'country');
$form->addElement($countrySelect);

// Country selection with pre-selected value
$countrySelect = new icms_form_elements_select_Country('Country', 'country', 'US');
$form->addElement($countrySelect);

// Country selection with custom size
$countrySelect = new icms_form_elements_select_Country('Country', 'country', null, 5);
$form->addElement($countrySelect);

// Country selection with pre-selected multiple values
$countrySelect = new icms_form_elements_select_Country(
    'Countries',
    'countries',
    array('US', 'CA', 'MX'),
    5
);
$form->addElement($countrySelect);
```

## Static Methods

### getCountryList()

Retrieves a localized list of all countries with their ISO 3166 codes.

```php
public static function getCountryList(): array
```

**Returns:**
- Associative array where keys are 2-letter country codes and values are localized country names

**Language Support:**
- Country names are loaded from language files
- Location: `/language/{language}/countries.php`
- Format: `_COUNTRY_{CODE}` constants (e.g., `_COUNTRY_US`, `_COUNTRY_GB`)
- Automatically sorted alphabetically

**Example:**
```php
// Get all countries
$countries = icms_form_elements_select_Country::getCountryList();
echo count($countries) . " countries available\n";

// Iterate through countries
foreach ($countries as $code => $name) {
    echo "$code: $name\n";
}

// Find specific country
if (isset($countries['US'])) {
    echo "United States: " . $countries['US'];
}

// Use in custom select element
$select = new icms_form_elements_Select('Country', 'country');
$select->addOptionArray(icms_form_elements_select_Country::getCountryList());
```

## ISO 3166 Country Codes

The Country Select element uses ISO 3166-1 alpha-2 standard 2-letter country codes:

### Common Country Codes

| Code | Country | Code | Country |
|------|---------|------|---------|
| US | United States | GB | United Kingdom |
| CA | Canada | AU | Australia |
| DE | Germany | FR | France |
| IT | Italy | ES | Spain |
| JP | Japan | CN | China |
| IN | India | BR | Brazil |
| MX | Mexico | ZA | South Africa |

### Complete List

All 249+ countries and territories are supported, including:
- All UN member states
- Territories and dependencies
- Special administrative regions
- Transitionally reserved codes (commented out)

## Inherited Methods

The Country Select element inherits all methods from `icms_form_elements_Select`, including:

- `addOption()` - Add a single option
- `addOptionArray()` - Add multiple options
- `getValue()` - Get the selected value
- `setValue()` - Set the selected value
- `setSize()` - Set the number of visible rows
- `render()` - Generate HTML markup

See the Select element documentation for details on these inherited methods.

## Common ImpressCMS Patterns

### Pattern 1: User Profile

```php
// In user profile form
$form = new icms_form_Theme('User Profile', 'profile', 'submit.php', 'post');

$countrySelect = new icms_form_elements_select_Country(
    'Country',
    'user_country',
    $user->getVar('country')
);
$form->addElement($countrySelect);

$form->display();
```

### Pattern 2: Address Form

```php
// In address/shipping form
$form = new icms_form_Theme('Shipping Address', 'address', 'checkout.php', 'post');

$countrySelect = new icms_form_elements_select_Country(
    'Shipping Country',
    'shipping_country',
    $currentCountry
);
$form->addElement($countrySelect, true);  // Required field

$form->display();
```

### Pattern 3: Module Preferences

```php
// In module preferences
$form = new icms_form_Theme('Module Settings', 'settings', 'admin.php', 'post');

$countrySelect = new icms_form_elements_select_Country(
    'Default Country',
    'default_country',
    $config['default_country']
);
$form->addElement($countrySelect);

$form->display();
```

### Pattern 4: IPF Object Form

```php
// In IPF object form
$form = new icms_form_Theme('Edit Object', 'form', 'submit.php', 'post');

$countrySelect = new icms_form_elements_select_Country(
    'Country',
    'country',
    $object->getVar('country', 'e')
);
$form->addElement($countrySelect);

$form->display();
```

## HTML Output Example

### Dropdown (size=1)

```html
<select name='country' id='country'>
    <option value=''>-</option>
    <option value='AD'>Andorra</option>
    <option value='AE'>United Arab Emirates</option>
    <option value='AF'>Afghanistan</option>
    ...
    <option value='US'>United States</option>
    <option value='ZW'>Zimbabwe</option>
</select>
```

### List Box (size>1)

```html
<select name='country' id='country' size='5'>
    <option value=''>-</option>
    <option value='AD'>Andorra</option>
    <option value='AE'>United Arab Emirates</option>
    ...
</select>
```

## Best Practices

1. **Always provide a caption**: Use descriptive labels like "Country" or "Country of Residence"
2. **Set pre-selected values**: When editing, always pre-select the existing country
3. **Use ISO codes**: Store and work with 2-letter country codes
4. **Validate on submit**: Always validate the selected country code
5. **Handle empty selection**: Check for empty string when processing form data
6. **Use static method**: Call `getCountryList()` statically when needed
7. **Consider size**: Use size=1 for most cases, larger sizes for special needs

## Form Submission Handling

```php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $country = isset($_POST['country']) ? trim($_POST['country']) : '';
    
    // Validate country code
    if (empty($country)) {
        $error = "Please select a country";
    } else {
        $validCountries = icms_form_elements_select_Country::getCountryList();
        if (!isset($validCountries[$country])) {
            $error = "Invalid country selected";
        } else {
            // Process valid country
            $countryName = $validCountries[$country];
        }
    }
}
```

## Language Files

Country names are defined in language files:

**Location**: `/language/{language}/countries.php`

**Format**:
```php
define('_COUNTRY_US', 'United States');
define('_COUNTRY_GB', 'United Kingdom');
define('_COUNTRY_FR', 'France');
// ... etc
```

**Supported Languages**:
- English (default)
- Simplified Chinese
- Italian
- Dutch
- And many more...

## Related Classes

- `icms_form_elements_Select` - Base select element class
- `icms_form_elements_select_Lang` - Language selection
- `icms_form_elements_select_Timezone` - Timezone selection
- `icms_form_Theme` - Form container class

## Security Considerations

- Country codes are validated as 2-letter strings
- Always validate selected country against the country list
- Sanitize country codes before storing in database
- Use prepared statements when storing country data
- Never trust user input without validation

## Common Issues and Solutions

### Issue: Country names not displaying correctly

**Solution**: Ensure language file is loaded and country constants are defined

```php
icms_loadLanguageFile('core', 'countries');
$countries = icms_form_elements_select_Country::getCountryList();
```

### Issue: Pre-selected country not showing

**Solution**: Ensure country code is uppercase and exists in the list

```php
$countryCode = strtoupper('us');  // Convert to uppercase
$countrySelect = new icms_form_elements_select_Country(
    'Country',
    'country',
    $countryCode  // Must be uppercase
);
```

### Issue: Custom country list needed

**Solution**: Use getCountryList() and modify the array

```php
$countries = icms_form_elements_select_Country::getCountryList();

// Remove specific countries
unset($countries['KP']);  // Remove North Korea

// Add custom entry
$countries['XX'] = 'Custom Country';

$select = new icms_form_elements_Select('Country', 'country');
$select->addOptionArray($countries);
```

## Performance Notes

- Country list is loaded from language files on each instantiation
- Consider caching the country list if used frequently
- Static method allows reuse without creating element instance
- Alphabetical sorting is performed on each call

