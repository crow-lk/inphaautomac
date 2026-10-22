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
    <div class="inv">
        <p><strong>{{ $invoice->is_invoice ? 'INVOICE' : 'QUOTATION' }}</strong></p>
    </div>
    <div class="container">
        <div class="right">
            @if($invoice->is_invoice)
                <p><strong>Invoice No:</strong> {{ $invoice->id }}</p>
            @else
            <p><strong>Quotation No:</strong> {{ $invoice->id }}</p>
            @endif
            <p>{{ $invoice->created_at->format('F j, Y') }}</p>
        </div>
        <div class="left">
            <p><strong>Invoice to:</strong></p>
            <p><strong>Customer Name:</strong>
                @if ($invoice->customer->title && $invoice->customer->title !== 'Company')
                    {{ $invoice->customer->title }}
                @endif
                {{ $invoice->customer->name }}
            </p>
            <p><strong>Vehicle Number:</strong> {{ $invoice->vehicle->number }}</p>
            <p><strong>Model:</strong> {{ $invoice->vehicle->brand->name ?? $invoice->vehicle->brand }} {{ $invoice->model }}</p>
            <p><strong>Mileage:</strong> {{ $invoice->mileage }} {{ $invoice->is_km ? 'KM' : 'Miles' }}</p>
        </div>

    </div>
    <table>
        <thead>
            <tr>
                <th style="width:7%; text-align: center;">No</th>
                <th style="width:50%; text-align: left;">Description</th>
                <th style="width:18%; text-align: right;">Unit Price(LKR)</th>
                <th style="width:7%; text-align: center;">Qty</th>
                <th style="width:18%; text-align: right;">Total(LKR)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoiceItems as $index => $item)
                @if($item->is_item || $item->is_service)
                    <tr>
                        <td style="width:7%; text-align: center; font-size: 11px;">{{ $index + 1 }}</td>
                        <td style="width:50%; text-align: left; font-size: 11px;">
                             @if($item->is_item)
                                  {{ $item->item->name ?? 'N/A' }} <!-- Display item name -->
                                  @if($item->warranty_available)
                                      <br>
                                      <span style="font-size: 0.8em; font-weight: bold;">({{ $item->warranty_type }} Warranty)</span>
                                  @endif
                                  @if(!empty($item->item->comment))
                                      <br>
                                      <span style="font-size: 0.8em;">{{ $item->item->comment }}</span>
                                  @endif
                             @elseif($item->is_service)
                                  {{ $item->service->name ?? 'N/A' }} <!-- Display service name -->
                                  @if($item->warranty_available)
                                      <br>
                                      <span style="font-size: 0.8em; font-weight: bold;">({{ $item->warranty_type }} Warranty)</span>
                                  @endif
                                  @if(!empty($item->service->comment))
                                      <br>
                                      <span style="font-size: 0.8em;">{{ $item->service->comment }}</span>
                                  @endif
                             @endif
                        </td>
                        <td style="width:18%; text-align: right; font-size: 11px;">{{ number_format($item->price, 2) }}</td>
                        <td style="width:7%; text-align: center; font-size: 11px;">
                            @if($item->is_item)
                                {{ $item->quantity }} <!-- Show quantity only for items -->
                            @endif
                        </td>
                        <td style="width:18%; text-align: right; font-size: 11px;">{{ number_format(($item->is_service ? 1 : (float) $item->quantity) * (float) $item->price, 2) }}</td>
                    </tr>
                @endif
            @endforeach
            @if($showGrandTotal)
                @php
                    $summaryItems = $invoice->invoiceItems->filter(fn ($item) => $item->is_item || $item->is_service);
                    $grandTotal = $summaryItems->sum(fn ($item) => ($item->is_service ? 1 : (float) $item->quantity) * (float) $item->price);
                    $totalDiscount = $summaryItems->sum('discount')
                        + $payments->where('discount_available', true)->sum('discount');
                    $toPay = max(0, $grandTotal - $totalDiscount - $totalPaid);
                @endphp
                <tr>
                    <td colspan="4" style="text-align: right; font-weight: bold; font-size: 12px; background-color: #ddd;">Grand Total:</td>
                    <td colspan="1" style="text-align: right; font-weight: bold; font-size: 12px; background-color: #ddd;">{{ number_format($grandTotal, 2) }}</td>
                </tr>
                @if($totalDiscount > 0)
                        <tr>
                            <td colspan="4" style="text-align: right; font-weight: bold; font-size: 12px; background-color: #ddd;">Total Discount:</td>
                            <td colspan="1" style="text-align: right; font-weight: bold; font-size: 12px; background-color: #ddd;">{{ number_format($totalDiscount, 2) }}</td>
                        </tr>
                    @endif

                <tr>
                    <td colspan="4" style="text-align: right; font-weight: bold; font-size: 12px; background-color: #ddd;">Paid Amount:</td>
                    <td colspan="1" style="text-align: right; font-weight: bold; font-size: 12px; background-color: #ddd;">{{ number_format($totalPaid, 2) }}</td>
                </tr>

                @if($toPay > 0)
                    <tr>
                        <td colspan="4" style="text-align: right; font-weight: bold; font-size: 12px; background-color: #ddd;">To Pay:</td>
                        <td colspan="1" style="text-align: right; font-weight: bold; font-size: 12px; background-color: #ddd;">{{ number_format($toPay, 2) }}</td>
                    </tr>
                @endif
            @endif
        </tbody>
    </table>
    <div class="text1">
        <p class="h1"><strong>Inpha Auto Mac & Hybrid Care</strong></p>
        <p class="p1"><strong>Thank you for buisness with us!</strong></p>
    </div>
    <div class="text2">
        <p class="h1"><strong>Term and Conditions :</strong></p>
        <p class="p1">It is mandatory to bring the invoice given to you</p>
        <p class="p1">for any Battery, ABS related services.</p>
        <p class="p1">Physical damages are not covered under warranty.</p>
    </div>
</body>
</html>
