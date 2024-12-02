# SearchTable
[![Latest Version on Packagist](https://img.shields.io/packagist/v/andreazorzi/search-table.svg?style=flat-square)](https://packagist.org/packages/andreazorzi/search-table)
[![Total Downloads](https://img.shields.io/packagist/dt/andreazorzi/search-table.svg?style=flat-square)](https://packagist.org/packages/andreazorzi/search-table)

Add a searchable model table to your view.
The package is based on [Bootstrap 5](https://getbootstrap.com/) for the table UI and [htmx](https://htmx.org/docs/) for all the requests.

## Installation
```bash
composer require andreazorzi/search-table
```

## Getting started
### Controller
Add the `SearchController` trait to any model controller you need to be searchable.
```php
<?php

namespace App\Http\Controllers;

use SearchTable\Traits\SearchController;

class UserController extends Controller
{
    use SearchController;
    
    ...
}
```

### Model
Add the `SearchModel` trait and the $table_fields variable to any model you need to be searchable.
```php
<?php

namespace App\Models;

use SearchTable\Traits\SearchModel;

class User extends Authenticatable
{
    use SearchModel;
    
    // Mandatory
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
    
    // Optional
    public function getTableActions($model_name, $key):array{
        return [
            [
                "attributes" => 'data-id="'.$key.'" hx-get="'.route($model_name.".show", [$key ?? 0]).'" hx-target="#modal .modal-content"',
                "content" => '<i class="table-search-preview fa-solid fa-pen"></i>'
            ]
        ];
    }
    
    ...
}
```

#### Displayed table fields
To select which fields of the table display, which field to sort and customize values and filter, you can edit the `$table_fields` variable.
This variable is an array where the keys are the model attributes and the values define how the attribute should be display.
The order of the attributes in the array will also be used in the table.
The parameter can be used as follow:
- sort: this parameter is used to define which attribute will be used to sort the table, can be set to `asc` or `desc`, normally it will be set only on one attribute, otherwise the table will be sorted according to the attributes order.
At least one attribute must have the sort parameter.
    ```php
    "sort" => "[asc|desc]"
    ```
- filter: set this parameter to `true` to enable the attribute to be used as a filter column.
    ```php
    "filter" => true
    ```
- custom-label: this parameter accept a string to be displayed in the table header, otherwise it will search the attribute label in the `lang/validation.php` file under the `attributes` key.
    ```php
    "cutom-label" => "First Name"
    ```
- custom-value: this parameter is used to display a custom value for an attribute.
The parameter value must be a model's function string name.
    ```php
    // Merging multiple attributes
    "cutom-label" => "getUserFullName"
    
    public function getUserFullName(){
        return $this->fisrt_name." ".$this->last_name;
    }
    
    
    // Data manipulation
    "cutom-label" => "getStatusText"
    
    public function getStatusText(){
        $statuses = ["New", "In Progress", "Finished"];
        return $statuses[$this->status];
    }
    
    
    // Model relationship
    "cutom-label" => "getDepartmentName"
    
    public function getDepartmentName(){
        return $this->department->name;
    }
    ```
- custom-filter: this parameter require a SQL string that will be used to filter the model directly with Eloquent.
The examples below matches the examples above.
    ```php
    // Merged attributes
    "cutom-filter" => "CONCAT(first_name, ' ', last_name)"
    
    // Manipulated data
    "cutom-filter" => "CASE WHEN status = 0 THEN 'New' WHEN status = 1 THEN 'In Progress' ELSE 'Finished' END"
    
    // Model relationship
    "cutom-filter" => "(SELECT d.name FROM departments d WHERE d.id = department_id)"
    ```
- advanced-type: this parameter change the attribute type when parsing the advanced filters, below the available types:
    - date-range (the value of the filter must be a valid date format, "Y-m-d - Y-m-d" or "Y-m-d")
    - in-array (the value of the attribute must be a comma separeted text, useful in combination between custom filter and a many to many relationship)
    - like (match the like "%filte%" confition)
- hidden: set this parameter to `true` to prevent the attribute's column to be displayed, for example sorting the table by model id without display it.
    ```php
    "filter" => true
    ```
## Usage
To include the SearchTable components, you can simply add the following code in your blade.
```html
<x-search-table::table  :model="new App\Models\User()"/>
```
### Additional parameters
- query (optional): initialize the table with a simple filter
- page (optional): initialize the table to the desired page
- disableaddbutton (optional): disable or enable the add button (call the Controller::create function)
- disablesearchbar (optional): disable or enable the search bar
- disabletotalrow (optional): disable or enable the totals row
- showadvancefilters (optional): enable or disable the advanced filters

### Advanced filters
To enable the advanced filters, you have to create a blade under `resources/views/components/search-table-filters/models-filters.blade.php`.
The content of the blade should be like this:
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

<!-- Many to many relationship filter -->
<div class="col-md-4">
    <label>Groups</label>
    <div class="input-group">
        <span class="input-group-text">
            <!-- Filter operators to change the Eloquent filter operator -->
            <select class="advance-filter form-control p-0 border-0 bg-transparent" name="advanced_search[filter_operators][groups]">
                <option value="AND">AND</option>
                <option value="OR">OR</option>
            </select>
        </span>
        <select class="selectize" multiple name="advanced_search[groups][]">
            @foreach (Group::get()->toArray() as $group)
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

## The MIT License (MIT)

Copyright © 2024 Andrea Zorzi <info@zorziandrea.com>

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