<?php
require_once "db_config.php";

$db_host = DB_HOST;
$db_user = DB_USER;
$db_password = DB_PASSWORD;
$db_name = DB_NAME;

$db_connection = new PDO(
    "mysql:host=$db_host;dbname=$db_name;charset=utf8mb4",
    $db_user,
    $db_password,
    [
        PDO::ATTR_EMULATE_PREPARES => false
    ]
);

$db_connection->setAttribute(
    PDO::ATTR_ERRMODE,
    PDO::ERRMODE_EXCEPTION
);

return $db_connection;
function getDB() {
    global $db_connection;
    return $db_connection;
}
?>