<?php
$target_dir = "Stored/";
$infoFilePath = "disposition/info.json";

// The ability to delete expired files
function deleteExpiredFiles($infoFilePath, $target_dir) {
    if (file_exists($infoFilePath)) {
        $infoJson = json_decode(file_get_contents($infoFilePath), true);
        $updatedInfo = [];

        foreach ($infoJson as $uploader => $files) {
            foreach ($files as $file) {
                if ($file['expiry'] <= time()) {
                    // 删除文件
                    $fileToDelete = $target_dir . $file['filename'];
                    if (file_exists($fileToDelete)) {
                        unlink($fileToDelete);
                    }
                } else {
                    // 保存未过期文件信息
                    if (!isset($updatedInfo[$uploader])) {
                        $updatedInfo[$uploader] = [];
                    }
                    $updatedInfo[$uploader][] = $file;
                }
            }
        }

        // 更新info.json文件
        file_put_contents($infoFilePath, json_encode($updatedInfo, JSON_PRETTY_PRINT));
    }
}

deleteExpiredFiles($infoFilePath, $target_dir);

include 'list_files.php';
?>
