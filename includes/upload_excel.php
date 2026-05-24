<?php

require 'vendor/autoload.php'; 
// Database connection parameters
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'test_excel';

// Create a database connection
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the form was submitted
if (isset($_POST["submit"])) {
    $targetDir = "uploads/"; // Adjust the directory as needed
    $targetFile = $targetDir . basename($_FILES["fileToUpload"]["name"]);
    $uploadOk = 1;
    $fileType = pathinfo($targetFile, PATHINFO_EXTENSION);

    // Check if the file is an Excel file
    if ($fileType != "xlsx" && $fileType != "xls") {
        echo "Only Excel files are allowed.";
        $uploadOk = 0;
    }

    if ($uploadOk == 0) {
        echo "Sorry, your file was not uploaded.";
    } else {
        if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $targetFile)) {
            echo "The file " . basename($_FILES["fileToUpload"]["name"]) . " has been uploaded.";

            // Load the uploaded Excel file
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($targetFile);
            $worksheet = $spreadsheet->getActiveSheet();

            // Get the column headers from the first row
            $headers = [];
            $cellIterator = $worksheet->getRowIterator()->current()->getCellIterator();
            foreach ($cellIterator as $cell) {
                $headers[] = $cell->getValue();
            }

            // Prepare and execute INSERT statements for each row
            foreach ($worksheet->getRowIterator(2) as $row) { // Start from the second row
                $cellIterator = $row->getCellIterator();
                $cellIterator->setIterateOnlyExistingCells(FALSE);

                $data = [];
                foreach ($cellIterator as $cell) {
                    $data[] = $cell->getValue();
                }

                // Assuming your Excel has columns named same as database columns
                $sql = "INSERT INTO test (" . implode(", ", $headers) . ") VALUES ('" . implode("', '", $data) . "')";
                if ($conn->query($sql) !== TRUE) {
                    echo "Error: " . $conn->error;
                }
            }
        } else {
            echo "Sorry, there was an error uploading your file.";
        }
    }
}

// Close the database connection
$conn->close();
?>
