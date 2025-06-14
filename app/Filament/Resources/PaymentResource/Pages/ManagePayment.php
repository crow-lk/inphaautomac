<?php

namespace App\Filament\Resources\PaymentResource\Pages;

use App\Filament\Resources\PaymentResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManagePayments extends ManageRecords
{
    protected static string $resource = PaymentResource::class;

    // Customize header actions (like create and delete)
    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(), // This will create a new payment
        ];
    }

    // Customize the actions for each row (edit and delete)
    protected function getTableActions(): array
    {
        return [
            Actions\EditAction::make(), // This will allow editing a specific payment
            Actions\DeleteAction::make(), // This will allow deleting a specific payment
        ];
    }

    // Customize the table column actions or modify the table columns if needed
    protected function getTableColumns(): array
    {
        return [
            // Here, you can customize the columns for the payment table.
            // Example: TextColumn::make('transaction_id')->label('Transaction ID'),
        ];
    }
}
