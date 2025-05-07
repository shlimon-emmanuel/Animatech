<?php
/**
 * Script to check and create necessary upload directories with proper permissions
 * Run this script if you're having issues with file uploads
 */

// Define directories to check/create
$directories = [
    'assets/uploads/',
    'assets/uploads/profiles/',
    'assets/images/',
    'assets/images/profiles/',
    'assets/img/'
];

echo "=======================================\n";
echo "CHECKING UPLOAD DIRECTORIES\n";
echo "=======================================\n\n";

// Check and create each directory
foreach($directories as $dir) {
    echo "Directory: $dir\n";
    echo "  Status: ";
    
    if(!file_exists($dir)) {
        echo "MISSING\n";
        echo "  Action: Creating directory... ";
        
        if(mkdir($dir, 0777, true)) {
            echo "SUCCESS\n";
        } else {
            echo "FAILED\n";
            echo "  Error: " . error_get_last()['message'] . "\n";
        }
    } else {
        echo "EXISTS\n";
        
        // Check permissions
        $perms = substr(sprintf('%o', fileperms($dir)), -4);
        echo "  Permissions: $perms\n";
        echo "  Writable: ";
        
        // Check if writable
        if(!is_writable($dir)) {
            echo "NO\n";
            echo "  Action: Updating permissions... ";
            
            if(chmod($dir, 0777)) {
                echo "SUCCESS\n";
            } else {
                echo "FAILED\n";
                echo "  Error: " . error_get_last()['message'] . "\n";
            }
        } else {
            echo "YES\n";
        }
    }
    echo "\n";
}

// Check default profile picture
echo "Default Profile Picture\n";
$defaultProfile = 'assets/img/default-profile.png';
echo "  Path: $defaultProfile\n";
echo "  Status: ";

if(!file_exists($defaultProfile)) {
    echo "MISSING\n";
    
    // Create a simple placeholder image if possible
    if(function_exists('imagecreate')) {
        echo "  Action: Creating placeholder image... ";
        
        $image = imagecreate(200, 200);
        $background = imagecolorallocate($image, 30, 30, 50);
        $textColor = imagecolorallocate($image, 157, 78, 221);
        
        // Draw user silhouette
        imagefilledellipse($image, 100, 80, 80, 80, $textColor);
        imagefilledrectangle($image, 60, 120, 140, 200, $textColor);
        
        if(imagepng($image, $defaultProfile)) {
            echo "SUCCESS\n";
        } else {
            echo "FAILED\n";
            echo "  Error: " . error_get_last()['message'] . "\n";
        }
        
        imagedestroy($image);
    } else {
        echo "\n  Error: GD library not available, can't create placeholder image.\n";
    }
} else {
    echo "EXISTS\n";
}

echo "\n=======================================\n";
echo "PHP CONFIGURATION\n";
echo "=======================================\n\n";

// Check PHP configuration
echo "  upload_max_filesize: " . ini_get('upload_max_filesize') . "\n";
echo "  post_max_size: " . ini_get('post_max_size') . "\n";
echo "  upload_tmp_dir: " . (ini_get('upload_tmp_dir') ?: 'system default') . "\n";
echo "  max_file_uploads: " . ini_get('max_file_uploads') . "\n";
echo "  memory_limit: " . ini_get('memory_limit') . "\n";

echo "\n=======================================\n";
echo "TROUBLESHOOTING TIPS\n";
echo "=======================================\n\n";

echo "If you're still having upload issues:\n\n";
echo "1. Make sure PHP has write permissions to these directories\n";
echo "2. Check if your web server (Apache/Nginx) has access to these paths\n";
echo "3. Increase upload_max_filesize and post_max_size in php.ini if needed\n";
echo "4. Verify the temporary upload directory is writable by PHP\n";
echo "5. Check the error logs for more detailed information\n";
echo "6. Try uploading smaller files first to test the system\n";
echo "7. Verify that file upload is enabled in PHP (file_uploads = On)\n";

?> 