<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="utf-8">
<title>فاتورة {{ $order->order_number }}</title>
<style>
    *{box-sizing:border-box;font-family:Tahoma,Arial,sans-serif}
    body{margin:0;padding:30px;background:#fff;color:#0f172a}
    .head{display:flex;justify-content:space-between;align-items:flex-start;border-bottom:3px solid #7c3aed;padding-bottom:14px;margin-bottom:20px}
    .head h1{margin:0;color:#7c3aed}
    .meta{font-size:13px;line-height:1.7}
    h3{margin:18px 0 8px;color:#7c3aed;font-size:15px;border-bottom:1px solid #e2e8f0;padding-bottom:4px}
    table{width:100%;border-collapse:collapse;font-size:13px;margin-top:10px}
    th,td{padding:8px 10px;border:1px solid #e2e8f0;text-align:right}
    th{background:#f1f5f9}
    .totals{margin-top:14px;font-size:14px;text-align:left}
    .totals div{margin:3px 0}
    .grand{font-size:18px;color:#7c3aed;font-weight:900;margin-top:6px}
    .btn{display:inline-block;padding:8px 16px;background:#7c3aed;color:#fff;border-radius:8px;text-decoration:none;font-size:13px}
    @media print { .no-print{display:none} body{padding:10px} }
</style>
</head>
<body>
<div class="no-print" style="text-align:left;margin-bottom:10px">
    <button onclick="window.print()" class="btn" style="border:0;cursor:pointer">طباعة / حفظ PDF</button>
</div>

<div class="head">
    <div>
        <h1>UNI-LAB MARKET</h1>
        <div class="meta">فاتورة طلب</div>
    </div>
    <div class="meta" style="text-align:left">
        <div><b>رقم الفاتورة:</b> {{ $order->order_number }}</div>
        <div><b>التاريخ:</b> {{ $order->created_at->format('Y-m-d H:i') }}</div>
        <div><b>الحالة:</b> {{ $order->statusLabel() }}</div>
    </div>
</div>

<h3>بيانات العميل</h3>
<div class="meta">
    <div><b>الاسم:</b> {{ $order->customer_name ?: '—' }}</div>
    <div><b>الإيميل:</b> {{ $order->email }}</div>
    <div><b>الهاتف:</b> {{ $order->phone ?: '—' }}</div>
    @if($order->shipping_country)
    <div><b>العنوان:</b> {{ $order->shipping_country }} / {{ $order->shipping_region }} — {{ $order->shipping_address }} {{ $order->shipping_city }} {{ $order->shipping_postcode }}</div>
    @endif
</div>

<h3>تفاصيل المنتجات</h3>
<table>
    <thead><tr><th>#</th><th>المنتج</th><th>سعر الوحدة</th><th>الكمية</th><th>الإجمالي</th></tr></thead>
    <tbody>
    @foreach($order->items as $i => $it)
    <tr>
        <td>{{ $i+1 }}</td>
        <td>{{ $it->product_name }}</td>
        <td>{{ number_format($it->unit_price,2) }}</td>
        <td>{{ $it->quantity }}</td>
        <td>{{ number_format($it->line_total,2) }}</td>
    </tr>
    @endforeach
    </tbody>
</table>

<div class="totals">
    <div>الإجمالي الفرعي: <b>{{ number_format($order->subtotal,2) }} {{ $order->currency }}</b></div>
    @if($order->discount_amount > 0)
    <div>الخصم {{ $order->coupon_code ? '('.$order->coupon_code.')' : '' }}: <b style="color:#e11d48">-{{ number_format($order->discount_amount,2) }} {{ $order->currency }}</b></div>
    @endif
    <div>الشحن: <b>{{ number_format($order->shipping_cost,2) }} {{ $order->currency }}</b></div>
    <div class="grand">الإجمالي النهائي: {{ number_format($order->total,2) }} {{ $order->currency }}</div>
</div>

<p style="margin-top:30px;font-size:12px;color:#64748b;text-align:center">شكراً لتعاملكم مع UNI-LAB MARKET</p>
<script>setTimeout(()=>{ try{ window.print(); }catch(e){} }, 300);</script>
</body>
</html>
