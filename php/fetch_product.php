<?php
require_once "db/index.php";
require_once "respond.php";

if (!isset($_GET["product_id"])) {
    error(400, "product_id expected");
}
$product_id = $_GET["product_id"];

try {
    respond(200, fetch_product($product_id));
} catch (RowNotFoundException $exception) {
    error(404, $exception->getMessage());
} catch (Exception $exception) {
    error(500, $exception->getMessage());
}
?>