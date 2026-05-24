<?php
$zipFilePath = 'vendor.zip'; // Specify the path to your zip file
$extractPath = '/'; // Specify the folder where you want to extract the contents

$zip = new ZipArchive();

if ($zip->open($zipFilePath) === true) {
    $zip->extractTo($extractPath);
    $zip->close();
    echo 'File successfully extracted.';
} else {
    echo 'Failed to extract the file.';
}
?>