<?php
require_once "../../config.php";

$disallowApps = json_decode(file_get_contents("../../disallowApps.json"), true);

function get_sponsor_list($user_id) {
    global $afdian_api_token;
    $token = $afdian_api_token;
    $params = array(
        "page" => 1
     );
    $params_str = json_encode($params);
    $ts = time();
    $sign = md5($token . "params" . $params_str . "ts" . $ts . "user_id" . $user_id);
    $data = array(
        "user_id" => $user_id,
        "params" => $params_str,
        "ts" => $ts,
        "sign" => $sign
    );
    $url = "https://afdian.com/api/open/query-sponsor";
    $options = array(
        "http" => array(
            "header" => "Content-Type: application/json",
            "method" => "POST",
            "content" => json_encode($data)
        )
    );
    $context = stream_context_create($options);
    $response = file_get_contents($url, false, $context);
    if ($response !== false) {
        $result = json_decode($response, true);
        if ($result["ec"] == 200) {
            $sponsors = $result["data"]["list"];
            $sponsor_list = "";
            foreach ($sponsors as $sponsor) {
                $user = $sponsor["user"];
                $sponsor_user_id = $user["user_id"];
                $username = $user["name"];
                $donate = $sponsor["all_sum_amount"];
                $sponsor_list .= $username . "（$donate 元）  ";
            }
            return $sponsor_list;
        }else{
            return $result["em"];
        }
    }else{
        return "Failed to retrieve sponsor list";
    }
}

$sponsor_list = get_sponsor_list("3d58db9c65b911efb36352540025c377");
$info = getAppInfo(getValueSafely($_GET["sn"]), getPostParam("appId"));
if(!empty($info)){
    // SN脱敏处理
    $info["sn"] = _DataDesensitization($info["sn"], 3, 13);
    echo generateRootMessage([
        "id" =>intval($info["id"]),
        "name" =>$info["name"],
        "enName" =>"",
        "summary" =>"用户上传",
        "remark" =>"🚀Crack By caofangkuai\n🛰️公告请见下方“应用权限”" . (in_array($info["pkgName"], $disallowApps) ? "\n⚠️重要：此应用在 PadMs 的包名检测数据库中，会每20分钟执行一次删除此应用操作。" : "") . "\n✈️AppInfo源数据：" . json_encode($info, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT),
        "changeLog" =>"无",
        "developer" =>"无处不在的草方块",
        "icon" =>$info["icon"],
        "apkUrl" =>$info["apkUrl"],
        "apkSize" =>250686161,
        "apkSizeStr" =>"11.45M",
        "apkName" =>$info["pkgName"],
        "apkVersion" =>"11.45",
        "apkMd5" =>$info["apkMd5"],
        "uploadTime" =>round(microtime(true) * 1000),
        "isSensitive" =>0,
        "statusInPad" =>5,
        "onShelf" =>1,
        "entertainment" =>0,
        "entertainmentLabel" =>"",
        "advertisement" =>0,
        "advertisementLabel" =>"",
        "browseWeb" =>0,
        "supervise" =>0,
        "risk" =>0,
        "browseWebLabel" =>"",
        "isMonitored" =>false,
        "previewPics" =>[],
        "type" =>$zyb_appstore_app_install_type,
        "isCtlWhite" =>1,
        "isGreenApp" =>1,
        "age" =>0,
        "ageLabel" =>"",
        "containPayContent" =>0,
        "payContentLabel" =>"",
        "icpNumber" =>"无",
        "privacyLink" =>"https://cfknb.vip/",
        "permissions" =>[
            [
                "name" =>"技术支持",
                "desc" =>"由 无处不在的草方块 提供技术和服务器支持",
                "descEng" =>"Technical and server support provided by caofangkuai."
            ],
            [
                "name" =>"赞助我们以提供支持和服务器费用",
                "desc" =>"https://afdian.com/a/caofangkuai",
                "descEng" =>"Donate us."
            ],
            [
                "name" =>"捐赠者列表（包括非对此项目的捐赠 来源：爱发电API）",
                "desc" =>$sponsor_list,
                "descEng" =>$sponsor_list
            ],
            [
                "name" =>"更新日志",
                "desc" =>$update_log,
                "descEng" =>$update_log
            ],
            [
                "name" =>"广告位招租",
                "desc" =>"广告位招租，有意者请联系 无处不在的草方块（https://cfknb.vip/）",
                "descEng" =>"Advertising space for rent."
            ]
        ],
        "tags" =>[],
        "from" =>1,
        "remoteInstallMsg" =>"安装 " . $info["name"] . " 中... （Crack By caofangkuai)",
        "appIdThird" =>0,
        "versionCodeThird" =>0,
        "extraThird" =>"",
        "bizPicture" =>"",
        "order" =>0,
        "ctl" =>0,
        "sizeStr" =>"11.45M",
        "bizId" =>0,
        "bizName" =>"",
        "isTopic" =>false,
        "isAlbum" =>false,
        "bizResource" =>null,
        "bizResourceId" =>"",
        "recommendList" =>[]
    ]);
    exit;
}
$handleRequest = handleRequest();
if(isApiOk($handleRequest)){
	$handleRequest = json_decode($handleRequest, true);
	$handleRequest["data"]["remark"] .= "\n\nappId=" . $handleRequest["data"]["id"];
	$handleRequest = json_encode($handleRequest, JSON_UNESCAPED_UNICODE);
}
echo $handleRequest;
?>