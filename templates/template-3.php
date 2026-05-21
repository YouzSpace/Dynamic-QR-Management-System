<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="format-detection" content="telephone=no">
    <title><?= $meta_title ?></title>
    <meta name="description" content="<?= $meta_description ?>">
    <?php if ($share_url): ?>
    <meta property="og:image" content="<?= $share_url ?>">
    <meta property="og:title" content="<?= $meta_title ?>">
    <meta property="og:description" content="<?= $meta_description ?>">
    <?php endif; ?>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", "PingFang SC", "Hiragino Sans GB", "Microsoft YaHei", sans-serif;
            background: #f2f2f7;
            color: #1c1c1e;
            -webkit-font-smoothing: antialiased;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 0;
        }
        .hero-section {
            width: 100%;
            background: linear-gradient(135deg, <?= $primary_color ?>, <?= $primary_color ?>dd);
            padding: 48px 24px 40px;
            text-align: center;
            border-radius: 0 0 32px 32px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.10);
        }
        .hero-title {
            font-size: 28px;
            font-weight: 800;
            color: #ffffff;
            line-height: 1.3;
            margin-bottom: 8px;
            text-shadow: 0 2px 4px rgba(0,0,0,0.10);
        }
        .hero-subtitle {
            font-size: 15px;
            color: rgba(255,255,255,0.85);
            line-height: 1.5;
            white-space: pre-wrap;
        }
        .container {
            width: 100%;
            max-width: 400px;
            padding: 24px 16px 40px;
            margin: 0 auto;
        }
        .card {
            background: rgba(255,255,255,0.92);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-radius: 16px;
            padding: 28px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.06);
            text-align: center;
            margin-top: -12px;
        }
        .qr-image {
            width: 200px;
            height: 200px;
            border-radius: 12px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.10);
        }
        .qr-label {
            font-size: 13px;
            color: <?= $primary_color ?>;
            font-weight: 500;
            margin-top: 12px;
            margin-bottom: 8px;
        }
        .footer-text {
            font-size: 13px;
            color: #8e8e93;
            line-height: 1.5;
            text-align: center;
            margin-top: 20px;
            white-space: pre-wrap;
        }
        .powered {
            text-align: center;
            font-size: 12px;
            color: #c7c7cc;
            margin-top: 16px;
        }
        @media (prefers-color-scheme: dark) {
            body { background: #1c1c1e; }
            .card { background: rgba(44,44,46,0.92); }
        }
    </style>
</head>
<body>
    <div class="hero-section">
        <div class="hero-title"><?= $title ?></div>
        <?php if ($intro): ?>
        <div class="hero-subtitle"><?= nl2br($intro) ?></div>
        <?php endif; ?>
    </div>
    <div class="container">
        <?php if ($qr_url): ?>
        <div class="card">
            <div class="qr-label">扫码获取</div>
            <img class="qr-image" src="<?= $qr_url ?>" alt="二维码">
        </div>
        <?php endif; ?>
        <?php if ($footer): ?>
        <div class="footer-text"><?= nl2br($footer) ?></div>
        <?php endif; ?>
        <div class="powered">活码系统</div>
    </div>
</body>
</html>
