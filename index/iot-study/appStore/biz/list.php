<?php
require_once "../../../config.php";
$handleRequest = handleRequest();
$json = json_decode($handleRequest, true);
if(isApiOk($handleRequest) && getPostParam("bizPosition") === 3){
    $searchWords = array("cfknb", "请输入文本");
    $json["data"]["list"][0]["total"] = count($searchWords);
    $json["data"]["searchList"] = $searchWords;
}
echo json_encode($json, JSON_UNESCAPED_UNICODE);
?>