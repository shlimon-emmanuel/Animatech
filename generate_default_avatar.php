<?php
// This script generates a simple default avatar image
$width = 200;
$height = 200;

// Create the image
$image = imagecreatetruecolor($width, $height);

// Define colors
$bgColor = imagecolorallocate($image, 50, 100, 150); // Blue-ish background
$textColor = imagecolorallocate($image, 255, 255, 255); // White text

// Fill the background
imagefill($image, 0, 0, $bgColor);

// Add a user icon or text
$text = "USER";
$font = 5; // Built-in font

// Center the text
$textWidth = imagefontwidth($font) * strlen($text);
$textHeight = imagefontheight($font);
$x = ($width - $textWidth) / 2;
$y = ($height - $textHeight) / 2;

// Draw the text
imagestring($image, $font, $x, $y, $text, $textColor);

// Add circle for head
$centerX = $width / 2;
$centerY = $height / 3;
$headRadius = $width / 6;
imagefilledellipse($image, $centerX, $centerY, $headRadius, $headRadius, $textColor);

// Add body shape
$bodyWidth = $width / 3;
$bodyHeight = $height / 2;
$bodyX = $centerX - ($bodyWidth / 2);
$bodyY = $centerY + ($headRadius / 2);
imagefilledrectangle($image, $bodyX, $bodyY, $bodyX + $bodyWidth, $bodyY + $bodyHeight, $textColor);

// Save the image
$targetPath = 'assets/img/default-profile.png';
imagepng($image, $targetPath);
imagedestroy($image);

echo "Default profile image created at $targetPath";
?> 