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
if you want to format specified column, use it manually. In your controller  
```php
use Chenyulingxi\LaravelAdmin\GridExporter\Exporter;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Events\BeforeExport;

...

protected function grid()
{
    $grid = new Grid(new Test);

    $grid->id('Id');
    $grid->name('Name');
    $grid->created_at('Created at');
    $grid->updated_at('Updated at');

    $exporter = new Exporter();
    
    //format the name column
    $exporter->format('name', function ($value) {
        //In the format callback closure, $this bindTo the eloquent model
        return strtolower($value); 
    });
    
    // replace the grid table header
    $exporter->withHeadings([
        'id' => '编号',
        'name' => '姓名',
        'created_at' => '创建时间',
        'updated_at' => '更新时间',
    ]);
    
    //change output file (xlsx) style
    $exporter->setEvents([
        BeforeExport::class  => function(BeforeExport $event) {
            $event->writer->getDelegate()->getProperties()->setCreator('xiaomlove');
        },
        AfterSheet::class => function ($event) {
            $sheet = $event->sheet;
            $highestColumn = $sheet->getHighestColumn();
            $highestRow = $sheet->getHighestRow();
            $styles = [
                'font' => [
                    'bold' => true,
                ],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_GRADIENT_LINEAR,
                    'rotation' => 90,
                    'startColor' => [
                        'argb' => 'FFA0A0A0',
                    ],
                    'endColor' => [
                        'argb' => 'FFFFFFFF',
                    ],
                ]
            ];
            $sheet->getStyle("A{$highestRow}:{$highestColumn}{$highestRow}")->applyFromArray($styles);
        }
    ]);
    
    // set write type, default xlsx
    $exporter->setWriteType(\Maatwebsite\Excel\Excel::CSV);
    
    // set the file name
    $exporter->setFileName('test-export');
    
    $grid->exporter($exporter);

    return $grid;
}

...
```

if you want more control over the output file, you can create a class that extents from `Chenyulingxi\LaravelAdmin\GridExporter\DataSource`, then inject it's instance to the exporter like this:

```php
$exporter->setDataSource(new TestDataSource());
```

more information reference to [Laravel Excel](https://docs.laravel-excel.com/3.1/exports/extending.html) and [PhpSpreadsheet](https://phpspreadsheet.readthedocs.io/en/latest/topics/recipes/#styles)

License
------------
Licensed under [The MIT License (MIT)](LICENSE).