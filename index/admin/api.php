<?php
include_once "../config.php";

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

// 检查序列号白名单
if ($enable_webui_sn_whitelist && !in_array(trim($_POST["sn"] ?? ""), $webui_sn_whitelist)) {
    die(json_encode(['success' => false, 'message' => '序列号不在白名单内']));
}

// 创建数据库连接
$conn = new mysqli($servername, $username, $password, $dbname);

// 检查连接
if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => '数据库连接失败: ' . $conn->connect_error]));
}

// 设置字符集
$conn->set_charset("utf8mb4");

// 获取请求动作
$action = $_GET['action'] ?? '';

// 处理不同的动作
switch ($action) {
    case 'add':
        addApp($conn);
        break;
    case 'update':
        updateApp($conn);
        break;
    case 'delete':
        deleteApp($conn);
        break;
    case 'search':
        searchApp($conn);
        break;
    default:
        echo json_encode(['success' => false, 'message' => '未知操作']);
        break;
}

$conn->close();

// 添加应用
function addApp($conn)
{
    // 获取并验证输入数据
    $id = intval($_POST['id'] ?? 0);
    $icon = trim($_POST['icon'] ?? '');
    $name = trim($_POST['name'] ?? '');
    $pkgName = trim($_POST['pkgName'] ?? '');
    $apkUrl = trim($_POST['apkUrl'] ?? '');
    $apkMd5 = trim($_POST['apkMd5'] ?? '');
    $sn = trim($_POST['sn'] ?? '');

    // 验证必填字段
    if ($id < 114514) {
        echo json_encode(['success' => false, 'message' => '应用ID必须大于等于114514']);
        return;
    }

    if (empty($icon) || empty($name) || empty($pkgName) || empty($apkUrl) || empty($apkMd5) || empty($sn)) {
        echo json_encode(['success' => false, 'message' => '所有字段都是必填的']);
        return;
    }

    // 检查相同SN的应用ID是否已存在
    $check_sql = "SELECT id FROM apps WHERE sn = ? AND id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("si", $sn, $id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => '相同SN的应用ID已存在']);
        $check_stmt->close();
        return;
    }
    $check_stmt->close();

    // 插入新应用
    $sql = "INSERT INTO apps (id, icon, name, pkgName, apkUrl, apkMd5, sn) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("issssss", $id, $icon, $name, $pkgName, $apkUrl, $apkMd5, $sn);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => '应用添加成功']);
    } else {
        echo json_encode(['success' => false, 'message' => '添加应用失败: ' . $stmt->error]);
    }

    $stmt->close();
}

// 更新应用
function updateApp($conn)
{
    // 获取并验证输入数据
    $id = intval($_POST['id'] ?? 0);
    $icon = trim($_POST['icon'] ?? '');
    $name = trim($_POST['name'] ?? '');
    $pkgName = trim($_POST['pkgName'] ?? '');
    $apkUrl = trim($_POST['apkUrl'] ?? '');
    $apkMd5 = trim($_POST['apkMd5'] ?? '');
    $sn = trim($_POST['sn'] ?? '');

    // 验证必填字段
    if ($id < 114514) {
        echo json_encode(['success' => false, 'message' => '应用ID必须大于等于114514']);
        return;
    }

    if (empty($icon) || empty($name) || empty($pkgName) || empty($apkUrl) || empty($apkMd5) || empty($sn)) {
        echo json_encode(['success' => false, 'message' => '所有字段都是必填的']);
        return;
    }

    // 检查应用是否存在
    $check_sql = "SELECT id FROM apps WHERE sn = ? AND id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("si", $sn, $id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => '应用不存在']);
        $check_stmt->close();
        return;
    }
    $check_stmt->close();

    // 更新应用信息
    $sql = "UPDATE apps SET icon = ?, name = ?, pkgName = ?, apkUrl = ?, apkMd5 = ?, sn = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssi", $icon, $name, $pkgName, $apkUrl, $apkMd5, $sn, $id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => '应用更新成功']);
    } else {
        echo json_encode(['success' => false, 'message' => '更新应用失败: ' . $stmt->error]);
    }

    $stmt->close();
}

// 删除应用
function deleteApp($conn)
{
    // 获取输入数据
    $id = intval($_POST['id'] ?? 0);
    $sn = trim($_POST['sn'] ?? '');

    // 验证输入
    if ($id < 114514 || empty($sn)) {
        echo json_encode(['success' => false, 'message' => '应用ID和SN号都是必填的']);
        return;
    }

    // 检查应用是否存在
    $check_sql = "SELECT id FROM apps WHERE sn = ? AND id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("si", $sn, $id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => '应用不存在']);
        $check_stmt->close();
        return;
    }
    $check_stmt->close();

    // 删除应用
    $sql = "DELETE FROM apps WHERE sn = ? AND id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $sn, $id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => '应用删除成功']);
    } else {
        echo json_encode(['success' => false, 'message' => '删除应用失败: ' . $stmt->error]);
    }

    $stmt->close();
}

// 查找应用
function searchApp($conn)
{
    // 获取输入数据
    $id = intval($_POST['id'] ?? 0);
    $sn = trim($_POST['sn'] ?? '');

    // 验证输入
    if ($id < 114514 || empty($sn)) {
        echo json_encode(['success' => false, 'message' => '应用ID和SN号都是必填的']);
        return;
    }

    // 查找应用
    $sql = "SELECT * FROM apps WHERE sn = ? AND id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $sn, $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $app = $result->fetch_assoc();
        echo json_encode(['success' => true, 'app' => $app]);
    } else {
        echo json_encode(['success' => false, 'message' => '应用不存在']);
    }

    $stmt->close();
}
