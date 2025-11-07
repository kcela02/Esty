<?php
/**
 * PHPMailer Installation Script for XAMPP
 * Run this script in your browser: http://localhost/Esty-main/ESTY/install_phpmailer.php
 */

$vendorDir = __DIR__ . '/vendor';
$phpmailerDir = $vendorDir . '/phpmailer';

if (!file_exists($vendorDir)) {
    mkdir($vendorDir, 0755, true);
}

echo "<h2>Installing PHPMailer...</h2>";

// Download PHPMailer from GitHub
$zipUrl = 'https://github.com/PHPMailer/PHPMailer/archive/refs/tags/v6.9.0.zip';
$zipFile = $vendorDir . '/phpmailer.zip';

echo "Downloading PHPMailer v6.9.0...<br>";
$fileContent = @file_get_contents($zipUrl);
if ($fileContent === false) {
    die("❌ Error: Could not download PHPMailer. Check your internet connection or try manual installation.<br><a href='https://github.com/PHPMailer/PHPMailer/releases/download/v6.9.0/PHPMailer-6.9.0.zip'>Download manually</a>");
}

file_put_contents($zipFile, $fileContent);
echo "✓ Downloaded successfully<br>";

// Extract zip
echo "Extracting files...<br>";
$zip = new ZipArchive;
if ($zip->open($zipFile) === true) {
    $zip->extractTo($vendorDir);
    $zip->close();
    
    // Move extracted folder to correct location
    $extractedDir = $vendorDir . '/PHPMailer-6.9.0';
    if (file_exists($extractedDir)) {
        // Create phpmailer/src directory structure
        if (!file_exists($phpmailerDir . '/src')) {
            mkdir($phpmailerDir . '/src', 0755, true);
        }
        
        // Copy src files
        $srcDir = $extractedDir . '/src';
        foreach (glob($srcDir . '/*.php') as $file) {
            copy($file, $phpmailerDir . '/src/' . basename($file));
        }
        
        // Copy Exception
        if (file_exists($srcDir . '/Exception.php')) {
            copy($srcDir . '/Exception.php', $phpmailerDir . '/src/Exception.php');
        }
        
        // Cleanup
        exec('rmdir /s /q "' . str_replace('/', '\\', $extractedDir) . '"');
    }
    
    unlink($zipFile);
    echo "✓ Extracted successfully<br>";
} else {
    die("❌ Error: Could not extract zip file.<br>");
}

// Create autoloader
$autoloadContent = '<?php
// Auto-loader for PHPMailer
if (!class_exists("PHPMailer\\PHPMailer\\PHPMailer")) {
    spl_autoload_register(function($class) {
        $prefix = "PHPMailer\\\\PHPMailer\\\\";
        if (strpos($class, $prefix) === 0) {
            $file = __DIR__ . "/src/" . str_replace($prefix, "", $class) . ".php";
            if (file_exists($file)) {
                require $file;
            }
        }
    });
}
?>';

file_put_contents($vendorDir . '/phpmailer/autoload.php', $autoloadContent);
echo "✓ Created autoloader<br>";

// Create main autoload
$mainAutoload = '<?php
// Main autoloader for vendor
spl_autoload_register(function($class) {
    $prefix = "PHPMailer\\\\PHPMailer\\\\";
    if (strpos($class, $prefix) === 0) {
        $file = __DIR__ . "/phpmailer/src/" . str_replace($prefix, "", $class) . ".php";
        if (file_exists($file)) {
            require $file;
        }
    }
});
?>';

file_put_contents($vendorDir . '/autoload.php', $mainAutoload);
echo "✓ Created main autoload.php<br>";

// Verify installation
if (file_exists($vendorDir . '/phpmailer/src/PHPMailer.php')) {
    echo "<h3 style='color: green;'>✓ PHPMailer installed successfully!</h3>";
    echo "<p>PHPMailer is now ready to use in your application.</p>";
    echo "<p><strong>Next steps:</strong></p>";
    echo "<ol>";
    echo "<li>Edit <code>ESTY/mail_settings.php</code> with your Gmail or Outlook credentials</li>";
    echo "<li>Test by registering a new account or logging in</li>";
    echo "</ol>";
} else {
    die("❌ Error: Installation failed. PHPMailer files not found.<br>");
}
?>
