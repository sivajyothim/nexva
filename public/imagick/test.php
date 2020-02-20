<?php 
error_reporting(E_ALL);
ini_set('display_errors', '1');

$image = new Imagick();
$draw = new ImagickDraw();
$pixel = new ImagickPixel( 'gray' );

/* New image */
$image->newImage(800, 75, $pixel);

/* Black text */
$draw->setFillColor('black');

/* Font properties */
$draw->setFontSize( 30 );

/* Create text */
$draw->annotation(10, 50, 'The quick brown fox jumps over the lazy dog');

/* Give image a format */
$image->drawimage($draw);
$image->setImageFormat('png');
/* Output the image with headers */
header('Content-type: image/png');
echo $image;