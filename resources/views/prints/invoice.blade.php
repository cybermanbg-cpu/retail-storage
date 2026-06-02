<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Фактура {{ $invoice->invoice_number }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            line-height: 1.4;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            font-size: 20px;
        }
        .header p {
            margin: 5px 0;
            font-size: 10px;
            color: #666;
        }
        .company-info, .client-info {
            margin-bottom: 20px;
            width: 100%;
        }
        .company-info td, .client-info td {
            padding: 3px 0;
        }
        .company-info .label, .client-info .label {
            font-weight: bold;
            width: 100px;
        }
        .invoice-info {
            margin-bottom: 20px;
            border: 1px solid #ddd;
            padding: 10px;
            background-color: #f9f9f9;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .text-right {
            text-align: right;
        }
        .totals {
            width: 300px;
            float: right;
            margin-bottom: 20px;
        }
        .totals td {
            padding: 5px;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>ФАКТУРА</h1>
        <p>Оригинал</p>
    </div>
    
    <table class="company-info">
        <tr>
            <td class="label">Фирма:</td>
            <td>{{ $invoice->owner->company_name ?? $invoice->owner->name ?? 'N/A' }}</td>
            <td class="label">ЕИК:</td>
            <td>{{ $invoice->owner->vat_number ?? '—' }}</td>
        </tr>
        <tr>
            <td class="label">Адрес:</td>
            <td colspan="3">—</td>
        </tr>
    </table>
    
    <div class="invoice-info">
        <table style="border: none; margin-bottom: 0;">
            <tr>
                <td style="border: none; width: 50%;">Номер: <strong>{{ $invoice->invoice_number }}</strong></td>
                <td style="border: none; width: 50%;">Дата на издаване: {{ $invoice->issue_date->format('d.m.Y') }}</td>
            </tr>
            @if($invoice->due_date)
            <tr>
                <td style="border: none;">Падеж: {{ $invoice->due_date->format('d.m.Y') }}</td>
                <td style="border: none;"></td>
            </tr>
            @endif
        </table>
    </div>
    
    <table class="client-info">
        <tr>
            <td class="label">Клиент:</td>
            <td colspan="3">{{ $invoice->client->name }}</td>
        </tr>
        @if($invoice->client->vat_number)
        <tr>
            <td class="label">ЕИК/ДДС:</td>
            <td colspan="3">{{ $invoice->client->vat_number }}</td>
        </tr>
        @endif
        @if($invoice->client->address)
        <tr>
            <td class="label">Адрес:</td>
            <td colspan="3">{{ $invoice->client->address }}</td>
        </tr>
        @endif
    </table>
    
    <h3>Детайли на фактурата</h3>
    
    @php
        $items = [];
        foreach($invoice->receipts as $receipt) {
            foreach($receipt->items as $item) {
                $items[] = $item;
            }
        }
    @endphp
    
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Продукт</th>
                <th>Вариант</th>
                <th class="text-right">Количество</th>
                <th class="text-right">Ед. цена</th>
                <th class="text-right">ДДС</th>
                <th class="text-right">Сума</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $index => $item)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $item->product_name_snapshot }}</td>
                <td>
                    @if($item->color_name || $item->size_name)
                        {{ $item->color_name ?? '' }} {{ $item->size_name ?? '' }}
                    @else
                        —
                    @endif
                </td>
                <td class="text-right">{{ number_format($item->quantity, 3) }} {{ $item->unit_of_measure_snapshot ?? 'бр.' }}</td>
                <td class="text-right">{{ number_format($item->unit_price, 2) }} €</td>
                <td class="text-right">{{ number_format($item->vat_rate, 0) }}%</td>
                <td class="text-right">{{ number_format($item->total, 2) }} €</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    
    <div class="totals">
        <table>
            <tr>
                <td style="border: none;">Междинна сума:</td>
                <td style="border: none;" class="text-right">{{ number_format($invoice->subtotal, 2) }} €</td>
            </tr>
            @if($invoice->discount > 0)
            <tr>
                <td style="border: none;">Отстъпка:</td>
                <td style="border: none;" class="text-right">{{ number_format($invoice->discount, 2) }} €</td>
            </tr>
            @endif
            <tr>
                <td style="border: none;">ДДС:</td>
                <td style="border: none;" class="text-right">{{ number_format($invoice->vat, 2) }} €</td>
            </tr>
            <tr>
                <td style="border: none; font-weight: bold;">ОБЩО:</td>
                <td style="border: none; font-weight: bold;" class="text-right">{{ number_format($invoice->total, 2) }} €</td>
            </tr>
        </table>
    </div>
    
    <div class="footer">
        <p>Моля, извършете плащане в срок до {{ $invoice->due_date ? $invoice->due_date->format('d.m.Y') : '14 дни' }}.</p>
        <p>Този документ е генериран автоматично и не изисква подпис.</p>
    </div>
</body>
</html>