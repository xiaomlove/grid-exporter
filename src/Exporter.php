<?php

namespace Chenyulingxi\LaravelAdmin\GridExporter;

use Encore\Admin\Grid\Exporters\AbstractExporter;
use Encore\Admin\Grid\Column;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Helpers\FileTypeDetector;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class Exporter extends AbstractExporter
{
    protected $columnFormatters = [];

    protected $fileName;

    protected $writeType = \Maatwebsite\Excel\Excel::XLSX;

    protected $headings = true;

    protected $events = [];

    public function format($name, $handler)
    {
        $this->columnFormatters[$name] = $handler;
    }

    public function setFileName($fileName)
    {
        $this->fileName = $fileName;
    }

    public function setWriteType($writeType)
    {
        $this->writeType = $writeType;
    }

    public function setEvents(array $events)
    {
        $this->events = $events;
    }

    public function withHeadings($headings)
    {
        if (is_bool($headings)) {
            // include headings or not
            $this->headings = $headings;
        } elseif (is_array($headings)) {
            // set the headings
            if (!Arr::isAssoc($headings)) {
                throw \InvalidArgumentException("'headings' should be an associative array.");
            }
            $this->headings = $headings;
        } else {
            throw \InvalidArgumentException("'headings' expect boolean or an associative array.");
        }

    }

    private function getHeadings(Collection $columns)
    {
        if (is_array($this->headings)) {
            return $this->headings;
        }
        $headings = [];
        if ($this->headings) {
            foreach ($columns as $index => $column) {
                $headings[] = $column->getLabel();
            }
        }
        return $headings;
    }

    private function getFileName()
    {
        if ($this->fileName) {
            return $this->fileName;
        }
        return sprintf("%s_%s", $this->getTable(), date('YmdHis'));
    }

    private function getFileExtension()
    {
        $writerType = FileTypeDetector::detectStrict($this->getFileName(), $this->writeType);
        if (stripos($writerType, 'pdf') !== false) {
            return 'pdf';
        }
        $extensionsMap = config('excel.extension_detector');
        return array_search($writerType, $extensionsMap);
    }


    public function export()
    {
        $fileName = $this->getFileName() . "." . $this->getFileExtension();
        if (method_exists($this->grid, 'visibleColumns')) {
            $columns = $this->grid->visibleColumns();
        } else {
            $columns = $this->grid->columns();
        }
        $headings = $this->getHeadings($columns);
        $dataSource = new DataSource();
        $dataSource->setHeadings(array_values($headings));
        $dataSource->setEvents($this->events);

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
                    $value = Arr::get($row, $name);
                    $value = strip_tags($value);
                    if (isset($this->columnFormatters[$name])) {
                        $handler = $this->columnFormatters[$name];
                        if ($handler instanceof \Closure) {
                            $handler = $handler->bindTo($collection->slice($rowIndex, 1)->first());
                            $value = call_user_func($handler, $value);
                        } elseif (is_scalar($handler)) {
                            $value = $handler;
                        }
                    }
                    $formattedRow[] = $value;
                }
                $dataSource->appendRow($formattedRow);
            }

        });
        Excel::download($dataSource, $fileName, $this->writeType)->send();
        exit;
    }

}