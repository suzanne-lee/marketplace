<?php
require_once "db/index.php";
require_once "respond.php";

if (!isset($_POST["product_id"])) {
    error(400, "product_id expected");
}

$product_id = $_POST["product_id"];

if (!ctype_digit($product_id)) {
    error(400, "product_id must an integer");
}

if (!isset($_POST["quantity"])) {
    error(400, "quantity expected");
}

$quantity = $_POST["quantity"];

if (!ctype_digit($quantity)) {
    error(400, "quantity must be an integer");
}

if ($quantity == 0) {
    error(400, "quantity must be greater than zero");
}

if (!isset($_POST["external_cart_id"])) {
    error(400, "external_cart_id expected");
}

$external_cart_id = $_POST["external_cart_id"];

try {
    add_to_cart($external_cart_id, $product_id, $quantity);
    respond(200, fetch_cart($external_cart_id));
} catch (RowNotFoundException $exception) {
    error(404, $exception->getMessage());
} catch (Exception $exception) {
    error(500, $exception->getMessage());
}
?>