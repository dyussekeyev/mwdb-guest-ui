<?php
session_start();

// Generate a random string
$captcha_text = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 6);

// Store the captcha text in the session
$_SESSION['captcha_text'] = $captcha_text;

// Create the image
$image = imagecreate(100, 40);
if (!$image) {
    error_log('Failed to create image.');
    exit('Failed to create image.');
}
$background_color = imagecolorallocate($image, 255, 255, 255);
if ($background_color === false) {
    error_log('Failed to allocate background color.');
    imagedestroy($image);
    exit('Failed to allocate background color.');
}
$text_color = imagecolorallocate($image, 0, 0, 0);
if ($text_color === false) {
    error_log('Failed to allocate text color.');
    imagedestroy($image);
    exit('Failed to allocate text color.');
}
$font = 5;

// Draw the text on the image
if (!imagestring($image, $font, 10, 10, $captcha_text, $text_color)) {
    error_log('Failed to draw text on image.');
    imagedestroy($image);
    exit('Failed to draw text on image.');
}

// Set headers and output the image
header('Content-Type: image/png');
if (!imagepng($image)) {
    error_log('Failed to output image.');
}
imagedestroy($image);
?>
