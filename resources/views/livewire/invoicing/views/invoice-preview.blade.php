
@props([
    'selectedInvoice' => 'selectedInvoice'
])

<div class="bg-white dark:bg-(--color-accent-dark) p-15 rounded-lg flex flex-col space-y-10">
    <div class="flex flex-col sm:flex-row sm:justify-between items-center gap-5">
        <div><x-app-logo-icon class="h-10 w-auto"/></div>
        <div class="text-3xl md:text-5xl font-bold text-(--color-accent)">Invoice</div>
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
                    @php
                        $rows = [
                            'Issued Date' => \Carbon\Carbon::parse($selectedInvoice->issued_date ?? '')->format('M d, Y'),
                            'Invoice #' => $selectedInvoice->invoice_number ?? '',
                            'Sales Agent' => $selectedInvoice->agent->name ?? '',
                            'Due:' => \Carbon\Carbon::parse($selectedInvoice->due_date ?? '')->format('M d, Y'),
                        ];
                    @endphp

                    @foreach ($rows as $label => $value)
                        <tr class="bg-white dark:bg-(--color-accent-2-dark)">
                            <th class="lg:px-6 md:py-4 pr-3 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                {{ $label }}
                            </th>
                            <td class="px-6 py-4 text-zinc-700 border border-(--color-accent) dark:bg-(--color-accent-4-dark) dark:border-none dark:text-white">
                                {{ $value }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="overflow-x-auto">
       <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
            <thead class="text-xs text-white uppercase bg-(--color-accent) border border-(--color-accent) dark:bg-(--color-accent-2-dark) dark:text-gray-400 dark:border-none">
                <tr>
                    <th colspan="2" class="px-6 py-3">BILL TO</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $customerFields = [
                        'Customer Name' => $selectedInvoice->customer->name ?? '',
                        'Company Name' => $selectedInvoice->customer->company_name ?? '',
                        'Adresss' => $selectedInvoice->customer->address ?? '',
                        'Phone' =>  $selectedInvoice->customer->phone ?? '',
                        'Email' =>  $selectedInvoice->customer->email ?? '',
                        'Payment Method' => ucfirst(str_replace('_', ' ', $selectedInvoice->payment_method ?? 'N/A')),
                    ];
                @endphp

                @foreach ($customerFields as $label => $value)
                    @if (!empty($value))
                        <tr class="bg-white border border-zinc-400 dark:bg-(--color-accent-4-dark) dark:border-none">
                            <th class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                {{ $label }}
                            </th>
                            <td class="px-6 py-4 text-zinc-700 dark:text-white">
                                {{ $value }}
                            </td>
                        </tr>
                    @endif
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="max-w-full">
        <div class="overflow-x-auto">
            @php
                $currency = '<span style="font-family: Arial;">&#8369;</span>';
            @endphp

            <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                <thead class="text-xs text-white uppercase bg-(--color-accent) border border-(--color-accent) dark:bg-(--color-accent-2-dark) dark:text-gray-400 dark:border-none">
                    <tr>
                        @foreach (['Item', 'Notes' => 2, 'Qty', 'Price', 'VAT (0.12%)', 'TOTAL'] as $label => $colspan)
                            <th scope="col" class="px-6 py-3" @if(is_int($colspan)) colspan="{{ $colspan }}" @endif>
                                {{ is_int($label) ? $colspan : $label }}
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach ($selectedInvoice->items ?? [] as $item)
                        <tr class="bg-white border-b border-x border-zinc-400 dark:bg-(--color-accent-4-dark) dark:border-none">
                            <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                {{ $item->product_name ?? '' }}
                            </th>
                            <td colspan="2" class="px-6 py-4 text-zinc-700 dark:text-white">{{ $item->notes ?? '' }}</td>
                            <td class="px-6 py-4 text-zinc-700 dark:text-white">{{ $item->quantity ?? 0 }}</td>
                            <td class="px-6 py-4 text-zinc-700 dark:text-white">{!! $currency !!} {{ number_format($item->price ?? 0, 2) }}</td>
                            <td class="px-6 py-4 text-zinc-700 dark:text-white">{!! $currency !!} {{ number_format($item->tax ?? 0, 2) }}</td>
                            <td class="px-6 py-4 text-zinc-700 dark:text-white">{!! $currency !!} {{ number_format($item->total ?? 0, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="flex flex-col-reverse lg:flex-row gap-4">
            <div class="flex-2 w-full lg:w-2/3 mt-5 mr-10">
                <div class="h-full overflow-x-auto">
                    <table class="w-full min-h-full text-sm text-left text-gray-500 dark:text-gray-400">
                        <thead class="text-xs text-white uppercase bg-(--color-accent) border border-(--color-accent) dark:bg-(--color-accent-2-dark) dark:text-gray-400 dark:border-none">
                            <tr>
                                <th scope="col" class="px-6 py-3">
                                Special Notes and Instructions
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="bg-white border-l border-r border-b dark:bg-(--color-accent-4-dark) dark:border-none border-zinc-400">
                                <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                    <span>{{ $selectedInvoice->notes }}</span>
                                </th>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="flex-1 lg:w-1/3 mt-10 sm:mt-0">
                <div class="overflow-x-auto">
                    @php
                        $base = $selectedInvoice->items->sum('total');
                        $discount = $selectedInvoice->discount_type === 'percentage' 
                            ? ($selectedInvoice->discount / 100) * $base 
                            : $selectedInvoice->discount;
                        $rate = $base > 0 ? ($discount / $base) * 100 : 0;
                    @endphp

                    <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                        <tbody>
                            <tr class="bg-white border-l border-r border-b dark:bg-(--color-accent-4-dark)  dark:border-none border-zinc-400">
                                <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">Subtotal</th>
                                <td class="px-6 py-4 text-(--color-accent) font-bold">
                                    <span style="font-family: Arial;">&#8369;</span> {{ number_format($base, 2) }}
                                </td>
                            </tr>
                            <tr class="bg-white border-l border-r border-b dark:bg-(--color-accent-4-dark) dark:border-none border-zinc-400">
                                <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                    Discount ({{ number_format($rate, 2) }}%)
                                </th>
                                <td class="px-6 py-4 text-(--color-accent-2)">
                                    -<span style="font-family: Arial;">&#8369;</span> {{ number_format($discount, 2) }}
                                </td>
                            </tr>
                            <tr class="bg-white border-l border-r border-b dark:bg-(--color-accent-4-dark)  dark:border-none border-zinc-400">
                                <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">VAT</th>
                                <td class="px-6 py-4 text-(--color-accent-2)">
                                    <span style="font-family: Arial;">&#8369;</span> {{ number_format($selectedInvoice->tax ?? 0, 2) }}
                                </td>
                            </tr>
                            <tr class="bg-(--color-accent) border-l border-r border-b dark:bg-(--color-accent-2-dark) dark:border-none border-zinc-400">
                                <th scope="row" class="px-6 py-4 font-bold text-white whitespace-nowrap dark:text-white">GRAND TOTAL</th>
                                <td class="px-6 py-4 text-white font-bold">
                                    <span class="text-base" style="font-family: Arial;">&#8369;</span> {{ number_format($selectedInvoice->grand_total ?? 0, 2) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div>
        <div>
            <span class="text-base lg:text-lg font-bold">Issued By</span>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                    <thead class="text-xs text-white uppercase bg-(--color-accent) border border-(--color-accent) dark:bg-(--color-accent-2-dark) dark:text-gray-400 dark:border-none">
                        <tr>
                            <th class="px-6 py-3">Name and Signature</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="bg-white dark:bg-(--color-accent-4-dark)">
                            @for ($i = 0; $i < 1; $i++)
                                <td class="px-6 py-4 border border-zinc-400 dark:border-none"></td>
                            @endfor
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