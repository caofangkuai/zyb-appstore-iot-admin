<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json; charset=utf-8');

//爱发电API token
$afdian_api_token = "token";
//隐藏应用
$zyb_appstore_app_install_type = 2; //2为系统应用，不会在应用商店的应用列表中显示
//更新日志
$update_log = "2025.11.26：⑴新增在应用商店已安装列表隐藏功能。
⑵修复了在搜索页面无法下载的情况（显示 需要家长同意）。
2025.11.25：⑴新增在应用信息中展示原始AppInfo数据。";
//作业帮智能token
$iotunion_token = "token";
$iotunion_secret = " secret";
//数据库配置
$servername = "mysql";
$username = "username";
$password = " password";
$dbname = "zyb-appstore-iot-admin";
//webui sn白名单
$enable_webui_sn_whitelist = false; //是否启用sn白名单
$webui_sn_whitelist = array(
    "sn"
);


$conn = mysqli_connect($servername, $username, $password, $dbname);
// 检查连接
if (mysqli_connect_errno()) {
    die("mysql数据库连接失败: " . mysqli_connect_errno());
}

function handleRequest()
{
    $requestMethod = $_SERVER['REQUEST_METHOD'];
    if ($requestMethod === 'GET') {
        $newUrl = 'https://iot-admin.zuoyebang.com' . $_SERVER['REQUEST_URI'];
        $context = stream_context_create([
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
            ]
        ]);
        return file_get_contents($newUrl, false, $context);
    } elseif ($requestMethod === 'POST') {
        $url = 'https://iot-admin.zuoyebang.com' . $_SERVER['REQUEST_URI'];
        $postData = json_decode(file_get_contents('php://input'));
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    } else {
        die('Unsupported request method: ' . $requestMethod);
    }
}

function generateRootMessage($data)
{
    $logId = '';
    for ($i = 0; $i < 10; $i++) {
        $logId .= mt_rand(0, 9);
    }
    $requestIdParts = [];
    for ($i = 0; $i < 3; $i++) {
        $part = '';
        for ($j = 0; $j < 16; $j++) {
            $part .= dechex(mt_rand(0, 15));
        }
        $requestIdParts[] = $part;
    }
    $requestId = implode(':', $requestIdParts) . ':1';
    $response = [
        "errNo" => 0,
        "errMsg" => "succ",
        "cost" => 11.45,
        "logId" => $logId,
        "requestId" => $requestId,
        "data" => $data
    ];
    return json_encode($response, JSON_UNESCAPED_UNICODE);
}

function getValueSafely($value)
{
    return isset($value) ? trim($value) : '';
}

function isApiOk($handleRequest)
{
    $json = json_decode($handleRequest, true);
    return ($json["errNo"] === 0 && $json["errMsg"] === "succ");
}

function getPostParam($name)
{
    return json_decode(file_get_contents('php://input'), true)[$name];
}

function searchApps($sn, $keyword)
{
    global $conn;
    $apps = array();
    if ($keyword == "") {
        return $apps;
    }
    if ($keyword === "all") {
        $keyword = "";
    }
    $sql = "SELECT * FROM apps WHERE sn = '{$sn}' and name LIKE '%{$keyword}%';";
    $result = mysqli_query($conn, $sql);
    if (mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            array_push($apps, $row);
        }
    }
    return $apps;
}

function getAppInfo($sn, $appId)
{
    global $conn;
    $info = [];
    $sql = $result = "SELECT * FROM apps WHERE sn = '{$sn}' and id = {$appId};";
    $result = mysqli_query($conn, $sql);
    if (mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $info = $row;
        }
    }
    return $info;
}

function _DataDesensitization($string, $start = 0, $length = 0, $mark = '*')
{
    if (empty($string)) {
        return false;
    }
    $container = array();
    $mb_strlen = mb_strlen($string);
    while ($mb_strlen) {
        $container[] = mb_substr($string, 0, 1, 'utf8');
        $string = mb_substr($string, 1, $mb_strlen, 'utf8');
        $mb_strlen = mb_strlen($string);
    }
    $strlen = count($container);
    $begin = $start >= 0 ? $start : ($strlen - abs($start));
    $end = $last = $strlen - 1;  //5
    if ($length > 0) {
        $end = $begin + $length - 1;
    } elseif ($length < 0) {
        $end = $end - abs($length); // 5 - 1 = 4
    }
    for ($i = $begin; $i <= $end; $i++) {
        $container[$i] = $mark;
    }
    if ($begin >= $end || $begin >= $last || $end > $last) {
        return false;
    }
    return implode('', $container);
}
