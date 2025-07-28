@props([
    'quotation' => 'null'
])

<div class="bg-white p-15 rounded-lg flex flex-col space-y-10">
    <div class="flex flex-col sm:flex-row sm:justify-between items-center gap-5">
            <div><x-app-logo-icon class="h-10 w-auto"/></div>
            <div class="text-3xl md:text-5xl font-bold text-(--color-accent)">Quotation</div>
    </div>

    <div class="flex flex-col gap-y-4 lg:flex-row lg:justify-between">
        <div>
            <div>
                <div>
                    <span class="text-base lg:text-lg font-bold">Sanven Medical Ent., Inc.</span>
                </div>
            </div>
            <div>
                <div>
                    <span class="text-sm">
                        Blk. 22 Lot 10 Phase 2, Nevada St., Suburbia North, Malpitic 2000, 
                    </span>
                </div>
                <div>
                    <span class="text-sm">
                        City of San Fernando (Capital), Pampanga, Philippines
                    </span>
                </div>
                <div>
                    <div>
                        <span class="text-sm">
                            Tel. # (045) 455-1402; (045) 455-1517
                        </span>
                    </div>
                    <div>
                        <span class="text-sm">
                           Cel. Nos. 0932-888-3548/0932-888-3547
                        </span>
                    </div>
                </div>
                <div>
                    <span class="text-sm">
                        VAT Reg. TIN: 219-532-832-00000
                    </span>
                </div>
                <div>
                    <span class="text-sm">
                        sanvenmedinc@yahoo.com.ph
                    </span>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                <tbody>
                    <tr class="bg-white dark:bg-gray-800">
                        <th scope="row" class="lg:px-6 md:py-4 pr-3 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                            Date
                        </th>
                        <td class="px-6 py-4 text-zinc-700 border dark:border-gray-700 border-(--color-accent)">
                            {{ $quotation->created_at->format('M d, Y') }}
                        </td>
                    </tr>
                    <tr class="bg-white dark:bg-gray-800">
                        <th scope="row" class="lg:px-6 lg:py-4 pr-3 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                            Quotation Number
                        </th>
                        <td class="px-6 py-4 text-zinc-700 border-l border-r border-b dark:border-gray-700 border-(--color-accent)">
                            {{ $quotation->quotation_number }}
                        </td>
                    </tr>
                    <tr class="bg-white dark:bg-gray-800">
                        <th scope="row" class="lg:px-6 lg:py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                            Customer ID
                        </th>
                        <td class="px-6 py-4 pr-3 text-zinc-700 border-l border-r border-b dark:border-gray-700 border-(--color-accent)">
                            {{ $quotation->customer->id }}
                        </td>
                    </tr>
                    <tr class="bg-white dark:bg-gray-800">
                        <th scope="row" class="lg:px-6 lg:py-4 pr-3 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                            Valid Until
                        </th>
                        <td class="px-6 py-4 text-zinc-700 border-l border-r border-b dark:border-gray-700 border-(--color-accent)">
                            {{ \Carbon\Carbon::parse($quotation->valid_until)->format('M d, Y') }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
            <thead class="text-xs text-white uppercase bg-(--color-accent) border border-(--color-accent) dark:bg-gray-700 dark:text-gray-400">
                <tr>
                    <th scope="col" colspan="2" class="px-6 py-3">
                        Customer Information
                    </th>
                </tr>
            </thead>
            <tbody>
                @if (!empty($quotation->customer->name))
                <tr class="bg-white border-l border-r border-b dark:bg-gray-800 dark:border-gray-700 border-zinc-400">
                    <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                        Customer Name
                    </th>
                    <td class="px-6 py-4 text-zinc-700">
                        {{ $quotation->customer->name }}
                    </td>
                </tr>
                @endif

                @if (!empty($quotation->customer->email))
                <tr class="bg-white border-l border-r border-b dark:bg-gray-800 dark:border-gray-700 border-zinc-400">
                    <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                        Email
                    </th>
                    <td class="px-6 py-4 text-zinc-700">
                        {{ $quotation->customer->email }}
                    </td>
                </tr>
                @endif

                @if (!empty($quotation->customer->phone))
                <tr class="bg-white border-l border-r border-b dark:bg-gray-800 dark:border-gray-700 border-zinc-400">
                    <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                        Phone
                    </th>
                    <td class="px-6 py-4 text-zinc-700">
                        {{ $quotation->customer->phone }}
                    </td>
                </tr>
                @endif
            </tbody>
        </table>
    </div>

    <div class="max-w-full">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                <thead class="text-xs text-white uppercase bg-(--color-accent) border border-(--color-accent) dark:bg-gray-700 dark:text-gray-400">
                    <tr>
                        <th scope="col" class="px-6 py-3">
                            Product name
                        </th>
                        <th scope="col" colspan="2" class="px-6 py-3">
                            Description
                        </th>
                        <th scope="col" class="px-6 py-3">
                            Qty
                        </th>
                        <th scope="col" class="px-6 py-3">
                            Unit Price
                        </th>
                        <th scope="col" class="px-6 py-3">
                            VAT (0.12%)
                        </th>
                        <th scope="col" class="px-6 py-3">
                            TOTAL
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($quotation->items as $item)
                        <tr class="bg-white border-l border-r border-b dark:bg-gray-800 dark:border-gray-700 border-zinc-400">
                            <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                <span>{{ $item->product->name ?? '' }}</span>
                            </th>
                            <td class="px-6 py-4 text-zinc-700 " colspan="2">
                                <span>{{ $item->description ?? ''}}</span>
                            </td>
                            <td class="px-6 py-4 text-zinc-700 ">
                                <span>{{ $item->quantity ?? 0 }}</span>
                            </td>
                            <td class="px-6 py-4 text-zinc-700 ">
                                <span style="font-family: Arial;">&#8369;</span> 
                                <span>{{ number_format($item->unit_price, 2) ?? 0 }}</span>
                            </td>
                            <td class="px-6 py-4 text-zinc-700 ">
                                <span style="font-family: Arial;">&#8369;</span> 
                                <span>{{ number_format($item->vat_tax) ?? 0.00  }}</span>
                            </td>
                            <td class="px-6 py-4 text-zinc-700 ">
                                <span style="font-family: Arial;">&#8369;</span> 
                                <span>{{ number_format($item->total_price, 2) ?? 0.00 }}</span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="flex flex-col-reverse lg:flex-row gap-4">
            <div class="flex-2 w-full lg:w-2/3 mt-5 mr-10">
                <div class="h-full overflow-x-auto">
                    <table class="w-full min-h-full text-sm text-left text-gray-500 dark:text-gray-400">
                        <thead class="text-xs text-white uppercase bg-(--color-accent) border border-(--color-accent) dark:bg-gray-700 dark:text-gray-400">
                            <tr>
                                <th scope="col" class="px-6 py-3">
                                Special Notes and Instructions
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="bg-white border-l border-r border-b dark:bg-gray-800 dark:border-gray-700 border-zinc-400">
                                <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                    <span>{{ $quotation->notes ?? '' }}</span>
                                </th>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="flex-1 lg:w-1/3 mt-10 sm:mt-0">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                        <tbody>
                            <tr class="bg-white border-l border-r border-b dark:bg-gray-800 dark:border-gray-700 border-zinc-400">
                                <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                    Subtotal
                                </th>
                                <td class="px-6 py-4 text-(--color-accent) font-bold">
                                    <span style="font-family: Arial;">&#8369;</span> 
                                    {{ number_format(($quotation->items->sum('total_price')), 2) }}
                                </td>
                            </tr>
                            <tr class="bg-white border-l border-r border-b dark:bg-gray-800 dark:border-gray-700 border-zinc-400">
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
                                <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                    Discount ({{ number_format($rate, 2) }}%)
                                </th>
                                <td class="px-6 py-4 text-(--color-accent-2)">
                                    -
                                    <span style="font-family: Arial;">&#8369;</span> 
                                    {{ number_format($discount, 2) }}
                                </td>
                            </tr>
                            <tr class="bg-white border-l border-r border-b dark:bg-gray-800 dark:border-gray-700 border-zinc-400">
                                <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                    VAT
                                </th>
                                <td class="px-6 py-4 text-(--color-accent-2)">
                                    <span style="font-family: Arial;">&#8369;</span> 
                                    {{ number_format($quotation->tax ?? 0, 2) }}
                                </td>
                            </tr>
                            <tr class="bg-(--color-accent) border-l border-r border-b dark:bg-gray-800 dark:border-gray-700 border-zinc-400">
                                <th scope="row" class="px-6 py-4 font-bold text-white whitespace-nowrap dark:text-white">
                                    GRAND TOTAL
                                </th>
                                <td class="px-6 py-4 text-white font-bold">
                                    <span class="text-base" style="font-family: Arial;">&#8369;</span> 
                                    {{ number_format($quotation->total_amount ?? 0, 2) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div>
        <span class="text-sm">This quotation is not a contract or a bill. It is our best guess at the total price for the service and goods described above. 
            The customer will be billed after indicating acceptance of this quote. Payment will be due prior to the delivery of service and goods.
            Please fax or mail the signed quote to the address listed above.
        </span>
    </div>

    <div>
        <div>
            <span class="text-base lg:text-lg font-bold">Customer Acceptance</span>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                    <thead class="text-xs text-white uppercase bg-(--color-accent) border border-(--color-accent) dark:bg-gray-700 dark:text-gray-400">
                        <tr>
                            <th scope="col" class="px-6 py-3">Name</th>
                            <th scope="col" class="px-6 py-3">Signature</th>
                            <th scope="col" class="px-6 py-3">Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="bg-white dark:bg-gray-800">
                            <td class="px-6 py-4 border-l border-r border-b border-zinc-400 dark:border-gray-700"></td>
                            <td class="px-6 py-4 border-l border-r border-b border-zinc-400 dark:border-gray-700"></td>
                            <td class="px-6 py-4 border-l border-r border-b border-zinc-400 dark:border-gray-700"></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="flex flex-col w-full justify-center items-center">
        <div class="flex items-center w-full justify-center text-center"> 
            <span class="text-sm">
                If you have any questions, feel free to contact us at <strong>(045) 455-1402</strong> or <strong>(045) 455-1517</strong>, <strong>0932-888-3548</strong> or <strong>0932-888-3547</strong>, or email us at <u>sanvenmedinc@yahoo.com.ph</u>.
            </span>

        </div>
        <div class="mt-2"> 
            <span class="text-sm">Thank you for your business!</span>
        </div>
    </div>
</div>