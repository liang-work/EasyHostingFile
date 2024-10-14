<?php
session_start();

$width = 125;
$height = 40;
$font_size = 20;
$font = 'c:\Users\Administrator\Downloads\upfile.rf.gd\upfile.rf.gd\htdocs\arial.ttf'; // Use an absolute path

while (ob_get_level()) {
    ob_end_clean();
}

$image = imagecreatetruecolor($width, $height);
$background_color = imagecolorallocate($image, 255, 255, 255);
$text_color = imagecolorallocate($image, 0, 0, 0);

imagefilledrectangle($image, 0, 0, $width, $height, $background_color);

$text = substr(str_shuffle('ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz23456789'), 0, 6);
$_SESSION['captcha'] = $text;

imagettftext($image, $font_size, 0, 15, 30, $text_color, $font, $text);

header('Content-Type: image/png');
imagepng($image);
imagedestroy($image);
?>
