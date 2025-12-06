<?php
require_once "../../config.php";
$handleRequest = handleRequest();
$info = getAppInfo(getValueSafely($_GET["sn"]), getValueSafely($_GET["appId"]));
if(!empty($info)){
    echo generateRootMessage([
            "id" =>intval($info["id"]),
            "apkName" =>$info["pkgName"],
            "version" =>"11.45",
            "url" =>$info["apkUrl"],
            "size" =>259634232,
            "md5" =>$info["apkMd5"],
            "patchInfo" =>null
    ]);
    exit;
}
echo $handleRequest;
?>