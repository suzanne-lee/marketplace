<?php
require_once "db.php";
require_once "exception.php";
require_once "product.php";

function create_cart () {
    $db = getDb();
    $random_id = uniqid(mt_rand(), true);

    $statement = $db->prepare("
        INSERT INTO
            cart (external_cart_id)
        VALUES (
            :random_id
        )
    ");
    $executed = $statement->execute(["random_id" => $random_id]);
    if (!$executed) {
        throw new Exception("An unexpected error occurred; could not create cart");
    }
    return $random_id;
}

function fetch_cart_id ($external_cart_id) {
    $db = getDb();
    $statement = $db->prepare("
        SELECT
            cart_id
        FROM
            cart
        WHERE
            external_cart_id = :external_cart_id
    ");
    $statement->execute(["external_cart_id"=>$external_cart_id]);
    $cart = $statement->fetchObject();
    if (!$cart) {
        throw new RowNotFoundException("Cart not found");
    }
    return $cart->cart_id;
}

function fetch_cart ($external_cart_id) {
    $db = getDb();
    $cart_id = fetch_cart_id($external_cart_id);

    $statement = $db->prepare("
        SELECT
            c.quantity,
            p.title,
            p.price,
            p.inventory_count,
            p.product_id
        FROM
            cart_content c
        INNER JOIN
            product p
        ON
            c.product_id = p.product_id
        WHERE
            cart_id = :cart_id
    ");
    $statement->execute(["cart_id" => $cart_id]);
    $products = $statement->fetchAll(PDO::FETCH_OBJ);

    $total = 0;
    foreach ($products as $product) {
        $total += $product->price * $product->quantity;
    }
    return (object)[
        "total" => $total,
        "products" => $products,
        "external_cart_id" => $external_cart_id,
    ];
}

function add_to_cart ($external_cart_id, $product_id, $quantity) {
    $db = getDb();
    $cart_id = fetch_cart_id($external_cart_id);
    assert_product_exists($product_id);

    $statement = $db->prepare("
        INSERT INTO
            cart_content (cart_id, product_id, quantity)
        VALUES (
            :cart_id,
            :product_id,
            :quantity
        )
        ON DUPLICATE KEY UPDATE
            quantity = quantity + :quantity2
    ");
    $executed = $statement->execute([
        "cart_id" => $cart_id,
        "product_id" => $product_id,
        "quantity" => $quantity,
        "quantity2" => $quantity,
    ]);
    if (!$executed) {
        throw new Exception("An unexpected error occurred; could not add to cart");
    }
}

function remove_from_cart ($external_cart_id, $product_id) {
    $db = getDb();
    $cart_id = fetch_cart_id($external_cart_id);

    $statement = $db->prepare("
        DELETE FROM
            cart_content
        WHERE (
            cart_id = :cart_id AND
            product_id = :product_id
        )
    ");
    $executed = $statement->execute([
        "cart_id" => $cart_id,
        "product_id" => $product_id,
    ]);
    if (!$executed) {
        throw new Exception("An unexpected error occurred; could not remove from cart");
    }
}

function checkout_cart ($external_cart_id) {
    $db = getDb();

    $transactionStarted = $db->beginTransaction();
    if (!$transactionStarted) {
        throw new Exception("An unexpected error occurred; failed to enter transaction");
    }

    $cart = fetch_cart($external_cart_id);
    if (count($cart->products) == 0) {
        throw new InvalidOperationException("Cart is empty");
    }
    foreach ($cart->products as $product) {
        purchase_product($product->product_id, $product->quantity);
    }

    $committed = $db->commit();
    if (!$committed) {
        throw new Exception("An unexpected error occurred; failed to commit");
    }
}
?>