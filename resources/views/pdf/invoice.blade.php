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
                <th style="width:40%;">Description</th>
                <th style="width:20%;">Quantity</th>
                <th style="width:20%;">Price (Rs.)</th>
                <th style="width:20%;">Total (Rs.)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->invoiceItems as $item)
                <tr>
                    <td style="width:40%;">{{ $item->description }}</td>
                    <td style="width:20%;">{{ $item->quantity }}</td>
                    <td style="width:20%;">{{ number_format($item->price, 2) }}</td>
                    <td style="width:20%;">{{ number_format($item->quantity * $item->price, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <div class="new">
        <p class="total">Total Amount:  Rs.{{ number_format($invoice->amount, 2) }}</p>
    </div>

</body>
</html>
