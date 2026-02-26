<?php
session_start();
header("Content-Type: text/html; charset=utf-8");

// 场景1：检测会话是否过期或被强制下线
$kicked_file = 'kicked_sessions.txt';
$current_session_id = session_id();
$is_kicked = file_exists($kicked_file) ? in_array($current_session_id, file($kicked_file, FILE_IGNORE_NEW_LINES)) : false;

// 会话无效（过期/被踢/未登录）：跳转登录页
if (!isset($_SESSION['username']) || (time() - $_SESSION['last_active'] > 60) || $is_kicked) {
    session_destroy();
    if ($is_kicked) {
        $kicked_sessions = file($kicked_file, FILE_IGNORE_NEW_LINES);
        $new_kicked = array();
        foreach ($kicked_sessions as $sess_id) {
            if ($sess_id !== $current_session_id) $new_kicked[] = $sess_id;
        }
        file_put_contents($kicked_file, implode(PHP_EOL, $new_kicked));
        header("Location: login.html?kicked=1");
    } else {
        header("Location: login.html?expired=1");
    }
    exit;
}

// 初始化页面数据（场景0）
$username = $_SESSION['username'];
$visit_count = $_SESSION['visit_count'];
$login_ip = $_SESSION['login_ip'];

// 强制检测异地登录请求（页面加载时直接判断）
$request_file = 'login_requests.json';
$login_requests = file_exists($request_file) ? json_decode(file_get_contents($request_file), true) : array();
$has_other_login = false;
$new_session_id = '';
foreach ($login_requests as $req) {
    if ($req['username'] == $username && $req['new_session_id'] != $current_session_id) {
        $has_other_login = true;
        $new_session_id = $req['new_session_id'];
        break;
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>欢迎页面</title>
    <style>
        body { text-align: center; margin-top: 100px; font-family: Arial, sans-serif; }
        h1 { color: #333; margin-bottom: 30px; }
        .count { font-size: 18px; color: #666; margin: 25px 0; }
        button { padding: 12px 24px; background: #28a745; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; }
        button:hover { background: #218838; }
        .scene2-notice { 
            width: 380px; margin: 30px auto; padding: 20px; 
            border: 2px solid #dc3545; background: #fff; 
            border-radius: 6px; color: #dc3545; font-size: 16px;
            display: <?php echo $has_other_login ? 'block' : 'none'; ?>;
        }
        .scene2-notice button { margin: 0 10px; padding: 8px 18px; font-size: 14px; }
        .deny-btn { background: #dc3545 !important; }
        .ip-info { font-size: 14px; color: #999; margin-top: 10px; }
    </style>
</head>
<body>
    <!-- 场景0：欢迎语+访问次数 -->
    <h1>Welcome <?php echo $username; ?>！</h1>
    <div class="ip-info">当前登录IP：<?php echo $login_ip; ?></div>
    <div class="count">当前访问次数：<span id="visitCount"><?php echo $visit_count; ?></span></div>
    <button onclick="refreshCount()">刷新访问次数</button>

    <!-- 场景2：强制显示异地登录询问弹窗 -->
    <div class="scene2-notice" id="scene2Notice">
        <p>⚠️ 用户 <?php echo $username; ?> 尝试在其他设备登录，是否允许？</p>
        <button onclick="handleScene2Confirm(true, '<?php echo $new_session_id; ?>')">允许（同时登录）</button>
        <button onclick="handleScene2Confirm(false, '<?php echo $new_session_id; ?>')" class="deny-btn">拒绝（清除新终端）</button>
    </div>

    <script>
        // 场景0：刷新访问次数
        function refreshCount() {
            const btn = document.querySelector('button');
            btn.disabled = true;
            btn.textContent = "刷新中...";
            
            fetch('update_count.php')
                .then(response => {
                    if (!response.ok) throw new Error('网络错误');
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        document.getElementById('visitCount').textContent = data.new_count;
                    } else {
                        window.location.href = 'login.html?expired=1';
                    }
                })
                .catch(() => {
                    window.location.href = 'login.html?expired=1';
                })
                .finally(() => {
                    btn.disabled = false;
                    btn.textContent = "刷新访问次数（场景0）";
                });
        }

        // 场景2：处理原终端的确认结果
        function handleScene2Confirm(allow, newSessionId) {
            fetch('handle_confirm.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=${allow ? 'allow_scene2' : 'deny_scene2'}&new_session_id=${newSessionId}&old_session_id=${'<?php echo $current_session_id; ?>'}`
            })
            .then(response => response.json())
            .then(data => {
                document.getElementById('scene2Notice').style.display = 'none';
                alert(allow ? '已允许新终端同时登录！' : '已拒绝新终端登录（新终端已下线）！');
                if (!allow) window.location.reload();
            });
        }

        // 场景1：实时检测会话过期
        setInterval(() => {
            fetch('check_expire.php')
                .then(response => response.json())
                .then(data => {
                    if (!data.valid) window.location.href = 'login.html?expired=1';
                });
        }, 10000);
    </script>
</body>
</html>