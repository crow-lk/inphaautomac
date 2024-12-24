<?php

use App\Http\Controllers\InvoiceController;
use Illuminate\Support\Facades\Route;

Route::get('invoices/{id}/pdf', [InvoiceController::class, 'generateInvoice'])->name('invoices.pdf');
