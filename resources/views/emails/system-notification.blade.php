<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $titleText }}</title>
</head>
<body style="margin:0; padding:0; background-color:#f4f6f5; font-family:Tahoma, Arial, 'Segoe UI', sans-serif; direction:rtl; text-align:right;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f6f5; padding:24px 0;">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px; width:100%; background-color:#ffffff; border-radius:10px; overflow:hidden; box-shadow:0 1px 4px rgba(0,0,0,0.08);">
                    <tr>
                        <td align="center" style="background-color:#1c9c55; padding:24px;">
                            <img src="{{ $logoUrl }}" alt="منصة اتفاق" height="48" style="display:block; height:48px; border:0;">
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:32px 28px;">
                            <p style="margin:0 0 16px; font-size:15px; color:#333333;">
                                مرحبًا {{ $recipientName }}،
                            </p>

                            <h1 style="margin:0 0 12px; font-size:20px; color:#1c9c55; font-weight:bold;">
                                {{ $titleText }}
                            </h1>

                            @if ($bodyText)
                                <p style="margin:0 0 24px; font-size:15px; line-height:1.9; color:#444444;">
                                    {{ $bodyText }}
                                </p>
                            @endif

                            @if ($actionUrl)
                                <table role="presentation" cellpadding="0" cellspacing="0" style="margin:8px 0;">
                                    <tr>
                                        <td align="center" bgcolor="#1c9c55" style="border-radius:6px;">
                                            <a href="{{ $actionUrl }}" style="display:inline-block; padding:12px 28px; font-size:15px; font-weight:bold; color:#ffffff; text-decoration:none; border-radius:6px;">
                                                عرض التفاصيل
                                            </a>
                                        </td>
                                    </tr>
                                </table>
                            @endif
                        </td>
                    </tr>

                    <tr>
                        <td align="center" style="padding:20px; background-color:#f4f6f5; border-top:1px solid #e6e9e8;">
                            <p style="margin:0; font-size:13px; color:#888888;">
                                منصة اتفاق &mdash; نظام إدارة المنافسات والعقود
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
