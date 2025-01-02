<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InvoiceItemRelationManagerResource\RelationManagers\InvoiceItemsRelationManager;
use App\Filament\Resources\InvoiceResource\Pages;
use App\Filament\Resources\RelationManagers;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Dompdf\Dompdf;
use App\Http\Controllers\InvoiceController;
use App\Models\Customer;
use App\Models\Vehicle;
use Filament\Actions\CreateAction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class InvoiceResource extends Resource
{
    protected static ?string $model = Invoice::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Invoicing';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Forms\Components\Select::make('customer_id')
                    ->label('Customer')
                    ->relationship('customer', 'name')
                    ->required()
                    ->reactive()
                    ->searchable()
                    ->afterStateUpdated(function ($state, callable $set) {
                        $set('vehicle_id', null); // Reset vehicle selection
                    }),


                Forms\Components\Select::make('vehicle_id')
                    ->label('Vehicle')
                    ->options(function (callable $get) {
                        $customerId = $get('customer_id');
                        return Vehicle::where('customer_id', $customerId)->pluck('number', 'id');
                    })
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set) {
                        // Load vehicle details when a vehicle is selected
                        $vehicle = Vehicle::find($state);
                        if ($vehicle) {
                            $set('model', $vehicle->model); // Assuming 'model' is the field in the Vehicle model
                        } else {
                            $set('model', null);
                        }
                    }),


                Forms\Components\TextInput::make('model')
                    ->required(),
                Forms\Components\Group::make([
                    Forms\Components\TextInput::make('mileage')
                        ->label('Mileage')
                        ->required()
                        ->numeric(),
                    Forms\Components\Checkbox::make('is_km')
                        ->label('km')
                        ->default(true)
                        ->reactive()
                        ->afterStateUpdated(function ($state, callable $set) {
                            if ($state) {
                                $set('is_miles', false); // Uncheck miles if km is checked
                            }
                        }),
                    Forms\Components\Checkbox::make('is_miles')
                        ->label('miles')
                        ->default(false)
                        ->reactive()
                        ->afterStateUpdated(function ($state, callable $set) {
                            if ($state) {
                                $set('is_km', false); // Uncheck km if miles is checked
                            }
                        }),
                ])
                    ->label('Mileage and Unit')
                    ->columns(3), // Adjust the number of columns as needed
                Forms\Components\Repeater::make('items')
                    ->relationship('invoiceItems') // Define the relationship
                    ->schema([
                        Forms\Components\TextInput::make('description')
                            ->required(),
                        Forms\Components\TextInput::make('quantity')
                            ->default(1)
                            ->numeric()
                            ->required()
                            ->reactive()
                            ->debounce(1000), // Reactive to trigger changes with debounce
                        Forms\Components\TextInput::make('price')
                            ->required()
                            ->numeric()
                            ->reactive()
                            ->debounce(1000), // Reactive to trigger changes with debounce
                        Forms\Components\Checkbox::make('warranty_available')
                            ->label('Is Warranty Available?')
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                // Reset warranty type when checkbox is unchecked
                                if (!$state) {
                                    $set('warranty_type', null);
                                }
                            }),
                        Forms\Components\Select::make('warranty_type')
                            ->label('Warranty Type')
                            ->options([
                                '1 month' => '1 Month',
                                '2 months' => '2 Months',
                                '3 months' => '3 Months',
                                '1 year' => '1 Year',
                                '2 years' => '2 Years',
                                '3 years' => '3 Years',
                            ])
                            ->reactive()
                            ->required(fn($get) => $get('warranty_available')) // Required if warranty is available
                            ->disabled(fn($get) => !$get('warranty_available')), // Disable if warranty is not available
                    ])
                    ->columns(3) // Adjust the number of columns
                    ->reactive() // Make the repeater reactive
                    ->afterStateUpdated(function ($state, callable $set) {
                        $total = collect($state)->sum(fn($item) => ($item['quantity'] ?? 0) * ($item['price'] ?? 0));
                        $set('amount', $total);
                    }),
                Forms\Components\TextInput::make('amount')
                    ->numeric()
                    ->label('Total Amount')
                    ->default(0)
                    ->reactive()
                // Make the field reactive
            ]);
    }


    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('Invoice ID')
                    ->sortable(),
                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Customer')
                    ->sortable()
                    ->searchable()
                    ->formatStateUsing(function ($state, $record) {
                        return $record->customer->title . ' ' . $state; // Assuming customer relationship is loaded
                    }),
                Tables\Columns\TextColumn::make('vehicle.number')
                    ->label('Vehicle No.')
                    ->sortable(),
                Tables\Columns\TextColumn::make('model')
                    ->label('Model')
                    ->sortable()
                    ->formatStateUsing(function ($state, $record) {
                        // Access the related vehicle and concatenate brand and model
                        $vehicle = $record->vehicle; // Eager load the vehicle relationship
                        return $vehicle ? "{$vehicle->brand} {$state}" : 'N/A'; // Return 'brand model' or 'N/A' if no vehicle
                    }),
                Tables\Columns\TextColumn::make('mileage')
                    ->label('Mileage')
                    ->sortable()
                    ->formatStateUsing(function ($state, $record) {
                        // Assuming 'is_km' is a boolean field in the Invoice model
                        return $state . ' ' . ($record->is_km ? 'KM' : 'Miles');
                    }),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Total Amount')
                    ->sortable(), // Format as currency
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date Created')
                    ->dateTime()
                    ->sortable(), // Concatenate item details
            ])
            ->actions([
                Tables\Actions\Action::make('Download PDF')
                    ->url(fn(Invoice $record) => route('invoices.pdf', $record->id))
                    ->label('Download PDF')
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInvoices::route('/'),
            'create' => Pages\CreateInvoice::route('/create'),
            'edit' => Pages\EditInvoice::route('/{record}/edit'),
        ];
    }
}
