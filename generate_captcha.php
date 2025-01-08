<?php
session_start();

// Generate a random string
$captcha_text = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 6);

// Store the captcha text in the session
$_SESSION['captcha_text'] = $captcha_text;

// Create the image
$image = imagecreate(100, 40);
$background_color = imagecolorallocate($image, 255, 255, 255);
$text_color = imagecolorallocate($image, 0, 0, 0);
$font = 5;

// Draw the text on the image
imagestring($image, $font, 10, 10, $captcha_text, $text_color);

// Set headers and output the image
header('Content-Type: image/png');
imagepng($image);
imagedestroy($image);
?>
