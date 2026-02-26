<?php
session_start();
header("Content-Type: application/json; charset=utf-8");

// 验证会话是否有效（未过期+已登录）
$valid = false;
if (isset($_SESSION['username']) && isset($_SESSION['last_active'])) {
    $valid = (time() - $_SESSION['last_active']) <= 60; // 1分钟有效期
}

echo json_encode(array('valid' => $valid));
exit;
?>