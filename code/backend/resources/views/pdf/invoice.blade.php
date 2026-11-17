<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Hóa đơn {{ $invoice->invoice_code }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #1e293b;
            line-height: 1.5;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            border-bottom: 2px dashed #cbd5e1;
            padding-bottom: 12px;
            margin-bottom: 15px;
        }
        .header h1 {
            margin: 0;
            font-size: 18px;
            color: #0f172a;
        }
        .header p {
            margin: 2px 0;
            color: #64748b;
            font-size: 11px;
        }
        .info-table {
            width: 100%;
            margin-bottom: 15px;
            font-size: 11px;
        }
        .info-table td {
            padding: 2px 0;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .items-table th {
            border-bottom: 1px solid #0f172a;
            border-top: 1px solid #0f172a;
            padding: 6px 4px;
            text-align: left;
            font-size: 11px;
        }
        .items-table td {
            padding: 6px 4px;
            border-bottom: 1px solid #f1f5f9;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .summary-table {
            width: 100%;
            border-top: 1px dashed #cbd5e1;
            padding-top: 8px;
            margin-bottom: 20px;
        }
        .summary-table td {
            padding: 3px 0;
        }
        .total-row {
            font-size: 14px;
            font-weight: bold;
            color: #ea580c;
            border-top: 1px solid #0f172a;
        }
        .footer {
            text-align: center;
            font-size: 11px;
            color: #64748b;
            border-top: 1px dashed #cbd5e1;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $restaurant->name ?? 'QUÁN BẾP NHỎ' }}</h1>
        <p>{{ $restaurant->address ?? '123 Đường Ẩm Thực, Q.1, TP.HCM' }}</p>
        <p>Điện thoại: {{ $restaurant->phone ?? '0901234567' }}</p>
    </div>

    <table class="info-table">
        <tr>
            <td><strong>Mã hóa đơn:</strong> {{ $invoice->invoice_code }}</td>
            <td class="text-right"><strong>Bàn:</strong> {{ $invoice->session->table->code ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td><strong>Ngày in:</strong> {{ now()->format('d/m/Y H:i') }}</td>
            <td class="text-right"><strong>Thu ngân:</strong> {{ $invoice->staff->name ?? 'Thu ngân' }}</td>
        </tr>
    </table>

    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 45%;">Tên món</th>
                <th class="text-center" style="width: 15%;">SL</th>
                <th class="text-right" style="width: 20%;">Đơn giá</th>
                <th class="text-right" style="width: 20%;">Thành tiền</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->items as $item)
            <tr>
                <td>{{ $item->dish_name }}</td>
                <td class="text-center">{{ $item->quantity }}</td>
                <td class="text-right">{{ number_format($item->unit_price, 0, ',', '.') }} đ</td>
                <td class="text-right">{{ number_format($item->subtotal, 0, ',', '.') }} đ</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <table class="summary-table">
        <tr>
            <td>Tiền món (Subtotal):</td>
            <td class="text-right">{{ number_format($invoice->subtotal, 0, ',', '.') }} đ</td>
        </tr>
        @if($invoice->discount_amount > 0)
        <tr>
            <td>Giảm giá (Chiết khấu):</td>
            <td class="text-right">-{{ number_format($invoice->discount_amount, 0, ',', '.') }} đ</td>
        </tr>
        @endif
        @if($invoice->service_charge > 0)
        <tr>
            <td>Phí phục vụ:</td>
            <td class="text-right">{{ number_format($invoice->service_charge, 0, ',', '.') }} đ</td>
        </tr>
        @endif
        @if($invoice->vat_amount > 0)
        <tr>
            <td>Thuế VAT ({{ $restaurant->vat_percent ?? 8 }}%):</td>
            <td class="text-right">{{ number_format($invoice->vat_amount, 0, ',', '.') }} đ</td>
        </tr>
        @endif
        <tr class="total-row">
            <td style="padding-top: 6px;">TỔNG THANH TOÁN:</td>
            <td class="text-right" style="padding-top: 6px;">{{ number_format($invoice->total_amount, 0, ',', '.') }} đ</td>
        </tr>
    </table>

    <div class="footer">
        <p>Cảm ơn quý khách và hẹn gặp lại!</p>
        <p style="font-size: 10px;">Hệ thống QR Order & KDS Quản lý Bếp</p>
    </div>
</body>
</html>
