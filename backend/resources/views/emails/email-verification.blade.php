<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kode Verifikasi Email CampusEvent</title>
</head>
<body style="margin:0;padding:24px;background:#f8fafc;font-family:Arial,Helvetica,sans-serif;color:#0f172a;">
    <div style="max-width:520px;margin:0 auto;padding:32px;background:#ffffff;border:1px solid #e2e8f0;border-radius:12px;">
        <h1 style="margin:0 0 16px;font-size:20px;">Verifikasi Email CampusEvent</h1>

        <p style="margin:0 0 12px;line-height:1.6;">Halo {{ $name }},</p>

        <p style="margin:0 0 20px;line-height:1.6;">
            Gunakan kode berikut untuk menyelesaikan pendaftaran akun CampusEvent Anda:
        </p>

        <div style="margin:0 0 20px;padding:16px;background:#f1f5f9;border-radius:8px;text-align:center;">
            <span style="font-size:32px;font-weight:700;letter-spacing:8px;">{{ $code }}</span>
        </div>

        <p style="margin:0 0 12px;line-height:1.6;">
            Kode ini berlaku selama {{ $expiresInMinutes }} menit dan hanya dapat digunakan sekali.
        </p>

        <p style="margin:0;line-height:1.6;color:#64748b;">
            Jika Anda tidak meminta kode ini, abaikan email ini. Jangan bagikan kode kepada siapa pun.
        </p>
    </div>
</body>
</html>
