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
                Tables\Columns\TextColumn::make('serial_number')->sortable()->searchable(),

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
                    ->sortable(),

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
        ];
    }
}
