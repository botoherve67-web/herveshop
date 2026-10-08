<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#17233f">
    <title>Maintenance — HerveShop</title>
    <style>
        body { margin:0; min-height:100vh; display:grid; place-items:center; padding:24px; background:#f7f7f5; color:#17233f; font-family:Arial, sans-serif; text-align:center; }
        .panel { width:min(100%,560px); padding:42px 28px; border:1px solid #eceef1; border-radius:20px; background:#fff; box-shadow:0 16px 40px rgba(19,31,52,.08); }
        img { width:100px; height:auto; margin-bottom:18px; }
        h1 { margin:0 0 12px; font-size:clamp(1.7rem, 5vw, 2.3rem); }
        p { margin:0; color:#52606d; line-height:1.7; }
        @media (max-width:480px) {
            body { min-height:100svh; padding:16px; padding-bottom:calc(16px + env(safe-area-inset-bottom)); }
            .panel { padding:30px 20px; border-radius:18px; }
            img { width:84px; }
        }
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