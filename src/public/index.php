<?php

namespace Example;

use Exception;
use TypeError;
use Throwable;
use ErrorException;
use Error;

# Custom error handler for warnings and errors
set_error_handler(function ($errno, $errstr, $errfile, $errline) {
    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
}, E_ERROR | E_WARNING);

try {
    # Path to Composer's autoloader relative to public/index.php
    $composerPath = sprintf("%s/vendor/autoload.php", dirname(__DIR__, 2));

    # Verify Composer autoload file exists
    if (!file_exists($composerPath)) {
        throw new Exception("Please run `composer install` in the root directory");
    }

    require_once($composerPath);

    phpinfo();
    // xdebug_info();

} catch (Exception $ex) {
    error_log("Exception: " . $ex->getMessage());
    echo "An error occurred. Please check the logs.";
} catch (TypeError $ex) {
    error_log("TypeError: " . $ex->getMessage());
    echo "A type error occurred. Please check the logs.";
} catch (Throwable $ex) {
    error_log("Throwable: " . $ex->getMessage());
    echo "An unexpected error occurred. Please check the logs.";
} catch (ErrorException $ex) {
    error_log("ErrorException: " . $ex->getMessage());
    echo "A fatal error occurred. Please check the logs.";
} catch (Error $ex) {
    error_log("Error: " . $ex->getMessage());
    echo "An error occurred. Please check the logs.";
}
