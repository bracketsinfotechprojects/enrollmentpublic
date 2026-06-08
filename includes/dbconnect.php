<?php
$connection=mysqli_connect('localhost','root','','auztraining');

if (!defined('DB_HOST'))    define('DB_HOST',    'localhost');
if (!defined('DB_NAME'))    define('DB_NAME',    'auztraining');
if (!defined('DB_USER'))    define('DB_USER',    'root');
if (!defined('DB_PASS'))    define('DB_PASS',    '');
if (!defined('DB_CHARSET')) define('DB_CHARSET', 'utf8mb4');
if (!defined('APP_URL'))    define('APP_URL',    'http://localhost/assessment/enrollmentpublic');
// $connection= mysqli_connect("localhost","u593282393_enq_dash_new","U593282393_enq_dash_new","u593282393_enq_dash_new");
require_once(__DIR__ . '/mail_function.php');






// define('MAX_FILE_SIZE', 10 * 1024 * 1024); // 10MB


// Timezone
// date_default_timezone_set('UTC');

// Error reporting (set to 0 in production)
// error_reporting(E_ALL);
// ini_set('display_errors', 1);