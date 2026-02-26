<?php
session_start();
// 未登录或无待确认信息，直接跳转登录页
if (!isset($_SESSION['pending_login'])) {
    header("Location: login.html");
    exit;
}
$pending = $_SESSION['pending_login']; // 待确认的登录信息
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>登录确认</title>
    <style>
        body { width: 420px; margin: 120px auto; font-family: Arial, sans-serif; }
        .confirm-box { border: 1px solid #ddd; padding: 25px; border-radius: 6px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
        h2 { text-align: center; color: #333; margin-top: 0; }
        p { font-size: 16px; color: #666; line-height: 1.6; text-align: center; }
        .btn-group { text-align: center; margin-top: 25px; }
        button { padding: 10px 22px; margin: 0 10px; border: none; border-radius: 4px; cursor: pointer; font-size: 14px; }
        .allow { background: #28a745; color: white; }
        .deny { background: #dc3545; color: white; }
    </style>
</head>
<body>
    <div class="confirm-box">
        <h2>异地登录提示</h2>
        <p>用户 <strong><?php echo $pending['username']; ?></strong> 已在其他设备（IP：<?php echo $pending['new_ip']; ?>）登录</p>
        <p>是否允许上述旧会话继续？</p>
        <div class="btn-group">
            <!-- 允许：保留原会话，新终端也登录 -->
            <form action="handle_confirm.php" method="post" style="display: inline-block;">
                <input type="hidden" name="action" value="allow">
                <input type="hidden" name="new_session_id" value="<?php echo $pending['new_session_id']; ?>">
                <input type="hidden" name="old_session_id" value="<?php echo $pending['old_session_id']; ?>">
                <button type="submit" class="allow">允许（两地同时登录）</button>
            </form>
            <!-- 拒绝：清除原会话，仅新终端登录 -->
            <form action="handle_confirm.php" method="post" style="display: inline-block;">
                <input type="hidden" name="action" value="deny">
                <input type="hidden" name="new_session_id" value="<?php echo $pending['new_session_id']; ?>">
                <input type="hidden" name="old_session_id" value="<?php echo $pending['old_session_id']; ?>">
                <button type="submit" class="deny">拒绝（强制原终端下线）</button>
            </form>
        </div>
    </div>
</body>
</html>