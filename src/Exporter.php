<?php

namespace Chenyulingxi\LaravelAdmin\GridExporter;

use Encore\Admin\Grid\Exporters\AbstractExporter;
use Encore\Admin\Grid\Column;
use Maatwebsite\Excel\Facades\Excel;

class Exporter extends AbstractExporter
{
    protected $columnFormatters = [];

    public function format($name, $handler)
    {
        $this->columnFormatters[$name] = $handler;
    }

    public function export()
    {
        $filename = sprintf("%s_%s.xlsx", $this->getTable(), date('YmdHis'));
        if (method_exists($this->grid, 'visibleColumns')) {
            $columns = $this->grid->visibleColumns();
        } else {
            $columns = $this->grid->columns();
        }

        $headings = [];
        foreach ($columns as $index => $column) {
            $headings[$column->getName()] = $column->getLabel();
        }
        unset($index, $column);

        $dataSource = new DataSource();
        $dataSource->setHeadings(array_values($headings));

        $this->chunk(function ($collection) use (&$dataSource, $columns) {
            Column::setOriginalGridModels($collection);
            $data = $collection->toArray();
            $columns->each(function ($column) use (&$data) {
                $data = $column->fill($data);
            });

            foreach ($data as $rowIndex => $row) {
                $formattedRow = [];
                foreach ($columns as $column) {
                    $name = $column->getName();
                    if (isset($row[$name])) {
                        $value = strip_tags($row[$name]);
                        if (isset($this->columnFormatters[$name])) {
                            $handler = $this->columnFormatters[$name];
                            if ($handler instanceof \Closure) {
                                $handler = $handler->bindTo($collection->slice($rowIndex, 1)->first());
                                $value = call_user_func($handler, $value);
                            } elseif (is_scalar($handler)) {
                                $value = $handler;
                            }
                        }
                    } else {
                        $value = null;
                    }
                    $formattedRow[] = $value;
                }
                $dataSource->appendRow($formattedRow);
            }

        });
        Excel::download($dataSource, $filename, \Maatwebsite\Excel\Excel::XLSX)->send();
        exit;
    }
}