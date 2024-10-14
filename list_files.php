<?php
$target_dir = "Stored/";
$infoFilePath = "disposition/info.json";
$recordingFilePath = "disposition/recording.json"; // 新增的记录文件
$configFilePath = "disposition/config.json";

$downloadAllowed = true;
if (file_exists($configFilePath)) {
    $configJson = json_decode(file_get_contents($configFilePath), true);
    if (isset($configJson['download']) && $configJson['download'] === false) {
        $downloadAllowed = false;
    }
}

// 检查并更新下载记录
function updateDownloadRecord($filename) {
    global $recordingFilePath;

    if (file_exists($recordingFilePath)) {
        $recordJson = json_decode(file_get_contents($recordingFilePath), true);
    } else {
        $recordJson = [];
    }

    // 清理过期记录
    $currentTime = time();
    foreach ($recordJson as $file => $data) {
        if (isset($data['lastUploadTime']) && ($currentTime - $data['lastUploadTime']) > 259200) { // 3天 = 259200秒
            unset($recordJson[$file]); // 移除过期记录
        }
    }

    // 更新下载次数
    if (isset($recordJson[$filename])) {
        $recordJson[$filename]['downloadCount'] += 1; // 增加下载次数
    } else {
        $recordJson[$filename] = [
            'downloadCount' => 1,
            'lastUploadTime' => $currentTime
        ];
    }

    file_put_contents($recordingFilePath, json_encode($recordJson, JSON_PRETTY_PRINT)); // 保存记录
}

// 记录下载行为
if (isset($_GET['download']) && $downloadAllowed) {
    $filename = basename($_GET['download']);
    $filePath = $target_dir . $filename;
    if (file_exists($filePath)) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filePath));
        readfile($filePath);
        exit; // 立即退出以防止输出后面的内容
    } else {
        http_response_code(404);
        echo '文件不存在';
        exit; // 在文件不存在时也要退出
    }
}

if (file_exists($infoFilePath)) {
    $infoJson = json_decode(file_get_contents($infoFilePath), true);
    $searchQuery = isset($_GET['search']) ? $_GET['search'] : '';
    $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
    $itemsPerPage = 10;

    if (!empty($searchQuery)) {
        $filteredFiles = [];
        foreach ($infoJson as $uploader => $files) {
            foreach ($files as $file) {
                if (strpos($uploader, $searchQuery) !== false || strpos($file['filename'], $searchQuery) !== false) {
                    $filteredFiles[$uploader][] = $file;
                }
            }
        }
        $infoJson = $filteredFiles;
    }

    $allFiles = [];
    foreach ($infoJson as $uploader => $files) {
        foreach ($files as $file) {
            $fileExpiryTime = $file['expiry'];
            // 仅在文件未过期时添加到列表
            if ($fileExpiryTime > time()) {
                $allFiles[] = ['uploader' => $uploader, 'file' => $file];
            }
        }
    }

    $totalFiles = count($allFiles);
    $totalPages = ceil($totalFiles / $itemsPerPage);
    $start = ($page - 1) * $itemsPerPage;
    $end = $start + $itemsPerPage;

    $fileList = [];
    for ($i = $start; $i < $end && $i < $totalFiles; $i++) {
        $file = $allFiles[$i]['file'];
        $uploader = $allFiles[$i]['uploader'];
        $expiryTime = date('Y-m-d H:i:s', $file['expiry']);
        $filePath = $target_dir . $file['filename']; // 获取文件的完整路径

        // 获取文件大小
        $fileSize = file_exists($filePath) ? filesize($filePath) : 0;
        $downloadLink = $downloadAllowed ? 'list_files.php?download=' . urlencode($file['filename']) : null;

        $fileList[] = [
            'filename' => htmlspecialchars($file['filename']),
            'uploader' => htmlspecialchars($uploader),
            'expiryTime' => htmlspecialchars($expiryTime),
            'fileSize' => $fileSize, // 添加文件大小
            'downloadLink' => $downloadLink
        ];
    }

    echo json_encode([
        'totalPages' => $totalPages,
        'fileList' => $fileList
    ]);
} else {
    echo json_encode([
        'totalPages' => 1,
        'fileList' => []
    ]);
}
?>