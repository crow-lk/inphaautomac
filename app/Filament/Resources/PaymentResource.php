<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentResource\Pages;
use App\Models\Payment;
use App\Models\Invoice;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Illuminate\Support\Str;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Section;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;
    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationGroup = 'Invoicing';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Payment Amount Details')
                    ->description('Details regarding the amounts involved in the payment')
                    ->icon('heroicon-o-currency-dollar'),

                Section::make('Meta')
                    ->schema([
                        Select::make('invoice_id')
                            ->label('Invoice ID')
                            ->relationship('invoice', 'id')
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                $invoice = Invoice::find($state);

                                if ($invoice) {
                                    $set('amount', $invoice->amount);
                                    $set('amount_to_pay', $invoice->credit_balance); // Set initial value of amount_to_pay

                                    // Get the current date in the desired format (e.g., Ymd for YYYYMMDD)
                                    $currentDate = now()->format('Ymd'); // Format: YYYYMMDD
                                     // Get the current date in the desired format (e.g., Ymd for YYYYMMDD)
                                    $currentDate = now()->format('Ymd'); // Format: YYYYMMDD

                                    // Retrieve the last payment ID
                                    $lastPayment = Payment::orderBy('id', 'desc')->first();
                                    $lastPaymentId = $lastPayment ? $lastPayment->id : 0; // Get the last payment ID or 0 if none

                                    $newPaymentId = $lastPaymentId + 1; // Increment the last payment ID
                                    // Generate the reference number
                                    $referenceNumber = 'IAM' . $currentDate . $invoice->id . $newPaymentId; // Concatenate JME, current date, and invoice ID
                                    $set('reference_number', $referenceNumber); // Set the reference number
                                } else {
                                    $set('amount', null);
                                    $set('amount_to_pay', null);
                                    $set('reference_number', null); // Clear reference number if no invoice
                                }
                            }),

                        Select::make('payment_method')
                            ->label('Payment Method')
                            ->options([
                                'credit_card' => 'Credit Card',
                                'bank_transfer' => 'Bank Transfer',
                                'debit_card'=>'Debit Card',
                                'cash' => 'Cash',
                            ])
                            ->required(),

                        TextInput::make('reference_number')
                            ->label('Reference Number')
                            ->required(),

                            DateTimePicker::make('payment_date')
                            ->label('Payment Date')
                            ->required()
                            ->default(now()),

                        Textarea::make('notes')
                            ->label('Notes')
                            ->nullable(),
                    ])->columnSpan(1),

                Section::make('Payment Breakdown')
                    ->schema([
                        TextInput::make('amount')
                            ->label('Total')
                            ->disabled()
                            ->numeric()
                            ->step(0.01) // Orange color for Total Amount

                        , TextInput::make('amount_paid')
                            ->label('Amount Paid')
                            ->required()
                            ->numeric()
                            ->step(0.01)
                            ->reactive() // Green color for Paid Amount




                        , TextInput::make('amount_to_pay')
                            ->label('To Pay')
                            ->disabled()
                            ->numeric()
                            ->step(0.01)
                            ->reactive(),
                        Forms\Components\Checkbox::make('discount_available')
                            ->label('Is Discount Available?')
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                // Reset warranty type when checkbox is unchecked
                                if (!$state) {
                                    $set('discount', null);
                                }
                            }),
                        Forms\Components\TextInput::make('discount')
                            ->label('Discount')
                            ->numeric()
                            ->reactive()
                            ->required(fn($get) => $get('discount_available')) // Required if warranty is available
                            ->disabled(fn($get) => !$get('discount_available')), // Disable if warranty is not available
                    ])->columnSpan(2)->columns(2),
            ])
            ->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('reference_number')->label('Reference Number')->sortable(),
                TextColumn::make('invoice_id')->label('Invoice')->sortable(),
                TextColumn::make('invoice.amount')->label('Total')->sortable(),
                TextColumn::make('amount_paid')->label('Amount Paid')->sortable(),
                TextColumn::make('discount')->label('Discount')->sortable(),
                //payment method
                TextColumn::make('payment_method')->label('Payment Method')->sortable(),
                TextColumn::make('payment_date')->label('Payment Date')->sortable(),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('Download PDF')
                    ->url(fn(Payment $record) => route('invoices.pdf', $record->invoice_id))
                    ->icon('heroicon-o-printer')
                    ->label('')
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                ])
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManagePayments::route('/'),
        ];
    }
}
