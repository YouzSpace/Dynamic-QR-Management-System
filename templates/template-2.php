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
            padding: 40px 16px;
        }
        .container {
            width: 100%;
            max-width: 400px;
        }
        .header-card {
            background: rgba(255,255,255,0.92);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-radius: 16px;
            padding: 28px 24px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.06);
            margin-bottom: 16px;
        }
        .title {
            font-size: 20px;
            font-weight: 700;
            color: <?= $title_color ?>;
            margin-bottom: 10px;
            line-height: 1.4;
        }
        .intro {
            font-size: 14px;
            color: #3a3a3c;
            line-height: 1.6;
            white-space: pre-wrap;
            border-top: 1px solid #e5e5ea;
            padding-top: 12px;
            margin-top: 4px;
        }
        .qr-card {
            background: rgba(255,255,255,0.92);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-radius: 16px;
            padding: 28px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.06);
            text-align: center;
            margin-bottom: 16px;
        }
        .qr-image {
            width: 200px;
            height: 200px;
            border-radius: 12px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.08);
        }
        .footer-card {
            background: rgba(255,255,255,0.92);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-radius: 16px;
            padding: 16px 24px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.06);
            text-align: center;
        }
        .footer-text {
            font-size: 13px;
            color: #8e8e93;
            line-height: 1.5;
            white-space: pre-wrap;
        }
        .powered {
            text-align: center;
            font-size: 12px;
            color: #c7c7cc;
            margin-top: 24px;
        }
        @media (prefers-color-scheme: dark) {
            body { background: #1c1c1e; }
            .header-card, .qr-card, .footer-card { background: rgba(44,44,46,0.92); }
            .intro { color: #e5e5ea; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header-card">
            <div class="title"><?= $title ?></div>
            <?php if ($intro): ?>
            <div class="intro"><?= nl2br($intro) ?></div>
            <?php endif; ?>
        </div>
        <?php if ($qr_url): ?>
        <div class="qr-card">
            <img class="qr-image" src="<?= $qr_url ?>" alt="二维码">
        </div>
        <?php endif; ?>
        <?php if ($footer): ?>
        <div class="footer-card">
            <div class="footer-text"><?= nl2br($footer) ?></div>
        </div>
        <?php endif; ?>
        <div class="powered">活码系统</div>
    </div>
</body>
</html>
