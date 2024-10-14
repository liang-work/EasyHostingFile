<?php
$target_dir = "Stored/";
$maxFiles = 40000;//Maximum Quantity
$maxSize = 3 * 1024 * 1024 * 1024; // max size

$files = glob($target_dir . '*');
$fileCount = count($files);
$fileSize = array_sum(array_map('filesize', $files));

echo json_encode([
    'fileCount' => $fileCount,
    'maxFiles' => $maxFiles,
    'fileSize' => $fileSize,
    'maxSize' => $maxSize
]);
?>
