<?php
session_start();

// Determine the captcha type based on the query parameter
$captcha_type = isset($_GET['type']) ? $_GET['type'] : 'search';

// Characters to exclude: 0, O, 1, l, I
$characters = "23456789abcdefghjkmnpqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ";

// Generate a random string
$captcha_text = substr(str_shuffle($characters), 0, 6);

// Store the captcha text in the session with different keys based on the type
$_SESSION["captcha_text_$captcha_type"] = $captcha_text;

// Create the image
$image = imagecreate(150, 60); // Increase the image size to accommodate larger text
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
$font = 5; // Default font size
$font_width = imagefontwidth($font);
$font_height = imagefontheight($font);

// Calculate the position to center the text
$x = (imagesx($image) - $font_width * strlen($captcha_text)) / 2;
$y = (imagesy($image) - $font_height) / 2;

// Draw the text on the image
if (!imagestring($image, $font, $x, $y, $captcha_text, $text_color)) {
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
