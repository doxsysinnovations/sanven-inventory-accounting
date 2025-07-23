<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quotation</title>
    <style>
        @page {
            size: letter;
        }

        * {
            margin: 0 auto;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            background-color: white;
            margin: 0.5in 0in;
        }

        .container {
            width: 100%;
            max-width: 7.5in;
        }

        .header {
            width: 100%;
            margin-bottom: 20px;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
        }

        .header-table td {
            padding: 0;
            vertical-align: middle;
        }

        .logo {
            height: 35px;
            width: auto;
        }

        .title {
            font-size: 36px;
            font-weight: bold;
            color: #057bba;
            text-align: right;
        }

        .company-info {
            width: 100%;
            margin-bottom: 20px;
        }

        .company-info-table {
            width: 100%;
            border-collapse: collapse;
        }

        .company-info-table td {
            padding: 0;
            vertical-align: top;
        }

        .company-details {
            width: 60%;
            padding-right: 20px;
        }

        .company-name {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 8px;
        }

        .company-address {
            font-size: 12px;
            line-height: 1.4;
        }

        .quote-details {
            width: 40%;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }

        .info-table th {
            background-color: white;
            color: #111;
            font-weight: 700;
            padding: 8px 12px;
            text-align: left;
            white-space: nowrap;
            border: 1px solid #057bba;
        }

        .info-table td {
            background-color: white;
            color: #374151;
            padding: 8px 12px;
            border: 1px solid #057bba;
        }

        .customer-section {
            margin-bottom: 20px;
        }

        .section-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }

        .section-table thead th {
            background-color: #057bba;
            color: white;
            text-transform: uppercase;
            font-size: 10px;
            font-weight: bold;
            padding: 10px 12px;
            text-align: left;
        }

        .section-table tbody th {
            background-color: white;
            color: #111;
            font-weight: 700;
            padding: 8px 12px;
            text-align: left;
            white-space: nowrap;
        }

        .section-table tbody td {
            background-color: white;
            color: #374151;
            padding: 8px 12px;
        }

        .section-table th,
        .section-table td {
            border: 1px solid #d4d4d8;
        }

        /* Products Section */
        .products-section {
            margin-bottom: 20px;
        }

        .products-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
        }

        .products-table thead th {
            background-color: #057bba;
            color: white;
            text-transform: uppercase;
            font-size: 9px;
            font-weight: bold;
            padding: 8px 12px;
            text-align: left;
        }

        .products-table tbody th {
            background-color: white;
            color: #111;
            font-weight: 700;
            padding: 8px 6px;
            text-align: left;
            font-size: 11px;
        }

        .products-table tbody td {
            background-color: white;
            color: #374151;
            padding: 8px 6px;
            text-align: right;
        }

        .products-table tbody td:nth-child(2),
        .products-table tbody td:nth-child(3) {
            text-align: left;
        }

        .products-table th,
        .products-table td {
            border: 1px solid #d4d4d8;
        }

        /* Bottom Section */
        .bottom-section {
            width: 100%;
            margin-bottom: 20px;
        }

        .bottom-table {
            width: 100%;
            border-collapse: collapse;
        }
        
         .bottom-table {
            width: 100%;
            border-collapse: collapse;
            
        }
        
        .bottom-table td {
            padding: 0;
            vertical-align: top;
           
        }
        
        .notes-cell {
            width: 65%;
            padding-right: 20px;
        }

        .totals-cell {
            width: 35%;
            padding-left: 20px
        }

        .notes-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
            padding-right: 20px;
        }

        .notes-table thead th {
            background-color: #057bba;
            color: white;
            text-transform: uppercase;
            font-size: 10px;
            font-weight: bold;
            padding: 10px 12px;
            text-align: left;
            border: 1px solid #9ca3af;
        }

        .notes-table tbody th {
            background-color: white;
            color: #111;
            font-weight: 400;
            padding: 12px;
            text-align: left;
            border: 1px solid #9ca3af;
            font-size: 11px;
            line-height: 1.3;
        }

        .totals-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }

        .totals-table th {
            background-color: white;
            color: #111;
            font-weight: 700;
            padding: 8px 12px;
            text-align: left;
            white-space: nowrap;
        }

        .totals-table td {
            color: #057bba;
            font-weight: bold;
            padding: 8px 12px;
            text-align: right;
        }

        .totals-table th,
        .totals-table td {
            border: 1px solid #d4d4d8;
        }

        .discount-color {
            color: #dc2626 !important;
        }

        .grand-total-row {
            background-color: #057bba !important;
        }

        .grand-total-row td {
            color: white !important;
            font-weight: bold;
        }
        
       .grand-total-row th {
            color: black !important;
            font-weight: bold;
        }

        .disclaimer {
            font-size: 11px;
            line-height: 1.4;
            margin-bottom: 20px;
            text-align: justify;
        }

        .acceptance-section {
            margin-bottom: 20px;
        }

        .acceptance-title {
            font-weight: bold;
            margin-bottom: 8px;
            font-size: 12px;
        }

        .acceptance-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }

        .acceptance-table thead th {
            background-color: #057bba;
            color: white;
            text-transform: uppercase;
            font-size: 10px;
            font-weight: bold;
            padding: 10px 12px;
            text-align: left;
            border: 1px solid #9ca3af;
        }

        .acceptance-table td {
            height: 35px;
            background-color: white;
            border: 1px solid #9ca3af;
            padding: 8px 12px;
        }

        .footer {
            text-align: center;
            font-size: 12px;
            line-height: 1.4;
            margin-top: 20px;
        }

        .currency {
            font-family: DejaVu Sans, Arial, sans-serif;
        }

        .no-break {
            page-break-inside: avoid;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header no-break">
            <table class="header-table">
                <tr>
                    <td style="width: 50%;">
                        <img src="{{ public_path('images/sanven-logo-3.png') }}" alt="Sanven" height="40">
                    </td>
                    <td style="width: 50%;">
                        <div class="title">Quotation</div>
                    </td>
                </tr>
            </table>
        </div>

        <div class="company-info no-break">
            <table class="company-info-table">
                <tr>
                    <td class="company-details">
                        <div class="company-name">Sanven Medical Ent., Inc.</div>
                        <div class="company-address">
                            <div>Address</div>
                            <div>City, State, ZIP</div>
                            <div>Phone:</div>
                            <div>Email:</div>
                            <div>Fax:</div>
                        </div>
                    </td>
                    <td class="quote-details">
                        <table class="info-table">
                            <tbody>
                                <tr>
                                    <th>Date</th>
                                    <td>{{ $quotation->created_at->format('M d, Y') }}</td>
                                </tr>
                                <tr>
                                    <th>Quotation Number</th>
                                    <td>{{ $quotation->quotation_number }}</td>
                                </tr>
                                <tr>
                                    <th>Customer ID</th>
                                    <td>{{ $quotation->customer->id }}</td>
                                </tr>
                                <tr>
                                    <th>Valid Until</th>
                                    <td>{{ \Carbon\Carbon::parse($quotation->valid_until)->format('M d, Y') }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </td>
                </tr>
            </table>
        </div>

        <div class="customer-section no-break">
            <table class="section-table">
                <thead>
                    <tr>
                        <th colspan="2">Customer Information</th>
                    </tr>
                </thead>
                <tbody>
                    @if (!empty($quotation->customer->name))
                    <tr>
                        <th>Customer Name</th>
                        <td>{{ $quotation->customer->name }}</td>
                    </tr>
                    @endif
                    @if (!empty($quotation->customer->email))
                    <tr>
                        <th>Email</th>
                        <td>{{ $quotation->customer->email }}</td>
                    </tr>
                    @endif
                    @if (!empty($quotation->customer->phone))
                    <tr>
                        <th>Phone</th>
                        <td>{{ $quotation->customer->phone }}</td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>

        <div class="products-section">
            <table class="products-table">
                <thead>
                    <tr>
                        <th style="width: 20%;">Product name</th>
                        <th style="width: 30%;" colspan="2">Description</th>
                        <th style="width: 8%;">Qty</th>
                        <th style="width: 14%;">Unit Price</th>
                        <th style="width: 14%;">VAT (0.12%)</th>
                        <th style="width: 14%;">TOTAL</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($quotation->items as $item)
                    <tr>
                        <th>{{ $item->product->name ?? '' }}</th>
                        <td colspan="2">{{ $item->description ?? ''}}</td>
                        <td>{{ $item->quantity ?? 0 }}</td>
                        <td><span class="currency">₱</span> {{ number_format($item->unit_price, 2) ?? 0 }}</td>
                        <td><span class="currency">₱</span> {{ number_format($item->vat_tax) ?? 0.00 }}</td>
                        <td><span class="currency">₱</span> {{ number_format($item->total_price, 2) ?? 0.00 }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="bottom-section no-break">
            <table class="bottom-table">
                <tr>
                    <td class="notes-cell">
                        <table class="notes-table">
                            <thead>
                                <tr>
                                    <th>Special Notes and Instructions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <th>{{ $quotation->notes ?? '' }}</th>
                                </tr>
                            </tbody>
                        </table>
                    </td>
                    <td class="totals-cell">
                        <table class="totals-table">
                            <tbody>
                                <tr>
                                    <th>Subtotal</th>
                                    <td><span class="currency">₱</span>{{ number_format(($quotation->items->sum('total_price')), 2) }}</td>
                                </tr>
                                <tr>
                                    @php
                                        $base = $quotation->items->sum('total_price');

                                        if ($quotation->discount_type === 'percentage') {
                                            $rate = $quotation->discount;
                                            $discount = ($rate / 100) * $base; 
                                        } else {
                                            $discount = $quotation->discount; 
                                            $rate = $base > 0 ? ($discount / $base) * 100 : 0;
                                        }
                                    @endphp
                                    <th>Discount ({{ number_format($rate, 2) }}%)</th>
                                    <td class="discount-color">-<span class="currency">₱</span>{{ number_format($discount, 2) }}</td>
                                </tr>
                                <tr>
                                    <th>Total VAT</th>
                                    <td class="discount-color"><span class="currency">₱</span> {{ number_format($quotation->tax ?? 0, 2) }}</td>
                                </tr>
                                <tr class="grand-total-row">
                                    <th>GRAND TOTAL</th>
                                    <td><span class="currency" style="font-size: 14px;">₱</span> {{ number_format($quotation->total_amount ?? 0, 2) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </td>
                </tr>
            </table>
        </div>

        <div class="disclaimer">
            This quotation is not a contract or a bill. It is our best guess at the total price for the service and goods described above. 
            The customer will be billed after indicating acceptance of this quote. Payment will be due prior to the delivery of service and goods.
            Please fax or mail the signed quote to the address listed above.
        </div>

        <div class="acceptance-section no-break">
            <p class="acceptance-title">Customer Acceptance</p>
            <table class="acceptance-table">
                <thead>
                    <tr>
                        <th style="width: 33.33%;">Name</th>
                        <th style="width: 33.33%;">Signature</th>
                        <th style="width: 33.33%;">Date</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="footer">
            <p>If you have any question please contact:</p>
            <p>Thank you for your business!</p>
        </div>
    </div>
</body>
</html>