<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Verification Code</title>
</head>
<body style="margin:0;padding:0;background:#f1f5f9;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,'Helvetica Neue',Arial,sans-serif;color:#0f172a;">
  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f1f5f9;padding:32px 16px;">
    <tr>
      <td align="center">
        <table role="presentation" width="560" cellpadding="0" cellspacing="0" style="max-width:560px;width:100%;background:#ffffff;border-radius:20px;overflow:hidden;box-shadow:0 12px 40px rgba(15,23,42,0.08);">

          {{-- Header --}}
          <tr>
            <td style="background:linear-gradient(135deg,#7c3aed 0%,#4f46e5 100%);padding:32px 32px 28px;text-align:center;">
              <div style="display:inline-block;width:56px;height:56px;border-radius:16px;background:rgba(255,255,255,0.15);line-height:56px;font-size:26px;color:#ffffff;margin-bottom:12px;">🛡️</div>
              <h1 style="margin:0;font-size:22px;font-weight:800;color:#ffffff;letter-spacing:-0.3px;">Security Verification</h1>
              <p style="margin:6px 0 0;font-size:13px;color:rgba(255,255,255,0.85);">{{ config('app.name') }} Admin Panel</p>
            </td>
          </tr>

          {{-- Body --}}
          <tr>
            <td style="padding:32px;">
              <p style="margin:0 0 8px;font-size:15px;color:#334155;">Hello <strong>{{ $adminName }}</strong>,</p>
              <p style="margin:0 0 20px;font-size:14px;line-height:1.6;color:#475569;">
                You're trying to perform a sensitive action: <strong style="color:#0f172a;">{{ $actionTitle }}</strong>.
                Use the code below to confirm it was really you.
              </p>

              <div style="background:#f8fafc;border:1px dashed #cbd5e1;border-radius:14px;padding:20px;text-align:center;margin:20px 0;">
                <p style="margin:0 0 10px;font-size:11px;text-transform:uppercase;letter-spacing:1.5px;color:#64748b;font-weight:700;">Your verification code</p>
                <div style="font-family:'SFMono-Regular',Menlo,Consolas,monospace;font-size:38px;font-weight:800;letter-spacing:12px;color:#4f46e5;padding:4px 0;">{{ $otp }}</div>
                <p style="margin:8px 0 0;font-size:12px;color:#94a3b8;">Expires in {{ $expiresInMinutes }} minutes</p>
              </div>

              <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#fff7ed;border:1px solid #fed7aa;border-radius:12px;margin-top:8px;">
                <tr>
                  <td style="padding:14px 16px;font-size:12px;color:#9a3412;line-height:1.6;">
                    <strong style="display:block;margin-bottom:4px;color:#7c2d12;">Action details</strong>
                    {{ $actionDetail }}
                  </td>
                </tr>
              </table>

              <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-top:16px;border-top:1px solid #e2e8f0;">
                <tr>
                  <td style="padding-top:16px;font-size:12px;color:#64748b;line-height:1.7;">
                    <div><strong style="color:#334155;">IP Address:</strong> <span style="font-family:monospace;">{{ $ip }}</span></div>
                    <div><strong style="color:#334155;">Device:</strong> <span style="word-break:break-all;">{{ $userAgent }}</span></div>
                  </td>
                </tr>
              </table>

              <div style="margin-top:24px;padding:14px 16px;background:#fef2f2;border-left:3px solid #ef4444;border-radius:8px;">
                <p style="margin:0;font-size:12.5px;color:#991b1b;line-height:1.6;">
                  <strong>Didn't request this?</strong> Ignore this email and change your admin password immediately. Your account may be at risk.
                </p>
              </div>
            </td>
          </tr>

          {{-- Footer --}}
          <tr>
            <td style="background:#0f172a;padding:20px 32px;text-align:center;">
              <p style="margin:0;font-size:12px;color:#94a3b8;">
                &copy; {{ date('Y') }} {{ config('app.name') }}. Automated security message — do not reply.
              </p>
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</body>
</html>
