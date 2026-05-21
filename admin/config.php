<?php
/**
 * 活码管理系统 - 核心配置
 * 密码存储：使用 password_hash() 加密存储
 */

// 默认管理员密码：admin123
// 如需修改密码，登录后台后在"基础设置"中修改
$admin_password_hash = '$2y$12$/eLC25/HXCSS.UbEapU8defoSbtrXlc9YRqFzNULVb4NT/OnnNP1i';

// 系统路径
define('ROOT_PATH', dirname(__DIR__) . DIRECTORY_SEPARATOR);
define('DATA_PATH', ROOT_PATH . 'data' . DIRECTORY_SEPARATOR);
define('UPLOADS_PATH', ROOT_PATH . 'uploads' . DIRECTORY_SEPARATOR);
define('TEMPLATES_PATH', ROOT_PATH . 'templates' . DIRECTORY_SEPARATOR);
define('ADMIN_PATH', ROOT_PATH . 'admin' . DIRECTORY_SEPARATOR);

// 二维码固定文件名
define('QR_FILENAME', 'wx-img.png');
define('SHARE_FILENAME', 'share.jpg');

// 文件大小限制 (3MB)
define('MAX_FILE_SIZE', 3 * 1024 * 1024);

// 允许的图片格式
$allowed_image_types = ['image/jpeg', 'image/png', 'image/jpg'];

// 敏感词列表
$sensitive_words = ['长按识别', '转发', '分享朋友圈', '点击关注', '关注公众号', '免费领取'];

/**
 * 初始化 Session
 */
function init_session() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

/**
 * 检查登录状态
 */
function check_login() {
    init_session();
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

/**
 * 记录操作日志
 */
function write_log($event) {
    $log_file = DATA_PATH . 'logs.txt';
    $time = date('Y-m-d H:i:s');
    $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    $line = "[{$time}] [{$ip}] {$event}" . PHP_EOL;
    file_put_contents($log_file, $line, FILE_APPEND | LOCK_EX);
}

/**
 * 读取数据 JSON
 */
function read_data($filename) {
    $path = DATA_PATH . $filename;
    if (!file_exists($path)) return [];
    $content = file_get_contents($path);
    return json_decode($content, true) ?: [];
}

/**
 * 写入数据 JSON
 */
function write_data($filename, $data) {
    $path = DATA_PATH . $filename;
    return file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);
}

/**
 * 检测敏感词
 */
function check_sensitive_words($text, $words) {
    $found = [];
    foreach ($words as $word) {
        if (mb_strpos($text, $word) !== false) {
            $found[] = $word;
        }
    }
    return $found;
}

/**
 * 发送 JSON 响应
 */
function json_response($data) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * 验证上传图片
 */
function validate_image($file) {
    global $allowed_image_types;

    if ($file['error'] !== UPLOAD_ERR_OK) {
        return '上传失败，错误代码：' . $file['error'];
    }

    if ($file['size'] > MAX_FILE_SIZE) {
        return '文件大小超过限制（最大 3MB）';
    }

    // 使用 getimagesize() 检测图片类型（不需要 fileinfo 扩展）
    $info = @getimagesize($file['tmp_name']);
    if ($info === false) {
        return '无法识别的图片格式';
    }
    $mime = $info['mime'];

    if (!in_array($mime, $allowed_image_types)) {
        return '仅支持 JPG/PNG 格式的图片';
    }

    return null;
}
