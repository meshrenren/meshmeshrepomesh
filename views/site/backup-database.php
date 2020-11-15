<?php

/* @var $this yii\web\View */
use app\helpers\Backup_Database;

$this->title = 'Backup Database';

ob_start();
// Report all errors
error_reporting(E_ALL);
// Set script max execution time
set_time_limit(900); // 15 minutes

if (php_sapi_name() != "cli") {
    echo '<div style="font-family: monospace;">';
}

//$host, $username, $passwd, $dbName, $charset
$backupDatabase = new Backup_Database($host, $username, $password, $db_name, "utf8");
$result = $backupDatabase->backupTables(TABLES, BACKUP_DIR) ? 'OK' : 'KO';
$backupDatabase->obfPrint('Backup result: ' . $result, 1);

// Use $output variable for further processing, for example to send it by email
//$output = $backupDatabase->getOutput();

if (php_sapi_name() != "cli") {
    echo '</div>';
}
ob_end_flush();
?>
