<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $target_dir = "Stored/";
    $uploadOk = 1;
    $totalFileSize = 0;
    $errorMessage = "";
    $uploaderName = $_POST["uploaderName"];
    $expiryHours = intval($_POST["expiry"]);
    $expiryTime = time() + ($expiryHours * 3600); // 计算过期时间，单位为秒
    $infoFilePath = "disposition/info.json";
    $configFilePath = "disposition/config.json";

    // 检查config.json中的upload键
    if (file_exists($configFilePath)) {
        $configJson = json_decode(file_get_contents($configFilePath), true);
        if (isset($configJson['upload']) && $configJson['upload'] === false) {
            $errorMessage .= "The upload feature has been disabled by your administrator.<br>";
            $uploadOk = 0;
        }
    } else {
        $errorMessage .= "The profile config.json does not exist.<br>";
        $uploadOk = 0;
    }

    // hCaptcha
    /*if (isset($_POST['h-captcha-response'])) {
        $captchaResponse = $_POST['h-captcha-response'];
        $secretKey = "your key";
        $verifyResponse = file_get_contents("https://hcaptcha.com/siteverify?secret={$secretKey}&response={$captchaResponse}");
        $responseData = json_decode($verifyResponse);
        if (!$responseData->success) {
            $errorMessage .= "hCaptcha validation failed<br>";
            $uploadOk = 0;
        }
    } else {
        $errorMessage .= "hCaptcha verification is not complete.<br>";
        $uploadOk = 0;
    }*/
    //Comes with a verification code
    if (!isset($_POST['captcha']) || strtolower($_POST['captcha']) != strtolower($_SESSION['captcha'])) {
        $errorMessage .= "The verification code is incorrect, please re-enter it。<br>";
        $uploadOk = 0;
    }

    if (count($_FILES["fileToUpload"]["name"]) > 25) {
        $errorMessage .= "Upload up to 25 files at a time.<br>";
        $uploadOk = 0;
    }

    foreach ($_FILES["fileToUpload"]["name"] as $key => $fileName) {
        $fileSize = $_FILES["fileToUpload"]["size"][$key];
        $totalFileSize += $fileSize;
        $target_file = $target_dir . basename($fileName);


        /*if ($fileSize > 120000000) {
            $errorMessage .= "The file $fileName is too large to exceed 120MB.<br>";
            $uploadOk = 0;
            continue; 
        }*///File size can be limited (single)


        if (file_exists($target_file)) {
            $errorMessage .= "File $fileName already exists. <br>";
            $uploadOk = 0;
            continue; 
        }


        if (!is_uploaded_file($_FILES["fileToUpload"]["tmp_name"][$key])) {
            $errorMessage .= "File $fileName was not uploaded via HTTP POST.<br>";
            $uploadOk = 0;
            continue; 
        }


        if ($uploaderName == "admin") {
            $errorMessage .= "The name is invalid, and the name: 'admin' is the reserved name.<br>";
            $uploadOk = 0;
            continue; 
        }
    }

    /*if ($totalFileSize > 120000000) {
        $errorMessage .= "所有文件总大小超过120MB。<br>";
        $uploadOk = 0;
    }

    // 检查Stored/目录下的文件总数和总大小
    $files = glob($target_dir . '*');
    $totalFiles = count($files);
    $totalSize = array_sum(array_map('filesize', $files));

    if ($totalFiles >= 40000) {
        $errorMessage .= "文件总数超过40000，上传功能已关闭。<br>";
        $uploadOk = 0;
    }

    if ($totalSize >= 3 * 1024 * 1024 * 1024) {
        $errorMessage .= "文件总大小超过3GB，上传功能已关闭。<br>";
        $uploadOk = 0;
    }*///Functions are optional

    // If the file passes all checks, try uploading
    if ($uploadOk == 1) {
        foreach ($_FILES["fileToUpload"]["name"] as $key => $fileName) {
            $target_file = $target_dir . basename($fileName);
            if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"][$key], $target_file)) {
                echo "file " . htmlspecialchars(basename($fileName)) . " Uploaded.";

                // Read existing info.json files
                if (file_exists($infoFilePath)) {
                    $infoJson = json_decode(file_get_contents($infoFilePath), true);
                } else {
                    $infoJson = [];
                }

                // Update info.json file
                if (!isset($infoJson[$uploaderName])) {
                    $infoJson[$uploaderName] = [];
                }
                $infoJson[$uploaderName][] = [
                    'filename' => basename($fileName),
                    'expiry' => $expiryTime
                ];

                // Write back info.json file
                file_put_contents($infoFilePath, json_encode($infoJson, JSON_PRETTY_PRINT));
            } else {
                $errorMessage .= "Error uploading file $fileName.<br>";
            }
        }
    }

    // Output an error message
    if (!empty($errorMessage)) {
        echo "上传失败：<br>" . $errorMessage;
    }
}

// A function used to calculate the file size
function formatSizeUnits($bytes) {
    if ($bytes >= 1073741824) {
        return number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    } else {
        return $bytes . ' bytes';
    }
}
?>
<!--!DOCTYPE html>
<html>
<head>
    <title>文件上传结果</title>
</head>
<body>
    <p>3秒后自动返回主页...</p>
    <script type="text/javascript">
        setTimeout(function() {
            window.location.href = 'index.php';
        }, 3000);
    </script>
</body>
</html-->
