<?php
require_once "db/index.php";
require_once "respond.php";

if (!isset($_POST["external_cart_id"])) {
    error(400, "external_cart_id expected");
}

$external_cart_id = $_POST["external_cart_id"];

try {
    checkout_cart($external_cart_id);
    respond(200, fetch_cart($external_cart_id));
} catch (RowNotFoundException $exception) {
    error(404, $exception->getMessage());
} catch (InvalidOperationException $exception) {
    error(400, $exception->getMessage());
} catch (Exception $exception) {
    error(500, $exception->getMessage());
}
?>