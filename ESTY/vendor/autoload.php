<?php
// Main autoloader for vendor
spl_autoload_register(function($class) {
    $prefix = "PHPMailer\\PHPMailer\\";
    if (strpos($class, $prefix) === 0) {
        $file = __DIR__ . "/phpmailer/src/" . str_replace($prefix, "", $class) . ".php";
        if (file_exists($file)) {
            require $file;
        }
    }
});
?>