<?php
// 包含配置文件
require_once '../../config.php';
require_once '../../iotUnionApi.php';

// 使用传入的参数或配置中的默认值
$token = $_POST['token'] ?? $iotunion_token;
$secret = $_POST['secret'] ?? $iotunion_secret;
$child_id = $_POST['child_id'] ?? "";

// 查询
$result = json_decode(usingTimeApi($token, $secret, $child_id), true);
echo json_encode($result, JSON_UNESCAPED_UNICODE);
?>