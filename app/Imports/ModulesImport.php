<?php

namespace App\Imports;

use App\Models\Module;
use App\Models\BatteryPack; // Make sure to import the BatteryPack model
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ModulesImport implements ToModel, WithHeadingRow
{
    use Importable;

    protected $moduleType;

    public function __construct($moduleType)
    {
        $this->moduleType = $moduleType;
    }

    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        // Get the latest battery pack based on the selected module type
        $batteryPack = BatteryPack::where('name', 'like', $this->moduleType . '%')
            ->latest()
            ->first();

        // Check if battery pack is found
        if (!$batteryPack) {
            // Handle the case where no battery pack is found (optional)
            return null; // or throw an exception, or log an error
        }

        // Create a new Module instance
        $module = new Module([
            'serial_number' => $row['text'],
            'battery_pack_id' => $batteryPack->id,
        ]);

        // If the module type is CINU, get ir and capacitance from the latest module related to the battery pack
        if ($this->moduleType === 'CINU') {
            // Retrieve the latest module associated with the battery pack
            $relatedModule = Module::where('serial_number', $row['text'])
                ->latest()
                ->first();

            // Check if a related module is found
            if ($relatedModule) {
                $module->ir_value = $relatedModule->ir_value; // Assuming 'ir' is a column in the Module model
                $module->capacitance = $relatedModule->capacitance; // Assuming 'capacitance' is a column in the Module model
            }
        }

        return $module;
    }
}
