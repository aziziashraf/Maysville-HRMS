<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use App\Models\Development;
use App\Models\User;
use App\Models\UserAttendance;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Sheet;

class BuildingExport implements FromCollection, WithHeadings, WithEvents
{

    public function __construct($attendance)
    {
        $this->attendance = $attendance;
    }

    public function collection()
    {
        $attendance = $this->attendance;
        $attendanceArray = explode(",",$attendance);
        
       // dd($attendanceArray);
        $findAttendance = UserAttendance::whereIn('id',$attendanceArray)->get();
        $data = array();
        $index = 1;
        foreach($findAttendance as $a){
        
            $data123 = [
                'num' => $index,
                'scan_date' => $a->scan_date,
                'scan_time' => $a->scan_time,
                'name' => $a->user->name,
                'email' => $a->user->email,
                'contact_no' => $a->user->contact_no,
                'role' => $a->user->role,
                'department_name' => $a->user->department->department_name??'-',
                'access' => $a->access->access_name??'-',
                'activity' => $a->access->activity??'-',
            ];
            array_push($data,$data123);
            $index++;
        }
        
        return collect($data);
    }

    public function headings(): array
    {
        return ["No.","Scan Date", "Scan Time", "Name", "Email", "Phone", "Role", "Department", "Access", "Activity"];
    }

    public function registerEvents(): array
    {
        Sheet::macro('setOrientation', function (Sheet $sheet, $orientation) {
            $sheet->getDelegate()->getPageSetup()->setOrientation($orientation);
        });

        Sheet::macro('setPaperSize', function (Sheet $sheet, $papersize) {
            $sheet->getDelegate()->getPageSetup()->setPaperSize($papersize);
        });

        Sheet::macro('styleCells', function (Sheet $sheet, string $cellRange, array $style) {
            $sheet->getDelegate()->getStyle($cellRange)->applyFromArray($style);
        });

        return [
            AfterSheet::class    => function(AfterSheet $event) {
                $event->sheet->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
                $event->sheet->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);
                // All headers - set font size to 14
                $cellRange = 'A1:W1';
                $event->sheet->getDelegate()->getStyle($cellRange)->getFont()->setSize(12);
                $event->sheet->getDelegate()->getStyle($cellRange)->getFont()->setBold(true);
                $event->sheet->getDelegate()->getColumnDimension('A')->setWidth(7);
                $event->sheet->getDelegate()->getColumnDimension('B')->setWidth(15);
                $event->sheet->getDelegate()->getColumnDimension('C')->setWidth(15);
                $event->sheet->getDelegate()->getColumnDimension('D')->setWidth(15);
                $event->sheet->getDelegate()->getColumnDimension('E')->setWidth(30);
                $event->sheet->getDelegate()->getColumnDimension('F')->setWidth(15);
                $event->sheet->getDelegate()->getColumnDimension('G')->setWidth(15);
                $event->sheet->getDelegate()->getColumnDimension('H')->setWidth(30);
                $event->sheet->getDelegate()->getColumnDimension('I')->setWidth(30);
                $event->sheet->getDelegate()->getColumnDimension('J')->setWidth(15);
                $event->sheet->styleCells(
                'A1:Z1000',
                [
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    ],
                ]
            );
                // Apply array of styles to B2:G8 cell range
                // $styleArray = [
                //     'borders' => [
                //         'outline' => [
                //             'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,
                //             'color' => ['argb' => 'FFFF0000'],
                //         ]
                //     ]
                // ];
                // $event->sheet->getDelegate()->getStyle('B2:G8')->applyFromArray($styleArray);

                // // Set first row to height 20
                // $event->sheet->getDelegate()->getRowDimension(1)->setRowHeight(20);

                // // Set A1:D4 range to wrap text in cells
                // $event->sheet->getDelegate()->getStyle('A1:D4')
                //     ->getAlignment()->setWrapText(true);
            },
        ];
    }
}
