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
        return $form
            ->schema([
                Forms\Components\TextInput::make('serial_number')->required(),
                Forms\Components\TextInput::make('ir_value')->required()->nullable()->numeric(),
                Forms\Components\TextInput::make('capacitance')->required()->nullable(),
                Forms\Components\Select::make('battery_pack_id')->relationship('batteryPack', 'name')->required()->searchable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable()->searchable()->label('Nuvant Number'),
                Tables\Columns\TextColumn::make('serial_number')->sortable()->searchable(),

                Tables\Columns\TextColumn::make('ir_value')
                    ->sortable()
                    ->searchable()
                    ->label('IR Value')
                    //show values with the units mili Ohms
                    ->formatStateUsing(fn (string $state): string => $state . ' mΩ')                
                    ,
                    


                Tables\Columns\TextColumn::make('capacitance')->sortable()->searchable()->formatStateUsing(fn (string $state): string => $state . ' mAh'),
                Tables\Columns\TextColumn::make('batteryPack.name')
                    ->label('Battery Pack')
                    ->sortable()
                    ->searchable(),
                //checkbox colomn to mark inpha auto mac owned modules
                Tables\Columns\CheckboxColumn::make('is_inpha_auto_mac_owned')
                    ->label('Inpha Auto Mac Owned')
                    ->sortable()
                    ->searchable(),

                //show created date without time
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created Date')->date(),
            ])
            ->filters([
                //filter by battery_pack
                Tables\Filters\SelectFilter::make('battery_pack_id')
                    ->relationship('batteryPack', 'name')
                    ->label('Battery Pack')
                    ->options(fn() => \App\Models\BatteryPack::pluck('name', 'id')->toArray()),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),

                    //bulk action to export selected modules
                    BulkAction::make('exoport')->label('Export to Excel')->icon('heroicon-o-document')->action(function (SupportCollection $records) {
                        return Excel::download(new \App\Exports\ModulesExport($records), 'modules.xlsx');
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
