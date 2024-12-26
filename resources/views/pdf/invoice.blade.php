<!-- resources/views/invoice.blade.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #{{ $invoice->id }}</title>
    <link rel="stylesheet" href="{{ public_path('css/pdf.css') }}">
</head>
<body>
    <div class="container">
        <div class="right">
            <p><strong>Invoice No:</strong> {{ $invoice->id }}</p>
            <p>{{ $invoice->created_at->format('F j, Y') }}</p>
        </div>
        <div class="left">
            <p><strong>Invoice to:</strong></p>
            <p><strong>Customer Name:</strong> <strong>{{ $invoice->customer_name }}</strong></p>
            <p><strong>Vehicle Number:</strong> {{ $invoice->vehicle_number }}</p>
            <p><strong>Model:</strong> {{ $invoice->model }}</p>
            <p><strong>Mileage:</strong> {{ $invoice->mileage }} KM</p>
        </div>

    </div>
    <table>
        <thead>
            <tr>
                <th style="width:10%; text-align: center;">NO</th>
                <th style="width:70%; text-align: left;">DESCRIPTION</th>
                <th style="width:20%; text-align: center;">PRICE (Rs.)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->invoiceItems as $item)
                <tr>
                    <td style="width:10%; text-align: center;">{{ $item->id }}</td>
                    <td style="width:70%; text-align: left;">{{ $item->description }}</td>
                    <td style="width:20%; text-align: right;">{{ number_format($item->price, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <div class="new">
        <p class="total">GRAND TOTAL:  Rs.{{ number_format($invoice->amount, 2) }}</p>
    </div>
    <div class="text1">
        <p class="h1"><strong>Inpha Auto Mac & Hybrid Care</strong></p>
        <p class="p1"><strong>Thank you for buisness with us!</strong></p>
    </div>
    <div class="text2">
        <p><strong>Term and Conditions :</strong></p>
        <p class="p1">It is mandatory to bring the invoice given to you</p>
        <p class="p1">for any Battery, ABS related services.</p>
        <p class="p1">Physical damages are not covered under warranty.</p>
    </div>
</body>
</html>
