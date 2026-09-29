<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nouveau message de contact</title>
</head>
<body style="margin:0;background:#f4f6f8;color:#1f2933;font-family:Arial,Helvetica,sans-serif;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f4f6f8;padding:32px 16px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:650px;background:#ffffff;border:1px solid #e5e7eb;border-radius:8px;overflow:hidden;">
                    <tr>
                        <td style="background:#173f5f;padding:24px 32px;">
                            <div style="color:#ffffff;font-size:22px;font-weight:700;">HerveShop</div>
                            <div style="color:#b9d7e8;font-size:13px;margin-top:5px;">Nouveau message de contact</div>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:32px;">
                            <h1 style="margin:0 0 18px;font-size:24px;color:#173f5f;">Message reçu depuis le formulaire de contact</h1>
                            <p style="margin:0 0 16px;line-height:1.7;color:#52606d;">Vous avez reçu un nouveau message provenant du site HerveShop.</p>

                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="margin:0 0 22px;background:#f7fafc;border-left:4px solid #2a9d8f;">
                                <tr><td style="padding:10px 14px;color:#52606d;width:35%;">Nom</td><td style="padding:10px 14px;font-weight:600;color:#1f2933;">{{ $name }}</td></tr>
                                <tr><td style="padding:10px 14px;color:#52606d;width:35%;">Email</td><td style="padding:10px 14px;font-weight:600;color:#1f2933;">{{ $email }}</td></tr>
                                <tr><td style="padding:10px 14px;color:#52606d;width:35%;">Téléphone</td><td style="padding:10px 14px;font-weight:600;color:#1f2933;">{{ $phone ?? 'Non renseigné' }}</td></tr>
                                <tr><td style="padding:10px 14px;color:#52606d;width:35%;">Objet</td><td style="padding:10px 14px;font-weight:600;color:#1f2933;">{{ $subject }}</td></tr>
                            </table>

                            <div style="margin:0 0 10px;font-weight:700;color:#173f5f;">Message</div>
                            <div style="padding:18px 16px;border:1px solid #e5e7eb;border-radius:8px;background:#fbfcfd;color:#334155;line-height:1.7;white-space:pre-line;">{{ $bodyMessage }}</div>
                        </td>
                    </tr>
                    <tr>
                        <td style="border-top:1px solid #e5e7eb;padding:20px 32px;background:#fbfcfd;color:#7b8794;font-size:12px;line-height:1.6;">
                            Email envoyé automatiquement depuis le formulaire de contact HerveShop.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
