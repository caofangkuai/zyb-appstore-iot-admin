<?php
function parentSearchApp($token, $secret, $serialNumber, $searchContent)
{
    $postParams = [
        "cid" => "cfknb",
        "sn" => $serialNumber,
        "nameOfApp" => $searchContent,
        "size" => 1000
    ];
    return serverCommonApi($token, $secret, $serialNumber, $postParams, "/appStore/appInfoNoAuth");
}

function parentInstallApp($token, $secret, $serialNumber, $appId)
{
    $postParams = [
        "sn" => $serialNumber,
        "appId" => $appId,
    ];
    return serverCommonApi($token, $secret, $serialNumber, $postParams, "/appStore/installToPad");
}

function serverCommonApi($token, $secret, $serialNumber, $postParams, $aimUrl)
{
	$originalParams = [
        "param" => json_encode($postParams),
        "aimUrl" => $aimUrl
    ];
    $fullParams = buildUrlParams(
        $originalParams,
        "6.7.2",
        $token,
        $secret,
        "7.1.2",
        "cfknb",
        false,
        "cfknb",
        "cfknb",
        "cfknb"
    );
    $paramsString = toUrlParamString($fullParams);
    $url = "https://iot-api.zybang.com/iot-server/api/app/pad/server/common?" . $paramsString;
    $options = [
        'http' => [
            'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
            'method' => 'POST',
            'content' => ''
        ]
    ];
    $context = stream_context_create($options);
    $response = file_get_contents($url, false, $context);
    if ($response === false) {
        return '{"errNo":-1,"errMsg":"请求失败"}';
    }
    $result = json_decode($response, true);
    if (isset($result['code']) && $result['code'] == 200) {
        return $result['data']['result'] ?? "{}";
    }
    return '{"errNo":-1,"errMsg":"父api错误"}';
}

// 辅助函数
function buildUrlParams($originalParams, $version, $token, $secret, $osVersion, $adid, $onlineServerTips, $rom, $phoneModel, $brand)
{
    $params = $originalParams ?? [];
    $params['rom'] = $rom ?? "";
    $params['os_version'] = $osVersion ?? "";
    $params['adid'] = $adid ?? "";
    $params['phone_model'] = $phoneModel ?? "";
    $params['brand'] = $brand ?? "";
    $params['app_version'] = $version ?? "";
    if ($onlineServerTips) {
        $params['__tips__'] = "1";
    }
    $params['stamp'] = strval(round(microtime(true) * 1000));
    if (!empty($token) && !empty($secret)) {
        $params['token'] = $token;
        $params['sig'] = generateSignature($params, $secret);
    }
    return $params;
}
function generateSignature($params, $secret)
{
    if (empty($params)) {
        return "";
    }
    ksort($params);
    $paramString = "";
    foreach ($params as $key => $value) {
        $paramString .= $key . '=' . $value . '&';
    }
    if (!empty($secret)) {
        $paramString .= "secret=" . $secret;
    }
    return md5($paramString);
}
function toUrlParamString($params)
{
    if (empty($params)) {
        return "";
    }
    $paramArray = [];
    foreach ($params as $key => $value) {
        $paramArray[] = urlencode($key) . '=' . urlencode($value);
    }
    return implode('&', $paramArray);
}
