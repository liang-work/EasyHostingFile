var totalPages = 1;
var searchQuery = '';
var selectedFiles = [];

document.getElementById('fileToUpload').addEventListener('change', handleFileSelect);

function handleFileSelect(event) {
    const files = Array.from(event.target.files);
    selectedFiles = selectedFiles.concat(files);
    updateFileListDisplay();
}

function updateFileListDisplay() {
    const fileListDisplay = document.getElementById('file-list-display');
    fileListDisplay.innerHTML = '<strong>已选择文件:</strong><ul>';
    selectedFiles.forEach((file, index) => {
        fileListDisplay.innerHTML += `
            <li class="file-item">
                ${file.name} (${(file.size / 1024 / 1024).toFixed(2)} MB)
                <span class="remove-file" onclick="removeFile(${index})">❌</span>
            </li>`;
    });
    fileListDisplay.innerHTML += '</ul>';
}

function removeFile(index) {
    selectedFiles.splice(index, 1);
    updateFileListDisplay();
}

document.getElementById('uploadForm').onsubmit = function(e) {
    e.preventDefault(); // Prevent form submissions
    uploadFiles();
};

function uploadFiles() {
    const totalFiles = selectedFiles.length; // Total number of files
    const delayBetweenUploads = 1000; // The latency between each request, in milliseconds
    let uploadedFileCount = 0; // Number of files processed

    function uploadFile(index) {
        if (index >= totalFiles) {
            alert('All documents have been uploaded successfully!');
            setTimeout(() => {
                window.location.href = 'index.php';
            }, 1500);
            return;
        }

        const file = selectedFiles[index];
        const individualFormData = new FormData();
        individualFormData.append('fileToUpload[]', file);
        individualFormData.append('uploaderName', document.getElementById('uploaderName').value);
        individualFormData.append('expiry', document.getElementById('expiry').value);

        const xhr = new XMLHttpRequest(); // Each file uses a new request
        
        // Update the information of the current uploaded file
        document.getElementById('current-file').innerText = `Current Uploaded Files: ${file.name}`;
        
        xhr.upload.addEventListener('progress', function(event) {
            if (event.lengthComputable) {
                const percentComplete = (event.loaded / event.total) * 100;
                const progressBar = document.getElementById('progress-bar');
                progressBar.style.width = percentComplete + '%';
                progressBar.textContent = Math.round(percentComplete) + '%';

                // Update the upload speed
                const uploadSpeed = (event.loaded / (event.timeStamp / 1000)) / 1024; // KB/s
                document.getElementById('upload-speed').textContent = 'Current upload speed: ' + uploadSpeed.toFixed(2) + ' KB/s';
            }
        });

        xhr.onreadystatechange = function() {
            if (xhr.readyState === XMLHttpRequest.DONE) {
                if (xhr.status === 200) {
                    uploadedFileCount++;
                    document.getElementById('processed-files').innerText = `Files Processed: ${uploadedFileCount}/${totalFiles}`;
                    
                    // Delay uploading the next file
                    setTimeout(() => uploadFile(index + 1), delayBetweenUploads);
                } else {
                    alert('There was an error during the upload process.error code:'+xhr.status);
                }
            }
        };

        // An upload progress bar is displayed
        document.getElementById('progress-container').style.display = 'block';
        xhr.open('POST', 'upload.php', true);
        xhr.send(individualFormData);
    }

    // Start uploading your first file
    uploadFile(0);
}


function formatSize(size) {
    var units = ['B', 'KB', 'MB', 'GB', 'TB'];
    var i = 0;
    while (size >= 1024 && i < units.length - 1) {
        size /= 1024;
        i++;
    }
    return size.toFixed(2) + ' ' + units[i];
}

function updateStorageInfo() {
    fetch('get_storage_info.php')
        .then(response => response.json())
        .then(data => {
            var fileCountElement = document.getElementById('file-count');
            var fileSizeElement = document.getElementById('file-size');
            
            fileCountElement.innerText = data.fileCount + '/' + data.maxFiles;
            if (data.fileCount >= data.maxFiles) {



                fileCountElement.className = 'danger';
            } else if (data.fileCount >= data.maxFiles * 0.75) {
                fileCountElement.className = 'warning';
            } else {
                fileCountElement.className = 'normal';
            }

            fileSizeElement.innerText = formatSize(data.fileSize) + '/' + formatSize(data.maxSize);
            if (data.fileSize >= data.maxSize) {
                fileSizeElement.className = 'danger';
            } else if (data.fileSize >= data.maxSize * 0.75) {
                fileSizeElement.className = 'warning';
            } else {
                fileSizeElement.className = 'normal';
            }
        })
        .catch(error => {
            console.error('Failed to get storage information:', error);
        });
}

function refreshFiles() {
    fetch('refresh_files.php')
        .then(response => response.text())
        .then(data => {
            document.getElementById('file-list').innerHTML = data;
            document.location.reload();
        })
        .catch(error => {
            console.error('Refresh file list failed:', error);
        });
}

function showAnnouncement() {
    fetch('disposition/announcement.txt')
        .then(response => response.text())
        .then(data => {
            var announcementContent = document.getElementById('announcement-content');
            announcementContent.innerHTML = data.replace(/\n/g, '<br>');
            var announcement = document.getElementById('announcement');
            announcement.style.display = 'block';
        })
        .catch(error => {
            console.error('Failed to get announcement:', error);
        });
}

function closeAnnouncement() {
    var announcement = document.getElementById('announcement');
    announcement.style.display = 'none';
    if (document.getElementById('no-show-again').checked) {
        localStorage.setItem('noShowAnnouncement', true);
    }
}

function goToPage(page) {
    currentPage = page;
    if (currentPage < 1) currentPage = 1;
    if (currentPage > totalPages) currentPage = totalPages;
    document.getElementById('current-page').innerText = currentPage;
    fetch('list_files.php?page=' + currentPage + '&search=' + encodeURIComponent(searchQuery))
        .then(response => response.json())
        .then(data => {
            totalPages = data.totalPages;
            document.getElementById('total-pages').innerText = totalPages;
            var fileListHtml = '';
            data.fileList.forEach(file => {
                fileListHtml += '<div class="file-item">';
                fileListHtml += '<div class="file-info">';
                fileListHtml += '<span class="file-name">' + file.filename + '</span>';
                fileListHtml += '<span class="file-size"> (' + formatSize(file.fileSize) + ')</span>';
                fileListHtml += '<span class="uploader-name">App uploaded by: ' + file.uploader + '</span>';
                fileListHtml += '<span class="expiry-time">Expiration Time: ' + file.expiryTime + '</span>';
                fileListHtml += '</div>';
                if (file.downloadLink) {
                    fileListHtml += '<a class="download-link" href="' + file.downloadLink + '" download>download it</a>';
                } else {
                    fileListHtml += '<span class="download-link">The download feature is disabled</span>';
                }
                fileListHtml += '</div>';
            });
            document.getElementById('file-list').innerHTML = fileListHtml;
        })
        .catch(error => {
            console.error('Refresh file list failed:', error);
        });
}

window.onload = function() {
    updateStorageInfo();
    
    // Check your local storage for a setting that is no longer prompted
    if (!localStorage.getItem('noShowAnnouncement')) {
        showAnnouncement();
        document.getElementById('no-show-again').checked = false;  // The default checkbox is unchecked
    } else {
        document.getElementById('no-show-again').checked = true;  // The default checkbox is checked
    }
    
    searchQuery = new URLSearchParams(window.location.search).get('search') || '';
    goToPage(1);
};

function closeAnnouncement() {
    var announcement = document.getElementById('announcement');
    announcement.style.display = 'none';
    
    // If the checkbox is checked, the selection is saved
    if (document.getElementById('no-show-again').checked) {
        localStorage.setItem('noShowAnnouncement', true);
    } else {
        // If the checkbox is not checked, clears Set
        localStorage.removeItem('noShowAnnouncement');
    }
}


function searchFiles() {
    searchQuery = document.getElementById('search').value;
    window.location.href = 'index.php?search=' + encodeURIComponent(searchQuery);
}


document.getElementById('theme-toggle').addEventListener('click', function() {
    var body = document.body;
    var toggleButton = document.getElementById('theme-toggle');
    if (body.classList.contains('dark-mode')) {
        body.classList.remove('dark-mode');
        toggleButton.innerText = 'dark mode';
    } else {
        body.classList.add('dark-mode');
        toggleButton.innerText = 'light mode';
    }
});

document.getElementById('close-announcement').addEventListener('click', closeAnnouncement);