<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delivery Note</title>
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

        .order-details {
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
        .products-table tbody td:nth-child(3),
        .products-table tbody td:nth-child(4) {
            text-align: left;
        }

         .products-table tbody td:nth-child(5),
         .products-table tbody td:nth-child(6),
         .products-table tbody td:nth-child(7) {
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

        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .status-pending {
            background-color: #fef3c7;
            color: #92400e;
        }

        .status-delivered {
            background-color: #d1fae5;
            color: #065f46;
        }

        .status-partial {
            background-color: #dbeafe;
            color: #1e40af;
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
                        <div class="title">Delivery Note</div>
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
                    <td class="order-details">
                        <table class="info-table">
                            <tbody>
                                <tr>
                                    <th>Delivery Date</th>
                                    <td>{{ \Carbon\Carbon::parse($deliveryNote->delivery_date)->format('M d, Y') ?? '' }}
                                    </td>
                                </tr>
                                <tr>
                                    <th>Delivery Note Number</th>
                                    <td>{{ $deliveryNote->delivery_note_number ?? '' }}</td>
                                </tr>
                                <tr>
                                    <th>Status</th>
                                    <td>
                                        <span class="status-badge status-{{ $deliveryNote->status ?? 'pending' }}">
                                            {{ $deliveryNote->status ?? 'pending' }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Sales Order Number</th>
                                    <td>{{ $deliveryNote->salesOrder->order_number ?? '' }}</td>
                                </tr>
                                <tr>
                                    <th>Sales Agent</th>
                                    <td>{{ $deliveryNote->salesOrder->agent->name ?? '' }}</td>
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
                        <th colspan="2">CUSTOMER INFORMATION</th>
                    </tr>
                </thead>
                @php $customer = $deliveryNote->salesOrder->customer; @endphp
                <tbody>
                    @if (!empty($customer->name))
                        <tr>
                            <th>Customer Name</th>
                            <td>{{ $customer->name }}</td>
                        </tr>
                    @endif

                    @if (!empty($customer->company_name))
                        <tr>
                            <th>Company Name</th>
                            <td>{{ $customer->company_name }}</td>
                        </tr>
                    @endif

                    @if (!empty($customer->address))
                        <tr>
                            <th>Address</th>
                            <td>{{ $customer->address }}</td>
                        </tr>
                    @endif

                    @if (!empty($customer->phone))
                        <tr>
                            <th>Phone</th>
                            <td>{{ $customer->phone }}</td>
                        </tr>
                    @endif

                    @if (!empty($customer->email))
                        <tr>
                            <th>Email</th>
                            <td>{{ $customer->email }}</td>
                        </tr>
                    @endif

                    @if (!empty($deliveryNote->salesOrder->payment_method))
                        <tr>
                            <th>Payment Method</th>
                            <td>{{ ucfirst(str_replace('_', ' ', $deliveryNote->salesOrder->payment_method)) }}</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>

        <div class="products-section">
            <table class="products-table">
                <thead>
                    <tr>
                        <th style="width: 15%;">Product Code</th>
                        <th style="width: 25%;">Product Name</th>
                        <th style="width: 10%;">Strength</th>
                        <th style="width: 8%;">Unit</th>
                        <th style="width: 8%;">Ordered Qty</th>
                        <th style="width: 8%;">Delivered Qty</th>
                        <th style="width: 8%;">Backorder Qty</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($deliveryNote->items as $item)
                        <tr>
                            <td>{{ $item->product->product_code ?? '' }}</td>
                            <td>{{ $item->product->name ?? '' }}</td>
                            <td>{{ $item->product->strength ?? '' }}</td>
                            <td>{{ $item->product->unit->name ?? '' }}</td>
                            <td>{{ $item->ordered_qty ?? 0 }}</td>
                            <td>{{ $item->delivered_qty ?? 0 }}</td>
                            <td>{{ $item->backorder_qty ?? 0 }}</td>
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
                                    <th>{{ $deliveryNote->remarks ?? '' }}</th>
                                </tr>
                            </tbody>
                        </table>
                    </td>
                    <td class="totals-cell">
                        <table class="totals-table">
                            <tbody>
                                <tr>
                                    <th>Total Items Ordered</th>
                                    <td>{{ $deliveryNote->items->sum('ordered_qty') ?? 0 }}</td>
                                </tr>
                                <tr>
                                    <th>Total Items Delivered</th>
                                    <td>{{ $deliveryNote->items->sum('delivered_qty') ?? 0 }}</td>
                                </tr>
                                <tr>
                                    <th>Total Backorder Items</th>
                                    <td>{{ $deliveryNote->items->sum('backorder_qty') ?? 0 }}</td>
                                </tr>
                                <tr class="grand-total-row">
                                    <th>Delivery Completion</th>
                                    <td>
                                        @php
                                            $ordered = $deliveryNote->items->sum('ordered_qty');
                                            $delivered = $deliveryNote->items->sum('delivered_qty');
                                            $completion = $ordered > 0 ? ($delivered / $ordered) * 100 : 0;
                                        @endphp
                                        {{ number_format($completion, 1) }}%
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </td>
                </tr>
            </table>
        </div>

        <div class="acceptance-section no-break">
            <p class="acceptance-title">Customer Acceptance</p>
            <table class="acceptance-table">
                <thead>
                    <tr>
                        <th>Name and Signature</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="footer">
            <p class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed">
                If you have any questions, feel free to contact us at <strong>(045) 455-1402</strong> or <strong>(045)
                    455-1517</strong>,<br><strong>0932-888-3548</strong> or <strong>0932-888-3547</strong>, or email us
                at <u>sanvenmedinc@yahoo.com.ph</u>.
            </p>
            <br>
            <p>Thank you for your business!</p>
        </div>
    </div>
</body>

</html>
