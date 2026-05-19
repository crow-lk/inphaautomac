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
                    ->reactive()
                    ->label('Item Name')
                    ->afterStateUpdated(function ($state, callable $set) {
                        $item = \App\Models\Item::find($state);

                        if ($item) {
                            $set('item_id', $item->id);
                        }
                    })
                    ->createOptionForm(function () {
                        return [
                            Forms\Components\TextInput::make('name')->label('Item Name')->required(),
                            Forms\Components\Select::make('unit')->options([
                                'l' => 'Liters',
                                'ml' => 'Milliliters',
                                'pcs' => 'Pieces',
                                'pair' => 'Pair',
                            ])->required(),
                            Forms\Components\TextInput::make('qty')->label('Quantity')->numeric()->required(),
                            Forms\Components\TextInput::make('selling_price')->label('Selling Price')->numeric()->default(0),
                            Forms\Components\TextInput::make('cost_price')->label('Cost Price')->numeric()->default(0),
                            Forms\Components\TextInput::make('comment')->label('Comment'),
                        ];
                    })
                    ->createOptionUsing(function (array $data) {
                        $item = \App\Models\Item::create([
                            'name' => $data['name'],
                            'unit' => $data['unit'],
                            'qty' => $data['qty'],
                            'selling_price' => $data['selling_price'] ?? 0,
                            'cost_price' => $data['cost_price'] ?? 0,
                            'comment' => $data['comment'] ?? null,
                        ]);
                        return $item->id; // Return the item ID
                    })
                    ->required(),

                Select::make('item_brand_id')
                    ->relationship('itemBrand', 'name')
                    ->label('Item Brand')
                    ->placeholder('Select a brand')
                    ->searchable()
                    ->preload()
                    ->createOptionUsing(fn (array $data): int => ItemBrand::create($data)->id)
                    ->createOptionForm([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->unique(ItemBrand::class, 'name')
                            ->maxLength(255),
                    ])
                    ->createOptionAction(
                        fn (Forms\Components\Actions\Action $action) => $action
                            ->modalHeading('Create Brand')
                            ->successNotificationTitle('Brand created'),
                    ),

                Select::make('vehicle_model')
                    ->label('Vehicle Model')
                    ->placeholder('Select a vehicle model')
                    ->options(fn () => \App\Models\Vehicle::whereNotNull('model')->distinct()->pluck('model', 'model'))
                    ->searchable()
                    ->preload(),

                TextInput::make('selling_price')
                    ->numeric()
                    ->step(0.01)
                    ->label('Selling Price (LKR)'),

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
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('item_id')
                    ->searchable()
                    ->sortable()
                    ->label('Item ID'),

                TextColumn::make('item.name')
                    ->searchable()
                    ->sortable()
                    ->label('Item Name'),

                TextColumn::make('vehicle.model')
                    ->label('Vehicle Model')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('selling_price')
                    ->sortable()
                    ->label('Selling Price'),

                TextColumn::make('unitcost')

                    ->sortable()
                    ->label('Unit Cost'),

                TextColumn::make('qty')
                    ->sortable()
                    ->label('Quantity'),

                TextColumn::make('totalcost')

                    ->sortable()
                    ->label('Total Cost'),

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
