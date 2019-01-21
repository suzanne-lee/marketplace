<?php
require_once "db/index.php";
require_once "respond.php";

$in_stock = isset($_GET["in_stock"]);

try {
    respond(200, fetch_all_products($in_stock));
} catch (Exception $exception) {
    error(500, $exception->getMessage());
}
?>