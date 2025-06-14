<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ModuleResource\Pages;
use App\Filament\Resources\ModuleResource\RelationManagers;
use App\Models\Module;
use Closure;
use Filament\Forms;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Form;
use Filament\Notifications\Collection;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Collection as SupportCollection;
use Maatwebsite\Excel\Facades\Excel;

class ModuleResource extends Resource
{
    protected static ?string $model = Module::class;

    protected static ?string $navigationIcon = 'heroicon-o-bolt';

    protected static ?string $navigationGroup = 'Inpha BMS';

    protected static ?int $navigationSort = 100;

    public static function form(Form $form): Form
    {
        // Fetch existing serial numbers from the database
        $serialNumbers = Module::pluck('serial_number')->toArray(); // Replace YourModel with the actual model name

        return $form
            ->schema([
                Forms\Components\TextInput::make('serial_number')
                    ->required()
                    ->autocomplete('off')
                    ->datalist($serialNumbers), // Pass the fetched serial numbers here
                Forms\Components\TextInput::make('ir_value')->required()->nullable()->numeric(),
                Forms\Components\TextInput::make('capacitance')->required()->nullable()->numeric(),
                Forms\Components\Select::make('battery_pack_id')->relationship('batteryPack', 'name')->required()->searchable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('serial_number')
                    ->sortable()
                    ->searchable()
                    ->getStateUsing(function ($record) {
                        $badge = '';

                        // Check if capacitance is below 1500 to display the badge
                        if ($record->capacitance < 1500) {
                            $badge = '<span style="color: black; background-color: yellow; padding: 2px 5px; border-radius: 5px;">Solar</span>';
                        } else {
                            // Define the ranges for capacitance and IR value
                            $capacitanceRange = 100; // Change this value as needed
                            $irValueRange = 0.001; // Change this value as needed

                            // Fetch the last two records for comparison
                            $previousRecords = Module::where('serial_number', $record->serial_number)
                                ->orderBy('created_at', 'desc')
                                ->take(2)
                                ->get();

                            // Check if we have at least two previous records
                            if ($previousRecords->count() === 2) {
                                $previousCapacitance = $previousRecords[1]->capacitance;
                                $previousIrValue = $previousRecords[1]->ir_value;

                                // Check if the capacitance and IR value have not changed more than the defined ranges
                                if (abs($record->capacitance - $previousCapacitance) <= $capacitanceRange &&
                                    abs($record->ir_value - $previousIrValue) <= $irValueRange) {
                                    $badge .= '<span style="color: green; background-color: lightgreen; padding: 2px 5px; border-radius: 5px;">Good Modules</span>';
                                }
                            }
                        }

                        return $record->serial_number . '<br>' . $badge; // Append badge to serial number
                    })
                    ->html() // Ensure HTML is rendered
                    ->label('Serial Number'),

                Tables\Columns\TextColumn::make('ir_value')
                    ->sortable()
                    ->searchable()
                    ->label('IR Value (Ω)')
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('capacitance')
                    ->sortable()
                    ->searchable()
                    ->label('Capacitance (mAh)')
                    ->alignCenter(),
                    

                //grade the battery pack based on the capacitance

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
                        'A' => 'success',    // Green color for A grade
                        'B' => 'primary',    // Blue color for B grade
                        'C' => 'warning',    // Yellow color for C grade
                        'D' => 'danger',     // Red color for D grade
                        'E' => 'gray',       // Gray color for E grade
                        'N/A' => 'secondary' // Light gray for N/A
                    })->alignCenter(),

                //4th letter of the serial number from A-Z. equal to the battery pack manufacture year A=1999, B=2000, etc
                Tables\Columns\TextColumn::make('batteryPack.manufacture_year')
                    ->label('Manufacture Year')
                    ->getStateUsing(function ($record) {
                        $letter = strtoupper(substr($record->serial_number, 3, 1)); // Get the fourth letter
                        $baseYear = 1999; // Base year for 'A'
                        $yearOffset = ord($letter) - ord('A'); // Calculate the offset from 'A'
                        return $baseYear + $yearOffset;
                    })->alignCenter(),
                Tables\Columns\TextColumn::make('batteryPack.name')
                    ->label('Battery Pack')
                    ->sortable(),
                //checkbox colomn to mark inpha auto mac owned modules
                Tables\Columns\CheckboxColumn::make('is_inpha_auto_mac_owned')
                    ->label('Inpha Auto Mac Owned')
                    ->sortable()
                    ->alignCenter(),

                //show created date without time
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created Date')->date(),
            ])
            ->filters([
                //filter by battery_pack
                Tables\Filters\SelectFilter::make('battery_pack_id')
                    ->relationship('batteryPack', 'name')
                    ->label('Battery Pack')
                    ->options(fn() => \App\Models\BatteryPack::pluck('name', 'id')->toArray())->default(fn() => \App\Models\BatteryPack::latest()->first()->id ?? null),
                Filter::make('ir_value_range')
                    ->label('IR Value Range')
                    ->form([
                        Forms\Components\TextInput::make('min_ir_value')
                            ->label('Min IR Value')
                            ->numeric(),
                        Forms\Components\TextInput::make('max_ir_value')
                            ->label('Max IR Value')
                            ->numeric(),
                    ])
                    ->query(function (Builder $query, array $data) {
                        if (!empty($data['min_ir_value'])) {
                            $query->where('ir_value', '>=', $data['min_ir_value']);
                        }
                        if (!empty($data['max_ir_value'])) {
                            $query->where('ir_value', '<=', $data['max_ir_value']);
                        }

                }),

                // Custom filter for Capacitance range
                Filter::make('capacitance_range')
                    ->label('Capacitance Range')
                    ->form([
                        Forms\Components\TextInput::make('min_capacitance')
                            ->label('Min Capacitance')
                            ->numeric(),
                        Forms\Components\TextInput::make('max_capacitance')
                            ->label('Max Capacitance')
                            ->numeric(),
                    ])
                    ->query(function (Builder $query, array $data) {
                        if (!empty($data['min_capacitance'])) {
                            $query->where('capacitance', '>=', $data['min_capacitance']);
                        }
                        if (!empty($data['max_capacitance'])) {
                            $query->where('capacitance', '<=', $data['max_capacitance']);
                        }
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])->reorderable('sort')
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),

                    // Bulk action to export selected modules
                    BulkAction::make('export')
                        ->label('Export to Excel')
                        ->icon('heroicon-o-document')
                        ->action(function (SupportCollection $records) {
                            // Explicitly fetch records with necessary attributes and relations
                            $modules = Module::query()
                                ->whereIn('id', $records->pluck('id'))
                                ->get(['id', 'serial_number', 'ir_value', 'capacitance']); // Adjust the fields as necessary

                            return Excel::download(new \App\Exports\ModulesExport($modules), 'modules.xlsx');
                        })
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageModules::route('/'),
            'all' => Pages\All::route('/all'),
            'grade-a' => Pages\GradeA::route('/grade-a'),
            'grade-b' => Pages\GradeB::route('/grade-b'),
            'grade-c' => Pages\GradeC::route('/grade-c'),
            'grade-d' => Pages\GradeD::route('/grade-d'),
            'grade-e' => Pages\GradeE::route('/grade-e'),
        ];
    }

    public static function getActiveBatteryPackFilter()
    {
        // Get the default battery pack ID from the filter
        return \App\Models\BatteryPack::latest()->first()->id ?? null;
    }

}