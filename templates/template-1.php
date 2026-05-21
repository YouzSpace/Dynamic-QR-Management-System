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
        .card {
            background: rgba(255,255,255,0.92);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-radius: 16px;
            padding: 32px 24px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.06);
            margin-bottom: 16px;
            text-align: center;
        }
        .title {
            font-size: 22px;
            font-weight: 700;
            color: <?= $title_color ?>;
            margin-bottom: 16px;
            line-height: 1.4;
        }
        .intro {
            font-size: 15px;
            color: #3a3a3c;
            line-height: 1.6;
            margin-bottom: 24px;
            white-space: pre-wrap;
        }
        .qr-wrapper {
            display: flex;
            justify-content: center;
            margin-bottom: 16px;
        }
        .qr-image {
            width: 200px;
            height: 200px;
            border-radius: 12px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.08);
        }
        .footer {
            font-size: 13px;
            color: #8e8e93;
            margin-top: 8px;
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
            .card { background: rgba(44,44,46,0.92); }
            .title { color: <?= $title_color ?>; }
            .intro { color: #e5e5ea; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="title"><?= $title ?></div>
            <?php if ($intro): ?>
            <div class="intro"><?= nl2br($intro) ?></div>
            <?php endif; ?>
            <?php if ($qr_url): ?>
            <div class="qr-wrapper">
                <img class="qr-image" src="<?= $qr_url ?>" alt="二维码">
            </div>
            <?php endif; ?>
            <?php if ($footer): ?>
            <div class="footer"><?= nl2br($footer) ?></div>
            <?php endif; ?>
        </div>
        <div class="powered">活码系统</div>
    </div>
</body>
</html>
