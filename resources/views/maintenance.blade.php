<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maintenance — HerveShop</title>
    <style>
        body { margin:0; min-height:100vh; display:grid; place-items:center; padding:24px; background:#f4faf6; color:#173f5f; font-family:Arial, sans-serif; text-align:center; }
        .panel { max-width:560px; padding:42px 28px; border:1px solid #dce8df; border-radius:12px; background:#fff; box-shadow:0 16px 40px rgba(20,104,48,.1); }
        img { width:100px; height:auto; margin-bottom:18px; }
        h1 { margin:0 0 12px; font-size:clamp(1.7rem, 5vw, 2.3rem); }
        p { margin:0; color:#52606d; line-height:1.7; }
    </style>
</head>
<body>
    <main class="panel">
        <img src="{{ asset('images/logo.png') }}" alt="HerveShop">
        <h1>Site en maintenance</h1>
        <p>{{ $message }}</p>
    </main>
</body>
</html>