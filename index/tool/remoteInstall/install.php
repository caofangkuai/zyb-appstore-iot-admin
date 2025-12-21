<?php
// 包含配置文件
require_once '../../config.php';
require_once '../../iotUnionApi.php';

// 获取POST数据
$data = json_decode(file_get_contents('php://input'), true);

// 验证必需参数
if (empty($data['sn']) || empty($data['appName'])) {
    echo json_encode(['errNo' => 1, 'errMsg' => '缺少必需参数']);
    exit;
}

// 使用传入的参数或配置中的默认值
$token = !empty($data['token']) ? $data['token'] : $iotunion_token;
$secret = !empty($data['secret']) ? $data['secret'] : $iotunion_secret;
$serialNumber = $data['sn'];
$searchContent = $data['appName'];

// 搜索应用
$searchResult = json_decode(parentSearchApp($token, $secret, $serialNumber, $searchContent), true);

if ($searchResult['errNo'] !== 0) {
    echo json_encode($searchResult, JSON_UNESCAPED_UNICODE);
    exit;
}

// 检查是否有搜索结果
if (empty($searchResult['data']['list']) || count($searchResult['data']['list']) === 0) {
    echo json_encode(['errNo' => 2, 'errMsg' => '未找到相关应用'], JSON_UNESCAPED_UNICODE);
    exit;
}

// 获取第一个应用的ID
$appId = $searchResult['data']['list'][0]['id'];

// 执行安装
$installResult = json_decode(parentInstallApp($token, $secret, $serialNumber, $appId), true);

echo json_encode([
	"errNo" => 0,
	"searchResult" => $searchResult['data']['list'][0],
	"installResult" => $installResult
], JSON_UNESCAPED_UNICODE);
?>