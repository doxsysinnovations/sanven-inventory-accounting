<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $po->po_number }}</title>
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

        .products-table tbody td:nth-child(1),
        .products-table tbody td:nth-child(2),
        .products-table tbody td:nth-child(3) {
            text-align: left;
        }

        .products-table tbody td:nth-child(4),
        .products-table tbody td:nth-child(5),
        .products-table tbody td:nth-child(6) {
            text-align: center;
        }

        .products-table th,
        .products-table td {
            border: 1px solid #d4d4d8;
        }

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
            text-align: center;
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
                        <div class="title">Purchase Order</div>
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
                            <div>Blk. 22 Lot 10 Phase 2, Nevada St., Suburbia North, Malpitic 2000,</div>
                            <div>City of San Fernando (Capital), Pampanga, Philippines</div>
                            <div>Tel. # (045) 455-1402; (045) 455-1517</div>
                            <div>Cel. Nos. 0932-888-3548/0932-888-3547</div>
                            <div>VAT Reg. TIN: 219-532-832-00000</div>
                            <div>sanvenmedinc@yahoo.com.ph</div>
                        </div>
                    </td>
                    <td class="quote-details">
                        <table class="info-table">
                            <tbody>
                                <tr>
                                    <th>Date</th>
                                    <td>{{ $po->created_at->format('M d, Y') }}</td>
                                </tr>
                                <tr>
                                    <th>PO Number</th>
                                    <td>{{ $po->po_number }}</td>
                                </tr>
                                <tr>
                                    <th>Supplier</th>
                                    <td>{{ $po->supplier->trade_name ?? '' }}</td>
                                </tr>
                                <tr>
                                    <th>Payment Terms</th>
                                    <td>{{ $po->payment_terms ?? '' }}</td>
                                </tr>
                                <tr>
                                    <th>Status</th>
                                    <td>
                                        @php
                                            $status = ucfirst($po->status ?? '');
                                            $color = match($po->status) {
                                                'pending' => 'color: #b45309;',
                                                'partially delivered' => 'color: #c2410c;',
                                                'delivered' => 'color: #166534;',
                                                'closed' => 'color: #374151;',
                                                'cancelled' => 'color: #991b1b;',
                                                default => 'color: #374151;',
                                            };
                                        @endphp
                                        <span style="font-weight: bold; {{ $color }}">
                                            {{ $status }}
                                        </span>
                                    </td>
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
                        <th colspan="2">Supplier Information</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <th>Supplier Name</th>
                        <td>{{ $po->supplier->trade_name ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Contact</th>
                        <td>{{ $po->supplier->contact_number ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Email</th>
                        <td>{{ $po->supplier->email ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Address</th>
                        <td>{{ $po->supplier->address ?? 'N/A' }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="products-section">
            <table class="products-table">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Product</th>
                        <th>Description</th>
                        <th>Qty</th>
                        <th>Unit Price</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($po->items as $item)
                    <tr>
                        <td>{{ $item->product->product_code ?? '' }}</td>
                        <td>{{ $item->product->name ?? '' }}</td>
                        <td>{{ $item->product->description ?? '' }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td><span class="currency">₱</span>{{ number_format($item->price, 2) }}</td>
                        <td><span class="currency">₱</span>{{ number_format($item->price * $item->quantity, 2) }}</td>
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
                                    <th>Remarks</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <th>{{ $po->remarks ?? '' }}</th>
                                </tr>
                            </tbody>
                        </table>
                    </td>
                    <td class="totals-cell">
                        <table class="totals-table">
                            <tbody>
                                <tr>
                                    <th>Subtotal</th>
                                    <td><span class="currency">₱</span>{{ number_format($po->items->sum(fn($i) => $i->price * $i->quantity), 2) }}</td>
                                </tr>
                                <tr class="grand-total-row">
                                    <th>GRAND TOTAL</th>
                                    <td><span class="currency" style="font-size: 14px;">₱</span>{{ number_format($po->items->sum(fn($i) => $i->price * $i->quantity), 2) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </td>
                </tr>
            </table>
        </div>

        <div class="footer">
            <p class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed">
                If you have any questions, feel free to contact us at <strong>(045) 455-1402</strong> or <strong>(045) 455-1517</strong>,<br><strong>0932-888-3548</strong> or <strong>0932-888-3547</strong>, or email us at <u>sanvenmedinc@yahoo.com.ph</u>.
            </p>
            <br>
            <p>Thank you for your business!</p>
        </div>
    </div>
</body>
</html>