<?php
require_once "../../config.php";
require_once "../../iotUnionApi.php";

$specialTag = "cfknb@";
$handleRequest = handleRequest();
$json = json_decode($handleRequest, true);
if(isApiOk($handleRequest)){
    $keyword = getValueSafely($_GET["keyword"]);
    if(str_starts_with($keyword, $specialTag)){
        $keyword = str_replace($specialTag, "", $keyword);
        $sn = getValueSafely($_GET["sn"]);
        $apps = searchApps($sn, $keyword);
        if($keyword !== "all"){
            $iotunionApps = parentSearchApp($iotunion_token, $iotunion_secret, $sn, $keyword);
            if(isApiOk($iotunionApps)){
                $iotunionAppsJson = json_decode($iotunionApps, true)["data"]["list"];
                if(count($iotunionAppsJson) > 0){
                    foreach ($iotunionAppsJson as $key => $item) {
           				array_push($json["data"], [
                			"apkName" =>$item["apkName"],
                			"ctl" =>0,
                			"isCtlWhite" =>1,
                			"isGreenApp" =>1,
                			"supervise" =>0,
                			"risk" =>0,
                			"icon" =>$item["icon"],
                			"id" =>$item["id"],
                			"name" =>$item["name"],
                			"source" =>2,
                			"size" =>$item["apkSize"],
                			"sizeStr" =>($item["apkSize"] / 1024 / 1024) . "M",
                			"summary" =>"家长端应用",
                			"version" =>$item["apkVersion"],
                			"type" =>$zyb_appstore_app_install_type,
                			"installNum" =>114514,
                			"enName" =>"",
                			"isEqualKeyword" =>0,
                			"publishTime" =>round(microtime(true) * 1000),
                			"appIdThird" =>0,
                			"versionCodeThird" =>0,
                			"extraThird" =>""
            			]);
        			}
                }
            }
        }
        foreach ($apps as $key => $item) {
            array_push($json["data"], [
                "apkName" =>$item["pkgName"],
                "ctl" =>0,
                "isCtlWhite" =>1,
                "isGreenApp" =>1,
                "supervise" =>0,
                "risk" =>0,
                "icon" =>$item["icon"],
                "id" =>intval($item["id"]),
                "name" =>$item["name"],
                "source" =>2,
                "size" =>28167842,
                "sizeStr" =>"11.45M",
                "summary" =>"用户上传",
                "version" =>"11.45",
                "type" =>$zyb_appstore_app_install_type,
                "installNum" =>114514,
                "enName" =>"",
                "isEqualKeyword" =>0,
                "publishTime" =>round(microtime(true) * 1000),
                "appIdThird" =>0,
                "versionCodeThird" =>0,
                "extraThird" =>""
            ]);
        }
    }
}
echo json_encode($json, JSON_UNESCAPED_UNICODE);
?>