/**
 * 活码管理系统 - 后台交互脚本
 */

(function() {
    'use strict';

    var currentPreviewTimeout = null;

    // ===== 工具函数 =====
    function $(selector, ctx) { return (ctx || document).querySelector(selector); }
    function $$(selector, ctx) { return Array.from((ctx || document).querySelectorAll(selector)); }

    function toast(msg, type) {
        type = type || 'success';
        var el = document.createElement('div');
        el.className = 'toast toast-' + type;
        el.innerHTML = msg;
        document.body.appendChild(el);
        requestAnimationFrame(function() {
            el.classList.add('show');
        });
        setTimeout(function() {
            el.classList.remove('show');
            setTimeout(function() { el.remove(); }, 300);
        }, 3000);
    }

    function ajax(data, cb) {
        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'ajax.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
        xhr.onload = function() {
            var resp;
            try {
                resp = JSON.parse(xhr.responseText);
            } catch(e) {
                console.error('JSON解析失败 - 状态:', xhr.status, '原始响应:', xhr.responseText.substring(0, 200));
                toast('响应解析失败 (HTTP ' + xhr.status + ')：' + xhr.responseText.substring(0, 60), 'error');
                return;
            }
            try {
                if (cb) cb(resp);
            } catch(e) {
                console.error('回调执行失败:', e);
                toast('操作执行出错，请查看控制台', 'error');
            }
        };
        xhr.onerror = function() {
            console.error('网络请求失败 - 状态:', xhr.status);
            toast('网络请求失败 (status=' + xhr.status + ')', 'error');
        };
        var params = [];
        for (var key in data) {
            if (data.hasOwnProperty(key)) {
                params.push(encodeURIComponent(key) + '=' + encodeURIComponent(data[key]));
            }
        }
        xhr.send(params.join('&'));
    }

    function ajaxFormData(formData, cb) {
        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'ajax.php', true);
        xhr.onload = function() {
            var resp;
            try {
                resp = JSON.parse(xhr.responseText);
            } catch(e) {
                console.error('JSON解析失败(FormData) - 状态:', xhr.status, '原始响应:', xhr.responseText.substring(0, 200));
                toast('响应解析失败 (HTTP ' + xhr.status + ')：' + xhr.responseText.substring(0, 60), 'error');
                return;
            }
            try {
                if (cb) cb(resp);
            } catch(e) {
                console.error('回调执行失败(FormData):', e);
                toast('操作执行出错，请查看控制台', 'error');
            }
        };
        xhr.onerror = function() {
            console.error('网络请求失败(FormData) - 状态:', xhr.status);
            toast('网络请求失败 (status=' + xhr.status + ')', 'error');
        };
        xhr.send(formData);
    }

    // ===== 导航切换 =====
    $$('.nav-item').forEach(function(item) {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            var page = this.dataset.page;
            $$('.nav-item').forEach(function(n) { n.classList.remove('active'); });
            this.classList.add('active');
            $$('.tab-page').forEach(function(p) { p.classList.remove('active'); });
            var target = document.getElementById('page-' + page);
            if (target) target.classList.add('active');
            if (page === 'dashboard') loadDashboard();
            if (page === 'qr') loadQRPreview();
            if (page === 'editor') loadEditor();
            if (page === 'settings') loadSettings();
        });
    });

    // ===== 检查登录状态 =====
    function checkSession() {
        ajax({ action: 'check_session' }, function(resp) {
            if (!resp.logged_in) {
                showLogin();
            } else {
                showAdmin();
                loadDashboard();
            }
        });
    }

    function showLogin() {
        $('#login-page').style.display = 'flex';
        $('#admin-page').style.display = 'none';
    }

    function showAdmin() {
        $('#login-page').style.display = 'none';
        $('#admin-page').style.display = 'flex';
    }

    // ===== 登录 =====
    $('#login-btn').addEventListener('click', function() {
        var pw = $('#login-password').value;
        var remember = $('#login-remember').checked;
        if (!pw) { toast('请输入密码', 'warning'); return; }
        ajax({ action: 'login', password: pw, remember: remember ? 'true' : 'false' }, function(resp) {
            if (resp.success) {
                toast('登录成功');
                showAdmin();
                loadDashboard();
            } else {
                toast(resp.message, 'error');
            }
        });
    });

    $('#login-password').addEventListener('keydown', function(e) {
        if (e.key === 'Enter') $('#login-btn').click();
    });

    // ===== 退出登录 =====
    $('#logout-btn').addEventListener('click', function() {
        ajax({ action: 'logout' }, function() {
            showLogin();
        });
    });

    // ===== 仪表盘 =====
    function loadDashboard() {
        ajax({ action: 'get_stats' }, function(resp) {
            if (resp.success) {
                $('#stat-total').textContent = resp.data.pv_total;
                $('#stat-today').textContent = resp.data.pv_today;
            }
        });
        ajax({ action: 'get_logs' }, function(resp) {
            if (resp.success) {
                var list = $('#log-list');
                list.innerHTML = '';
                resp.data.forEach(function(line) {
                    var div = document.createElement('div');
                    div.className = 'log-item';
                    div.textContent = line;
                    list.appendChild(div);
                });
            }
        });
    }

    // ===== 二维码管理 =====
    function loadQRPreview() {
        var img = $('#qr-current-img');
        var ts = new Date().getTime();
        img.src = '../uploads/wx-img.png?t=' + ts;
        img.onerror = function() {
            img.style.display = 'none';
            $('#qr-no-image').style.display = 'flex';
        };
        img.onload = function() {
            img.style.display = 'inline';
            $('#qr-no-image').style.display = 'none';
        };
        loadHistory();
    }

    function loadHistory() {
        ajax({ action: 'get_history' }, function(resp) {
            if (resp.success) {
                var list = $('#history-list');
                list.innerHTML = '';
                if (resp.data.length === 0) {
                    list.innerHTML = '<div style="color:#8e8e93;font-size:13px;text-align:center;padding:20px;">暂无历史记录</div>';
                    return;
                }
                resp.data.forEach(function(item, index) {
                    var div = document.createElement('div');
                    div.className = 'history-item';
                    var thumbSrc = '../uploads/' + item.file + '?t=' + new Date().getTime();
                    div.innerHTML = '<img class="history-thumb" src="' + thumbSrc + '" onerror="this.style.display=\'none\'">' +
                        '<span class="history-time">' + item.time + '</span>' +
                        '<div class="history-actions">' +
                        '<button class="btn btn-sm btn-secondary" onclick="window.rollbackHistory(' + index + ')">回滚</button>' +
                        '</div>';
                    list.appendChild(div);
                });
            }
        });
    }

    window.rollbackHistory = function(index) {
        if (!confirm('确认回滚到此历史版本？')) return;
        ajax({ action: 'rollback', index: index }, function(resp) {
            if (resp.success) {
                toast('回滚成功');
                loadQRPreview();
            } else {
                toast(resp.message, 'error');
            }
        });
    };

    // 上传二维码
    $('#qr-upload-input').addEventListener('change', function() {
        var file = this.files[0];
        if (!file) return;
        if (file.size > 3 * 1024 * 1024) {
            toast('文件大小超过3MB限制', 'error');
            return;
        }
        var fd = new FormData();
        fd.append('action', 'upload_qr');
        fd.append('qr_image', file);
        ajaxFormData(fd, function(resp) {
            if (resp.success) {
                toast('二维码上传成功');
                loadQRPreview();
            } else {
                toast(resp.message, 'error');
            }
        });
        this.value = '';
    });

    // ===== 页面编辑器 =====
    function loadEditor() {
        ajax({ action: 'get_settings' }, function(resp) {
            if (resp.success) {
                var s = resp.data;
                $('#editor-title').value = s.content_title || '';
                $('#editor-intro').value = s.content_intro || '';
                $('#editor-footer').value = s.content_footer || '';
                selectTheme(s.theme || 'theme-blue');
                selectTemplate(s.template || 'template-1');
                $('#custom-html-editor').value = s.custom_html || '';
                $('#use-custom-html').checked = s.use_custom_html || false;
                toggleEditorMode();
                updatePreview();
            }
        });
    }

    function selectTheme(theme) {
        $$('.theme-option').forEach(function(el) { el.classList.remove('active'); });
        var target = $('[data-theme="' + theme + '"]');
        if (target) target.classList.add('active');
        $('#selected-theme').value = theme;
    }

    function selectTemplate(tpl) {
        $$('.template-option').forEach(function(el) { el.classList.remove('active'); });
        var target = $('[data-template="' + tpl + '"]');
        if (target) target.classList.add('active');
        $('#selected-template').value = tpl;
    }

    // 主题选择
    $$('.theme-option').forEach(function(el) {
        el.addEventListener('click', function() {
            selectTheme(this.dataset.theme);
            updatePreview();
        });
    });

    // 模板选择
    $$('.template-option').forEach(function(el) {
        el.addEventListener('click', function() {
            selectTemplate(this.dataset.template);
            updatePreview();
        });
    });

    // 输入内容实时更新预览
    ['#editor-title', '#editor-intro', '#editor-footer'].forEach(function(sel) {
        $(sel).addEventListener('input', function() {
            clearTimeout(currentPreviewTimeout);
            currentPreviewTimeout = setTimeout(updatePreview, 300);
        });
    });

    // 更新预览
    function updatePreview() {
        var oldForm = document.getElementById('preview-form');
        if (oldForm) oldForm.remove();

        var useCustom = $('#use-custom-html').checked;
        var customHtml = $('#custom-html-editor').value;

        // 自定义HTML模式：注入 <base> 修正相对路径后写入iframe
        if (useCustom) {
            var iframe = $('#page-preview');
            var doc = iframe.contentDocument || iframe.contentWindow.document;
            doc.open();
            if (customHtml) {
                var baseTag = '<base href="../">';
                if (customHtml.indexOf('<head>') !== -1) {
                    customHtml = customHtml.replace('<head>', '<head>' + baseTag);
                } else if (customHtml.indexOf('<HEAD>') !== -1) {
                    customHtml = customHtml.replace('<HEAD>', '<HEAD>' + baseTag);
                } else {
                    customHtml = baseTag + customHtml;
                }
                doc.write(customHtml);
            } else {
                doc.write('<div style="padding:40px;text-align:center;color:#8e8e93;font-family:-apple-system,BlinkMacSystemFont,sans-serif;">（自定义HTML为空）</div>');
            }
            doc.close();
            return;
        }

        // 正常模式：通过隐藏表单 POST 到 preview.php
        var form = document.createElement('form');
        form.id = 'preview-form';
        form.method = 'POST';
        form.action = '../preview.php';
        form.target = 'page-preview-frame';
        form.style.display = 'none';

        var fields = {
            'title': $('#editor-title').value,
            'intro': $('#editor-intro').value,
            'footer': $('#editor-footer').value,
            'theme': $('#selected-theme').value,
            'template': $('#selected-template').value,
            'use_custom_html': 'false',
            'custom_html': ''
        };

        for (var key in fields) {
            var input = document.createElement('input');
            input.type = 'hidden';
            input.name = key;
            input.value = fields[key];
            form.appendChild(input);
        }

        document.body.appendChild(form);

        // 确保 iframe 有 name 属性
        var iframe = $('#page-preview');
        iframe.name = 'page-preview-frame';
        iframe.removeAttribute('srcdoc');

        form.submit();
    }

    // 切换编辑模式
    function toggleEditorMode() {
        var useCustom = $('#use-custom-html').checked;
        $('#normal-editor').style.display = useCustom ? 'none' : 'block';
        $('#custom-html-editor').style.display = useCustom ? 'block' : 'none';
        $('#custom-editor-section').style.display = useCustom ? 'block' : 'none';
    }

    $('#use-custom-html').addEventListener('change', function() {
        toggleEditorMode();
        // 自动保存自定义开关状态到服务器
        ajax({
            action: 'save_custom_html',
            custom_html: $('#custom-html-editor').value,
            use_custom_html: $('#use-custom-html').checked ? 'true' : 'false'
        }, function(resp) {
            if (resp.success) updatePreview();
            else toast(resp.message, 'error');
        });
    });

    // 发布页面（保存 + 生成静态页）
    $('#publish-btn').addEventListener('click', function() {
        var useCustom = $('#use-custom-html').checked;

        if (useCustom) {
            // 自定义模式：先保存，再发布
            ajax({
                action: 'save_custom_html',
                custom_html: $('#custom-html-editor').value,
                use_custom_html: 'true'
            }, function(resp) {
                if (resp.success) {
                    doPublish();
                } else {
                    toast(resp.message, 'error');
                }
            });
        } else {
            // 正常模式：保存页面内容（同时关闭自定义标记）+ 发布
            var saveData = {
                action: 'save_page',
                title: $('#editor-title').value,
                intro: $('#editor-intro').value,
                footer: $('#editor-footer').value,
                theme: $('#selected-theme').value,
                template: $('#selected-template').value
            };
            ajax(saveData, function(resp) {
                if (resp.success) {
                    // 关闭自定义标记
                    ajax({
                        action: 'save_custom_html',
                        custom_html: $('#custom-html-editor').value,
                        use_custom_html: 'false'
                    }, function() {
                        doPublish();
                    }, function() {
                        toast('保存自定义标记失败', 'error');
                    });
                } else {
                    toast(resp.message, 'error');
                }
            });
        }
    });

    function doPublish() {
        ajax({ action: 'publish' }, function(resp2) {
            if (resp2.success) {
                toast('发布成功，顾客端已更新');
            } else {
                toast(resp2.message, 'error');
            }
        });
    }

    // 保存自定义HTML
    $('#save-custom-btn').addEventListener('click', function() {
        ajax({
            action: 'save_custom_html',
            custom_html: $('#custom-html-editor').value,
            use_custom_html: $('#use-custom-html').checked ? 'true' : 'false'
        }, function(resp) {
            if (resp.success) toast('自定义HTML已保存');
            else toast(resp.message, 'error');
        });
    });

    // ===== 基础设置 =====
    function loadSettings() {
        ajax({ action: 'get_settings' }, function(resp) {
            if (resp.success) {
                var s = resp.data;
                $('#setting-meta-title').value = s.meta_title || '';
                $('#setting-meta-desc').value = s.meta_description || '';
            }
        });
    }

    // 保存基础设置
    $('#save-settings-btn').addEventListener('click', function() {
        ajax({
            action: 'save_settings',
            meta_title: $('#setting-meta-title').value,
            meta_description: $('#setting-meta-desc').value
        }, function(resp) {
            if (resp.success) toast('设置已保存');
            else toast(resp.message, 'error');
        });
    });

    // 上传分享封面
    $('#share-upload-input').addEventListener('change', function() {
        var file = this.files[0];
        if (!file) return;
        var fd = new FormData();
        fd.append('action', 'upload_share');
        fd.append('share_image', file);
        ajaxFormData(fd, function(resp) {
            if (resp.success) {
                toast('分享封面上传成功');
                $('#share-preview-img').src = '../share.jpg?t=' + new Date().getTime();
                $('#share-preview-img').style.display = 'inline';
                $('#share-no-image').style.display = 'none';
            } else {
                toast(resp.message, 'error');
            }
        });
        this.value = '';
    });

    // 修改密码
    $('#change-pw-btn').addEventListener('click', function() {
        var oldPw = $('#old-password').value;
        var newPw = $('#new-password').value;
        var confirmPw = $('#confirm-password').value;

        if (!oldPw || !newPw || !confirmPw) {
            toast('请填写完整', 'warning');
            return;
        }
        if (newPw !== confirmPw) {
            toast('两次新密码不一致', 'warning');
            return;
        }
        if (newPw.length < 6) {
            toast('新密码至少6位', 'warning');
            return;
        }

        ajax({
            action: 'change_password',
            old_password: oldPw,
            new_password: newPw
        }, function(resp) {
            if (resp.success) {
                toast('密码修改成功');
                $('#old-password').value = '';
                $('#new-password').value = '';
                $('#confirm-password').value = '';
            } else {
                toast(resp.message, 'error');
            }
        });
    });

    // ===== 初始化 =====
    checkSession();

})();
