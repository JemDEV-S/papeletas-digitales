<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ReportExport implements FromArray, WithHeadings, WithStyles, WithTitle
{
    private $data;
    private $reportType;
    
    public function __construct(array $data, string $reportType)
    {
        $this->data = $data;
        $this->reportType = $reportType;
    }

    public function array(): array
    {
        return $this->data;
    }

    public function headings(): array
    {
        if (empty($this->data)) {
            return [];
        }

        return array_keys($this->data[0]);
    }

    public function title(): string
    {
        $titles = [
            'requests_by_status' => 'Solicitudes por Estado',
            'requests_by_type' => 'Solicitudes por Tipo',
            'requests_by_department' => 'Solicitudes por Departamento',
            'absenteeism' => 'Reporte de Ausentismo',
            'active_employees' => 'Empleados Activos',
            'supervisor_performance' => 'Rendimiento Supervisores'
        ];

        return $titles[$this->reportType] ?? 'Reporte';
    }

    public function styles(Worksheet $sheet)
    {
        // Style headers
        $sheet->getStyle('1:1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF']
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4F46E5']
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ]
        ]);

        // Auto-size columns
        foreach (range('A', $sheet->getHighestDataColumn()) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Set row height for header
        $sheet->getRowDimension('1')->setRowHeight(20);

        // Apply borders to all data
        $highestRow = $sheet->getHighestRow();
        $highestCol = $sheet->getHighestDataColumn();
        
        $sheet->getStyle("A1:{$highestCol}{$highestRow}")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['rgb' => 'CCCCCC']
                ]
            ]
        ]);

        return [];
    }
}