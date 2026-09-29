<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'HerveShop')</title>
    <meta name="description" content="@yield('meta_description', 'HerveShop, votre boutique en ligne en Afrique de l’Ouest : produits disponibles, précommandes et livraison.')">
    <meta name="robots" content="@yield('meta_robots', 'index,follow')">
    <link rel="canonical" href="@yield('canonical', url()->current())">
    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:title" content="@yield('title', 'HerveShop')">
    <meta property="og:description" content="@yield('meta_description', 'HerveShop, votre boutique en ligne en Afrique de l’Ouest : produits disponibles, précommandes et livraison.')">
    <meta property="og:url" content="@yield('canonical', url()->current())">
    <style>
        :root {
            --vert: #0969ed;
            --vert-fonce: #062b52;
            --vert-clair: #e9f3ff;
            --blanc: #ffffff;
            --texte: #173f5f;
            --surface: #f7faff;
            --bordure: #e2eaf3;
            --ombre: 0 12px 30px rgba(16, 46, 85, .08);
        }
        * { box-sizing: border-box; }
        html { min-width: 320px; }
        body {
            margin: 0;
            font-family: 'Segoe UI', Arial, sans-serif;
            background: linear-gradient(180deg, #f7faff 0, #ffffff 320px);
            color: var(--texte);
            line-height: 1.5;
        }
        a { text-decoration: none; color: inherit; }
        .utility-bar { display:flex; justify-content:space-between; gap:16px; padding:6px clamp(16px,4vw,48px); color:#fff; background:var(--vert-fonce); font-size:.65rem; }
        .utility-bar span { display:flex; align-items:center; gap:6px; }
        header.site {
            background:var(--blanc);
            color:var(--texte);
            padding: 15px clamp(16px, 4vw, 48px);
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 10px;
            box-shadow: 0 2px 14px rgba(16,46,85,.08);
        }
        header.site .logo { display:flex; align-items:center; font-size: 1.4rem; font-weight: bold; white-space:nowrap; }
        header.site nav a {
            margin-left: 16px;
            color: var(--texte);
            font-weight: 500;
            font-size:.78rem;
        }
        .main-nav { display:flex; align-items:center; justify-content:center; gap:4px; }
        .main-nav a { padding:8px 10px; }
        .main-nav a:first-child { color:var(--vert); font-weight:800; border-bottom:2px solid var(--vert); }
        .action-nav { display:flex; align-items:center; gap:14px; }
        .action-nav a { margin:0; display:inline-flex; align-items:center; color:var(--texte); }
        .action-nav a:hover { color:var(--vert); }
        .cart-link { position:relative; }
        .cart-badge { position:absolute; top:-10px; right:-10px; display:grid; place-items:center; min-width:15px; height:15px; padding:0 3px; border-radius:10px; background:#0969ed; color:#fff; font-size:.58rem; }
        header.site form.search {
            display: flex;
            flex: 1 1 260px;
            max-width: 420px;
        }
        header.site form.search input {
            padding: 10px 12px;
            border: none;
            border-radius: 4px 0 0 4px;
            margin: 0;
        }
        header.site form.search button {
            padding: 8px 14px;
            border: none;
            background: var(--vert);
            color: var(--blanc);
            border-radius: 0 4px 4px 0;
            cursor: pointer;
        }
        main { max-width: 1180px; margin: 0 auto; padding: clamp(18px, 4vw, 40px) clamp(14px, 4vw, 32px); min-height: 60vh; }
        footer.site {
            background: var(--vert-fonce);
            color: var(--blanc);
            text-align: center;
            padding: 18px;
            margin-top: 40px;
            font-size: 0.9rem;
        }
        .btn {
            display: inline-block;
            background: var(--vert);
            color: var(--blanc);
            padding: 8px 16px;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            font-weight: 600;
            text-align: center;
            min-height: 40px;
        }
        .btn:hover { background: var(--vert-fonce); }
        .btn.outline {
            background: var(--blanc);
            color: var(--vert);
            border: 2px solid var(--vert);
        }
        .card {
            border: 1px solid var(--bordure);
            border-radius: 8px;
            padding: 14px;
            background: var(--blanc);
            box-shadow: var(--ombre);
        }
        .wishlist-item { min-width: 0; }
        .home-hero { display:grid; grid-template-columns: minmax(0, .94fr) minmax(0, 1.06fr); min-height:390px; overflow:hidden; margin:-40px -32px 42px; background:linear-gradient(112deg,#edf8ff,#fff 58%,#e7f5ff); }
        .hero-copy { padding:58px clamp(24px, 6vw, 72px); align-self:center; }
        .eyebrow { color:#1c8a3f; font-size:.72rem; font-weight:800; letter-spacing:.08em; text-transform:uppercase; }
        .hero-copy h1 { color:#102e55; font-size:clamp(2.1rem,4vw,3.5rem); letter-spacing:-.04em; margin:12px 0 14px; }
        .hero-copy h1 strong { color:#0969ed; }
        .hero-copy p { max-width:470px; color:#52606d; font-size:1rem; }
        .hero-actions { display:flex; flex-wrap:wrap; gap:10px; margin:24px 0 30px; }
        .home-btn { display:inline-flex; align-items:center; gap:14px; padding:12px 20px; border-radius:24px; font-weight:700; font-size:.88rem; }
        .home-btn-primary { background:#0969ed; color:white; box-shadow:0 8px 18px rgba(9,105,237,.2); }
        .home-btn-light { border:1px solid #0969ed; color:#0969ed; background:#fff; }
        .hero-promises { display:flex; gap:20px; color:#173f5f; font-size:.72rem; font-weight:700; }
        .hero-promises span { display:block; }
        .hero-promises b { color:#0969ed; font-size:1.1rem; margin-right:4px; }
        .hero-promises small { color:#6b7c93; font-size:.65rem; font-weight:400; }
        .hero-showcase { position:relative; min-height:390px; background:radial-gradient(circle at 50% 50%,#fff 0,#eef6f8 54%,#dceef8 100%); }
        .hero-featured-product { position:absolute; inset:0; display:grid; place-items:center; padding:22px; color:#173f5f; }
        .hero-featured-product img { width:100%; height:100%; object-fit:contain; mix-blend-mode:multiply; filter:drop-shadow(0 18px 14px rgba(16,46,85,.2)); transition:transform .25s; }
        .hero-featured-product:hover img { transform:scale(1.04); }
        .hero-featured-product span { position:absolute; right:18px; bottom:16px; max-width:70%; padding:8px 12px; border-radius:6px; color:#173f5f; background:rgba(255,255,255,.9); font-size:.72rem; font-weight:700; }
        .hero-product { position:absolute; display:grid; place-items:center; width:44%; height:44%; transition:transform .2s; }
        .hero-product:hover { transform:translateY(-5px) scale(1.03); }
        .hero-product img { width:100%; height:100%; object-fit:contain; mix-blend-mode:multiply; filter:drop-shadow(0 14px 12px rgba(16,46,85,.15)); }
        .hero-product-1 { width:40%; height:55%; top:17%; left:8%; }
        .hero-product-2 { width:42%; height:52%; top:5%; right:6%; }
        .hero-product-3 { width:35%; height:38%; bottom:2%; left:28%; }
        .hero-product-4 { width:34%; height:40%; bottom:5%; right:6%; }
        .hero-device { position:absolute; display:grid; place-items:center; color:#173f5f; font-weight:800; text-align:center; border-radius:18px; background:#fff; box-shadow:0 16px 24px rgba(16,46,85,.16); }
        .hero-device-phone { width:125px; height:205px; top:52px; left:14%; background:linear-gradient(145deg,#26384d,#8393a7); color:#fff; transform:rotate(-12deg); }
        .hero-device-audio { width:150px; height:100px; top:72px; right:13%; background:#fff; transform:rotate(7deg); }
        .hero-device-watch { width:112px; height:112px; bottom:34px; left:26%; border-radius:28px; background:#202d3d; color:#8cffbd; }
        .hero-device-shoe { width:175px; height:75px; bottom:34px; right:10%; border-radius:60% 30% 25% 20%; background:#f8fbff; transform:rotate(-8deg); }
        .hero-script { position:absolute; top:24px; right:25px; color:#0969ed; font-family:cursive; font-size:1.5rem; line-height:.88; transform:rotate(-8deg); }
        .home-section { margin:48px 0; }
        .section-heading { display:flex; align-items:end; justify-content:space-between; gap:16px; margin-bottom:20px; }
        .section-heading h2 { color:#102e55; margin:4px 0; font-size:1.55rem; }
        .section-heading p { color:#8393a7; margin:0; font-size:.85rem; }
        .section-heading > a { color:#0969ed; font-size:.75rem; font-weight:700; white-space:nowrap; }
        .section-heading.centered { justify-content:center; text-align:center; }
        .category-grid { display:grid; grid-template-columns:repeat(8,1fr); gap:10px; }
        .category-tile { display:flex; flex-direction:column; align-items:center; gap:4px; padding:14px 8px; border-radius:10px; border:1px solid #e9edf3; background:#fff; text-align:center; transition:transform .2s, box-shadow .2s; }
        .category-tile:hover { transform:translateY(-4px); box-shadow:var(--ombre); }
        .category-art { display:grid; place-items:center; width:62px; height:54px; border-radius:12px; color:#0969ed; font-size:1.65rem; background:#eef6ff; }
        .category-2 .category-art,.category-5 .category-art { background:#fff2e5; color:#ba6c18; }
        .category-3 .category-art,.category-6 .category-art { background:#edf9ef; color:#1c8a3f; }
        .category-tile strong { color:#173f5f; font-size:.69rem; }
        .category-tile small { color:#94a3b8; font-size:.62rem; }
        .home-products { display:grid; grid-template-columns:repeat(5,minmax(0,1fr)); gap:12px; }
        .home-products .card { padding:10px; }
        .home-products .product-card-image { height:150px !important; }
        .mock-product { position:relative; padding:10px; }.mock-product .product-tag { position:absolute; top:8px; left:8px; padding:3px 7px; border-radius:5px; background:#0969ed; color:#fff; font-size:.58rem; font-weight:700; }.mock-product:nth-child(2) .product-tag,.mock-product:nth-child(5) .product-tag { background:#f0a31a; }.mock-product:nth-child(3) .product-tag { background:#16ad61; }.mock-product-art { display:grid; place-items:center; height:150px; margin-bottom:9px; border-radius:6px; color:#0969ed; background:linear-gradient(145deg,#f0f6fc,#dbeafe); font-size:4rem; }.mock-product h3 { margin:4px 0; color:#173f5f; font-size:.88rem; }.mock-product small { color:#8393a7; font-size:.65rem; }.mock-product .stars { margin:8px 0 2px; }.stars { color:#f4a623; letter-spacing:2px; font-size:.72rem; }.stars em { color:#8393a7; font-size:.6rem; font-style:normal; letter-spacing:0; }.mock-price { display:block; margin:3px 0 8px; color:#0969ed; font-size:.88rem; }
        .promo-grid { display:grid; grid-template-columns:1fr 1fr; gap:14px; margin:52px 0; }
        .promo-panel { min-height:190px; padding:28px 30px; border-radius:10px; color:#fff; overflow:hidden; position:relative; }
        .promo-panel:after { content:''; position:absolute; width:180px; height:180px; right:-24px; bottom:-60px; border-radius:50%; background:rgba(255,255,255,.12); }
        .promo-tech { background:linear-gradient(115deg,#062b52,#0969ed); }
        .promo-style { background:linear-gradient(115deg,#e5a15e,#f6d3ac); color:#173f5f; }
        .promo-panel span,.promo-panel small,.promo-panel strong,.promo-panel b { display:block; position:relative; z-index:1; }
        .promo-panel span { font-size:.7rem; font-weight:800; margin-bottom:8px; }
        .promo-panel strong { font-size:1.7rem; line-height:1.05; }
        .promo-panel small { max-width:220px; margin:10px 0 18px; font-size:.72rem; opacity:.85; }
        .promo-panel b { width:max-content; padding:8px 13px; border-radius:18px; background:#fff; color:#173f5f; font-size:.7rem; }
        .why-section { margin:50px -32px; padding:40px 32px; background:#f5faff; }
        .why-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:24px; text-align:center; }
        .why-grid div { display:flex; flex-direction:column; align-items:center; gap:3px; }
        .why-grid b { display:grid; place-items:center; width:45px; height:45px; margin-bottom:7px; border-radius:50%; color:#0969ed; background:#fff; box-shadow:0 4px 14px rgba(9,105,237,.12); font-size:1.4rem; }
        .why-grid strong { color:#173f5f; font-size:.78rem; }.why-grid small { color:#8393a7; font-size:.68rem; }
        .testimonial-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:14px; }
        .testimonial { padding:20px; border:1px solid #e4ebf2; border-radius:9px; background:#fff; }.testimonial-top { display:flex; align-items:center; gap:10px; color:#173f5f; font-size:.82rem; }.avatar { display:grid; place-items:center; width:34px; height:34px; border-radius:50%; color:#fff; background:#0969ed; font-weight:800; }.stars { color:#f4a623; letter-spacing:2px; font-size:.72rem; }.testimonial p { color:#66788c; font-size:.78rem; line-height:1.6; }
        .newsletter { display:flex; align-items:center; justify-content:space-between; gap:20px; padding:18px 28px; border-radius:9px; color:#fff; background:linear-gradient(100deg,#0969ed,#0786ee); }.newsletter > div,.newsletter form { display:flex; align-items:center; gap:12px; }.newsletter > div > strong { display:grid; place-items:center; width:38px; height:38px; border-radius:50%; background:rgba(255,255,255,.2); }.newsletter b,.newsletter small { display:block; }.newsletter small { font-size:.7rem; opacity:.8; }.newsletter input { width:230px; margin:0; border:0; }.newsletter button { min-height:40px; padding:8px 14px; border:1px solid #fff; border-radius:5px; color:#fff; background:transparent; font-weight:700; }
        .shop-banner { position:relative; display:flex; align-items:center; min-height:148px; margin:-40px 0 16px; padding:26px 30px; overflow:hidden; border-radius:8px; color:#fff; background:linear-gradient(110deg,#062b52,#0969ed 70%,#092a62); }.shop-banner h1 { margin:6px 0; font-size:clamp(1.55rem,3vw,2.25rem); line-height:1.05; }.shop-banner p { margin:0; font-size:.76rem; }.shop-kicker { display:inline-block; padding:4px 10px; border-radius:12px; background:#0969ed; font-size:.65rem; font-weight:700; }.shop-banner-products { display:flex; align-items:end; gap:3px; width:38%; height:145px; margin-left:auto; }.shop-banner-products a { width:25%; height:100%; display:flex; align-items:end; }.shop-banner-products img { max-width:100%; width:100%; height:95%; object-fit:contain; filter:drop-shadow(0 12px 10px rgba(0,0,0,.3)); }.shop-banner-promises { display:flex; gap:13px; font-size:.6rem; font-weight:700; }.shop-banner-promises small { font-weight:400; opacity:.8; }.shop-layout { display:grid; grid-template-columns:212px minmax(0,1fr); gap:22px; }.shop-sidebar { align-self:start; padding:16px 13px; border:1px solid var(--bordure); border-radius:8px; background:#fff; box-shadow:var(--ombre); }.shop-filter-title { display:flex; gap:9px; align-items:center; padding-bottom:13px; border-bottom:1px solid var(--bordure); color:#0969ed; font-size:.86rem; }.shop-filter-title strong { color:#173f5f; }.shop-category-list { padding:8px 0 13px; border-bottom:1px solid var(--bordure); }.shop-category-list a { display:flex; justify-content:space-between; gap:8px; padding:8px 9px; border-radius:5px; color:#294766; font-size:.68rem; }.shop-category-list a:hover,.shop-category-list a.active { color:#0969ed; background:#edf5ff; }.shop-category-list small { color:#7890a8; }.shop-filter-block { padding:14px 0; border-bottom:1px solid var(--bordure); }.shop-filter-block > strong { display:block; margin-bottom:10px; font-size:.75rem; }.price-fields { display:flex; gap:7px; }.price-fields input { min-width:0; padding:7px; font-size:.62rem; }.shop-filter-block label { display:block; margin:8px 0; color:#52606d; font-size:.7rem; font-weight:400; }.shop-filter-block label input { width:auto; margin:0 6px 0 0; }.shop-sidebar .btn { width:100%; margin-top:14px; font-size:.68rem; }.shop-reset { display:block; margin-top:10px; color:#0969ed; text-align:center; font-size:.65rem; }.shop-results-head { display:flex; align-items:center; justify-content:space-between; gap:12px; margin-bottom:16px; }.shop-results-head h2 { margin:0; font-size:1.25rem; }.shop-results-head p { margin:2px 0; color:#8393a7; font-size:.72rem; }.shop-sort select { width:auto; min-width:180px; margin:0; padding:9px; border-color:var(--bordure); color:#294766; font-size:.68rem; }.shop-product-grid { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:12px; }.shop-product-grid .card { padding:9px; }.shop-product-grid .product-card-image { height:145px !important; }.shop-product-grid .product-card h3 { color:#173f5f; font-size:.78rem !important; }.shop-product-grid .btn { padding:7px 8px; min-height:34px; font-size:.63rem; }.shop-product-grid .btn.outline { color:#0969ed; border-width:1px; }.shop-pagination { margin-top:20px; }
        .category-hero { position:relative; display:flex; align-items:center; min-height:218px; margin:-40px 0 18px; padding:30px; overflow:hidden; border-radius:8px; background:linear-gradient(105deg,#eef7ff,#dcebfa 58%,#c7e0f4); }.category-hero h1 { margin:8px 0; color:#102e55; font-size:clamp(1.9rem,4vw,3rem); line-height:1.02; }.category-hero h1 strong { color:#0969ed; }.category-hero p { max-width:390px; margin:0; color:#52606d; font-size:.76rem; }.category-hero-products { display:flex; align-items:end; gap:0; width:45%; height:190px; margin-left:auto; }.category-hero-products a { width:25%; height:100%; display:flex; align-items:end; }.category-hero-products img { width:100%; height:95%; object-fit:contain; filter:drop-shadow(0 12px 9px rgba(16,46,85,.16)); }.category-hero-promises { position:absolute; left:30px; bottom:14px; display:flex; gap:20px; color:#173f5f; font-size:.61rem; font-weight:700; }.category-hero-promises span { display:flex; align-items:flex-start; gap:5px; }.category-hero-promises small { display:block; color:#6b7c93; font-weight:400; }.category-page-layout { display:grid; grid-template-columns:212px minmax(0,1fr); gap:22px; }.category-results { min-width:0; }.category-card-grid { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:16px; }.category-large-card { overflow:hidden; border:1px solid var(--bordure); border-radius:10px; background:#fff; box-shadow:var(--ombre); transition:transform .2s,box-shadow .2s; }.category-large-card:hover { transform:translateY(-4px); box-shadow:0 18px 32px rgba(16,46,85,.13); }.category-large-image { display:grid; place-items:center; height:145px; color:#0969ed; background:#eaf4ff; }.category-large-image img { width:100%; height:100%; object-fit:contain; mix-blend-mode:multiply; }.category-large-2 .category-large-image,.category-large-5 .category-large-image { background:#fff0f0; }.category-large-3 .category-large-image,.category-large-6 .category-large-image { background:#f0faef; }.category-large-4 .category-large-image,.category-large-7 .category-large-image { background:#fff6e5; }.category-large-info { position:relative; display:flex; flex-direction:column; gap:2px; padding:10px 12px; }.category-large-icon { display:grid; place-items:center; width:28px; height:28px; margin-top:-25px; margin-bottom:4px; border:3px solid #fff; border-radius:50%; color:#fff; background:#0969ed; }.category-large-info strong { color:#173f5f; font-size:.76rem; }.category-large-info small { color:#8393a7; font-size:.64rem; }.category-arrow { position:absolute; right:12px; bottom:12px; display:grid; place-items:center; width:22px; height:22px; border:1px solid #b9d8fa; border-radius:50%; color:#0969ed; font-size:.75rem; }.category-benefits { display:flex; align-items:center; justify-content:space-between; gap:20px; margin-top:20px; padding:15px 22px; border-radius:9px; background:#eef6ff; color:#173f5f; }.category-benefits > div { display:flex; align-items:center; gap:8px; font-size:.68rem; font-weight:700; }.category-benefits > div:first-child { flex-direction:column; align-items:flex-start; gap:0; }.category-benefits svg { color:#0969ed; }.category-benefits small { display:block; color:#8393a7; font-size:.58rem; font-weight:400; }.category-benefits .home-btn { padding:9px 15px; white-space:nowrap; font-size:.68rem; }
        .about-hero { position:relative; display:flex; align-items:center; min-height:265px; margin:-40px 0 20px; padding:32px; overflow:hidden; border-radius:8px; background:linear-gradient(105deg,#eef7ff,#fff 58%,#dceefa); }.about-hero h1 { margin:9px 0; color:#102e55; font-size:clamp(1.9rem,4vw,3rem); line-height:1.04; }.about-hero h1 strong { color:#0969ed; }.about-hero p { max-width:470px; color:#52606d; font-size:.78rem; }.about-hero-products { display:flex; align-items:end; width:47%; height:225px; margin-left:auto; }.about-hero-products a { width:25%; height:100%; display:flex; align-items:end; }.about-hero-products img { width:100%; height:94%; object-fit:contain; filter:drop-shadow(0 12px 9px rgba(16,46,85,.17)); }.about-script { position:absolute; top:28px; right:30px; color:#0969ed; font-family:cursive; font-size:1.4rem; line-height:.88; transform:rotate(-8deg); }.about-story { display:grid; grid-template-columns:225px 1fr 1.15fr; gap:22px; align-items:center; margin:20px 0; }.about-story-image { height:208px; overflow:hidden; border-radius:9px; background:#eef6ff; }.about-story-image img { width:100%; height:100%; object-fit:cover; }.about-story-copy h2,.about-values h2 { margin:4px 0 12px; color:#102e55; font-size:1.3rem; }.about-story-copy p { color:#52606d; font-size:.75rem; line-height:1.65; }.about-story-copy strong,.about-story-copy small { display:block; color:#173f5f; }.about-story-copy small { color:#8393a7; font-size:.65rem; }.about-values { padding:20px; border-radius:10px; background:#eef6ff; }.values-grid { display:grid; grid-template-columns:1fr 1fr; gap:17px; }.values-grid div { display:grid; grid-template-columns:35px 1fr; column-gap:8px; }.values-grid b { grid-row:span 2; display:grid; place-items:center; width:35px; height:35px; border-radius:50%; color:#0969ed; background:#fff; }.values-grid strong { color:#173f5f; font-size:.72rem; }.values-grid small { color:#8393a7; font-size:.62rem; }.about-stats { display:grid; grid-template-columns:repeat(4,1fr); gap:15px; margin:20px 0; padding:18px 24px; border-radius:9px; background:#eef6ff; }.about-stats div { display:grid; grid-template-columns:35px 1fr; column-gap:8px; align-items:center; border-right:1px solid #c9dff5; }.about-stats div:last-child { border:0; }.about-stats svg { grid-row:span 2; color:#0969ed; }.about-stats strong { color:#0969ed; font-size:1.1rem; }.about-stats small { color:#8393a7; font-size:.6rem; }.about-cta { display:flex; align-items:center; justify-content:space-between; gap:20px; padding:16px 25px; border-radius:9px; color:#fff; background:linear-gradient(100deg,#0969ed,#0572ed); }.about-cta > div { display:flex; align-items:center; gap:14px; }.about-cta strong,.about-cta small { display:block; }.about-cta small { margin-top:3px; color:#dceafa; font-size:.68rem; }.about-cta .home-btn-light { color:#0969ed; border:0; }
        .site-footer { margin-top:60px; padding:38px clamp(18px,5vw,64px) 18px; color:#fff; background:#062b52; }
        .footer-grid { display:grid; grid-template-columns:1.5fr 1fr 1fr 1fr; gap:30px; max-width:1180px; margin:auto; }.footer-grid h3 { margin:0 0 12px; font-size:.78rem; }.footer-grid a,.footer-grid p { display:block; margin:6px 0; color:#c8d8e8; font-size:.72rem; }.footer-brand img { height:36px; width:auto; background:#fff; border-radius:4px; padding:3px; }.footer-brand p { max-width:220px; line-height:1.6; }.footer-bottom { max-width:1180px; margin:30px auto 0; padding-top:14px; border-top:1px solid rgba(255,255,255,.18); color:#a9bfd4; font-size:.65rem; display:flex; justify-content:space-between; }
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(min(220px, 100%), 1fr));
            gap: 18px;
        }
        .alert-success {
            background: var(--vert-clair);
            border: 1px solid var(--vert);
            color: var(--vert-fonce);
            padding: 10px 14px;
            border-radius: 6px;
            margin-bottom: 16px;
        }
        .alert-error {
            background: #fdeaea;
            border: 1px solid #d9534f;
            color: #a33;
            padding: 10px 14px;
            border-radius: 6px;
            margin-bottom: 16px;
        }
        .prix { color: var(--vert-fonce); font-weight: bold; }
        .badge-precommande {
            background: var(--vert);
            color: var(--blanc);
            font-size: 0.75rem;
            padding: 2px 8px;
            border-radius: 12px;
        }
        .badge-stock, .badge-rupture {
            display: inline-block;
            font-size: 0.75rem;
            padding: 2px 8px;
            border-radius: 12px;
        }
        .badge-stock { color: #087443; background: #dff7e9; }
        .badge-rupture { color: #a33a3a; background: #fde5e5; }
        input, select, textarea {
            width: 100%;
            padding: 8px;
            margin-bottom: 12px;
            border: 1px solid #ccc;
            border-radius: 6px;
        }
        label { font-weight: 600; display: block; margin-bottom: 4px; }
        h1, h2, h3 { line-height: 1.2; }
        img { max-width: 100%; }
        button, input, select, textarea { font: inherit; }

        @media (max-width: 760px) {
            .utility-bar { gap:8px; overflow-x:auto; white-space:nowrap; font-size:.58rem; }
            .utility-bar span:nth-child(2) { display:none; }
            header.site { align-items: stretch; }
            header.site .logo { flex: 1 1 auto; }
            header.site form.search { order: 3; flex-basis: 100%; max-width: none; }
            header.site nav { display: flex; align-items: center; gap: 8px; overflow-x: auto; padding-bottom: 2px; }
            header.site nav a { margin-left: 0; padding: 8px 2px; white-space: nowrap; font-size: .92rem; }
            .main-nav { order:4; flex-basis:100%; justify-content:flex-start; }
            .main-nav a { padding:8px 10px; }
            .action-nav { margin-left:auto; gap:12px; }
            main { padding-top: 24px; }
            .grid { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px; }
            .card { padding: 11px; }
            .btn { width: 100%; }
            form.card .btn, .card form .btn { width: 100%; }
            footer.site { margin-top: 24px; padding: 16px 14px; }
            .admin-shell { gap: 14px !important; }
            .admin-sidebar { width: 100% !important; }
            .admin-sidebar .card { display: flex; gap: 10px; overflow-x: auto; padding: 10px; }
            .admin-sidebar .card a { margin: 0 !important; padding: 8px 4px; white-space: nowrap; font-size: .9rem; }
            .admin-content { min-width: 0 !important; width: 100%; }
            .product-card-image { height: 128px !important; }
            .home-hero { grid-template-columns:1fr; margin:-24px -14px 30px; min-height:0; }
            .hero-copy { padding:34px 20px 24px; }.hero-copy h1 { font-size:2rem; }.hero-copy p { font-size:.88rem; }
            .hero-promises { gap:10px; justify-content:space-between; }.hero-promises span { font-size:.62rem; }.hero-promises small { font-size:.55rem; }
            .hero-showcase { min-height:250px; }.hero-script { font-size:1.1rem; right:16px; }.hero-product-1 { left:1%; }.hero-product-2 { right:0; }.hero-device-phone { width:82px; height:135px; left:12%; }.hero-device-audio { width:105px; height:70px; right:8%; }.hero-device-watch { width:76px; height:76px; left:25%; }.hero-device-shoe { width:120px; height:52px; right:7%; }
            .section-heading { align-items:flex-start; flex-direction:column; }.section-heading > a { align-self:flex-end; }.category-grid { grid-template-columns:repeat(4,1fr); }.category-art { width:52px; height:45px; }
            .home-products { grid-template-columns:repeat(2,minmax(0,1fr)); }.home-products .product-card-image { height:128px !important; }
            .promo-grid { grid-template-columns:1fr; margin:34px 0; }.promo-panel { min-height:165px; padding:22px; }.promo-panel strong { font-size:1.4rem; }
            .why-section { margin:34px -14px; padding:30px 14px; }.why-grid { grid-template-columns:repeat(2,1fr); gap:24px 10px; }.testimonial-grid { grid-template-columns:1fr; }
            .newsletter { align-items:stretch; flex-direction:column; padding:18px; }.newsletter form { flex-wrap:wrap; }.newsletter input { flex:1; min-width:180px; }.newsletter button { flex:0 0 auto; }
            .footer-grid { grid-template-columns:repeat(2,1fr); gap:22px 16px; }.footer-brand { grid-column:1/-1; }.footer-bottom { flex-direction:column; gap:6px; }
            .shop-banner { margin:-24px -14px 20px; padding:22px 18px; min-height:250px; align-items:flex-start; }.shop-banner-products { position:absolute; bottom:0; right:4%; width:55%; height:125px; }.shop-banner-promises { position:absolute; left:18px; bottom:14px; gap:8px; }.shop-layout { grid-template-columns:1fr; }.shop-sidebar { order:1; }.shop-results { order:2; }.shop-product-grid { grid-template-columns:repeat(2,minmax(0,1fr)); }.shop-results-head { align-items:flex-start; flex-direction:column; }.shop-sort,.shop-sort select { width:100%; }
            .about-hero { margin:-24px -14px 20px; min-height:340px; padding:22px 18px; align-items:flex-start; }.about-hero-products { position:absolute; right:2%; bottom:8px; width:58%; height:150px; }.about-script { top:20px; right:15px; font-size:1rem; }.about-story { grid-template-columns:1fr; }.about-story-image { height:220px; }.about-values { padding:16px; }.about-stats { grid-template-columns:repeat(2,1fr); padding:16px; }.about-stats div:nth-child(2) { border:0; }.about-cta { align-items:stretch; flex-direction:column; padding:18px; }
            .category-hero { margin:-24px -14px 20px; min-height:330px; padding:22px 18px; align-items:flex-start; }.category-hero-products { position:absolute; right:3%; bottom:38px; width:58%; height:145px; }.category-hero-promises { left:18px; bottom:12px; gap:8px; }.category-hero-promises span { font-size:.55rem; }.category-page-layout { grid-template-columns:1fr; }.category-card-grid { grid-template-columns:repeat(2,minmax(0,1fr)); gap:10px; }.category-large-image { height:120px; }.category-benefits { align-items:stretch; flex-wrap:wrap; padding:14px; }.category-benefits > div { flex:1 1 40%; }.category-benefits .home-btn { flex:1 1 100%; text-align:center; }
            .contact-hero { margin:-24px -14px 20px; padding:22px 18px; min-height:360px; display:grid; grid-template-columns:1.2fr .8fr; gap:18px; align-items:center; position:relative; overflow:hidden; background:linear-gradient(135deg,#0f2b46,#143a5d,#1a4a7a); color:#fff; border-radius:0 0 26px 26px; } .contact-hero::before { content:""; position:absolute; inset:0; background:radial-gradient(circle at right center, rgba(255,255,255,.12), transparent 32%); } .contact-hero > * { position:relative; z-index:1; } .contact-hero p { color:rgba(255,255,255,.82); } .contact-hero-cards { display:grid; gap:12px; } .info-card { background:rgba(255,255,255,.08); border:1px solid rgba(255,255,255,.12); border-radius:18px; padding:18px 16px; display:flex; flex-direction:column; gap:8px; box-shadow:0 16px 30px rgba(7,20,38,.18); } .info-card-highlight { background:linear-gradient(135deg,rgba(255,255,255,.18),rgba(78,150,255,.14)); } .info-icn { width:42px; height:42px; display:inline-flex; align-items:center; justify-content:center; border-radius:12px; background:rgba(255,255,255,.12); } .contact-grid { display:grid; grid-template-columns:1.25fr .75fr; gap:20px; margin-top:24px; } .contact-card, .contact-sidebar { background:var(--card); border:1px solid rgba(21,39,69,.08); border-radius:22px; box-shadow:0 18px 35px rgba(16,34,58,.06); } .contact-card { padding:26px; } .contact-sidebar { padding:24px 20px; } .contact-form { display:flex; flex-direction:column; gap:18px; } .form-row { display:grid; gap:16px; } .two-cols { grid-template-columns:repeat(2, minmax(0, 1fr)); } .contact-form label { display:block; margin-bottom:8px; font-weight:700; color:var(--heading); } .contact-form input, .contact-form textarea { width:100%; border:1px solid rgba(17,24,39,.12); border-radius:14px; padding:14px 15px; background:#f6f9ff; color:var(--heading); } .contact-form textarea { resize:vertical; min-height:140px; } .contact-points { display:grid; gap:16px; margin:18px 0 22px; } .contact-points > div { display:flex; align-items:flex-start; gap:12px; } .icon-wrap { width:36px; height:36px; display:inline-flex; align-items:center; justify-content:center; border-radius:12px; background:rgba(34,111,227,.12); color:var(--primary); } .contact-meta { border-top:1px solid rgba(11,18,32,.08); padding-top:18px; display:grid; gap:12px; } .contact-meta p { display:flex; align-items:center; gap:8px; margin:0; color:var(--body); } .compact { margin-bottom:0; } .section-heading.compact h2 { margin-top:8px; }
        }

        @media (max-width: 480px) {
            header.site .logo { font-size: 1.15rem; }
            header.site .logo img { height: 29px !important; margin-right: 5px !important; }
            .grid { grid-template-columns: 1fr; }
            .category-grid { grid-template-columns:repeat(2,1fr); }.category-tile { padding:12px 6px; }.hero-actions { flex-direction:column; }.home-btn { justify-content:center; }
            h1 { font-size: 1.65rem; }
            h2 { font-size: 1.3rem; }
            .alert-success, .alert-error { padding: 10px; }
            input, select, textarea { font-size: 16px; }
        }
    </style>
</head>
<body>
    <div class="utility-bar">
        <span><x-icon name="truck" size="14"/> Livraison rapide partout au Togo</span>
        <span><x-icon name="shield" size="14"/> Paiement sécurisé (Mobile Money & autres)</span>
        <span><x-icon name="headset" size="14"/> Service client disponible · {{ \App\Models\AppSetting::read('contact_phone', '+228 96 29 20 39') }}</span>
    </div>
    <header class="site">
        <a class="logo" href="{{ route('home') }}"><img src="{{ asset('images/logo.png') }}" alt="HerveShop" style="height:38px; width:auto;"></a>
        <form class="search" action="{{ route('products.index') }}" method="GET">
            <input type="text" name="q" placeholder="Rechercher un produit..." value="{{ request('q') }}">
            <button type="submit" aria-label="Rechercher"><svg width="16" height="16" viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="m15.5 14 4.2 4.2-1.5 1.5-4.2-4.2A7 7 0 1 1 15.5 14ZM10 15a5 5 0 1 0 0-10 5 5 0 0 0 0 10Z"/></svg></button>
        </form>
        <nav class="main-nav">
            <a href="{{ route('home') }}">Accueil</a>
            <a href="{{ route('products.index') }}">Boutique</a>
            <a href="{{ route('categories.index') }}">Catégories</a>
            <a href="{{ route('about.index') }}">À propos</a>
            <a href="{{ route('contact.index') }}">Contact</a>
        </nav>
        <nav class="action-nav" aria-label="Actions du compte">
            @auth
                <a href="{{ Auth::user()->is_admin ? route('admin.dashboard') : route('account.dashboard') }}" aria-label="Mon compte" title="Mon compte"><svg width="19" height="19" viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm-7 8a7 7 0 0 1 14 0H5Z"/></svg></a>
                @if(!Auth::user()->is_admin)<a href="{{ route('wishlist.index') }}" aria-label="Mes favoris" title="Mes favoris"><svg width="19" height="19" viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="m12 20-1.4-1.3C5.6 14.2 2 10.9 2 7a5 5 0 0 1 9-3 5 5 0 0 1 9 3c0 3.9-3.6 7.2-8.6 11.7L12 20Z"/></svg></a>@endif
            @else
                <a href="{{ route('login') }}" aria-label="Connexion" title="Connexion"><svg width="19" height="19" viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm-7 8a7 7 0 0 1 14 0H5Z"/></svg></a>
            @endauth
            <a href="{{ route('cart.index') }}" class="cart-link" aria-label="Panier" title="Panier"><svg width="20" height="20" viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M5 7h14l-1 13H6L5 7Zm3-2a4 4 0 0 1 8 0h-2a2 2 0 0 0-4 0H8Z"/></svg>@if(count(session('cart', [])) > 0)<span class="cart-badge">{{ count(session('cart', [])) }}</span>@endif</a>
        </nav>
    </header>

    <main>
        @if (session('success'))
            <div class="alert-success">{{ session('success') }}</div>
        @endif
        @if ($errors->any())
            <div class="alert-error">
                <ul style="margin:0; padding-left:18px;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>

    <footer id="contact" class="site-footer">
        <div class="footer-grid">
            <div class="footer-brand"><img src="{{ asset('images/logo.png') }}" alt="HerveShop"><p>Vos envies, notre priorité. Des produits choisis avec soin au meilleur prix.</p></div>
            <div><h3>Liens utiles</h3><a href="{{ route('home') }}">Accueil</a><a href="{{ route('products.index') }}">Boutique</a><a href="{{ route('products.index') }}">Catégories</a><a href="{{ route('compare.index') }}">Comparer</a></div>
            <div><h3>Service client</h3><a href="{{ route('products.index') }}">FAQ</a><a href="{{ route('products.index') }}">Livraison</a><a href="{{ route('products.index') }}">Retours & Remboursement</a><a href="{{ route('cart.index') }}">Suivi de commande</a></div>
            <div><h3>Contact</h3><p><x-icon name="headset" size="14"/> {{ \App\Models\AppSetting::read('contact_phone', '+228 96 29 20 39') }}</p><p><x-icon name="mail" size="14"/> botoherve67@gmail.com</p><p><x-icon name="pin" size="14"/> Lomé, Togo</p><a href="{{ route('contact.index') }}" class="footer-link">Nous écrire</a></div>
        </div>
        <div class="footer-bottom"><span>&copy; {{ date('Y') }} HerveShop. Tous droits réservés.</span><span>Vos envies, notre priorité — HerveShop</span></div>
    </footer>
</body>
</html>
