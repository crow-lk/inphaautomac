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
    public function __construct(public Collection $records)
    {
    }
    
    use Exportable;
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
        ];
    }

    public function map($module): array
    {
        return [
            $module->serial_number,
            $module->ir_value,
            $module->capacitance,
        ];
    }
}
