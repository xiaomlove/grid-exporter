export grid data for laravel-admin
======


## Installation

```
$ composer require xiaomlove/grid-exporter -vvv

```

## Configuration

`grid-exporter` supports 1 configuration, open `config/admin.php` find `extensions`:
```php

    'extensions' => [
    
        'grid-exporter' => [
        
            // Set this to false if you want to disable this extension
            'enable' => true,

        ]
    ]

```

## Usage

if this extension is enabled, it will register as the default exporter (replace `Encore\Admin\Grid\Exporters\CsvExporter`)  
if want to format specified column, use it manually. In your controller  
```php
use Chenyulingxi\LaravelAdmin\GridExporter\Exporter;

...

protected function grid()
{
    $grid = new Grid(new Test);

    $grid->id('Id');
    $grid->name('Name');
    $grid->created_at('Created at');
    $grid->updated_at('Updated at');

    $exporter = new Exporter();
    $exporter->format('name', function ($value) {
        return strtolower($value);
    });
    $grid->exporter($exporter);

    return $grid;
}

...
```
**In the format callback closure, `$this` bindTo the eloquent model**

License
------------
Licensed under [The MIT License (MIT)](LICENSE).