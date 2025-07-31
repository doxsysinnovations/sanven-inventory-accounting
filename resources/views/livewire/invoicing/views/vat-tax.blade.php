@if(isset($item['vat_tax']) && $item['vat_tax'] > 0)
    <span>
        Php {{ number_format($item['vat_tax'], 2) }}
    </span>
@else
    <span>
        Php 0.00
    </span>
@endif