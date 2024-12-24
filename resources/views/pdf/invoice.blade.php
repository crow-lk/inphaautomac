<!-- resources/views/invoice.blade.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #{{ $invoice->id }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding:20px;
            background-image: url('{{ public_path('images/BG.jpg') }}'); /* Path to your background image */
            background-size: cover; /* Cover the entire page */
            background-repeat: no-repeat; /* Prevent repeating the image */
            background-position: center; /* Center the image */
            color: #333;
        }

        .container {
            display: flex;
            flex-direction:row;
            width:100%;
            justify-content: space-between; /* Optional: space between items */
            margin-top:140px;
        }
        .name {
            font-size: 24px;
        }

        .right {
            text-align:right;
            margin-bottom: 20px;
        }
        .left {
            margin-bottom: 20px;
        }
        .left p {
            margin: 5px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table, th, td {
            border-bottom: 1px solid #ddd;
            border-top: 1px solid #ddd;
        }
        th, td {
            padding: 5px;
            text-align: left;
        }
        th {
            background-color:black;
            color:white;
        }
        .total {
            margin-top:20px;
            margin-left:47%;
            width:50%;
            padding:8px;
            background-color:black;
            color:white;
            font-size: 18px;
            font-weight: bold;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="right">
            <p><strong>Invoice No:</strong> {{ $invoice->id }}</p>
            <p>{{ $invoice->created_at->format('F j, Y') }}</p>
        </div>
        <div class="left">
            <p><strong>Invoice to:</strong></p>
            <p class="name"><strong>Customer Name:</strong> {{ $invoice->customer_name }}</p>
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
    <p class="total">Total Amount:  Rs.{{ number_format($invoice->amount, 2) }}</p>
</body>
</html>
