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
use Filament\Actions\CreateAction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class InvoiceResource extends Resource
{
    protected static ?string $model = Invoice::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('customer_name')
                    ->required(),
                Forms\Components\TextInput::make('vehicle_number')
                    ->required(),
                Forms\Components\TextInput::make('model')
                    ->required(),
                Forms\Components\TextInput::make('mileage')
                    ->required(),
                Forms\Components\Repeater::make('items')
                    ->relationship('invoiceItems') // Define the relationship
                    ->schema([
                        Forms\Components\TextInput::make('description')
                            ->required(),
                        Forms\Components\TextInput::make('quantity')
                            ->required()
                            ->numeric(),
                        Forms\Components\TextInput::make('price')
                            ->required()
                            ->numeric(),
                    ])
                    ->columns(3), // Adjust the number of columns
            ]);
    }

    public static function afterCreate(Invoice $record, array $data): void
    {
        $record->amount = $record->calculateTotalAmount();
        $record->save();
    }

    public static function afterUpdate(Invoice $record, array $data): void
    {
        $record->amount = $record->calculateTotalAmount();
        $record->save();
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('Invoice ID')
                    ->sortable(),
                Tables\Columns\TextColumn::make('customer_name')
                    ->label('Customer Name')
                    ->sortable(),
                Tables\Columns\TextColumn::make('vehicle_number')
                    ->label('Vehicle No.')
                    ->sortable(),
                Tables\Columns\TextColumn::make('model')
                    ->label('Model')
                    ->sortable(),
                Tables\Columns\TextColumn::make('mileage')
                    ->label('Mileage')
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Total Amount')
                    ->sortable()
                    ->money('USD'), // Format as currency
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date Created')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('items_count') // Custom column for item details
                    ->label('Items')
                    ->counts('invoiceItems'), // Concatenate item details
            ])
            ->actions([
                Tables\Actions\Action::make('Download PDF')
                    ->url(fn (Invoice $record) => route('invoices.pdf', $record->id))
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

    // public static function generateInvoice($invoiceId)
    // {
    //     $invoice = Invoice::with('invoiceItems')->findOrFail($invoiceId); // Eager load invoice items

    //     // Load the view and pass the invoice data
    //     $html = view('invoice', compact('invoice'))->render();

    //     // Initialize Dompdf
    //     $dompdf = new Dompdf();
    //     $dompdf->loadHtml($html);
    //     $dompdf->setPaper('A4', 'portrait');
    //     $dompdf->render();

    //     // Output the generated PDF to Browser
    //     return $dompdf->stream("invoice_{$invoice->id}.pdf"); // Set Attachment to false to open in browser
    // }
}
