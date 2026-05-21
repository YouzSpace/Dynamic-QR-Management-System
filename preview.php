<?php
/**
 * 活码管理系统 - 实时预览渲染器
 * 接收 POST 参数，渲染对应模板并输出 HTML 到 iframe
 */

// 错误显示（调试用，正式环境可关闭）
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 引入配置（preview.php 在站点根目录，config.php 在 admin/ 下）
$config_file = __DIR__ . '/admin/config.php';
if (!file_exists($config_file)) {
    http_response_code(500);
    exit('配置文件不存在：' . htmlspecialchars($config_file));
}
require_once $config_file;

// 检查登录（iframe 请求会携带 session cookie）
if (!check_login()) {
    http_response_code(403);
    exit('<div style="padding:40px;text-align:center;color:#e74c3c;font-family:-apple-system,BlinkMacSystemFont,sans-serif;font-size:14px;">会话已过期，请刷新页面重新登录</div>');
}

// 只接受 POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('<div style="padding:40px;text-align:center;color:#8e8e93;font-family:-apple-system,BlinkMacSystemFont,sans-serif;">请通过后台编辑器预览</div>');
}

// ===== 自定义HTML模式 =====
$use_custom = isset($_POST['use_custom_html']) && $_POST['use_custom_html'] === 'true';
$custom_html = $_POST['custom_html'] ?? '';

if ($use_custom) {
    echo $custom_html ?: '<div style="padding:40px;text-align:center;color:#8e8e93;font-family:-apple-system,BlinkMacSystemFont,sans-serif;">（自定义HTML为空）</div>';
    exit;
}

// ===== 正常模板模式 =====
$title    = $_POST['title']    ?? '';
$intro     = $_POST['intro']    ?? '';
$footer    = $_POST['footer']   ?? '';
$theme     = $_POST['theme']    ?? 'theme-blue';
$template  = basename($_POST['template'] ?? 'template-1');

// 主题色
$theme_colors = [
    'theme-red'    => '#c0392b',
    'theme-green'  => '#2d7d46',
    'theme-blue'   => '#2980b9',
    'theme-gray'   => '#555555',
    'theme-yellow' => '#d4a017',
];
$primary_color = $theme_colors[$theme] ?? '#2980b9';
$title_color   = $primary_color;
$link_color    = $primary_color;
$badge_bg     = $primary_color;
$badge_text    = '#ffffff';

// 二维码图片路径（相对于站点根目录，与 publish.php 一致）
$qr_url = 'uploads/' . QR_FILENAME;
if (!file_exists(ROOT_PATH . $qr_url)) {
    $qr_url = '';
}

// 分享封面
$share_url = SHARE_FILENAME;
if (!file_exists(ROOT_PATH . $share_url)) {
    $share_url = '';
}

// 模板变量（与 publish.php 一致）
$vars = [
    'title'              => htmlspecialchars($title ?: '欢迎扫码'),
    'intro'              => htmlspecialchars($intro),
    'footer'             => htmlspecialchars($footer),
    'meta_title'         => htmlspecialchars($title ?: '活码页面'),
    'meta_description'    => '',
    'qr_url'             => $qr_url,
    'share_url'          => $share_url,
    'primary_color'      => $primary_color,
    'title_color'        => $title_color,
    'link_color'         => $link_color,
    'badge_bg'           => $badge_bg,
    'badge_text'         => $badge_text,
];

// 确定模板文件
$template_file = TEMPLATES_PATH . $template . '.php';
if (!file_exists($template_file)) {
    // 回退到 template-1
    $template_file = TEMPLATES_PATH . 'template-1.php';
    if (!file_exists($template_file)) {
        http_response_code(500);
        exit('模板文件不存在：' . htmlspecialchars($template));
    }
}

// 渲染模板
extract($vars);
ob_start();
include $template_file;
$html = ob_get_clean();

echo $html;
