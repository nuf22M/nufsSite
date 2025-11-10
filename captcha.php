<?php 
session_start();

// Generate random 6-character code (excluding confusing characters)
$code = substr(str_shuffle('23456789bcdfghjkmnpqrstvwxyz'), 0, 6);

// Create image with better dimensions
$image = imagecreate(140, 50);
$bg = imagecolorallocate($image, 240, 240, 240);
$text = imagecolorallocate($image, 50, 50, 50);
$noise1 = imagecolorallocate($image, 200, 200, 200);
$noise2 = imagecolorallocate($image, 150, 150, 150);

// Add some noise lines for security
for($i = 0; $i < 5; $i++) {
    imageline($image, rand(0, 140), rand(0, 50), rand(0, 140), rand(0, 50), $noise1);
}

// Add text with better positioning
imagestring($image, 5, 25, 15, $code, $text);

// Add some random dots for additional security
for($i = 0; $i < 20; $i++) {
    imagesetpixel($image, rand(0, 140), rand(0, 50), $noise2);
}

// Output
header('Content-Type: image/png');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');
imagepng($image);
imagedestroy($image);

$_SESSION['captcha_code'] = $code;
?>
