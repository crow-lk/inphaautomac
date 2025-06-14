<?php

namespace App\Filament\Resources\ModuleResource\Pages;

use App\Filament\Resources\ModuleResource;
use App\Models\Module;
use Carbon\Carbon;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Pages\Page;
use Filament\Tables\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;

class GradeE extends ListRecords
{
    protected static string $resource = ModuleResource::class;

    protected static string $view = 'filament.resources.module-resource.pages.grade-e';

    protected function getTableQuery(): Builder
    {
        $startDate = Carbon::create(2025, 4, 7);
        return Module::query()->whereBetween('capacitance', [0, 1000])
            ->whereDoesntHave('batteryPack', function ($query) {
                $query->where('name', 'like', 'CINU%'); // Adjust the column name as necessary
            })
            ->where('created_at', '>', $startDate);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('serial_number')
                    ->sortable()
                    ->searchable()
                    ->getStateUsing(function ($record) {
                        $badge = '';

                        if ($record->capacitance < 1500) {
                            $badge = '<span style="color: black; background-color: yellow; padding: 2px 5px; border-radius: 5px;">Solar</span>';
                        } else {
                            $capacitanceRange = 200;
                            $irValueRange = 0.002;

                            $previousRecords = Module::where('serial_number', $record->serial_number)
                                ->orderBy('created_at', 'desc')
                                ->take(2)
                                ->get();

                            if ($previousRecords->count() === 2) {
                                $previousCapacitance = $previousRecords[1]->capacitance;
                                $previousIrValue = $previousRecords[1]->ir_value;

                                if (abs($record->capacitance - $previousCapacitance) <= $capacitanceRange &&
                                    abs($record->ir_value - $previousIrValue) <= $irValueRange) {
                                    $badge .= '<span style="color: green; background-color: lightgreen; padding: 2px 5px; border-radius: 5px;">Good Modules</span>';
                                }
                            }
                        }

                        return $record->serial_number . '<br>' . $badge;
                    })
                    ->html()
                    ->label('Serial Number'),

                Tables\Columns\TextInputColumn::make('ir_value')
                    ->sortable()
                    ->searchable()
                    ->label('IR Value (Ω)')->alignEnd()->type('number')->rules(['regex:/^\d{1,3}$/']),

                Tables\Columns\TextInputColumn::make('capacitance')
                    ->sortable()
                    ->searchable()
                    ->label('Capacitance (mAh)')
                    ->alignEnd()
                    ->rules(['regex:/^\d{1,4}$/']),

                Tables\Columns\TextColumn::make('grade')
                    ->label('Grade')
                    ->getStateUsing(function ($record) {
                        $capacity = $record->capacitance;

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
                    })
                    ->color(fn(string $state): string => match ($state) {
                        'A' => 'success',
                        'B' => 'primary',
                        'C' => 'warning',
                        'D' => 'danger',
                        'E' => 'gray',
                        'N/A' => 'secondary'
                    })->alignCenter(),

                Tables\Columns\TextColumn::make('batteryPack.manufacture_year')
                    ->label('Manufacture Year')
                    ->getStateUsing(function ($record) {
                        $letter = strtoupper(substr($record->serial_number, 3, 1));
                        $baseYear = 1999;
                        $yearOffset = ord($letter) - ord('A');
                        return $baseYear + $yearOffset;
                    })->alignCenter(),

                Tables\Columns\TextColumn::make('batteryPack.name')
                    ->label('Battery Pack')
                    ->sortable(),

                Tables\Columns\CheckboxColumn::make('is_inpha_auto_mac_owned')
                    ->label('Inpha Auto Mac Owned')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created Date')->date(),
            ]);
    }
}


