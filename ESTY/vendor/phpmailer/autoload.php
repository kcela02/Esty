<?php
// Auto-loader for PHPMailer
if (!class_exists("PHPMailer\PHPMailer\PHPMailer")) {
    spl_autoload_register(function($class) {
        $prefix = "PHPMailer\\PHPMailer\\";
        if (strpos($class, $prefix) === 0) {
            $file = __DIR__ . "/src/" . str_replace($prefix, "", $class) . ".php";
            if (file_exists($file)) {
                require $file;
            }
        }
    });
}
?>