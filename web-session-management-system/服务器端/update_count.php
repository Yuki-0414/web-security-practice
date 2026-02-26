<?php
session_start();
header("Content-Type: application/json; charset=utf-8");

// 未登录或会话过期：返回失败
if (!isset($_SESSION['username']) || (time() - $_SESSION['last_active'] > 60)) {
    echo json_encode(array('success' => false));
    exit;
}

// 场景0：递增访问次数；场景1：重置会话有效期
$_SESSION['visit_count'] = intval($_SESSION['visit_count']) + 1;
$_SESSION['last_active'] = time();

echo json_encode(array(
    'success' => true,
    'new_count' => $_SESSION['visit_count']
));
exit;
?>