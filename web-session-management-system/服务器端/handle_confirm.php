<?php
session_start();
header("Content-Type: application/json; charset=utf-8");

// 必要参数验证（防止非法请求）
if (!isset($_SESSION['username']) || !isset($_POST['action']) || !isset($_POST['new_session_id']) || !isset($_POST['old_session_id'])) {
    echo json_encode(array('success' => false));
    exit;
}

// 初始化文件路径
$session_file = 'user_sessions.json';   // 所有会话记录
$kicked_file = 'kicked_sessions.txt';   // 被踢会话记录
$request_file = 'login_requests.json';   // 场景2请求记录
$user_sessions = file_exists($session_file) ? json_decode(file_get_contents($session_file), true) : array();
$kicked_sessions = file_exists($kicked_file) ? file($kicked_file, FILE_IGNORE_NEW_LINES) : array();
$login_requests = file_exists($request_file) ? json_decode(file_get_contents($request_file), true) : array();
if (!is_array($user_sessions)) $user_sessions = array();
if (!is_array($kicked_sessions)) $kicked_sessions = array();
if (!is_array($login_requests)) $login_requests = array();

$action = $_POST['action'];
$new_session_id = $_POST['new_session_id'];
$old_session_id = $_POST['old_session_id'];
$username = $_SESSION['username'];
$success = false;

// 1. 处理场景3的确认（新终端对原终端的操作）
if ($action === 'allow' || $action === 'deny') {
    // 场景3-允许：保留原会话，新终端正常登录
    if ($action === 'allow') {
        $_SESSION['visit_count'] = 1;
        $_SESSION['last_active'] = time();
        $user_sessions[$new_session_id] = array(
            'username' => $username,
            'ip' => $_SESSION['pending_login']['new_ip'],
            'last_active' => time()
        );
        $success = true;
    }
    // 场景3-拒绝：清除原会话，仅新终端登录
    else if ($action === 'deny') {
        if (isset($user_sessions[$old_session_id])) {
            unset($user_sessions[$old_session_id]); // 清除原会话
            $kicked_sessions[] = $old_session_id;   // 记录原会话为“被踢”
            file_put_contents($kicked_file, implode(PHP_EOL, $kicked_sessions));
        }
        $_SESSION['visit_count'] = 1;
        $_SESSION['last_active'] = time();
        $user_sessions[$new_session_id] = array(
            'username' => $username,
            'ip' => $_SESSION['pending_login']['new_ip'],
            'last_active' => time()
        );
        $success = true;
    }
    // 清除场景3的待确认信息
    unset($_SESSION['pending_login']);
}

// 2. 处理场景2的确认（原终端对新终端的操作）
else if ($action === 'allow_scene2' || $action === 'deny_scene2') {
    // 场景2-允许：保留新会话，两地同时登录
    if ($action === 'allow_scene2') {
        if (isset($user_sessions[$new_session_id])) {
            $user_sessions[$new_session_id]['last_active'] = time();
        }
        $success = true;
    }
    // 场景2-拒绝：清除新会话，新终端下线
    else if ($action === 'deny_scene2') {
        if (isset($user_sessions[$new_session_id])) {
            unset($user_sessions[$new_session_id]); // 清除新会话
            $kicked_sessions[] = $new_session_id;   // 记录新会话为“被踢”
            file_put_contents($kicked_file, implode(PHP_EOL, $kicked_sessions));
        }
        $success = true;
    }
    // 清除已处理的场景2请求
    $new_requests = array();
    foreach ($login_requests as $req) {
        if ($req['new_session_id'] !== $new_session_id) $new_requests[] = $req;
    }
    file_put_contents($request_file, json_encode($new_requests));
}

// 保存修改后的会话记录
file_put_contents($session_file, json_encode($user_sessions));
echo json_encode(array('success' => $success));
exit;
?>