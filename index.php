<!DOCTYPE html>
<html lang="EN-US">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>File hosting platform</title>
    <link rel="stylesheet" href="css/indexcss.css">
    <link rel="shortcut icon" href="icon.ico">
    <style>
        .normal {
            color: green;
        }
        .warning {
            color: orange;
        }
        .danger {
            color: red;
        }
        .progress {
            background-color: #f3f3f3;
            border: 1px solid #ccc;
            border-radius: 5px;
            height: 20px;
            margin: 10px 0;
        }
        .progress-bar {
            height: 100%;
            width: 0;
            background-color: #4caf50;
            text-align: center;
            color: white;
        }
    </style>
</head>
<body>
    <div class="announcement" id="announcement">
        <div id="announcement-content">close</div>
        <label><input type="checkbox" id="no-show-again"> no show again</label>
        <div class="close" id="close-announcement">close</div>
    </div>
    <div class="theme-toggle" id="theme-toggle">dary mode</div>
    <div class="container">
        <h2>File upload</h2>
        <!--h3>还在为网盘数据分享，需要登录而烦恼吗？免费使用！临时存储你的文件（单个文件不大于120MB）！<br>下载速度快，但不是24小时在线的备站：<a href="http://backupfile.work.gd:12701">upfile备站</a></h3-->
        <div class="storage-info">
            <div>Number of files stored on the website:<span id="file-count" class="value"></span></div>
            <div>The file takes up space：<span id="file-size" class="value"></span></div>
        </div>
        <form id="uploadForm" method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label for="uploaderName">Uploader's Name:</label>
                <input type="text" name="uploaderName" id="uploaderName" required>
            </div>
            <div class="form-group">
                <label for="fileToUpload">Select File:</label>
                <input type="file" name="fileToUpload[]" id="fileToUpload" multiple required>
                <div id="file-list-display" style="margin-top: 10px;"></div>
            </div>
            <div class="form-group">
                <label for="expiry">Select the retention time:</label>
                <select name="expiry" id="expiry" required>
                    <option value="2">2hours</option>
                    <option value="4">4hours</option>
                    <option value="12">12hours</option>
                    <option value="24">1day</option>
                    <option value="72">3days</option>
                    <option value="168">1week</option>
                    <option value="336">2weeks</option>
                </select>
            </div>
            <div class="form-group">
                <!--div class="h-captcha" data-sitekey="your sitekey"></div-->
                <!--You can choose one of the two, the self-contained captcha is simple and convenient, but it is not secure, and the h-captcha is safe, but it is troublesome to set up.-->
                <h4 style="text-align:center">By using this website, you agree to this<a href="xieyi.htm">《Site Agreement》</a></h4>
                <label for="captcha">Captcha:</label>
                <img src="captcha.php" alt="captcha">
                <input type="text" name="captcha" id="captcha" required>
                <input type="submit" value="upload" class="btn">
            </div>
            <div id="progress-container" style="display:none;">
            <div id="progress-bar" style="width: 0%; height: 20px; background-color: green;"></div>
            <div id="upload-speed">Current upload speed: 0 KB/s</div>
            <div id="current-file">Current Uploaded Files: </div>
            <div id="processed-files">Files Processed: 0/0</div>
        </div>
        </form>
        <div class="form-group" style="margin-top: 20px;">
            <label for="search">Search for files:</label>
            <input type="text" name="search" id="search" placeholder="Enter the uploader's name or the file's name...">
            <button class="btn" onclick=" searchFiles()">Search</button>
            <button class="btn" onclick=" refreshFiles()">refresh</button>
            <button class="btn" onclick="showAnnouncement()">announcement</button>
        </div>
        <h2>A list of files that have been uploaded</h2>
        <div class="file-list" id="file-list">
            <?php include 'list_files.php'; ?>
        </div>
        <div class="pagination">
            <button class="btn" onclick="goToPage(1)">first page</button>
            <button class="btn" onclick="goToPage(currentPage - 1)">Previous</button>
            <span id="current-page">1</span> / <span id="total-pages">1</span>
            <button class="btn" onclick="goToPage(currentPage + 1)">Next</button>
            <button class="btn" onclick="goToPage(totalPages)">last page</button>
        </div>
    </div>
    <script src="https://js.hcaptcha.com/1/api.js" async defer></script>
    <script src="js/indexjs.js"></script>
</body>
</html>
