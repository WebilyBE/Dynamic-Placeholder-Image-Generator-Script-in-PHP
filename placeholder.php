<?php
// Get width and height from _GET parameters, ensuring they are within bounds
$maxWidth = 5000;
$maxHeight = 5000;
$width = isset($_GET['w']) ? min(intval($_GET['w']), $maxWidth) : 1000;
$height = isset($_GET['h']) ? min(intval($_GET['h']), $maxHeight) : 1000;

// Get color from _GET parameter, default to #ccc if not provided
$colorHex = isset($_GET['color']) ? $_GET['color'] : 'ccc';

// Get textColor from _GET parameter, default to null if not provided
$textColorHex = isset($_GET['textColor']) ? $_GET['textColor'] : null;

// Ensure the color is in the correct format (3 or 6 hex digits)
if (!preg_match('/^[a-fA-F0-9]{3}$|^[a-fA-F0-9]{6}$/', $colorHex)) {
    $colorHex = 'ccc';
}

// Ensure the textColor is in the correct format (3 or 6 hex digits)
if ($textColorHex && !preg_match('/^[a-fA-F0-9]{3}$|^[a-fA-F0-9]{6}$/', $textColorHex)) {
    $textColorHex = null;
}

// Convert the color to RGB
if (strlen($colorHex) == 3) {
    $colorHex = $colorHex[0].$colorHex[0].$colorHex[1].$colorHex[1].$colorHex[2].$colorHex[2];
}
$bg_red = hexdec(substr($colorHex, 0, 2));
$bg_green = hexdec(substr($colorHex, 2, 2));
$bg_blue = hexdec(substr($colorHex, 4, 2));

// Create a blank image with the specified dimensions
$image = imagecreatetruecolor($width, $height);

// Define the base color for the background
$bg_color = imagecolorallocate($image, $bg_red, $bg_green, $bg_blue);
imagefill($image, 0, 0, $bg_color);

// Calculate brightness of the background color
$brightness = ($bg_red * 299 + $bg_green * 587 + $bg_blue * 114) / 1000;

// Define text color based on user input or brightness of the background
if ($textColorHex) {
    // Use user-defined text color if provided
    $textColorRed = hexdec(substr($textColorHex, 0, 2));
    $textColorGreen = hexdec(substr($textColorHex, 2, 2));
    $textColorBlue = hexdec(substr($textColorHex, 4, 2));
} else {
    // Otherwise, calculate text color based on brightness of background
    if ($brightness < 128) {
        // Dark background, use a lighter text color
        $textColorRed = min(255, $bg_red + 50);
        $textColorGreen = min(255, $bg_green + 150);
        $textColorBlue = min(255, $bg_blue + 150);
    } else {
        // Light background, use a darker text color
        $textColorRed = max(0, $bg_red - 50);
        $textColorGreen = max(0, $bg_green - 50);
        $textColorBlue = max(0, $bg_blue - 50);
    }
}

// Define text to display
$text = "{$width} x {$height}";

// Determine text size and position based on image size
$textSizeFactor = min($width / 30, $height / 30); // Adjust the divisor to change text size relative to image size
$fontSize = (int)($textSizeFactor * 1.8);
$font = __DIR__ . '/arial.ttf'; // Path to the font file, ensure the font file exists

// Calculate text bounding box
$textBox = imagettfbbox($fontSize, 0, $font, $text);
$textWidth = $textBox[2] - $textBox[0];
$textHeight = $textBox[1] - $textBox[7];
$x = ($width - $textWidth) / 2;
$y = ($height - $textHeight) / 2 + $fontSize;

// Add the text to the image
imagettftext($image, $fontSize, 0, $x, $y, imagecolorallocate($image, $textColorRed, $textColorGreen, $textColorBlue), $font, $text);

// Set the content type header - the image will be PNG
header('Content-Type: image/png');
header('Content-Disposition: inline; filename="webily-' . $width . 'x' . $height . '--' . date("YHis") . '.png"');
header('Cache-Control: max-age=63072000'); // 2 years
header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 63072000) . ' GMT'); // 2 years
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
header('ETag: webilydesign');

// Output the image
imagepng($image);

// Free up memory
imagedestroy($image);
?>
