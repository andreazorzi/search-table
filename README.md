# SearchTable
[![Latest Version on Packagist](https://img.shields.io/packagist/v/andreazorzi/search-table.svg?style=flat-square)](https://packagist.org/packages/andreazorzi/search-table)
[![Total Downloads](https://img.shields.io/packagist/dt/andreazorzi/search-table.svg?style=flat-square)](https://packagist.org/packages/andreazorzi/search-table)

A Laravel package that adds dynamic data tables to Eloquent models with filtering, sorting, and customizable action buttons. Built with Bootstrap 5 and htmx for seamless, modern web applications.

## What This Package Does

This package transforms your Eloquent models into powerful, interactive data tables with:

- **Dynamic Data Tables** - Automatically generate tables from your models
- **Advanced Filtering** - Search and filter data with multiple criteria
- **Sorting Capabilities** - Sort by any column with ascending/descending options
- **Customizable Actions** - Add custom action buttons for each row
- **Real-time Updates** - htmx-powered interactions without page reloads
- **Bootstrap 5 Styling** - Modern, responsive UI components

All tables are fully responsive and provide a seamless user experience with instant filtering and sorting.

## Requirements

### Frontend Dependencies
This package assumes you have the following assets available in your project:
- [Bootstrap 5](https://getbootstrap.com/) - for styling the generated tables and UI components
- [htmx](https://htmx.org/) - for handling AJAX requests and seamless interactions

## Installation

1) ### Install the package:
    ```bash
    composer require andreazorzi/search-table
    ```

2) ### Install frontend dependencies (if not already installed):
    ```bash
    npm install bootstrap@5 htmx.org
    ```

## Setup
### Controller Configuration
Add the `SearchController` trait to any model controller you need to be searchable:
```php
<?php

namespace App\Http\Controllers;

use SearchTable\Traits\SearchController;

class UserController extends Controller
{
    use SearchController;
    
    // Your existing controller methods...
}
```

### Model Configuration
Add the `SearchModel` trait and configure the `$table_fields` variable in your model:
```php
<?php

namespace App\Models;

use SearchTable\Traits\SearchModel;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use SearchModel;
    
    // Mandatory: Define which fields to display and their behavior
    protected static $table_fields = [
        "username" => [
            "filter" => true,
            "sort" => "asc"
        ],
        "name" => [
            "filter" => true,
        ],
        "email" => [
            "filter" => true,
        ]
    ];
    
    // Optional: Define custom actions for each row
    public function getTableActions($model_name, $key): array
    {
        return [
            [
                "attributes" => 'data-id="'.$key.'" hx-get="'.route($model_name.".show", [$key ?? 0]).'" hx-target="#modal .modal-content"',
                "content" => '<i class="table-search-preview fa-solid fa-pen"></i>'
            ]
        ];
    }
}
```

## Usage

### Basic Implementation
Include the SearchTable component in your Blade views:
```html
<x-search-table::table :model="new App\Models\User()"/>
```

### Component Parameters

| Parameter | Type | Description |
| --------- | ---- | ----------- |
| `query` | string | Initialize the table with a simple filter (non persistent) |
| `advanced` | array | Initialize the table with advanced filters (non persistent) |
| `modelfilter` | array | Initialize the table with persistent filters |
| `page` | integer | Initialize the table to the desired page |
| `limit` | integer | Set the table entries limit |
| `size` | integer | Set the table size (Bootstrap column size) |
| `addRedirect` | string | Add a redirect link on the add button |
| `disableaddbutton` | boolean | Disable or enable the add button |
| `disablesearchbar` | boolean | Disable or enable the search bar |
| `disabletotalrow` | boolean | Disable or enable the totals row |
| `showadvancefilters` | boolean | Enable or disable the advanced filters |
| `fit` | boolean | Enable or disable the column fit style |

### Example with Parameters

## Configuration

### Table Fields Configuration
The `$table_fields` array defines how each model attribute should be displayed and behave in the table:

#### Basic Parameters
| Parameter | Description | Example |
| --------- | ---- | ----------- |
| `sort` | Default sort direction (`asc` or `desc`) | `"sort" => "asc"` |
| `filter` | Enable filtering for this column | `"filter" => true` |
| `hidden` | Hide column but keep it sortable | `"hidden" => true` |

#### Customization Parameters
| Parameter | Description | Example |
| --------- | ---- | ----------- |
| `custom-label` | Custom header label | `"custom-label" => "Full Name"` |
| `custom-value` | Method name for custom value display | `"custom-value" => "getFullName"` |
| `custom-filter` | SQL for custom filtering | `"custom-filter" => "CONCAT(first_name, ' ', last_name)"` |
| `advanced-type` | Define the custom filter evaluating method | `"custom-filter" => "CONCAT(first_name, ' ', last_name)"` |


##### Custom filters attribute example
```php
// Merged attributes
"custom-filter" => "CONCAT(first_name, ' ', last_name)"

// Manipulated data
"custom-filter" => "CASE WHEN status = 0 THEN 'New' WHEN status = 1 THEN 'In Progress' ELSE 'Finished' END"

// Model relationship
"custom-filter" => "(SELECT d.name FROM departments d WHERE d.id = department_id)"

// Model multiple relationship (Advanced Type: in-array)
"custom-filter" => "(SELECT GROUP_CONCAT(t.name SEPARATOR ', ') FROM tags t join user_tag ut on ut.tag_id = t.id WHERE ut.user_username = users.username)"
```

#### Advanced Filter Types
| Type | Description | Usage |
| --------- | ---- | ----------- |
| `date-range` | Date range filtering | `"advanced-type" => "date-range"` |
| `in-array` | Comma-separated values | `"advanced-type" => "in-array"` |
| `like` | LIKE pattern matching | `"advanced-type" => "like"` |

#### Example: Complete Field Configuration
```php
protected static $table_fields = [
    "id" => [
        "sort" => "desc",
        "hidden" => true
    ],
    "full_name" => [
        "filter" => true,
        "custom-label" => "Full Name",
        "custom-value" => "getFullName",
        "custom-filter" => "CONCAT(first_name, ' ', last_name)"
    ],
    "email" => [
        "filter" => true,
    ],
    "created_at" => [
        "filter" => true,
        "advanced-type" => "date-range",
        "custom-label" => "Registration Date"
    ],
    "department" => [
        "filter" => true,
        "custom-value" => "getDepartmentName",
        "custom-filter" => "(SELECT d.name FROM departments d WHERE d.id = department_id)"
    ],
    "enabled" => [
        "filter" => true,
        "custom-label" => "Status"
        "custom-value" => "getStatus",
    ]
];

// Custom value methods
public function getFullName(){
    return $this->first_name . ' ' . $this->last_name;
}

public function getDepartmentName(){
    return $this->department->name;
}

public function getStatus(){
    return $this->enabled ? "Enabled" : "Disabled";
}
```

## Advanced Features

### Advanced Filters
Create custom advanced filters by adding a Blade file at:
`resources/views/components/search-table-filters/[model-name]-filters.blade.php`

The example below use [Selectize](https://selectize.dev/) for manage the multiple select.
```html
@use(App\Models\User)

<div class="col-md-4">
    <label>Status</label>
    <select class="selectize" multiple name="advanced_search[enabled][]">
        @foreach (["Disabled", "Enabled"] as $key => $value)
            <option value="{{$key}}">{{$value}}</option>
        @endforeach
    </select>
</div>

<div class="col-md-4">
    <label>Type</label>
    <select class="selectize" multiple name="advanced_search[type][]">
        @foreach (User::groupBy("type")->pluck("type")->toArray() as $type)
            <option>{{$type}}</option>
        @endforeach
    </select>
</div>

<!-- Many-to-many relationship filter with operators -->
<div class="col-md-4">
    <label>Groups</label>
    <div class="input-group">
        <span class="input-group-text">
            <select class="advance-filter form-control p-0 border-0 bg-transparent" name="advanced_search[filter_operators][groups]">
                <option value="AND">AND</option>
                <option value="OR">OR</option>
            </select>
        </span>
        <select class="selectize" multiple name="advanced_search[groups][]">
            @foreach (Group::get() as $group)
                <option value="{{$group->id}}">{{$group->name}}</option>
            @endforeach
        </select>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        $(".selectize").selectize({
            plugins: ["remove_button"],
            onChange: function(value) {
                $("#page").val(1);
                htmx.trigger("#page", "change");
            },
            onDropdownOpen: function() {
                for (const select of $(".selectize.selectized")) {
                    if(select !== this.$input[0]){
                        select.selectize.close();
                    }
                }
            }
        });
    });
</script>
```

### Custom Action Buttons
Define custom actions for each table row:

```php
public function getTableActions($model_name, $key): array{
    return [
        [
            "attributes" => 'hx-get="'.route($model_name.".edit", $key).'" hx-target="#modal .modal-content"',
            "content" => '<i class="fa-solid fa-pen"></i> Edit'
        ],
        [
            "attributes" => 'hx-delete="'.route($model_name.".destroy", $key).'" hx-confirm="Are you sure?"',
            "content" => '<i class="fa-solid fa-trash text-danger"></i> Delete'
        ]
    ];
}
```

## Excample
### Simple User Table
```php
// Model
protected static $table_fields = [
    "name" => ["filter" => true, "sort" => "asc"],
    "email" => ["filter" => true],
    "created_at" => ["custom-label" => "Joined"]
];

// Blade
<x-search-table::table :model="new App\Models\User()"/>
```

### Advanced Product Table with Relationships
```php
// Model
protected static $table_fields = [
    "name" => ["filter" => true, "sort" => "asc"],
    "category" => [
        "filter" => true,
        "custom-value" => "getCategoryName",
        "custom-filter" => "(SELECT c.name FROM categories c WHERE c.id = category_id)"
    ],
    "price" => ["sort" => "desc"],
    "tags" => [
        "filter" => true,
        "custom-value" => "getTagsText",
        "advanced-type" => "in-array"
    ]
];

// Blade with advanced filters
<x-search-table::table 
    :model="new App\Models\Product()" 
    :showadvancefilters="true"
    :disableaddbutton="false"
/>
```

## The MIT License (MIT)

Copyright © 2025 Andrea Zorzi <info@zorziandrea.com>

Permission is hereby granted, free of charge, to any person
obtaining a copy of this software and associated documentation
files (the “Software”), to deal in the Software without
restriction, including without limitation the rights to use,
copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the
Software is furnished to do so, subject to the following
conditions:

The above copyright notice and this permission notice shall be
included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED “AS IS”, WITHOUT WARRANTY OF ANY KIND,
EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
OTHER DEALINGS IN THE SOFTWARE.
