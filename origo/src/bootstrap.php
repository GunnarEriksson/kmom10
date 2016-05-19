<?php
/**
 * Bootstrapping functions, essential and needed for Origo to work together with some common helpers.
 *
 */

/**
 * Default exception handler.
 *
 */
function myExceptionHandler($exception)
{
    echo "Origo: Uncaught exception: <p>" . $exception->getMessage() . "</p><pre>" . $exception->getTraceAsString(), "</pre>";
}
set_exception_handler('myExceptionHandler');


/**
 * Autoloader for classes.
 *
 */
function myAutoloader($class)
{
    $isFileFound = false;
    $dir = ORIGO_INSTALL_PATH . "/src/*";
    $dirs = array_filter(glob($dir), 'is_dir');
    foreach ($dirs as $dir) {
        $path = $dir . "/{$class}.php";
        if(is_file($path)) {
          include($path);
          $isFileFound = true;
        }
    }

    if (!$isFileFound) {
        throw new Exception("Classfile '{$class}' does not exists.");
    }
}

spl_autoload_register('myAutoloader');

function dump($array)
{
  echo "<pre>" . htmlentities(print_r($array, 1)) . "</pre>";
}
