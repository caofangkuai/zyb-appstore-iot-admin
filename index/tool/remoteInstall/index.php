<?php
require_once '../../config.php';
require_once '../../iotUnionApi.php';
header('Content-Type: text/html; charset=utf-8');
require_once '../../MkEncrypt.php';
MkEncrypt('yourpwd');

$data = json_decode(file_get_contents('php://input'), true);

if ($data) {
    if (empty($data['sn']) || empty($data['appName'])) {
        echo json_encode(['errNo' => 1, 'errMsg' => '缺少必需参数']);
        exit;
    }

    $token = !empty($data['token']) ? $data['token'] : $iotunion_token;
    $secret = !empty($data['secret']) ? $data['secret'] : $iotunion_secret;
    $serialNumber = $data['sn'];
    $searchContent = $data['appName'];

    $searchResult = json_decode(parentSearchApp($token, $secret, $serialNumber, $searchContent), true);

    if ($searchResult['errNo'] !== 0) {
        echo json_encode($searchResult, JSON_UNESCAPED_UNICODE);
        exit;
    }

    if (empty($searchResult['data']['list']) || count($searchResult['data']['list']) === 0) {
        echo json_encode(['errNo' => 2, 'errMsg' => '未找到相关应用'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $appId = $searchResult['data']['list'][0]['id'];
    $installResult = json_decode(parentInstallApp($token, $secret, $serialNumber, $appId), true);

    echo json_encode([
        "errNo" => 0,
        "searchResult" => $searchResult['data']['list'][0],
        "installResult" => $installResult
    ], JSON_UNESCAPED_UNICODE);
    exit;
}
?>
<!DOCTYPE html>
<html lang="zh-CN">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>应用远程安装</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: #0b0f1a;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            padding: 1rem;
        }

        .card {
            max-width: 880px;
            width: 100%;
            background: #141a2b;
            border-radius: 32px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.8);
            overflow: hidden;
            border: 1px solid #2a3145;
        }

        .card-header {
            padding: 2.5rem 2.5rem 1.5rem;
            background: linear-gradient(145deg, #1a2235, #131a2a);
            border-bottom: 1px solid #2d354a;
        }

        .card-header h1 {
            color: #fff;
            font-size: 2rem;
            font-weight: 600;
            letter-spacing: -0.02em;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .card-header h1 i {
            color: #5f8aff;
            font-size: 2rem;
        }

        .card-header p {
            color: #8e9bb5;
            margin-top: 8px;
            font-size: 0.95rem;
        }

        .card-body {
            padding: 2rem 2.5rem;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.25rem;
            margin-bottom: 1.5rem;
        }

        @media (max-width: 640px) {
            .grid {
                grid-template-columns: 1fr;
            }

            .card-body {
                padding: 1.5rem;
            }

            .card-header {
                padding: 1.8rem 1.5rem;
            }
        }

        .input-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .input-group label {
            color: #b9c4da;
            font-size: 0.85rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .input-group label i {
            color: #5f8aff;
            font-size: 0.9rem;
        }

        .input-field {
            background: #0f1625;
            border: 1.5px solid #262f44;
            border-radius: 18px;
            padding: 0.9rem 1.2rem;
            color: #fff;
            font-size: 0.95rem;
            transition: all 0.2s;
            width: 100%;
            outline: none;
        }

        .input-field:focus {
            border-color: #5f8aff;
            background: #131b2c;
        }

        .input-field::placeholder {
            color: #3e4a62;
        }

        .badge {
            background: #1e2740;
            color: #8e9bb5;
            font-size: 0.7rem;
            padding: 0.2rem 0.6rem;
            border-radius: 30px;
            margin-left: 8px;
            font-weight: 400;
            text-transform: none;
        }

        .btn {
            background: linear-gradient(145deg, #5f8aff, #3d6aff);
            border: none;
            border-radius: 40px;
            padding: 1.1rem 2rem;
            color: white;
            font-weight: 600;
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            width: 100%;
            cursor: pointer;
            transition: all 0.2s;
            border: 1px solid rgba(255, 255, 255, 0.1);
            margin: 1.5rem 0 1rem;
            box-shadow: 0 8px 20px rgba(63, 106, 255, 0.3);
        }

        .btn:hover {
            background: linear-gradient(145deg, #6f96ff, #4d78ff);
            transform: translateY(-2px);
            box-shadow: 0 12px 28px rgba(63, 106, 255, 0.5);
        }

        .btn:active {
            transform: translateY(0);
        }

        .btn i {
            font-size: 1.2rem;
        }

        .loader {
            display: none;
            align-items: center;
            justify-content: center;
            gap: 14px;
            padding: 2rem 0;
            color: #8e9bb5;
        }

        .loader.active {
            display: flex;
        }

        .spinner {
            width: 22px;
            height: 22px;
            border: 3px solid #2d374f;
            border-top: 3px solid #5f8aff;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        .result {
            background: #0f1625;
            border-radius: 28px;
            padding: 1.8rem;
            margin-top: 1.2rem;
            border: 1px solid #262f44;
            display: none;
        }

        .result.show {
            display: block;
        }

        .result-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 1.5rem;
            color: #fff;
            font-size: 1.2rem;
            font-weight: 600;
        }

        .app-card {
            background: #1a2235;
            border-radius: 22px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            border: 1px solid #2d374f;
        }

        .app-row {
            display: flex;
            gap: 1.2rem;
            align-items: center;
            margin-bottom: 1.2rem;
        }

        .app-icon {
            width: 58px;
            height: 58px;
            background: #262f44;
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #5f8aff;
            font-size: 1.8rem;
            border: 1px solid #37415c;
        }

        .app-title {
            font-size: 1.4rem;
            font-weight: 600;
            color: #fff;
        }

        .app-dev {
            color: #8e9bb5;
            font-size: 0.9rem;
        }

        .meta-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1rem;
            margin: 1.5rem 0;
            background: #0f1625;
            padding: 1.2rem;
            border-radius: 20px;
        }

        .meta-item {
            display: flex;
            flex-direction: column;
        }

        .meta-label {
            color: #8e9bb5;
            font-size: 0.75rem;
            text-transform: uppercase;
        }

        .meta-value {
            color: #fff;
            font-weight: 600;
            font-size: 1rem;
            margin-top: 4px;
        }

        .tag-list {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 1rem;
        }

        .tag {
            background: #262f44;
            color: #b9c4da;
            padding: 0.3rem 1rem;
            border-radius: 30px;
            font-size: 0.8rem;
        }

        .install-status {
            background: #142132;
            border-left: 4px solid #5f8aff;
            padding: 1.3rem 1.5rem;
            border-radius: 18px;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
        }

        .status-info {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .status-info i {
            color: #5f8aff;
            font-size: 1.8rem;
        }

        .status-text {
            color: #fff;
            font-weight: 500;
        }

        .status-small {
            color: #8e9bb5;
            font-size: 0.8rem;
        }

        .error-box {
            background: #221a2c;
            border-left: 4px solid #ff5f6d;
            padding: 1.8rem;
            text-align: center;
            border-radius: 24px;
        }

        .error-box i {
            font-size: 2.5rem;
            color: #ff5f6d;
            margin-bottom: 1rem;
        }

        .error-box h3 {
            color: #fff;
            font-weight: 500;
            margin-bottom: 0.5rem;
        }

        .error-box p {
            color: #b9a1b5;
        }
    </style>
</head>

<body>
    <div class="card">
        <div class="card-header">
            <h1><i class="fa-solid fa-cloud-arrow-up"></i> 远程安装</h1>
            <p>输入设备信息，搜索并推送应用</p>
        </div>
        <div class="card-body">
            <div class="grid">
                <div class="input-group">
                    <label><i class="fa-regular fa-qrcode"></i> SN码 <span class="badge">必填</span></label>
                    <input type="text" class="input-field" id="sn" placeholder="例如: SN2024001">
                </div>
                <div class="input-group">
                    <label><i class="fa-regular fa-magnifying-glass"></i> 应用名 <span class="badge">必填</span></label>
                    <input type="text" class="input-field" id="appName" placeholder="输入应用名称">
                </div>
            </div>
            <div class="grid">
                <div class="input-group">
                    <label><i class="fa-regular fa-lock-key"></i> Token <span class="badge">可选</span></label>
                    <input type="text" class="input-field" id="token" placeholder="留空使用默认">
                </div>
                <div class="input-group">
                    <label><i class="fa-regular fa-lock"></i> Secret <span class="badge">可选</span></label>
                    <input type="text" class="input-field" id="secret" placeholder="留空使用默认">
                </div>
            </div>

            <button class="btn" id="installBtn"><i class="fa-regular fa-rocket"></i> 执行安装</button>

            <div class="loader" id="loading">
                <div class="spinner"></div>
                <span>搜索应用中…</span>
            </div>

            <div class="result" id="result"></div>
        </div>
    </div>

    <script>
        const sn = document.getElementById('sn');
        const appName = document.getElementById('appName');
        const token = document.getElementById('token');
        const secret = document.getElementById('secret');
        const installBtn = document.getElementById('installBtn');
        const loading = document.getElementById('loading');
        const resultDiv = document.getElementById('result');

        installBtn.addEventListener('click', async () => {
            if (!sn.value.trim() || !appName.value.trim()) {
                showError('SN码和应用名称不能为空');
                return;
            }

            loading.classList.add('active');
            resultDiv.classList.remove('show');
            installBtn.disabled = true;

            try {
                const response = await fetch('', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        sn: sn.value.trim(),
                        appName: appName.value.trim(),
                        token: token.value.trim(),
                        secret: secret.value.trim()
                    })
                });

                const data = await response.json();
                if (data.errNo === 0 && data.installResult.errNo === 0) {
                    showSuccess(data.searchResult, data.installResult, sn.value.trim());
                } else if (data.errNo !== 0) {
                    showError(data.errMsg);
                } else if (data.installResult.errNo !== 0) {
                    showError(data.installResult.errMsg);
                } else {
                    showError('未知错误');
                }
            } catch (e) {
                showError('网络错误: ' + e.message);
            } finally {
                loading.classList.remove('active');
                installBtn.disabled = false;
            }
        });

        function showSuccess(app, install, deviceSn) {
            const formatSize = (b) => {
                if (b >= 1073741824) return (b / 1073741824).toFixed(2) + ' GB';
                if (b >= 1048576) return (b / 1048576).toFixed(2) + ' MB';
                if (b >= 1024) return (b / 1024).toFixed(2) + ' KB';
                return b + ' B';
            };

            const time = new Date(app.uploadTime).toLocaleString('zh-CN');

            let tagsHtml = '';
            if (app.tags && app.tags.length) {
                tagsHtml = '<div class="tag-list">' + app.tags.map(t => `<span class="tag">${t.name}</span>`).join('') + '</div>';
            }

            resultDiv.innerHTML = `
            <div class="result-header"><i class="fa-regular fa-circle-check" style="color:#5f8aff;"></i> 安装任务已创建</div>
            <div class="app-card">
                <div class="app-row">
                    <div class="app-icon"><i class="fa-regular fa-cube"></i></div>
                    <div>
                        <div class="app-title">${app.name}</div>
                        <div class="app-dev">${app.developer || '未知开发者'}</div>
                    </div>
                </div>
                <div style="color:#b9c4da; margin-bottom: 1rem;">${app.summary || ''}</div>
                ${tagsHtml}
                <div class="meta-grid">
                    <div class="meta-item"><span class="meta-label">包名</span><span class="meta-value">${app.apkName || '—'}</span></div>
                    <div class="meta-item"><span class="meta-label">版本</span><span class="meta-value">${app.apkVersion || '—'}</span></div>
                    <div class="meta-item"><span class="meta-label">大小</span><span class="meta-value">${formatSize(app.apkSize)}</span></div>
                    <div class="meta-item"><span class="meta-label">上传</span><span class="meta-value">${time}</span></div>
                </div>
            </div>
            <div class="install-status">
                <div class="status-info">
                    <i class="fa-regular fa-circle-notch"></i>
                    <div>
                        <div class="status-text">安装中 (任务ID: ${install.logId})</div>
                        <div class="status-small">设备 ${deviceSn}</div>
                    </div>
                </div>
                <i class="fa-regular fa-arrow-right" style="color:#5f8aff;"></i>
            </div>
        `;
            resultDiv.classList.add('show');
        }

        function showError(msg) {
            resultDiv.innerHTML = `
            <div class="error-box">
                <i class="fa-regular fa-circle-exclamation"></i>
                <h3>${msg}</h3>
                <p>请检查信息后重试</p>
            </div>
        `;
            resultDiv.classList.add('show');
        }

        [sn, appName].forEach(i => i.addEventListener('input', function() {
            this.style.borderColor = '#262f44';
        }));
    </script>
</body>

</html>