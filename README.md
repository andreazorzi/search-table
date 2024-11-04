# SearchTable
Add a searchable model table to your view. All the request are made via [htmx](https://htmx.org/docs/).

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