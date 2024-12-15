<?php

namespace App\Filament\Resources;

use App\Filament\Resources\IssueBatteryPacksResource\Pages;
use App\Filament\Resources\IssueBatteryPacksResource\RelationManagers;
use App\Models\IssueBatteryPacks;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class IssueBatteryPacksResource extends Resource
{
    protected static ?string $model = IssueBatteryPacks::class;

    protected static ?string $navigationIcon = 'heroicon-s-rocket-launch';

    protected static ?string $navigationGroup = 'Inpha BMS';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Forms\Components\TextInput::make('name')
                    ->default(function () {
                        $lastRecord = IssueBatteryPacks::where('name', 'like', 'CINU-%')->orderBy('id', 'desc')->first();
                        $lastNumber = $lastRecord ? intval(substr($lastRecord->name, 5)) : 0;
                        return 'CINU-' . str_pad($lastNumber + 1, 5, '0', STR_PAD_LEFT);
                    })
                    ->required(),

                Forms\Components\Select::make('vehicle_id')
                    ->relationship('vehicle', 'number')
                    ->nullable()
                    ->searchable()
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set) {
                        if ($state) {
                            $previousRecord = IssueBatteryPacks::where('vehicle_id', $state)->orderBy('id', 'desc')->first();
                            $noOfModules = $previousRecord ? $previousRecord->no_of_modules : 0;
                            $set('no_of_modules', $noOfModules);
                        } else {
                            $set('no_of_modules', 0);
                        }
                    })
                    ->required(),

                    Forms\Components\TextInput::make('no_of_modules')
                    ->default(0)
                    ->required()
                    ->extraAttributes(['readonly' => true]) // Makes the field readonly instead of disabled
                    ->afterStateHydrated(function ($component, $state) {
                        if ($state) {
                            $component->state($state); // Reflect the saved state in the form
                        }
                    }),


            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('no_of_modules')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('vehicle.number')->sortable()->searchable()
            ])
            ->filters([
                //packs starting with CINU are issue battery packs
                Tables\Filters\SelectFilter::make('name')
                    ->query(function (Builder $query) {
                        $query->where('name', 'like', 'CINU-%');
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageIssueBatteryPacks::route('/'),
        ];
    }
}
