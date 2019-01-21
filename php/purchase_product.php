<?php
require_once "db/index.php";
require_once "respond.php";

if (!isset($_POST["product_id"])) {
    error(400, "product_id expected");
}

$product_id = $_POST["product_id"];

if (!ctype_digit($product_id)) {
    error(400, "product_id must be an integer");
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

try {
    purchase_product($product_id, $quantity);
    $product = fetch_product($product_id);
    respond(200, [
        "product" => $product,
        "quantity_purchased" => $quantity,
    ]);
} catch (RowNotFoundException $exception) {
    error(404, $exception->getMessage());
} catch (InvalidOperationException $exception) {
    error(400, $exception->getMessage());
} catch (Exception $exception) {
    error(500, $exception->getMessage());
}
?>