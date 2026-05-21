<?php
/**
 * 活码管理系统 - 静态页面生成器
 * 读取 settings.json 和所选模板，生成 index.html
 */

function publish_page() {
    $settings = read_data('settings.json');
    $template_name = $settings['template'] ?? 'template-1';
    $template_file = TEMPLATES_PATH . $template_name . '.php';
    $output_file = ROOT_PATH . 'index.html';

    // 如果启用了自定义HTML
    if (!empty($settings['use_custom_html']) && !empty($settings['custom_html'])) {
        $html = $settings['custom_html'];
        file_put_contents($output_file, $html, LOCK_EX);
        return true;
    }

    if (!file_exists($template_file)) {
        return '模板文件不存在：' . $template_name;
    }

    // 构建模板变量
    $theme_colors = [
        'theme-red'    => '#c0392b',
        'theme-green'  => '#2d7d46',
        'theme-blue'   => '#2980b9',
        'theme-gray'   => '#555555',
        'theme-yellow' => '#d4a017'
    ];

    $theme = $settings['theme'] ?? 'theme-blue';
    $primary_color = $theme_colors[$theme] ?? '#2980b9';

    // 标题颜色（比主色深一点或亮一点，取决于主题）
    $title_color = $primary_color;
    $link_color = $primary_color;
    $badge_bg = $primary_color;
    $badge_text = '#ffffff';

    // 二维码图片路径
    $qr_url = 'uploads/' . QR_FILENAME;
    if (!file_exists(ROOT_PATH . $qr_url)) {
        $qr_url = '';
    }

    // 分享封面
    $share_url = SHARE_FILENAME;
    if (!file_exists(ROOT_PATH . $share_url)) {
        $share_url = '';
    }

    // 模板变量数组
    $vars = [
        'title'           => htmlspecialchars($settings['content_title'] ?? '欢迎扫码'),
        'intro'           => htmlspecialchars($settings['content_intro'] ?? ''),
        'footer'          => htmlspecialchars($settings['content_footer'] ?? ''),
        'meta_title'      => htmlspecialchars($settings['meta_title'] ?? '活码页面'),
        'meta_description'=> htmlspecialchars($settings['meta_description'] ?? ''),
        'qr_url'          => $qr_url,
        'share_url'       => $share_url,
        'primary_color'   => $primary_color,
        'title_color'     => $title_color,
        'link_color'      => $link_color,
        'badge_bg'        => $badge_bg,
        'badge_text'      => $badge_text,
    ];

    // 加载模板并提取变量
    extract($vars);
    ob_start();
    include $template_file;
    $html = ob_get_clean();

    // 写入 index.html
    file_put_contents($output_file, $html, LOCK_EX);

    return true;
}
