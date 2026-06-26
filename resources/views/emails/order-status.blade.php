<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head><meta charset="utf-8"><title>{{ $order->order_number }}</title></head>
<body style="font-family: Tahoma, Arial, sans-serif; background:#f5f7fb; padding:24px;">
    <div style="max-width:600px;margin:auto;background:#fff;border-radius:12px;overflow:hidden;border:1px solid #e2e8f0;">
        <div style="background:#7c3aed;color:#fff;padding:20px 24px;">
            <h2 style="margin:0;">UNI-LAB MARKET</h2>
            <p style="margin:4px 0 0;opacity:.85">طلب رقم: {{ $order->order_number }}</p>
        </div>
        <div style="padding:24px;color:#0f172a;">
            <p>مرحباً {{ $order->customer_name ?? $order->email }}،</p>
            <p>
                @switch($kind)
                    @case('placed') شكراً لطلبك! تم استلام طلبك بنجاح وسنبدأ في معالجته قريباً. @break
                    @case('shipped') تم شحن طلبك. @if($order->tracking_number) رقم التتبع: <b>{{ $order->tracking_number }}</b> @endif @break
                    @case('delivered') تم توصيل طلبك بنجاح. نتمنى أن ينال إعجابك! @break
                    @case('cancelled') تم إلغاء طلبك. @break
                    @case('refunded') تم استرداد قيمة طلبك. @break
                    @default تم تحديث حالة طلبك إلى: {{ $order->statusLabel() }}.
                @endswitch
            </p>

            <table style="width:100%;border-collapse:collapse;margin-top:16px;">
                <thead>
                    <tr style="background:#f1f5f9;text-align:right;font-size:13px;">
                        <th style="padding:8px">المنتج</th>
                        <th style="padding:8px">الكمية</th>
                        <th style="padding:8px">السعر</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->items as $item)
                    <tr style="border-top:1px solid #e2e8f0;font-size:13px;">
                        <td style="padding:8px">{{ $item->product_name }}</td>
                        <td style="padding:8px;text-align:center;">{{ $item->quantity }}</td>
                        <td style="padding:8px">{{ number_format($item->line_total, 2) }} {{ $order->currency }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <div style="margin-top:16px;font-size:14px;">
                <div>الإجمالي الفرعي: <b>{{ number_format($order->subtotal, 2) }} {{ $order->currency }}</b></div>
                @if($order->discount_amount > 0)
                    <div>الخصم: <b>-{{ number_format($order->discount_amount, 2) }} {{ $order->currency }}</b></div>
                @endif
                <div>الشحن: <b>{{ number_format($order->shipping_cost, 2) }} {{ $order->currency }}</b></div>
                <div style="font-size:16px;margin-top:6px;">الإجمالي: <b style="color:#7c3aed">{{ number_format($order->total, 2) }} {{ $order->currency }}</b></div>
            </div>

            <p style="margin-top:24px;font-size:12px;color:#64748b">إذا كان لديك أي استفسار، فقط رد على هذه الرسالة.</p>
        </div>
    </div>
</body>
</html>
