<?php
require_once "db/index.php";
require_once "respond.php";

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    error(405, "Only POST allowed");
}

try {
    $external_cart_id = create_cart();
    respond(200, fetch_cart($external_cart_id));
} catch (Exception $exception) {
    error(500, $exception->getMessage());
}
?>