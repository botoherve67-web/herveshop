<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>
</head>
<body style="margin:0;background:#f4f6f8;color:#1f2933;font-family:Arial,Helvetica,sans-serif;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f4f6f8;padding:24px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:600px;background:#ffffff;border:1px solid #e5e7eb;">
                    <tr>
                        <td style="background:#173f5f;padding:22px 28px;color:#ffffff;">
                            <div style="font-size:22px;font-weight:700;">HerveShop</div>
                            <div style="font-size:13px;margin-top:5px;color:#d7e7ef;">Votre boutique, simplement.</div>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:30px 28px;">
                            <h1 style="margin:0 0 18px;color:#173f5f;font-size:24px;line-height:1.3;">{{ $title }}</h1>
                            <p style="margin:0 0 16px;font-size:16px;line-height:1.6;color:#1f2933;">{{ $greeting }}</p>
                            <p style="margin:0 0 22px;font-size:15px;line-height:1.7;color:#52606d;">{{ $intro }}</p>

                            @if (!empty($details))
                                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="margin:0 0 24px;background:#f7fafc;border-left:4px solid #2a9d8f;">
                                    @foreach ($details as $label => $value)
                                        <tr>
                                            <td style="padding:12px 14px;color:#52606d;font-size:14px;width:35%;">{{ $label }}</td>
                                            <td style="padding:12px 14px;color:#111827;font-size:22px;font-weight:700;letter-spacing:3px;">{{ $value }}</td>
                                        </tr>
                                    @endforeach
                                </table>
                            @endif

                            @if (!empty($actionUrl) && !empty($actionText))
                                <p style="margin:0 0 24px;">
                                    <a href="{{ $actionUrl }}" style="display:inline-block;background:#2a9d8f;color:#ffffff;text-decoration:none;padding:13px 22px;font-size:14px;font-weight:700;">
                                        {{ $actionText }}
                                    </a>
                                </p>
                            @endif

                            <p style="margin:0;color:#52606d;font-size:14px;line-height:1.7;">{{ $closing }}</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="border-top:1px solid #e5e7eb;padding:18px 28px;background:#fbfcfd;color:#7b8794;font-size:12px;line-height:1.6;">
                            Cet e-mail a ete envoye automatiquement par HerveShop.<br>
                            Merci de ne pas repondre directement a ce message.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
