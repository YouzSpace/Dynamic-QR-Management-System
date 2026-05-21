<?php
/**
 * 活码管理系统 - 顾客访问入口
 * 作用：加载生成的静态页面，并记录PV访问量
 */

require_once __DIR__ . '/admin/config.php';

// 拒绝静态资源请求（favicon.ico 等会被 PHP 内置服务器路由过来）
$uri = $_SERVER['REQUEST_URI'] ?? '/';
if (preg_match('/\.(ico|png|jpg|jpeg|gif|svg|css|js|woff2?|ttf|map|txt|json|xml)$/i', $uri)) {
    http_response_code(404);
    exit;
}

// PV 计数（排除管理员自己访问，通过 cookie 标记判断，不依赖 session）
$is_admin = isset($_COOKIE['admin_token']) && $_COOKIE['admin_token'] !== '';

if (!$is_admin) {
    $settings = read_data('settings.json');
    $today = date('Y-m-d');

    if (!isset($settings['pv_total'])) $settings['pv_total'] = 0;
    if (!isset($settings['pv_today'])) $settings['pv_today'] = 0;
    if (!isset($settings['pv_date'])) $settings['pv_date'] = $today;

    // 如果是新的一天，重置今日PV
    if ($settings['pv_date'] !== $today) {
        $settings['pv_today'] = 0;
        $settings['pv_date'] = $today;
    }

    $settings['pv_total']++;
    $settings['pv_today']++;

    // 写入PV统计
    write_data('settings.json', $settings);
}

// 加载静态页面
$static_file = __DIR__ . '/index.html';
if (file_exists($static_file)) {
    readfile($static_file);
} else {
    // 初始页面
    ?>
    <!DOCTYPE html>
    <html lang="zh-CN">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
        <title>活码页面</title>
        <style>
            * { margin: 0; padding: 0; box-sizing: border-box; }
            body {
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", "PingFang SC", "Microsoft YaHei", sans-serif;
                background: #f2f2f7;
                display: flex;
                justify-content: center;
                align-items: center;
                min-height: 100vh;
                padding: 20px;
            }
            .card {
                background: #ffffff;
                border-radius: 16px;
                padding: 40px 24px;
                box-shadow: 0 2px 12px rgba(0,0,0,0.06);
                text-align: center;
                max-width: 360px;
                width: 100%;
            }
            .card h2 { font-size: 20px; color: #2980b9; margin-bottom: 12px; }
            .card p { font-size: 14px; color: #8e8e93; line-height: 1.6; }
        </style>
    </head>
    <body>
        <div class="card">
            <h2>页面建设中</h2>
            <p>管理员尚未发布页面内容，请稍后再来访问。</p>
        </div>
    </body>
    </html>
    <?php
}
