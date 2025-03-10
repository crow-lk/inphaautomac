<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProcurementResource\Pages;
use App\Models\Procurement;
use Filament\Forms;
use Filament\Tables;
use Filament\Resources\Resource;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;

class ProcurementResource extends Resource
{
    protected static ?string $model = Procurement::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
    protected static ?string $navigationGroup = 'Inpha Auto Mac Management';

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                Select::make('name')
                    ->relationship('item', 'name')
                    ->searchable()
                    ->required()
                    ->reactive()
                    ->label('Item Name')

                    ->afterStateUpdated(function ($state, callable $set) {
                        $item = \App\Models\Item::find($state);

                        if ($item) {
                            $set('item_id', $item->id);
                        }
                    }),

                TextInput::make('unitcost')
                    ->numeric()
                    ->step(0.01)
                    ->required()
                    ->reactive()
                    ->label('Unit Cost (LKR)')
                    ->afterStateUpdated(function ($state, callable $get, callable $set) {
                        $qty = $get('qty') ?: 0;
                        $unitcost = $get('unitcost') ?: 0;
                        $totalcost = ($qty * $unitcost); // Calculate total cost

                        // Set the total cost value in the form
                        $set('totalcost', $totalcost);}),


                TextInput::make('qty')
                    ->numeric()
                    ->required()
                    ->label('Quantity')
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $get, callable $set) {
                        $qty = $get('qty') ?: 0;
                        $unitcost = $get('unitcost') ?: 0;
                        $totalcost = ($qty * $unitcost); // Calculate total cost

                        // Set the total cost value in the form
                        $set('totalcost', $totalcost);}),

                TextInput::make('totalcost')
                    ->numeric()
                    ->step(0.01)

                    ->reactive()
                    ->label('Total Cost (LKR)'),

                TextInput::make('item_id')
                    ->required()
                    ->label('Item ID'),
            ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                TextColumn::make('item.name')
                    ->searchable()
                    ->sortable()
                    ->label('Item Name'),

                TextColumn::make('unitcost')

                    ->sortable()
                    ->label('Unit Cost'),

                TextColumn::make('qty')
                    ->sortable()
                    ->label('Quantity'),

                TextColumn::make('totalcost')

                    ->sortable()
                    ->label('Total Cost'),

                TextColumn::make('item_id')
                    ->searchable()
                    ->sortable()
                    ->label('Item ID'),
            ])
            ->filters([
                // Add filters if needed
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManagePorcurement::route('/'),
        ];
    }
}
