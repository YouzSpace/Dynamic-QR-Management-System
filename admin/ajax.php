<?php
/**
 * 活码管理系统 - AJAX 请求处理器
 */

require_once __DIR__ . '/config.php';

header('Content-Type: application/json; charset=utf-8');

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {

    // ====== 登录相关 ======
    case 'login':
        $password = $_POST['password'] ?? '';
        $remember = isset($_POST['remember']) && $_POST['remember'] === 'true';

        if (empty($password)) {
            json_response(['success' => false, 'message' => '请输入密码']);
        }

        if (password_verify($password, $admin_password_hash)) {
            init_session();
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['login_time'] = time();

            // 设置管理员标记 cookie（用于 PV 统计排除，不依赖 session）
            setcookie('admin_token', '1', 0, '/', '', false, true);

            if ($remember) {
                // 记住我 - 设置7天有效的 session cookie (PHP 默认)
                session_set_cookie_params(7 * 24 * 3600);
            }

            write_log('管理员登录成功');
            json_response(['success' => true, 'message' => '登录成功']);
        } else {
            write_log('管理员登录失败 - 密码错误');
            json_response(['success' => false, 'message' => '密码错误']);
        }
        break;

    case 'check_session':
        json_response(['logged_in' => check_login()]);
        break;

    case 'logout':
        init_session();
        $_SESSION = [];
        session_destroy();
        // 清除管理员标记 cookie
        setcookie('admin_token', '', time() - 3600, '/', '', false, true);
        json_response(['success' => true]);
        break;

    case 'change_password':
        if (!check_login()) {
            json_response(['success' => false, 'message' => '未登录']);
        }

        $old_pw = $_POST['old_password'] ?? '';
        $new_pw = $_POST['new_password'] ?? '';

        if (!password_verify($old_pw, $admin_password_hash)) {
            json_response(['success' => false, 'message' => '当前密码错误']);
        }

        if (strlen($new_pw) < 6) {
            json_response(['success' => false, 'message' => '新密码至少6位']);
        }

        $new_hash = password_hash($new_pw, PASSWORD_DEFAULT);

        // 更新 config.php 中的密码哈希
        // 注意：用 str_replace 而非 preg_replace，避免 bcrypt 哈希中的 $ 被误解析为正则反向引用
        $config_file = __DIR__ . '/config.php';
        $content = file_get_contents($config_file);
        $old_line = "\$admin_password_hash = '{$admin_password_hash}';";
        $new_line = "\$admin_password_hash = '{$new_hash}';";
        $content = str_replace($old_line, $new_line, $content);
        file_put_contents($config_file, $content);

        write_log('管理员修改密码');
        json_response(['success' => true, 'message' => '密码修改成功']);
        break;

    // ====== 二维码管理 ======
    case 'upload_qr':
        if (!check_login()) {
            json_response(['success' => false, 'message' => '未登录']);
        }

        $file = $_FILES['qr_image'] ?? null;
        if (!$file) {
            json_response(['success' => false, 'message' => '请选择图片']);
        }

        $error = validate_image($file);
        if ($error) {
            json_response(['success' => false, 'message' => $error]);
        }

        // 保存旧图片到历史记录（如果当前二维码存在）
        $current_qr = UPLOADS_PATH . QR_FILENAME;
        $history = read_data('history.json');

        if (file_exists($current_qr)) {
            $backup_name = 'backup_' . date('Ymd_His') . '.png';
            $backup_path = UPLOADS_PATH . $backup_name;

            if (copy($current_qr, $backup_path)) {
                $history[] = [
                    'time' => date('Y-m-d H:i:s'),
                    'file' => $backup_name,
                    'size' => filesize($current_qr)
                ];

                // 只保留最近10条
                if (count($history) > 10) {
                    // 删除多余的备份文件
                    $to_remove = array_slice($history, 0, count($history) - 10);
                    foreach ($to_remove as $old) {
                        $old_path = UPLOADS_PATH . $old['file'];
                        if (file_exists($old_path)) {
                            @unlink($old_path);
                        }
                    }
                    $history = array_slice($history, -10);
                }

                write_data('history.json', $history);
            }
        }

        // 覆盖上传
        if (move_uploaded_file($file['tmp_name'], $current_qr)) {
            write_log('上传/更换二维码');
            json_response(['success' => true, 'message' => '二维码上传成功']);
        } else {
            json_response(['success' => false, 'message' => '文件保存失败']);
        }
        break;

    case 'get_history':
        if (!check_login()) {
            json_response(['success' => false, 'message' => '未登录']);
        }

        $history = read_data('history.json');
        // 倒序显示（最新的在前）
        $history = array_reverse($history);
        json_response(['success' => true, 'data' => $history]);
        break;

    case 'rollback':
        if (!check_login()) {
            json_response(['success' => false, 'message' => '未登录']);
        }

        $index = intval($_POST['index'] ?? -1);
        $history = read_data('history.json');
        $history_reversed = array_reverse($history);

        if ($index < 0 || $index >= count($history_reversed)) {
            json_response(['success' => false, 'message' => '无效的历史记录索引']);
        }

        $selected = $history_reversed[$index];
        $backup_path = UPLOADS_PATH . $selected['file'];

        if (!file_exists($backup_path)) {
            json_response(['success' => false, 'message' => '历史备份图片文件不存在']);
        }

        // 保存当前二维码为历史备份，再回滚
        $current_qr = UPLOADS_PATH . QR_FILENAME;
        if (file_exists($current_qr)) {
            $new_backup = 'backup_' . date('Ymd_His') . '.png';
            $new_backup_path = UPLOADS_PATH . $new_backup;
            if (copy($current_qr, $new_backup_path)) {
                $history[] = [
                    'time' => date('Y-m-d H:i:s'),
                    'file' => $new_backup,
                    'size' => filesize($current_qr)
                ];
                if (count($history) > 10) {
                    $to_remove = array_slice($history, 0, count($history) - 10);
                    foreach ($to_remove as $old) {
                        $old_path = UPLOADS_PATH . $old['file'];
                        if (file_exists($old_path)) {
                            @unlink($old_path);
                        }
                    }
                    $history = array_slice($history, -10);
                }
                write_data('history.json', $history);
            }
        }

        // 回滚：复制历史备份为当前二维码
        if (copy($backup_path, $current_qr)) {
            write_log('回滚二维码到历史版本');
            json_response(['success' => true, 'message' => '回滚成功']);
        } else {
            json_response(['success' => false, 'message' => '回滚失败']);
        }
        break;

    // ====== 页面编辑 ======
    case 'save_page':
        if (!check_login()) {
            json_response(['success' => false, 'message' => '未登录']);
        }

        $settings = read_data('settings.json');
        $settings['content_title'] = $_POST['title'] ?? $settings['content_title'];
        $settings['content_intro'] = $_POST['intro'] ?? $settings['content_intro'];
        $settings['content_footer'] = $_POST['footer'] ?? $settings['content_footer'];
        $settings['theme'] = $_POST['theme'] ?? $settings['theme'];
        $settings['template'] = $_POST['template'] ?? $settings['template'];

        write_data('settings.json', $settings);

        // 敏感词检测
        $all_text = $settings['content_title'] . ' ' . $settings['content_intro'] . ' ' . $settings['content_footer'];
        $found_words = check_sensitive_words($all_text, $sensitive_words);

        write_log('保存页面内容');
        json_response([
            'success' => true,
            'message' => '保存成功',
            'sensitive_words' => $found_words
        ]);
        break;

    case 'save_custom_html':
        if (!check_login()) {
            json_response(['success' => false, 'message' => '未登录']);
        }

        $settings = read_data('settings.json');
        $settings['custom_html'] = $_POST['custom_html'] ?? '';
        $settings['use_custom_html'] = isset($_POST['use_custom_html']) && $_POST['use_custom_html'] === 'true';

        write_data('settings.json', $settings);
        write_log('保存自定义HTML');
        json_response(['success' => true, 'message' => '自定义HTML保存成功']);
        break;

    case 'publish':
        if (!check_login()) {
            json_response(['success' => false, 'message' => '未登录']);
        }

        require_once __DIR__ . '/publish.php';

        $result = publish_page();
        if ($result === true) {
            write_log('发布页面');
            json_response(['success' => true, 'message' => '发布成功，顾客端已更新']);
        } else {
            json_response(['success' => false, 'message' => $result]);
        }
        break;

    // ====== 基础设置 ======
    case 'save_settings':
        if (!check_login()) {
            json_response(['success' => false, 'message' => '未登录']);
        }

        $settings = read_data('settings.json');
        $settings['meta_title'] = $_POST['meta_title'] ?? $settings['meta_title'];
        $settings['meta_description'] = $_POST['meta_description'] ?? $settings['meta_description'];

        write_data('settings.json', $settings);
        write_log('更新基础设置');
        json_response(['success' => true, 'message' => '设置保存成功']);
        break;

    case 'upload_share':
        if (!check_login()) {
            json_response(['success' => false, 'message' => '未登录']);
        }

        $file = $_FILES['share_image'] ?? null;
        if (!$file) {
            json_response(['success' => false, 'message' => '请选择图片']);
        }

        $error = validate_image($file);
        if ($error) {
            json_response(['success' => false, 'message' => $error]);
        }

        $dest = ROOT_PATH . SHARE_FILENAME;
        if (move_uploaded_file($file['tmp_name'], $dest)) {
            write_log('上传微信分享封面');
            json_response(['success' => true, 'message' => '封面上传成功']);
        } else {
            json_response(['success' => false, 'message' => '文件保存失败']);
        }
        break;

    // ====== 数据获取 ======
    case 'get_settings':
        if (!check_login()) {
            json_response(['success' => false, 'message' => '未登录']);
        }
        $settings = read_data('settings.json');
        json_response(['success' => true, 'data' => $settings]);
        break;

    case 'get_stats':
        if (!check_login()) {
            json_response(['success' => false, 'message' => '未登录']);
        }
        $settings = read_data('settings.json');
        json_response([
            'success' => true,
            'data' => [
                'pv_total' => $settings['pv_total'] ?? 0,
                'pv_today' => $settings['pv_today'] ?? 0
            ]
        ]);
        break;

    case 'get_logs':
        if (!check_login()) {
            json_response(['success' => false, 'message' => '未登录']);
        }
        $log_file = DATA_PATH . 'logs.txt';
        if (!file_exists($log_file)) {
            json_response(['success' => true, 'data' => []]);
        }
        $lines = file($log_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $lines = array_reverse($lines);
        $lines = array_slice($lines, 0, 30);
        json_response(['success' => true, 'data' => $lines]);
        break;

    default:
        json_response(['success' => false, 'message' => '未知操作：' . $action]);
}
