<?php
session_start();
header("Content-Type: text/html; charset=utf-8");

// 场景0要求：3个合法用户（含“张惠媛”）
$valid_users = array(
    '张惠媛' => 'zhy123',
    '张三' => 'zhangsan',
    '李四' => 'lisi'
);

// 会话存储文件（记录所有登录会话，用于场景2/3检测）
$session_file = 'user_sessions.json';
$user_sessions = file_exists($session_file) && filesize($session_file) > 0 ? json_decode(file_get_contents($session_file), true) : array();
if (!is_array($user_sessions)) $user_sessions = array();

// 处理POST登录请求
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // 场景0：验证账号密码
    if (!isset($valid_users[$username]) || $valid_users[$username] !== $password) {
        header("Location: login.html?error=1");
        exit;
    }

    // 获取当前登录IP（兼容本地/服务器环境）
    $login_ip = $_SERVER['REMOTE_ADDR'];
    $current_session_id = session_id();
    $has_old_session = false; // 是否存在异地旧会话（触发场景2/3）
    $old_session_id = '';     // 旧会话ID

    // 检测该用户是否已在其他IP/浏览器登录（场景2/3触发条件）
    foreach ($user_sessions as $sess_id => $sess_info) {
        if ($sess_info['username'] === $username && $sess_info['ip'] !== $login_ip) {
            $has_old_session = true;
            $old_session_id = $sess_id;
            break;
        }
    }

    // 场景3：新终端登录时，先显示“是否允许原会话”的询问页
    if ($has_old_session) {
        $_SESSION['pending_login'] = array(
            'username' => $username,
            'new_ip' => $login_ip,
            'new_session_id' => $current_session_id,
            'old_session_id' => $old_session_id
        );
        header("Location: login_confirm.php"); // 跳转场景3询问页
        exit;
    }

    // 无异地会话：正常登录（场景0/1）
    $_SESSION['username'] = $username;
    $_SESSION['login_ip'] = $login_ip;
    $_SESSION['visit_count'] = 1;       // 场景0：初始访问次数
    $_SESSION['last_active'] = time();  // 场景1：初始活动时间

    // 场景1：设置会话有效期1分钟（自上次访问起）
    ini_set('session.gc_maxlifetime', 60);   // 服务器端会话有效期
    ini_set('session.gc_divisor', 1);        // 强制GC检测
    ini_set('session.gc_probability', 1);
    session_set_cookie_params(60, '/', '', false, true); // 客户端Cookie有效期

    // 记录当前会话到文件（供后续异地检测）
    $user_sessions[$current_session_id] = array(
        'username' => $username,
        'ip' => $login_ip,
        'last_active' => time()
    );
    file_put_contents($session_file, json_encode($user_sessions));

    // 核心修复：强制写入异地登录请求（确保原终端弹窗触发）
    $request_file = 'login_requests.json';
    $login_requests = file_exists($request_file) ? json_decode(file_get_contents($request_file), true) : array();
    if (!is_array($login_requests)) $login_requests = array();
    $login_requests[] = array(
        'username' => $username,
        'new_session_id' => $current_session_id,
        'ip' => $login_ip
    );
    file_put_contents($request_file, json_encode($login_requests));

    // 跳转欢迎页（场景0）
    header("Location: welcome.php");
    exit;
}

// 非POST请求直接跳转登录页
header("Location: login.html");
exit;
?>