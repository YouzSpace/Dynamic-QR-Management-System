<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>活码管理系统</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .login-loader { text-align: center; padding: 20px; }
        .login-loader .loading { width: 32px; height: 32px; }
    </style>
</head>
<body>

<!-- ===== 登录页 ===== -->
<div id="login-page" class="login-page">
    <div class="login-card">
        <div class="login-title">活码管理系统</div>
        <div class="login-subtitle">请登录以管理您的活码页面</div>
        <div class="login-form">
            <input type="password" id="login-password" placeholder="请输入管理员密码" autocomplete="current-password">
            <div class="login-options">
                <label><input type="checkbox" id="login-remember"> 记住我</label>
                <span style="font-size:12px;color:#c7c7cc;">默认密码：admin123</span>
            </div>
            <button id="login-btn" class="btn btn-primary btn-lg" style="width:100%;justify-content:center;">登 录</button>
        </div>
    </div>
</div>

<!-- ===== 管理后台 ===== -->
<div id="admin-page" class="admin-container" style="display:none;">

    <!-- 侧边栏 -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <div class="sidebar-logo">
                <span>活码管理</span>
            </div>
        </div>
        <nav class="sidebar-nav">
            <button class="nav-item active" data-page="dashboard">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                <span>仪表盘</span>
            </button>
            <button class="nav-item" data-page="qr">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                <span>二维码管理</span>
            </button>
            <button class="nav-item" data-page="editor">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                <span>页面编辑器</span>
            </button>
            <button class="nav-item" data-page="settings">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
                <span>基础设置</span>
            </button>
            <button class="nav-item" data-page="about">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                <span>关于</span>
            </button>
        </nav>
        <div class="sidebar-footer">
            <button id="logout-btn" class="nav-item" style="color:#ff6b6b;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                <span>退出登录</span>
            </button>
        </div>
    </aside>

    <!-- 主内容区 -->
    <main class="main-content">

        <!-- ===== Tab: 仪表盘 ===== -->
        <div id="page-dashboard" class="tab-page active">
            <div class="page-header">
                <h1 class="page-title">仪表盘</h1>
            </div>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number" id="stat-total">0</div>
                    <div class="stat-label">总访问量</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number" id="stat-today">0</div>
                    <div class="stat-label">今日访问</div>
                </div>
            </div>
            <div class="card" style="margin-top:20px;">
                <div class="card-title">近期操作日志</div>
                <div id="log-list" class="log-list">
                    <div style="color:#8e8e93;font-size:13px;padding:12px 0;">加载中...</div>
                </div>
            </div>
        </div>

        <!-- ===== Tab: 二维码管理 ===== -->
        <div id="page-qr" class="tab-page">
            <div class="page-header">
                <h1 class="page-title">二维码管理</h1>
            </div>
            <div class="card">
                <div class="card-title">当前二维码</div>
                <div class="qr-preview">
                    <img id="qr-current-img" src="../uploads/wx-img.png" alt="当前二维码" style="display:none;">
                    <div id="qr-no-image" class="no-image">暂无二维码图片</div>
                </div>
            </div>
            <div class="card">
                <div class="card-title">上传/更换二维码</div>
                <div class="file-upload">
                    <div class="upload-area">
                        <div class="upload-text">点击选择图片（JPG/PNG，最大3MB）</div>
                    </div>
                    <input type="file" id="qr-upload-input" accept="image/jpeg,image/png">
                </div>
            </div>
            <div class="card">
                <div class="card-title">更换历史 <span class="badge">最多10条</span></div>
                <div id="history-list" class="history-list">
                    <div style="color:#8e8e93;font-size:13px;text-align:center;padding:20px;">加载中...</div>
                </div>
            </div>
        </div>

        <!-- ===== Tab: 页面编辑器 ===== -->
        <div id="page-editor" class="tab-page">
            <div class="page-header">
                <h1 class="page-title">页面编辑器</h1>
                <div style="display:flex;gap:8px;">
                    <button id="publish-btn" class="btn btn-primary">发布</button>
                </div>
            </div>

            <!-- 敏感词警告 -->
            <div id="sensitive-warning" class="sensitive-warning">
                检测到敏感词：<span id="sensitive-words"></span>（不影响保存，建议修改）
            </div>

            <div class="editor-layout">

                <!-- 编辑区 -->
                <div>
                    <div id="normal-editor">
                        <div class="card">
                            <div class="card-title">页面内容</div>
                            <div class="form-group">
                                <label>标题</label>
                                <input type="text" id="editor-title" placeholder="请输入页面标题">
                            </div>
                            <div class="form-group">
                                <label>简介</label>
                                <textarea id="editor-intro" placeholder="请输入介绍文字" rows="4"></textarea>
                            </div>
                            <div class="form-group">
                                <label>底部提示文字</label>
                                <textarea id="editor-footer" placeholder="如：如有问题，请联系客服" rows="2"></textarea>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-title">主题色</div>
                            <div class="theme-selector">
                                <div class="theme-option theme-red active" data-theme="theme-red">红</div>
                                <div class="theme-option theme-green" data-theme="theme-green">绿</div>
                                <div class="theme-option theme-blue" data-theme="theme-blue">蓝</div>
                                <div class="theme-option theme-gray" data-theme="theme-gray">灰</div>
                                <div class="theme-option theme-yellow" data-theme="theme-yellow">黄</div>
                            </div>
                            <input type="hidden" id="selected-theme" value="theme-blue">
                        </div>

                        <div class="card">
                            <div class="card-title">页面模板</div>
                            <div class="template-selector">
                                <div class="template-option active" data-template="template-1">简洁文字版</div>
                                <div class="template-option" data-template="template-2">卡片式</div>
                                <div class="template-option" data-template="template-3">美食风格</div>
                            </div>
                            <input type="hidden" id="selected-template" value="template-1">
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-title">高级模式</div>
                        <label style="display:flex;align-items:center;gap:8px;font-size:13px;cursor:pointer;margin-bottom:12px;">
                            <input type="checkbox" id="use-custom-html"> 使用自定义 HTML（启用后将完全控制页面内容）
                        </label>
                        <div id="custom-editor-section" style="display:none;">
                            <div style="font-size:12px;color:#8e8e93;margin-bottom:8px;line-height:1.6;">
                                引用二维码图片：<code style="background:#f2f2f7;padding:2px 6px;border-radius:4px;">&lt;img src="uploads/wx-img.png" alt="二维码"&gt;</code>
                            </div>
                            <textarea id="custom-html-editor" class="code-editor" placeholder="粘贴完整的 HTML 代码..."></textarea>
                            <div style="margin-top:8px;">
                                <button id="save-custom-btn" class="btn btn-sm btn-secondary">保存自定义HTML</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 预览区 -->
                <div>
                    <div class="card">
                        <div class="card-title">实时预览</div>
                        <iframe id="page-preview" name="page-preview-frame" class="preview-frame"></iframe>
                    </div>
                </div>

            </div>
        </div>

        <!-- ===== Tab: 基础设置 ===== -->
        <div id="page-settings" class="tab-page">
            <div class="page-header">
                <h1 class="page-title">基础设置</h1>
            </div>
            <div class="card">
                <div class="card-title">页面信息</div>
                <div class="form-group">
                    <label>浏览器标签标题（Meta Title）</label>
                    <input type="text" id="setting-meta-title" placeholder="如：我的活码页面">
                </div>
                <div class="form-group">
                    <label>页面描述（Meta Description）</label>
                    <textarea id="setting-meta-desc" placeholder="用于SEO和微信分享摘要" rows="3"></textarea>
                </div>
                <div style="margin-top:16px;">
                    <button id="save-settings-btn" class="btn btn-primary">保存设置</button>
                </div>
            </div>
            <div class="card">
                <div class="card-title">微信分享封面</div>
                <p style="font-size:13px;color:#8e8e93;margin-bottom:16px;">上传的图片将作为微信内分享时显示的缩略图</p>
                <div style="text-align:center;margin-bottom:16px;">
                    <img id="share-preview-img" src="../share.jpg" alt="分享封面" style="width:120px;height:120px;border-radius:8px;object-fit:cover;display:none;box-shadow:0 2px 8px rgba(0,0,0,0.06);">
                    <div id="share-no-image" class="no-image" style="width:120px;height:120px;margin:0 auto;display:flex;">暂无封面</div>
                </div>
                <div class="file-upload" style="display:block;text-align:center;">
                    <div class="upload-area" style="padding:16px;">
                        <div class="upload-text">点击选择封面图片</div>
                    </div>
                    <input type="file" id="share-upload-input" accept="image/jpeg,image/png">
                </div>
            </div>
            <div class="card">
                <div class="card-title">修改密码</div>
                <div class="form-group">
                    <label>当前密码</label>
                    <input type="password" id="old-password" placeholder="输入当前密码">
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>新密码</label>
                        <input type="password" id="new-password" placeholder="至少6位">
                    </div>
                    <div class="form-group">
                        <label>确认新密码</label>
                        <input type="password" id="confirm-password" placeholder="再次输入新密码">
                    </div>
                </div>
                <div style="margin-top:8px;">
                    <button id="change-pw-btn" class="btn btn-secondary">修改密码</button>
                </div>
            </div>
        </div>

        <!-- ===== Tab: 关于 ===== -->
        <div id="page-about" class="tab-page">
            <div class="page-header">
                <h1 class="page-title">关于</h1>
            </div>
            <div class="card">
                <div class="card-title">活码管理系统</div>
                <div style="display:flex;align-items:center;gap:12px;margin-bottom:16px;">
                    <span style="font-size:13px;color:#8e8e93;">当前版本</span>
                    <span style="font-size:14px;font-weight:600;color:#1c1c1e;">v1.0.0</span>
                </div>
                <p style="font-size:13px;color:#8e8e93;line-height:1.6;">一个轻量的微信活码管理工具，支持二维码更换、页面编辑、主题切换等功能。</p>
            </div>
        </div>

    </main>
</div>

<script src="js/app.js"></script>
</body>
</html>
