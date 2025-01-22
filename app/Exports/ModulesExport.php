<?php

namespace App\Exports;

use App\Models\Module;
use Illuminate\Database\Eloquent\Collection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ModulesExport implements FromCollection, WithHeadings, WithMapping
{
    use Exportable;

    public function __construct(public Collection $records)
    {
    }
    
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return $this->records;
    }

    public function headings(): array
    {
        return [
            'Serial Number',
            'IR Value',
            'Capacitance',
            'Grade', // Add Grade column
        ];
    }

    public function map($module): array
    {
        return [
            $module->serial_number,
            $module->ir_value,
            $module->capacitance,
            $this->calculateGrade($module->capacitance), // Add Grade value
        ];
    }

    private function calculateGrade($capacity): string
    {
        if (is_null($capacity)) {
            return 'N/A';
        } elseif ($capacity >= 4000 && $capacity <= 6000) {
            return 'A';
        } elseif ($capacity >= 3000 && $capacity < 4000) {
            return 'B';
        } elseif ($capacity >= 2000 && $capacity < 3000) {
            return 'C';
        } elseif ($capacity >= 1000 && $capacity < 2000) {
            return 'D';
        } else {
            return 'E';
        }
    }
}
