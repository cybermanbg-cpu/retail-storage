<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Стокова разписка {{ $receipt->receipt_number }}</title>
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
            font-size: 18px;
        }
        .header p {
            margin: 5px 0;
            font-size: 10px;
            color: #666;
        }
        .info {
            margin-bottom: 20px;
            width: 100%;
        }
        .info td {
            padding: 3px 0;
        }
        .info .label {
            font-weight: bold;
            width: 100px;
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
        /* .totals {
            width: 300px;
            float: right;
            margin-bottom: 20px;
        } */
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
        .payment-method {
            margin-top: 20px;
            padding: 10px;
            background-color: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>СТОКОВА РАЗПИСКА</h1>
        <p>Оригинал</p>
    </div>
    
    <table class="info">
        <tr>
            <td class="label">Номер:</td>
            <td>{{ $receipt->receipt_number }}</td>
            <td class="label">Дата:</td>
            <td>{{ $receipt->created_at->format('d.m.Y H:i') }}</td>
        </tr>
        <tr>
            <td class="label">Обект:</td>
            <td>{{ $receipt->storageObject->name }}</td>
            <td class="label">Касиер:</td>
            <td>{{ $receipt->user->name }}</td>
        </tr>
        @if($receipt->client)
        <tr>
            <td class="label">Клиент:</td>
            <td colspan="3">{{ $receipt->client->name }} ({{ $receipt->client->phone ?? 'без телефон' }})</td>
        </tr>
        @endif
    </table>
    
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
            @foreach($receipt->items as $index => $item)
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
                <td style="border: none;" class="text-right">{{ number_format($receipt->total_amount, 2) }} €</td>
            </tr>
            <tr>
                <td style="border: none;">ДДС (20%):</td>
                <td style="border: none;" class="text-right">{{ number_format($receipt->total_vat, 2) }} €</td>
            </tr>
            <tr>
                <td style="border: none; font-weight: bold;">ОБЩО:</td>
                <td style="border: none; font-weight: bold;" class="text-right">{{ number_format($receipt->total_amount + $receipt->total_vat, 2) }} €</td>
            </tr>
        </table>
    </div>
    
    <div class="payment-method">
        <strong>Начин на плащане:</strong> 
        @switch($receipt->payment_method)
            @case('cash') В брой @break
            @case('card') Карта @break
            @case('bank_transfer') Банков превод @break
            @default — @endswitch
        
        @if($receipt->amount_paid)
            <br><strong>Получена сума:</strong> {{ number_format($receipt->amount_paid, 2) }} €
        @endif
        @if($receipt->change_amount)
            <br><strong>Ресто:</strong> {{ number_format($receipt->change_amount, 2) }} €
        @endif
    </div>
    
    <div class="footer">
        <p>Благодарим ви за покупката!</p>
        <p>Този документ е генериран автоматично и не изисква подпис.</p>
    </div>
</body>
</html>