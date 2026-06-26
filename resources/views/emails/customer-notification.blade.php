<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head><meta charset="utf-8"><title>{{ $subj }}</title></head>
<body style="font-family:Tahoma,Arial,sans-serif;background:#f5f7fb;padding:24px;">
    <div style="max-width:600px;margin:auto;background:#fff;border-radius:12px;border:1px solid #e2e8f0;overflow:hidden">
        <div style="background:#7c3aed;color:#fff;padding:18px 24px"><h2 style="margin:0">UNI-LAB MARKET</h2></div>
        <div style="padding:24px;color:#0f172a;line-height:1.7">
            <p>مرحباً {{ $customerName ?? 'عميلنا العزيز' }}،</p>
            <div>{!! nl2br(e($messageBody)) !!}</div>
            <p style="margin-top:30px;font-size:12px;color:#64748b">— فريق UNI-LAB MARKET</p>
        </div>
    </div>
</body>
</html>
