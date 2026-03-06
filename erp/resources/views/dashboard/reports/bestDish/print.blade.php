<!DOCTYPE html>
<html lang="{{app()->getLocale()}}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{app()->getLocale() == 'en' ? 'Best Seller Report' : 'تقرير الاكثر مبيعا'}}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            direction: {{app()->getLocale() == 'en' ? 'ltr' : 'rtl'}};
            margin: 30px;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .table th, .table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: {{ app()->getLocale() == 'ar' ? 'right' : 'left' }};
        }
        .table th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>

<h1>@lang('print.DishDetails'): {{ app()->getLocale() == 'en' ? $dish->name_en : $dish->name_ar }}</h1>
<p><strong>@lang('print.Description'):</strong> {{ app()->getLocale() == 'en' ? $dish->description_en : $dish->description_ar }}</p>
<p><strong>@lang('print.Price'):</strong> {{ $dish->price }} {{ $dish->currency_symbol }}</p>
<p><strong>@lang('print.BranchesOrdered'):</strong> {{ $dish->branch_names }}</p>

<h2>@lang('print.BranchesDetails')</h2>
<table class="table">
    <thead>
    <tr>
        <th>@lang('print.BranchName')</th>
        <th>@lang('print.Address')</th>
        <th>@lang('print.Quantity')</th>
        <th>@lang('print.TotalPrice')</th>
        <th>@lang('print.PriceBeforeTax')</th>
        <th>@lang('print.PriceAfterTax')</th>
        <th>@lang('print.TaxValue')</th>
        <th>@lang('print.Notes')</th>
    </tr>
    </thead>
    <tbody>
    @foreach ($branches as $branch)
        <tr>
            <td>{{ app()->getLocale() == 'en' ? $branch->name_en : $branch->name_ar }}</td>
            <td>{{ app()->getLocale() == 'en' ? $branch->address_en : $branch->address_ar }}</td>
            <td>{{ $branch->quantity }}</td>
            <td>{{ $branch->total_price }} {{ $dish->currency_symbol }}</td>
            <td>{{ $branch->price_befor_tax }} {{ $dish->currency_symbol }}</td>
            <td>{{ $branch->price_after_tax }} {{ $dish->currency_symbol }}</td>
            <td>{{ $branch->tax_value }} {{ $dish->currency_symbol }}</td>
            <td>{{ $branch->note }}</td>
        </tr>
    @endforeach
    </tbody>
</table>

<script>
    // Trigger the print dialog as soon as the page loads
    window.onload = function() {
        window.print();
    };
</script>

</body>
</html>
