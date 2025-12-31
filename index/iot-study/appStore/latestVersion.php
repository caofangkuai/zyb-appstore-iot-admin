<?php
require_once "../../config.php";
$handleRequest = handleRequest();
if (isApiOk($handleRequest)) {
    $data = json_decode($handleRequest, true);
    $data["sourceJSON"] = $data;
    $blackList = ['com.android.launcher3', 'com.zuoyebang.iot.pad.appstore', 'com.zuoyebang.iot.pad.zpreport'];
    foreach ($data["data"] as $key => $item) {
        if ($item['apkName'] && in_array($item['apkName'], $blackList)) {
            unset($data["data"][$key]);
        }
    }
    $data['data'] = array_values($data['data']);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
} else {
    return $handleRequest;
}
?>