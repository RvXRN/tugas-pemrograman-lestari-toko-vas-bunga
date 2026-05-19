<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Lestari — Toko Vas Bunga & Dekorasi</title>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;0,700;1,400;1,600&family=Jost:wght@300;400;500&display=swap" rel="stylesheet">
<style>
  :root {
    --sage:    #7A9175;
    --sage-lt: #EDF2EB;
    --sage-dk: #4A6147;
    --clay:    #C97B5A;
    --clay-lt: #F5EDE7;
    --clay-dk: #9A5538;
    --cream:   #FAF8F4;
    --warm:    #F3EDE4;
    --ivory:   #FDFBF8;
    --text:    #2C2520;
    --muted:   #8C8078;
    --border:  #E4DDD5;
    --white:   #FFFFFF;
    --dark:    #1E1A16;
  }

  * { margin:0; padding:0; box-sizing:border-box; }
  html { scroll-behavior: smooth; }

  body {
    font-family: 'Jost', sans-serif;
    background: var(--cream);
    color: var(--text);
    overflow-x: hidden;
  }

  /* ── TOPBAR ── */
  .topbar {
    background: var(--sage-dk);
    text-align: center;
    padding: 8px 24px;
    font-size: 12px;
    color: rgba(255,255,255,.85);
    letter-spacing: .6px;
  }
  .topbar strong { color: #D4E8C2; }

  /* ── NAVBAR ── */
  nav {
    position: sticky; top: 0; z-index: 100;
    background: var(--ivory);
    border-bottom: 1px solid var(--border);
    display: flex; align-items: center; justify-content: space-between;
    padding: 0 60px; height: 70px;
    animation: slideDown .5s ease;
  }
  @keyframes slideDown { from { transform:translateY(-100%); } to { transform:translateY(0); } }

  .nav-logo {
    font-family: 'Cormorant Garamond', serif;
    font-size: 26px; font-weight: 700;
    color: var(--sage-dk);
    letter-spacing: 1px;
    display: flex; align-items: center; gap: 8px;
  }
  .logo-icon { font-size: 22px; }

  .nav-links { display: flex; gap: 36px; }
  .nav-links a {
    color: var(--muted); text-decoration: none;
    font-size: 13px; font-weight: 400; letter-spacing: .5px;
    transition: color .2s;
    position: relative;
  }
  .nav-links a::after {
    content: ''; position: absolute; bottom: -4px; left: 0;
    width: 0; height: 1px; background: var(--clay);
    transition: width .25s;
  }
  .nav-links a:hover { color: var(--text); }
  .nav-links a:hover::after { width: 100%; }

  .nav-actions { display: flex; align-items: center; gap: 8px; }
  .nav-search-wrap {
    display: flex; align-items: center;
    background: var(--warm); border: 1px solid var(--border);
    border-radius: 24px; padding: 7px 16px; gap: 8px;
  }
  .nav-search {
    background: none; border: none; outline: none;
    font-family: 'Jost', sans-serif; font-size: 13px;
    color: var(--text); width: 160px;
  }
  .nav-search::placeholder { color: var(--muted); }
  .search-icon { font-size: 14px; color: var(--muted); }

  .nav-btn {
    background: none; border: none; cursor: pointer;
    width: 40px; height: 40px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 18px; color: var(--text);
    transition: background .2s;
    position: relative;
  }
  .nav-btn:hover { background: var(--warm); }
  .badge {
    position: absolute; top: 4px; right: 4px;
    background: var(--clay); color: white;
    font-size: 9px; font-weight: 500;
    width: 15px; height: 15px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
  }

  /* ── HERO ── */
  .hero {
    display: grid; grid-template-columns: 1fr 1fr;
    min-height: 90vh;
    background: var(--warm);
    overflow: hidden;
  }

  .hero-left {
    display: flex; flex-direction: column; justify-content: center;
    padding: 80px 60px 80px 80px;
    animation: fadeLeft .8s ease .1s both;
  }
  @keyframes fadeLeft { from { opacity:0; transform:translateX(-40px); } to { opacity:1; transform:translateX(0); } }

  .hero-eyebrow {
    display: flex; align-items: center; gap: 10px;
    margin-bottom: 24px;
  }
  .eyebrow-line { width: 32px; height: 1px; background: var(--clay); }
  .eyebrow-text {
    font-size: 11px; font-weight: 500; letter-spacing: 2px;
    text-transform: uppercase; color: var(--clay);
  }

  .hero-title {
    font-family: 'Cormorant Garamond', serif;
    font-size: clamp(48px, 5.5vw, 76px);
    font-weight: 700; line-height: 1.0;
    color: var(--dark); margin-bottom: 20px;
  }
  .hero-title em { font-style: italic; color: var(--sage-dk); }

  .hero-desc {
    font-size: 15px; line-height: 1.8;
    color: var(--muted); max-width: 400px; margin-bottom: 40px;
  }

  .hero-cta { display: flex; gap: 14px; align-items: center; flex-wrap: wrap; }
  .btn-clay {
    background: var(--clay); color: white; border: none;
    padding: 13px 32px; border-radius: 2px;
    font-family: 'Jost', sans-serif;
    font-size: 13px; font-weight: 500; letter-spacing: .8px;
    cursor: pointer; text-decoration: none; display: inline-block;
    transition: background .2s, transform .15s;
  }
  .btn-clay:hover { background: var(--clay-dk); transform: translateY(-1px); }
  .btn-ghost {
    color: var(--sage-dk); font-size: 13px; font-weight: 400;
    display: flex; align-items: center; gap: 6px;
    text-decoration: none; letter-spacing: .3px;
    transition: gap .2s;
  }
  .btn-ghost:hover { gap: 10px; }

  .hero-badges { display: flex; gap: 32px; margin-top: 52px; padding-top: 36px; border-top: 1px solid var(--border); }
  .hero-badge-item .num {
    font-family: 'Cormorant Garamond', serif;
    font-size: 32px; font-weight: 700; color: var(--sage-dk); line-height: 1;
  }
  .hero-badge-item .lbl { font-size: 11px; color: var(--muted); margin-top: 3px; letter-spacing: .3px; }

  .hero-right {
    position: relative; overflow: hidden;
    animation: fadeRight .9s ease .2s both;
  }
  @keyframes fadeRight { from { opacity:0; transform:translateX(40px); } to { opacity:1; transform:translateX(0); } }

  .hero-img-main {
    width: 100%; height: 100%;
    background: linear-gradient(160deg, #C8D8C0 0%, #8FAD87 40%, #5A7A54 100%);
    display: flex; align-items: center; justify-content: center;
    position: relative;
  }
  .hero-vase-display {
    display: flex; flex-direction: column; align-items: center; gap: 0;
    animation: floatUp 3s ease-in-out infinite;
  }
  @keyframes floatUp {
    0%,100% { transform: translateY(0); }
    50%      { transform: translateY(-10px); }
  }
  .hero-vase-emoji { font-size: 140px; filter: drop-shadow(0 24px 48px rgba(0,0,0,.2)); }
  .hero-vase-label {
    background: rgba(255,255,255,.9);
    border-radius: 20px; padding: 8px 20px;
    font-size: 12px; font-weight: 500; color: var(--sage-dk);
    letter-spacing: .5px; margin-top: 16px;
  }

  .hero-float-card {
    position: absolute; bottom: 48px; left: -20px;
    background: white; border-radius: 12px;
    padding: 14px 18px; box-shadow: 0 12px 40px rgba(0,0,0,.12);
    display: flex; align-items: center; gap: 12px;
    animation: floatCard 4s ease-in-out infinite .5s;
  }
  @keyframes floatCard {
    0%,100% { transform: translateY(0); }
    50%      { transform: translateY(-6px); }
  }
  .float-icon { font-size: 28px; }
  .float-name { font-size: 12px; font-weight: 500; color: var(--text); }
  .float-price { font-family: 'Cormorant Garamond', serif; font-size: 16px; font-weight: 700; color: var(--clay); }

  .hero-float-review {
    position: absolute; top: 56px; right: 32px;
    background: white; border-radius: 12px;
    padding: 12px 16px; box-shadow: 0 8px 28px rgba(0,0,0,.1);
    animation: floatCard 4s ease-in-out infinite 1s;
  }
  .float-stars { color: #D4A853; font-size: 12px; }
  .float-review-text { font-size: 11px; color: var(--muted); margin-top: 3px; }

  /* ── CATEGORIES ── */
  .section { padding: 80px 80px; }
  .section-alt { background: var(--ivory); }

  .sec-head { display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 44px; }
  .sec-tag {
    display: flex; align-items: center; gap: 10px;
    font-size: 11px; letter-spacing: 2px; text-transform: uppercase;
    color: var(--clay); font-weight: 500; margin-bottom: 10px;
  }
  .sec-tag::before { content:''; width:24px; height:1px; background:var(--clay); }
  .sec-title {
    font-family: 'Cormorant Garamond', serif;
    font-size: 38px; font-weight: 700; color: var(--dark);
    line-height: 1.1;
  }
  .sec-title em { font-style: italic; color: var(--sage-dk); }
  .see-all {
    font-size: 12px; font-weight: 500; letter-spacing: .5px;
    color: var(--clay); text-decoration: none;
    display: flex; align-items: center; gap: 5px;
    transition: gap .2s;
  }
  .see-all:hover { gap: 9px; }

  .cat-grid { display: grid; grid-template-columns: repeat(6, 1fr); gap: 14px; }
  .cat-card {
    background: var(--white); border: 1px solid var(--border);
    border-radius: 12px; padding: 24px 12px;
    text-align: center; cursor: pointer;
    transition: transform .2s, box-shadow .2s, border-color .2s;
  }
  .cat-card:hover { transform: translateY(-4px); box-shadow: 0 12px 36px rgba(90,122,84,.12); border-color: var(--sage); }
  .cat-emoji { font-size: 34px; margin-bottom: 10px; }
  .cat-name { font-size: 12px; font-weight: 500; color: var(--dark); }
  .cat-count { font-size: 10px; color: var(--muted); margin-top: 3px; }

  /* ── PRODUCTS ── */
  .prod-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 24px; }

  .prod-card {
    background: var(--white); border-radius: 10px;
    border: 1px solid var(--border); overflow: hidden; cursor: pointer;
    transition: transform .25s, box-shadow .25s;
  }
  .prod-card:hover { transform: translateY(-6px); box-shadow: 0 20px 48px rgba(61,43,31,.1); }
  .prod-card:hover .prod-overlay { opacity: 1; }

  .prod-img-wrap { position: relative; overflow: hidden; }
  .prod-img {
    width: 100%; height: 210px;
    display: flex; align-items: center; justify-content: center;
    font-size: 72px; transition: transform .4s;
  }
  .prod-card:hover .prod-img { transform: scale(1.06); }

  .bg-sage  { background: linear-gradient(145deg, #C8D8C0, #A0C098); }
  .bg-clay  { background: linear-gradient(145deg, #E8D0C0, #D4B098); }
  .bg-rose  { background: linear-gradient(145deg, #F0D8D8, #E0B8B8); }
  .bg-sky   { background: linear-gradient(145deg, #D0E0F0, #B0C8E0); }
  .bg-sand  { background: linear-gradient(145deg, #EEE4D4, #DDD0B8); }
  .bg-moss  { background: linear-gradient(145deg, #C8D4B0, #A8BC88); }
  .bg-blush { background: linear-gradient(145deg, #F0DCE0, #E0C0C4); }
  .bg-stone { background: linear-gradient(145deg, #D8D4CC, #C4BEB4); }

  .prod-overlay {
    position: absolute; inset: 0;
    background: rgba(30,26,22,.45);
    display: flex; align-items: center; justify-content: center; gap: 10px;
    opacity: 0; transition: opacity .25s;
  }
  .ov-btn {
    background: white; border: none;
    width: 38px; height: 38px; border-radius: 50%;
    font-size: 15px; cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    transition: transform .15s, background .15s;
  }
  .ov-btn:hover { transform: scale(1.1); background: var(--clay); }

  .prod-badge-wrap { position: absolute; top: 12px; left: 12px; display: flex; flex-direction: column; gap: 4px; }
  .pb {
    font-size: 10px; font-weight: 500; letter-spacing: .5px;
    padding: 3px 9px; border-radius: 10px; width: fit-content;
    text-transform: uppercase;
  }
  .pb-sale  { background: var(--clay);    color: white; }
  .pb-new   { background: var(--sage-dk); color: white; }
  .pb-best  { background: var(--dark);    color: #D4E8C2; }

  .prod-body { padding: 16px; }
  .prod-cat { font-size: 10px; color: var(--muted); letter-spacing: .8px; text-transform: uppercase; margin-bottom: 5px; }
  .prod-name { font-weight: 400; font-size: 14px; color: var(--text); margin-bottom: 6px; line-height: 1.45; }
  .prod-rating { display: flex; align-items: center; gap: 5px; font-size: 11px; color: var(--muted); margin-bottom: 10px; }
  .stars { color: #C8A050; font-size: 10px; }
  .prod-foot { display: flex; justify-content: space-between; align-items: center; }
  .prod-price { font-family: 'Cormorant Garamond', serif; font-size: 20px; font-weight: 700; color: var(--clay); }
  .prod-old { font-size: 11px; color: var(--muted); text-decoration: line-through; }
  .add-btn {
    background: var(--sage-dk); color: white; border: none;
    width: 32px; height: 32px; border-radius: 6px;
    font-size: 18px; cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    transition: background .2s, transform .15s;
    line-height: 1;
  }
  .add-btn:hover { background: var(--clay); transform: scale(1.08); }

  /* ── FEATURE STRIP ── */
  .feature-strip {
    background: var(--sage-dk);
    padding: 40px 80px;
    display: grid; grid-template-columns: repeat(4, 1fr);
    gap: 24px;
  }
  .feat-item { display: flex; align-items: flex-start; gap: 16px; }
  .feat-icon { font-size: 28px; flex-shrink: 0; }
  .feat-title { font-size: 13px; font-weight: 500; color: white; margin-bottom: 3px; }
  .feat-desc { font-size: 12px; color: rgba(255,255,255,.55); line-height: 1.5; }

  /* ── FEATURED / PROMO ── */
  .promo-grid {
    display: grid; grid-template-columns: 1fr 1fr;
    gap: 24px;
  }
  .promo-card {
    border-radius: 16px; overflow: hidden; position: relative;
    min-height: 320px; cursor: pointer;
    display: flex; align-items: flex-end;
    transition: transform .25s;
  }
  .promo-card:hover { transform: scale(1.01); }
  .promo-card.big { min-height: 420px; }

  .promo-bg-1 { background: linear-gradient(145deg, #8FAD87 0%, #4A6147 100%); }
  .promo-bg-2 { background: linear-gradient(145deg, #D4A878 0%, #9A5538 100%); }
  .promo-bg-3 { background: linear-gradient(145deg, #B0C8D8 0%, #6888A8 100%); }

  .promo-deco {
    position: absolute; inset: 0;
    display: flex; align-items: center; justify-content: center;
    font-size: 120px; opacity: .25;
    pointer-events: none;
  }
  .promo-deco-sm { font-size: 90px; }
  .promo-content {
    position: relative; z-index: 2;
    padding: 28px; width: 100%;
    background: linear-gradient(to top, rgba(0,0,0,.55) 0%, transparent 100%);
  }
  .promo-tag-label {
    font-size: 10px; letter-spacing: 1.5px; text-transform: uppercase;
    color: rgba(255,255,255,.7); margin-bottom: 6px;
  }
  .promo-card-title {
    font-family: 'Cormorant Garamond', serif;
    font-size: 26px; font-weight: 700; color: white; line-height: 1.15;
    margin-bottom: 10px;
  }
  .promo-card-title.lg { font-size: 34px; }
  .promo-link {
    font-size: 12px; font-weight: 500; color: rgba(255,255,255,.85);
    text-decoration: none; display: flex; align-items: center; gap: 5px;
    transition: gap .2s;
  }
  .promo-link:hover { gap: 9px; }

  /* ── TESTIMONIAL ── */
  .testi-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; }
  .testi-card {
    background: var(--white); border: 1px solid var(--border);
    border-radius: 10px; padding: 24px;
  }
  .testi-quote {
    font-family: 'Cormorant Garamond', serif;
    font-size: 36px; color: var(--sage); line-height: .8; margin-bottom: 8px;
  }
  .testi-text { font-size: 13px; line-height: 1.75; color: var(--muted); margin-bottom: 18px; font-style: italic; }
  .testi-row { display: flex; align-items: center; justify-content: space-between; }
  .testi-av {
    width: 36px; height: 36px; border-radius: 50%;
    font-size: 16px; display: flex; align-items: center; justify-content: center;
    margin-right: 10px; flex-shrink: 0;
  }
  .av-s { background: var(--sage-lt); }
  .av-c { background: var(--clay-lt); }
  .av-d { background: #E8E0D8; }
  .testi-info { display: flex; align-items: center; }
  .testi-name { font-size: 13px; font-weight: 500; color: var(--dark); }
  .testi-city { font-size: 11px; color: var(--muted); }
  .testi-stars { color: #C8A050; font-size: 12px; }

  /* ── NEWSLETTER ── */
  .newsletter {
    background: var(--warm);
    padding: 72px 80px;
    display: grid; grid-template-columns: 1fr 1fr;
    gap: 60px; align-items: center;
  }
  .nl-title {
    font-family: 'Cormorant Garamond', serif;
    font-size: 40px; font-weight: 700; color: var(--dark);
    line-height: 1.1; margin-bottom: 10px;
  }
  .nl-title em { color: var(--sage-dk); font-style: italic; }
  .nl-desc { font-size: 14px; color: var(--muted); line-height: 1.7; }
  .nl-form { display: flex; gap: 0; }
  .nl-input {
    flex: 1; padding: 14px 20px;
    border: 1px solid var(--border); border-right: none;
    border-radius: 4px 0 0 4px;
    font-family: 'Jost', sans-serif; font-size: 13px;
    background: white; color: var(--text); outline: none;
  }
  .nl-input:focus { border-color: var(--sage); }
  .nl-btn {
    background: var(--sage-dk); color: white; border: none;
    padding: 14px 24px; border-radius: 0 4px 4px 0;
    font-family: 'Jost', sans-serif; font-size: 13px; font-weight: 500;
    cursor: pointer; transition: background .2s; white-space: nowrap;
  }
  .nl-btn:hover { background: var(--clay); }
  .nl-note { font-size: 11px; color: var(--muted); margin-top: 10px; }
  .nl-img { display: flex; align-items: center; justify-content: center; font-size: 100px; }

  /* ── FOOTER ── */
  footer { background: var(--dark); padding: 60px 80px 32px; }
  .footer-top {
    display: grid; grid-template-columns: 2fr 1fr 1fr 1fr;
    gap: 48px; margin-bottom: 48px;
  }
  .f-logo {
    font-family: 'Cormorant Garamond', serif;
    font-size: 26px; font-weight: 700; color: var(--sage);
    margin-bottom: 12px;
  }
  .f-desc { font-size: 13px; line-height: 1.8; color: rgba(255,255,255,.45); max-width: 260px; margin-bottom: 24px; }
  .f-social { display: flex; gap: 8px; }
  .soc-btn {
    width: 34px; height: 34px; border-radius: 6px;
    background: rgba(255,255,255,.06); border: 1px solid rgba(255,255,255,.1);
    display: flex; align-items: center; justify-content: center;
    font-size: 15px; cursor: pointer; color: white; text-decoration: none;
    transition: background .2s;
  }
  .soc-btn:hover { background: var(--sage-dk); }
  .f-col-title {
    font-size: 11px; font-weight: 500; letter-spacing: 1.5px;
    text-transform: uppercase; color: rgba(255,255,255,.5);
    margin-bottom: 18px;
  }
  .f-links { list-style: none; }
  .f-links li { margin-bottom: 10px; }
  .f-links a { font-size: 13px; color: rgba(255,255,255,.45); text-decoration: none; transition: color .2s; }
  .f-links a:hover { color: var(--sage); }
  .footer-bottom {
    border-top: 1px solid rgba(255,255,255,.08);
    padding-top: 24px;
    display: flex; justify-content: space-between; align-items: center;
    font-size: 12px; color: rgba(255,255,255,.25);
  }
  .pay-chips { display: flex; gap: 6px; }
  .pay-chip {
    background: rgba(255,255,255,.07);
    border: 1px solid rgba(255,255,255,.1);
    border-radius: 4px; padding: 3px 10px;
    font-size: 10px; color: rgba(255,255,255,.45); font-weight: 500;
  }

  /* ── CART PANEL ── */
  .cart-overlay {
    position: fixed; inset: 0; background: rgba(0,0,0,.35);
    z-index: 199; opacity: 0; pointer-events: none; transition: opacity .3s;
  }
  .cart-overlay.open { opacity: 1; pointer-events: all; }
  .cart-panel {
    position: fixed; top: 0; right: -400px;
    width: 400px; height: 100vh;
    background: var(--ivory); z-index: 200;
    box-shadow: -4px 0 32px rgba(0,0,0,.12);
    transition: right .35s cubic-bezier(.22,.68,0,1.2);
    display: flex; flex-direction: column;
  }
  .cart-panel.open { right: 0; }
  .cart-head {
    padding: 24px; border-bottom: 1px solid var(--border);
    display: flex; justify-content: space-between; align-items: center;
  }
  .cart-head-title {
    font-family: 'Cormorant Garamond', serif;
    font-size: 22px; font-weight: 700; color: var(--dark);
  }
  .close-x {
    background: var(--warm); border: 1px solid var(--border);
    width: 34px; height: 34px; border-radius: 50%;
    font-size: 16px; cursor: pointer; color: var(--text);
    display: flex; align-items: center; justify-content: center;
    transition: background .2s;
  }
  .close-x:hover { background: var(--border); }
  .cart-body { flex: 1; overflow-y: auto; padding: 16px; }
  .cart-item {
    display: flex; gap: 12px; align-items: center;
    padding: 12px; border-radius: 8px;
    border: 1px solid var(--border); margin-bottom: 10px;
    background: white;
  }
  .ci-img { font-size: 30px; width: 52px; height: 52px; background: var(--warm); border-radius: 6px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
  .ci-name { font-size: 13px; font-weight: 500; color: var(--text); }
  .ci-sub { font-size: 11px; color: var(--muted); }
  .ci-price { font-family: 'Cormorant Garamond', serif; font-size: 17px; font-weight: 700; color: var(--clay); margin-left: auto; white-space: nowrap; }
  .cart-foot { padding: 20px 24px; border-top: 1px solid var(--border); }
  .cart-summary { display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 14px; }
  .cart-sum-lbl { font-size: 13px; color: var(--muted); }
  .cart-sum-val { font-family: 'Cormorant Garamond', serif; font-size: 24px; font-weight: 700; color: var(--dark); }
  .checkout-btn {
    width: 100%; background: var(--clay); color: white; border: none;
    padding: 14px; border-radius: 4px;
    font-family: 'Jost', sans-serif; font-size: 14px; font-weight: 500;
    cursor: pointer; transition: background .2s; letter-spacing: .5px;
  }
  .checkout-btn:hover { background: var(--clay-dk); }

  /* ── TOAST ── */
  .toast {
    position: fixed; bottom: 28px; left: 50%;
    transform: translateX(-50%) translateY(60px);
    background: var(--dark); color: rgba(255,255,255,.9);
    padding: 11px 22px; border-radius: 6px;
    font-size: 13px; font-weight: 400;
    box-shadow: 0 8px 32px rgba(0,0,0,.2);
    z-index: 300; transition: transform .3s ease;
    pointer-events: none; display: flex; align-items: center; gap: 8px;
  }
  .toast.show { transform: translateX(-50%) translateY(0); }
</style>
</head>
<body>

<!-- TOPBAR -->
<div class="topbar">🚚 Gratis ongkir untuk pembelian di atas <strong>Rp 250.000</strong> — Berlaku hari ini!</div>

<!-- NAVBAR -->
<nav>
  <div class="nav-logo"><span class="logo-icon">🌸</span> Lestari</div>
  <div class="nav-links">
    <a href="#">Beranda</a>
    <a href="#">Vas Bunga</a>
    <a href="#">Buket & Rangkaian</a>
    <a href="#">Dekorasi</a>
    <a href="#">Promo</a>
    <a href="#">Tentang</a>
  </div>
  <div class="nav-actions">
    <div class="nav-search-wrap">
      <span class="search-icon">🔍</span>
      <input class="nav-search" type="text" placeholder="Cari vas, buket...">
    </div>
    <button class="nav-btn" onclick="openCart()">🛒<span class="badge" id="cartBadge">2</span></button>
    <button class="nav-btn">♡</button>
    <button class="nav-btn">👤</button>
  </div>
</nav>

<!-- HERO -->
<section class="hero">
  <div class="hero-left">
    <div class="hero-eyebrow">
      <div class="eyebrow-line"></div>
      <span class="eyebrow-text">Koleksi Baru 2025</span>
    </div>
    <h1 class="hero-title">
      Hiasi Ruanganmu<br>dengan <em>Keindahan</em><br>Alam
    </h1>
    <p class="hero-desc">Vas bunga dan dekorasi botanik pilihan untuk mempercantik setiap sudut rumahmu. Dibuat dengan cinta, dikirim dengan aman.</p>
    <div class="hero-cta">
      <a href="#" class="btn-clay">Belanja Sekarang →</a>
      <a href="#" class="btn-ghost">Lihat Koleksi ↗</a>
    </div>
    <div class="hero-badges">
      <div class="hero-badge-item">
        <div class="num">2.400+</div>
        <div class="lbl">Produk Tersedia</div>
      </div>
      <div class="hero-badge-item">
        <div class="num">15K+</div>
        <div class="lbl">Pelanggan Puas</div>
      </div>
      <div class="hero-badge-item">
        <div class="num">100%</div>
        <div class="lbl">Produk Original</div>
      </div>
    </div>
  </div>
  <div class="hero-right">
    <div class="hero-img-main">
      <div class="hero-vase-display">
        <div class="hero-vase-emoji">💐</div>
        <div class="hero-vase-label">✨ Koleksi Terfavorit</div>
      </div>
      <div class="hero-float-card">
        <div class="float-icon">🏺</div>
        <div>
          <div class="float-name">Vas Keramik Nordic</div>
          <div class="float-price">Rp 185.000</div>
        </div>
      </div>
      <div class="hero-float-review">
        <div class="float-stars">★★★★★</div>
        <div class="float-review-text">4.9 · 3.200+ ulasan</div>
      </div>
    </div>
  </div>
</section>

<!-- CATEGORIES -->
<section class="section section-alt">
  <div class="sec-head">
    <div>
      <div class="sec-tag">Kategori</div>
      <h2 class="sec-title">Semua yang Kamu <em>Butuhkan</em></h2>
    </div>
    <a href="#" class="see-all">Lihat Semua →</a>
  </div>
  <div class="cat-grid">
    <div class="cat-card">
      <div class="cat-emoji">🏺</div>
      <div class="cat-name">Vas Keramik</div>
      <div class="cat-count">348 produk</div>
    </div>
    <div class="cat-card">
      <div class="cat-emoji">🌹</div>
      <div class="cat-name">Buket Segar</div>
      <div class="cat-count">124 produk</div>
    </div>
    <div class="cat-card">
      <div class="cat-emoji">🪴</div>
      <div class="cat-name">Pot & Tanaman</div>
      <div class="cat-count">216 produk</div>
    </div>
    <div class="cat-card">
      <div class="cat-emoji">🎍</div>
      <div class="cat-name">Rangkaian Kering</div>
      <div class="cat-count">98 produk</div>
    </div>
    <div class="cat-card">
      <div class="cat-emoji">🕯️</div>
      <div class="cat-name">Dekorasi Meja</div>
      <div class="cat-count">175 produk</div>
    </div>
    <div class="cat-card">
      <div class="cat-emoji">🎁</div>
      <div class="cat-name">Hamper & Gift</div>
      <div class="cat-count">62 produk</div>
    </div>
  </div>
</section>

<!-- PRODUCTS TERLARIS -->
<section class="section">
  <div class="sec-head">
    <div>
      <div class="sec-tag">Terlaris</div>
      <h2 class="sec-title">Produk <em>Pilihan</em> Minggu Ini</h2>
    </div>
    <a href="#" class="see-all">Lihat Semua →</a>
  </div>
  <div class="prod-grid">

    <div class="prod-card">
      <div class="prod-img-wrap">
        <div class="prod-img bg-sage">🏺</div>
        <div class="prod-badge-wrap"><span class="pb pb-best">TERLARIS</span></div>
        <div class="prod-overlay">
          <button class="ov-btn" onclick="addToCart('Vas Keramik Nordic')">🛒</button>
          <button class="ov-btn">♡</button>
          <button class="ov-btn">👁</button>
        </div>
      </div>
      <div class="prod-body">
        <div class="prod-cat">Vas Keramik</div>
        <div class="prod-name">Vas Keramik Nordic Matte 25cm</div>
        <div class="prod-rating"><span class="stars">★★★★★</span> 4.9 (312)</div>
        <div class="prod-foot">
          <div><div class="prod-price">Rp 185.000</div></div>
          <button class="add-btn" onclick="addToCart('Vas Keramik Nordic')">+</button>
        </div>
      </div>
    </div>

    <div class="prod-card">
      <div class="prod-img-wrap">
        <div class="prod-img bg-rose">🌹</div>
        <div class="prod-badge-wrap"><span class="pb pb-sale">-25%</span></div>
        <div class="prod-overlay">
          <button class="ov-btn" onclick="addToCart('Buket Mawar Merah')">🛒</button>
          <button class="ov-btn">♡</button>
          <button class="ov-btn">👁</button>
        </div>
      </div>
      <div class="prod-body">
        <div class="prod-cat">Buket Segar</div>
        <div class="prod-name">Buket Mawar Merah Premium 20 Tangkai</div>
        <div class="prod-rating"><span class="stars">★★★★★</span> 4.8 (198)</div>
        <div class="prod-foot">
          <div><div class="prod-price">Rp 225.000</div><div class="prod-old">Rp 300.000</div></div>
          <button class="add-btn" onclick="addToCart('Buket Mawar Merah')">+</button>
        </div>
      </div>
    </div>

    <div class="prod-card">
      <div class="prod-img-wrap">
        <div class="prod-img bg-moss">🪴</div>
        <div class="prod-badge-wrap"><span class="pb pb-new">BARU</span></div>
        <div class="prod-overlay">
          <button class="ov-btn" onclick="addToCart('Pot Teraso Minimalis')">🛒</button>
          <button class="ov-btn">♡</button>
          <button class="ov-btn">👁</button>
        </div>
      </div>
      <div class="prod-body">
        <div class="prod-cat">Pot & Tanaman</div>
        <div class="prod-name">Pot Teraso Minimalis + Tanaman Sukulen</div>
        <div class="prod-rating"><span class="stars">★★★★☆</span> 4.6 (87)</div>
        <div class="prod-foot">
          <div><div class="prod-price">Rp 135.000</div></div>
          <button class="add-btn" onclick="addToCart('Pot Teraso Minimalis')">+</button>
        </div>
      </div>
    </div>

    <div class="prod-card">
      <div class="prod-img-wrap">
        <div class="prod-img bg-clay">🎍</div>
        <div class="prod-overlay">
          <button class="ov-btn" onclick="addToCart('Rangkaian Pampas')">🛒</button>
          <button class="ov-btn">♡</button>
          <button class="ov-btn">👁</button>
        </div>
      </div>
      <div class="prod-body">
        <div class="prod-cat">Rangkaian Kering</div>
        <div class="prod-name">Rangkaian Pampas Grass Boho Natural</div>
        <div class="prod-rating"><span class="stars">★★★★★</span> 4.9 (156)</div>
        <div class="prod-foot">
          <div><div class="prod-price">Rp 98.000</div></div>
          <button class="add-btn" onclick="addToCart('Rangkaian Pampas')">+</button>
        </div>
      </div>
    </div>

    <div class="prod-card">
      <div class="prod-img-wrap">
        <div class="prod-img bg-sky">🏺</div>
        <div class="prod-badge-wrap"><span class="pb pb-sale">-30%</span></div>
        <div class="prod-overlay">
          <button class="ov-btn" onclick="addToCart('Vas Kaca Skandinavia')">🛒</button>
          <button class="ov-btn">♡</button>
          <button class="ov-btn">👁</button>
        </div>
      </div>
      <div class="prod-body">
        <div class="prod-cat">Vas Keramik</div>
        <div class="prod-name">Vas Kaca Skandinavia Set 3 Ukuran</div>
        <div class="prod-rating"><span class="stars">★★★★★</span> 4.7 (241)</div>
        <div class="prod-foot">
          <div><div class="prod-price">Rp 165.000</div><div class="prod-old">Rp 235.000</div></div>
          <button class="add-btn" onclick="addToCart('Vas Kaca Skandinavia')">+</button>
        </div>
      </div>
    </div>

    <div class="prod-card">
      <div class="prod-img-wrap">
        <div class="prod-img bg-blush">💐</div>
        <div class="prod-badge-wrap"><span class="pb pb-new">BARU</span></div>
        <div class="prod-overlay">
          <button class="ov-btn" onclick="addToCart('Buket Wisuda Pink')">🛒</button>
          <button class="ov-btn">♡</button>
          <button class="ov-btn">👁</button>
        </div>
      </div>
      <div class="prod-body">
        <div class="prod-cat">Buket Segar</div>
        <div class="prod-name">Buket Wisuda Pink Pastel Mix Bunga</div>
        <div class="prod-rating"><span class="stars">★★★★★</span> 4.8 (423)</div>
        <div class="prod-foot">
          <div><div class="prod-price">Rp 175.000</div></div>
          <button class="add-btn" onclick="addToCart('Buket Wisuda Pink')">+</button>
        </div>
      </div>
    </div>

    <div class="prod-card">
      <div class="prod-img-wrap">
        <div class="prod-img bg-sand">🕯️</div>
        <div class="prod-overlay">
          <button class="ov-btn" onclick="addToCart('Set Dekorasi Meja')">🛒</button>
          <button class="ov-btn">♡</button>
          <button class="ov-btn">👁</button>
        </div>
      </div>
      <div class="prod-body">
        <div class="prod-cat">Dekorasi Meja</div>
        <div class="prod-name">Set Dekorasi Meja Lilin + Vas Mini</div>
        <div class="prod-rating"><span class="stars">★★★★☆</span> 4.5 (112)</div>
        <div class="prod-foot">
          <div><div class="prod-price">Rp 120.000</div></div>
          <button class="add-btn" onclick="addToCart('Set Dekorasi Meja')">+</button>
        </div>
      </div>
    </div>

    <div class="prod-card">
      <div class="prod-img-wrap">
        <div class="prod-img bg-stone">🎁</div>
        <div class="prod-badge-wrap"><span class="pb pb-best">FAVORIT</span></div>
        <div class="prod-overlay">
          <button class="ov-btn" onclick="addToCart('Hamper Bunga Ultah')">🛒</button>
          <button class="ov-btn">♡</button>
          <button class="ov-btn">👁</button>
        </div>
      </div>
      <div class="prod-body">
        <div class="prod-cat">Hamper & Gift</div>
        <div class="prod-name">Hamper Bunga Ulang Tahun Lengkap</div>
        <div class="prod-rating"><span class="stars">★★★★★</span> 4.9 (567)</div>
        <div class="prod-foot">
          <div><div class="prod-price">Rp 350.000</div><div class="prod-old">Rp 420.000</div></div>
          <button class="add-btn" onclick="addToCart('Hamper Bunga Ultah')">+</button>
        </div>
      </div>
    </div>

  </div>
</section>

<!-- FEATURE STRIP -->
<div class="feature-strip">
  <div class="feat-item">
    <div class="feat-icon">🚚</div>
    <div>
      <div class="feat-title">Pengiriman Aman</div>
      <div class="feat-desc">Dikemas khusus agar bunga tetap segar sampai tujuan</div>
    </div>
  </div>
  <div class="feat-item">
    <div class="feat-icon">🌸</div>
    <div>
      <div class="feat-title">Bunga Segar Pilihan</div>
      <div class="feat-desc">Dipilih langsung dari kebun terbaik setiap hari</div>
    </div>
  </div>
  <div class="feat-item">
    <div class="feat-icon">✂️</div>
    <div>
      <div class="feat-title">Desain Custom</div>
      <div class="feat-desc">Rangkaian sesuai keinginan & tema acaramu</div>
    </div>
  </div>
  <div class="feat-item">
    <div class="feat-icon">💬</div>
    <div>
      <div class="feat-title">Konsultasi Gratis</div>
      <div class="feat-desc">Tim kami siap membantu 7 hari, 08.00–21.00</div>
    </div>
  </div>
</div>

<!-- PROMO / KOLEKSI UNGGULAN -->
<section class="section section-alt">
  <div class="sec-head">
    <div>
      <div class="sec-tag">Koleksi Pilihan</div>
      <h2 class="sec-title">Temukan <em>Inspirasi</em>mu</h2>
    </div>
  </div>
  <div class="promo-grid">
    <div class="promo-card big promo-bg-1">
      <div class="promo-deco">🌿</div>
      <div class="promo-content">
        <div class="promo-tag-label">Koleksi Spesial</div>
        <div class="promo-card-title lg">Vas Keramik<br>Artisan Series</div>
        <a href="#" class="promo-link">Belanja Sekarang →</a>
      </div>
    </div>
    <div style="display:flex; flex-direction:column; gap:24px;">
      <div class="promo-card promo-bg-2">
        <div class="promo-deco promo-deco-sm">💐</div>
        <div class="promo-content">
          <div class="promo-tag-label">Flash Sale · Hari Ini</div>
          <div class="promo-card-title">Buket Segar<br>Diskon 40%</div>
          <a href="#" class="promo-link">Lihat Promo →</a>
        </div>
      </div>
      <div class="promo-card promo-bg-3">
        <div class="promo-deco promo-deco-sm">🎁</div>
        <div class="promo-content">
          <div class="promo-tag-label">Kado Spesial</div>
          <div class="promo-card-title">Hamper &<br>Gift Box</div>
          <a href="#" class="promo-link">Pesan Sekarang →</a>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- TESTIMONIALS -->
<section class="section">
  <div class="sec-head">
    <div>
      <div class="sec-tag">Ulasan</div>
      <h2 class="sec-title">Kata Mereka yang <em>Sudah Merasakan</em></h2>
    </div>
  </div>
  <div class="testi-grid">
    <div class="testi-card">
      <div class="testi-quote">"</div>
      <p class="testi-text">Vasnya cantik banget, persis seperti foto. Keramiknya terasa premium dan bunga yang saya masukkan jadi makin indah. Packing-nya juga sangat aman!</p>
      <div class="testi-row">
        <div class="testi-info">
          <div class="testi-av av-s">🌸</div>
          <div>
            <div class="testi-name">Sari Wulandari</div>
            <div class="testi-city">Yogyakarta</div>
          </div>
        </div>
        <div class="testi-stars">★★★★★</div>
      </div>
    </div>
    <div class="testi-card">
      <div class="testi-quote">"</div>
      <p class="testi-text">Pesan buket wisuda untuk adik, hasilnya luar biasa! Bunganya segar, rangkaiannya rapi dan elegan. Adik saya sampai nangis senang. Terima kasih Lestari!</p>
      <div class="testi-row">
        <div class="testi-info">
          <div class="testi-av av-c">🌷</div>
          <div>
            <div class="testi-name">Cahaya Putri</div>
            <div class="testi-city">Surabaya</div>
          </div>
        </div>
        <div class="testi-stars">★★★★★</div>
      </div>
    </div>
    <div class="testi-card">
      <div class="testi-quote">"</div>
      <p class="testi-text">Sudah 3 kali order di sini. Kualitasnya konsisten bagus. Vas Pampas Grass yang saya beli masih cantik sampai sekarang sudah 6 bulan. Highly recommended!</p>
      <div class="testi-row">
        <div class="testi-info">
          <div class="testi-av av-d">🍃</div>
          <div>
            <div class="testi-name">Dinda Maharani</div>
            <div class="testi-city">Bandung</div>
          </div>
        </div>
        <div class="testi-stars">★★★★★</div>
      </div>
    </div>
  </div>
</section>

<!-- NEWSLETTER -->
<div class="newsletter">
  <div>
    <h2 class="nl-title">Dapatkan <em>Inspirasi</em><br>Setiap Minggu</h2>
    <p class="nl-desc">Daftar ke newsletter kami dan dapatkan tips dekorasi bunga, promo eksklusif, serta info koleksi terbaru langsung di inboxmu.</p>
  </div>
  <div>
    <div class="nl-form">
      <input class="nl-input" type="email" placeholder="email@kamu.com">
      <button class="nl-btn">Daftar Sekarang</button>
    </div>
    <p class="nl-note">🔒 Kami tidak pernah membagikan emailmu ke pihak manapun.</p>
  </div>
</div>

<!-- FOOTER -->
<footer>
  <div class="footer-top">
    <div>
      <div class="f-logo">🌸 Lestari</div>
      <p class="f-desc">Toko vas bunga dan dekorasi botanik pilihan. Menghadirkan keindahan alam ke dalam rumahmu sejak 2018.</p>
      <div class="f-social">
        <a class="soc-btn" href="#">📘</a>
        <a class="soc-btn" href="#">📸</a>
        <a class="soc-btn" href="#">🎵</a>
        <a class="soc-btn" href="#">▶️</a>
      </div>
    </div>
    <div>
      <div class="f-col-title">Produk</div>
      <ul class="f-links">
        <li><a href="#">Vas Keramik</a></li>
        <li><a href="#">Buket Segar</a></li>
        <li><a href="#">Rangkaian Kering</a></li>
        <li><a href="#">Pot & Tanaman</a></li>
        <li><a href="#">Hamper & Gift</a></li>
      </ul>
    </div>
    <div>
      <div class="f-col-title">Informasi</div>
      <ul class="f-links">
        <li><a href="#">Tentang Kami</a></li>
        <li><a href="#">Blog Dekorasi</a></li>
        <li><a href="#">Karir</a></li>
        <li><a href="#">Kebijakan Privasi</a></li>
        <li><a href="#">Syarat & Ketentuan</a></li>
      </ul>
    </div>
    <div>
      <div class="f-col-title">Bantuan</div>
      <ul class="f-links">
        <li><a href="#">Cara Pemesanan</a></li>
        <li><a href="#">Pengiriman & Ongkir</a></li>
        <li><a href="#">Pengembalian Barang</a></li>
        <li><a href="#">Lacak Pesanan</a></li>
        <li><a href="#">Hubungi Kami</a></li>
      </ul>
    </div>
  </div>
  <div class="footer-bottom">
    <span>© 2025 Lestari. Semua hak cipta dilindungi.</span>
    <div class="pay-chips">
      <span class="pay-chip">BCA</span>
      <span class="pay-chip">GoPay</span>
      <span class="pay-chip">OVO</span>
      <span class="pay-chip">QRIS</span>
      <span class="pay-chip">COD</span>
    </div>
  </div>
</footer>

<!-- CART PANEL -->
<div class="cart-overlay" id="cartOverlay" onclick="closeCart()"></div>
<div class="cart-panel" id="cartPanel">
  <div class="cart-head">
    <div class="cart-head-title">Keranjang (2)</div>
    <button class="close-x" onclick="closeCart()">✕</button>
  </div>
  <div class="cart-body">
    <div class="cart-item">
      <div class="ci-img">🏺</div>
      <div>
        <div class="ci-name">Vas Keramik Nordic Matte</div>
        <div class="ci-sub">1 pcs · Sage Green</div>
      </div>
      <div class="ci-price">Rp 185.000</div>
    </div>
    <div class="cart-item">
      <div class="ci-img">💐</div>
      <div>
        <div class="ci-name">Buket Wisuda Pink Pastel</div>
        <div class="ci-sub">1 buket</div>
      </div>
      <div class="ci-price">Rp 175.000</div>
    </div>
  </div>
  <div class="cart-foot">
    <div class="cart-summary">
      <span class="cart-sum-lbl">Total Belanja</span>
      <span class="cart-sum-val" id="cartTotal">Rp 360.000</span>
    </div>
    <button class="checkout-btn">Lanjut ke Pembayaran →</button>
  </div>
</div>

<!-- TOAST -->
<div class="toast" id="toast"></div>

<script>
  let count = 2, total = 360000;
  function openCart() {
    document.getElementById('cartPanel').classList.add('open');
    document.getElementById('cartOverlay').classList.add('open');
  }
  function closeCart() {
    document.getElementById('cartPanel').classList.remove('open');
    document.getElementById('cartOverlay').classList.remove('open');
  }
  function addToCart(name) {
    count++;
    total += 125000;
    document.getElementById('cartBadge').textContent = count;
    document.getElementById('cartTotal').textContent = 'Rp ' + total.toLocaleString('id-ID');
    showToast('🌸 ' + name + ' ditambahkan ke keranjang');
  }
  function showToast(msg) {
    const t = document.getElementById('toast');
    t.textContent = msg;
    t.classList.add('show');
    setTimeout(() => t.classList.remove('show'), 2600);
  }
</script>
</body>
</html>
