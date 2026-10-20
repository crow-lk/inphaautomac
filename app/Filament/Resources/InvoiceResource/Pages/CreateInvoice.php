<?php

namespace App\Filament\Resources\InvoiceResource\Pages;

use App\Filament\Resources\InvoiceResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateInvoice extends CreateRecord
{
    protected static string $resource = InvoiceResource::class;

    protected function beforeValidate(): void
    {
        InvoiceResource::validateConfirmedItems($this->form->getRawState()['items'] ?? [], $this->form->getStatePath());
    }
}
