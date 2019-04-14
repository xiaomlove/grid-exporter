<?php

namespace Chenyulingxi\LaravelAdmin\GridExporter;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style;

class DataSource implements FromArray, WithHeadings, ShouldAutoSize, WithStrictNullComparison, WithEvents
{
    protected $rows = [];

    protected $headings = [];

    public function setRows(array $rows)
    {
        $this->rows = $rows;
    }

    public function setHeadings(array $headings)
    {
        $this->headings = $headings;
    }

    public function appendRow(array $row)
    {
        $this->rows[] = $row;
    }

    public function array(): array
    {
        return $this->rows;
    }

    public function headings(): array
    {
        return $this->headings;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet;
                $highestColumn = $sheet->getHighestColumn();
                $highestRow = $sheet->getHighestRow();
                $styles = [
                    'alignment' => [
                        'horizontal' => Style\Alignment::HORIZONTAL_LEFT,
                    ]
                ];
                $sheet->getStyle("A1:{$highestColumn}{$highestRow}")->applyFromArray($styles);
                if (!empty($this->headings)) {
                    $styles = [
                        'font' => [
                            'bold' => true,
                        ]
                    ];
                    $sheet->getStyle("A1:{$highestColumn}1")->applyFromArray($styles);
                }
            },
        ];
    }

}