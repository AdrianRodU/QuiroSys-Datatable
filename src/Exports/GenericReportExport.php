<?php

namespace Esolutions\Datatable\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Exportador genérico reutilizable para cualquier reporte tabular.
 */
class GenericReportExport implements
    FromArray,
    WithHeadings,
    WithStyles,
    WithEvents,
    ShouldAutoSize,
    WithCustomStartCell
{
    protected $data;
    protected $headings;
    protected $title;

    /**
     * @param array $data     Datos de la tabla (array de arrays).
     * @param array $headings Encabezados de columna.
     * @param string $title   Título grande para el reporte.
     */
    public function __construct(array $data, array $headings, string $title = 'Reporte')
    {
        $this->data = $data;
        $this->headings = $headings;
        $this->title = $title;
    }

    public function array(): array
    {
        return $this->data;
    }

    public function headings(): array
    {
        return $this->headings;
    }

    public function startCell(): string
    {
        return 'A2';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $lastCol = $this->getLastColumn();
                $event->sheet->mergeCells("A1:{$lastCol}1");
                $event->sheet->setCellValue("A1", $this->title);

                $event->sheet->getStyle("A1")->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 18,
                        'color' => ['rgb' => '000000']
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);

                $event->sheet->getRowDimension(1)->setRowHeight(30);
            },
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $lastCol = $this->getLastColumn();
        $lastRow = count($this->data) + 2;

        $sheet->getStyle("A2:{$lastCol}2")->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'ffffff']
            ],
            'fill' => [
                'fillType' => 'solid',
                'startColor' => ['rgb' => '1976d2']
            ],
        ]);

        $sheet->getStyle("A2:{$lastCol}{$lastRow}")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => 'thin',
                    'color' => ['rgb' => 'e0e0e0']
                ]
            ]
        ]);

        $sheet->getStyle("A3:{$lastCol}{$lastRow}")
            ->getAlignment()
            ->setWrapText(true)
            ->setVertical(Alignment::VERTICAL_CENTER);
    }

    private function getLastColumn(): string
    {
        $lastColIndex = count($this->headings);
        return Coordinate::stringFromColumnIndex($lastColIndex);
    }
}
