<?php

namespace App\Imports;

use App\Models\Module;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ModulesImport implements ToModel, WithHeadingRow
{
    use Importable;
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {

        //get latest battery pack id
        $battery_pack_id = \App\Models\BatteryPack::latest()->first()->id;


        

        return new Module([
            'serial_number' => $row['text'],
            'battery_pack_id' => $battery_pack_id,            
        ]);
    }
}
