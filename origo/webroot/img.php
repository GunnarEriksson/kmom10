<?php
/**
 * This is a PHP skript to process images using PHP GD.
 *
 */

// Ensure error reporting is on
error_reporting(-1);              // Report all type of errors
ini_set('display_errors', 1);     // Display all errors
ini_set('output_buffering', 0);   // Do not buffer outputs, write directly

include('../src/CImage/Image.php');

$src = isset($_GET['src']) ? $_GET['src'] : null;
$params['verbose'] = isset($_GET['verbose']) ? true : null;
$params['saveAs'] = isset($_GET['save-as']) ? $_GET['save-as'] : null;
$params['quality'] = isset($_GET['quality']) ? $_GET['quality'] : 60;
$params['ignoreCache'] = isset($_GET['no-cache']) ? true : null;
$params['newWidth'] = isset($_GET['width']) ? $_GET['width'] : null;
$params['newHeight'] = isset($_GET['height']) ? $_GET['height'] : null;
$params['cropToFit'] = isset($_GET['crop-to-fit']) ? true : null;
$params['sharpen'] = isset($_GET['sharpen']) ? true : null;
$params['filterType'] = isset($_GET['filter-type']) ? $_GET['filter-type'] : null;

$image = new Image(__DIR__, $src);
$image->processAndOutputImage($params);
