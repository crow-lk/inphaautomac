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
            <p><strong>Customer Name:</strong> <strong>{{ $invoice->customer->title }}{{ $invoice->customer->name }}</strong></p>
            <p><strong>Vehicle Number:</strong> {{ $invoice->vehicle->number }}</p>
            <p><strong>Model:</strong> {{ $invoice->vehicle->brand }} {{ $invoice->model }}</p>
            <p><strong>Mileage:</strong> {{ $invoice->mileage }} {{ $invoice->is_km ? 'KM' : 'Miles' }}</p>
        </div>

    </div>
    <table>
        <thead>
            <tr>
                <th style="width:10%; text-align: center;">NO</th>
                <th style="width:40%; text-align: left;">DESCRIPTION</th>
                <th style="width:10%; text-align: center;">QTY</th>
                <th style="width:20%; text-align: right;">PRICE (Rs.)</th>
                <th style="width:20%; text-align: right;">TOTAL (Rs.)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoiceItems as $index => $item)
                <tr>
                    <td style="width:10%; text-align: center;">{{ $index + 1 }}</td>
                    <td style="width:40%; text-align: left;">
                        {{ $item->description }}
                        @if($item->warranty_available)
                            <br>
                            <span style="font-size: 0.8em; font-weight: bold;">({{ $item->warranty_type }} Warranty)</span>
                        @endif
                    </td>
                    <td style="width:10%; text-align: center;">
                        @if($item->is_item)
                            {{ $item->quantity }}
                        @endif
                    </td>
                    <td style="width:20%; text-align: right;">{{ number_format($item->price, 2) }}</td>
                    <td style="width:20%; text-align: right;">{{ number_format($item->quantity*$item->price, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    @if($showGrandTotal)
        <p class="total">GRAND TOTAL:  Rs.{{ number_format($invoice->amount, 2) }}</p>
    @endif
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
