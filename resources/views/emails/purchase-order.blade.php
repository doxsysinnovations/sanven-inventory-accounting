{{-- filepath: resources/views/emails/purchase-order.blade.php --}}
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Purchase Order #{{ $po->po_number }}</title>
    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: #f8fafc;
            color: #222;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 700px;
            margin: 30px auto;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 8px #e2e8f0;
            padding: 32px;
        }

        .logo {
            text-align: center;
            margin-bottom: 24px;
        }

        .logo img {
            max-height: 60px;
        }

        h2 {
            color: #2563eb;
            margin-bottom: 0;
        }

        h3 {
            color: #0f172a;
            margin-top: 32px;
        }

        table {
            border-collapse: collapse;
            width: 100%;
            margin-top: 16px;
        }

        th,
        td {
            border: 1px solid #e5e7eb;
            padding: 8px 12px;
            text-align: left;
        }

        th {
            background: #f1f5f9;
            color: #1e293b;
        }

        tr:nth-child(even) {
            background: #f9fafb;
        }

        .summary {
            margin-top: 24px;
        }

        .summary td {
            border: none;
        }

        .label {
            color: #64748b;
            font-size: 13px;
        }

        .value {
            font-weight: 500;
        }

        .footer {
            margin-top: 40px;
            color: #64748b;
            font-size: 13px;
            text-align: center;
        }

        .cta-btn {
            display: inline-block;
            margin: 24px 0 0 0;
            padding: 12px 32px;
            background: #2563eb;
            color: #fff !important;
            border-radius: 6px;
            text-decoration: none;
            font-size: 16px;
            font-weight: 600;
            box-shadow: 0 2px 6px #e0e7ef;
            transition: background 0.2s;
        }

        .cta-btn:hover {
            background: #1d4ed8;
        }

        .section-title {
            font-size: 18px;
            color: #2563eb;
            margin-top: 32px;
            margin-bottom: 8px;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="logo">
            <img src="https://sanven.doxsys.io/wp-content/uploads/2025/03/Sanven-Logo.svg" alt="SANVEN Logo">
        </div>

        <h2>Purchase Order #{{ $po->po_number }}</h2>
        <p style="font-size: 16px; color: #222;">
            Dear {{ $po->supplier->trade_name ?? 'Supplier' }},<br>
            Please find below the details of your purchase order from SANVEN. If you have any questions or need
            clarification, kindly reply to this email or contact us directly.<br>
            Thank you for your partnership.
        </p>
        <p class="label">Date: <span class="value">{{ $po->created_at ? $po->created_at->format('F d, Y') : '' }}</span>
        </p>
        <p class="label">Status: <span class="value">{{ ucfirst($po->status) }}</span></p>

        <div class="section-title">Supplier Information</div>
        <table>
            <tr>
                <th>Name</th>
                <td>{{ $po->supplier->trade_name ?? '' }}</td>
            </tr>
            <tr>
                <th>Email</th>
                <td>{{ $po->supplier->email ?? '' }}</td>
            </tr>
            <tr>
                <th>Address</th>
                <td>{{ $po->supplier->address ?? '' }}</td>
            </tr>
            <tr>
                <th>Contact</th>
                <td>{{ $po->supplier->contact_number ?? '' }}</td>
            </tr>
        </table>

        <div class="section-title">Order Information</div>
        <table>
            <tr>
                <th>Order Type</th>
                <td>{{ ucfirst($po->order_type) }}</td>
            </tr>
            <tr>
                <th>Payment Terms</th>
                <td>{{ $po->payment_terms ?? 'N/A' }}</td>
            </tr>
            <tr>
                <th>Remarks</th>
                <td>{{ $po->remarks ?? 'N/A' }}</td>
            </tr>
            <tr>
                <th>Requested By</th>
                <td>{{ $po->purchaser->name ?? 'N/A' }}</td>
            </tr>
        </table>

        <div class="section-title">Order Items</div>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Product Code</th>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Qty</th>
                    <th>UOM</th>
                    <th>Unit Price</th>
                    <th>VATable</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($po->items as $i => $item)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $item->product->product_code ?? 'N/A' }}</td>
                        <td>{{ $item->product->name ?? 'N/A' }}</td>
                        <td>{{ $item->product->description ?? 'N/A' }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>{{ $item->uom ?? ($item->product->uom ?? '') }}</td>
                        <td>₱{{ number_format($item->price, 2) }}</td>
                        <td>
                            @if (isset($item->product) && $item->product->is_vatable)
                                <span style="color: #16a34a;">Yes</span>
                            @else
                                <span style="color: #64748b;">No</span>
                            @endif
                        </td>
                        <td>₱{{ number_format($item->quantity * $item->price, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        @php
            $vatRate = 0.12;
            $vatableTotal = $po->items->sum(function ($item) {
                return isset($item->product) && $item->product->is_vatable ? $item->price * $item->quantity : 0;
            });
            $vat = $vatableTotal * $vatRate;
            $subtotal = $po->items->sum(fn($item) => $item->price * $item->quantity);
            $grandTotal = $subtotal + $vat;
        @endphp

        <table class="summary">
            <tr>
                <td class="label">Subtotal:</td>
                <td class="value">₱{{ number_format($subtotal, 2) }}</td>
            </tr>
            <tr>
                <td class="label">VAT (12%):</td>
                <td class="value">₱{{ number_format($vat, 2) }}</td>
            </tr>
            <tr>
                <td class="label" style="font-size: 16px;">Grand Total:</td>
                <td class="value" style="font-size: 16px;">₱{{ number_format($grandTotal, 2) }}</td>
            </tr>
        </table>

        {{-- Approval Button (only show if sending to admin/approver) --}}
        @if (isset($approvalUrl) && $approvalUrl)
            <div style="text-align:center;">
                <a href="{{ $approvalUrl }}" class="cta-btn">Approve this Purchase Order</a>
            </div>
        @endif

        <div class="footer">
            This is an automated purchase order from SANVEN.<br>
            For questions, please contact {{ $po->purchaser->email ?? 'our office' }}.
        </div>
    </div>
</body>

</html>
