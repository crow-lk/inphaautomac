<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #{{ $invoice->id }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }
        .letterhead {
            text-align: center;
            margin-bottom: 20px;
        }
        .letterhead img {
            max-width: 150px; /* Adjust the size of the logo */
        }
        .company-info {
            text-align: center;
            margin-top: 10px;
        }
        .invoice-header {
            text-align: center;
            margin-top: 20px;
        }
        .invoice-details {
            margin-top: 20px;
            border: 1px solid #ccc;
            padding: 10px;
        }
        .invoice-details p {
            margin: 5px 0;
        }
        .total {
            font-weight: bold;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="letterhead">
        <h2>Inpha Auto Mac & Hybrid Care</h2>
        <p>Delkanda, No.15/4, Somathalagala Mawatha, Pangiriwatta Rd, Nugegoda</p>
        <p>Phone: 0772292233</p>
    </div>
    
    <div class="invoice-header">
        <h1>Invoice #{{ $invoice->id }}</h1>
        <p>Date: {{ $invoice->created_at->format('Y-m-d') }}</p>
    </div>
    
    <div class="invoice-details">
        <p><strong>Customer Name:</strong> {{ $invoice->customer_name }}</p>
        <p><strong>Vehicle Number:</strong> {{ $invoice->vehicle_number }}</p>
        <p><strong>Model:</strong> {{ $invoice->model }}</p>
        <p><strong>Total Amount:</strong> ${{ number_format($invoice->amount, 2) }}</p>
        <h3>Items:</h3>
        <ul>
            @foreach ($invoice->invoiceItems as $item)
                <li>{{ $item->description }} - Quantity: {{ $item->quantity }} - Price: ${{ number_format($item->price, 2) }}</li>
            @endforeach
        </ul>
    </div>

    <div class="footer">
        <p>Thank you for your business!</p>
        <p>It is mandatory to bring the invoice given to you for any Battery, ABS related services.</p>
        <p>Physical damages are not covered under warranty.</p>
    </div>
</body>
</html>