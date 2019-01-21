<?php
require_once "db.php";
require_once "exception.php";
require_once "product.php";

/* Generates a random alphanumeric string,
 * which will act as an identifier for the cart
 */
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

/* Fetch the cart id of an existing cart
 * Throws error if the cart doesn't exist
 */
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

/* Fetch a cart by its external id if it exists
 * Calculates the total price of all the products in the cart
 */
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

/* Adds specified number of an item to a given cart
 */
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

/* Removes all quantities of an item from a cart
 */
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

/* "Complete" a cart if all items are available to be purchased
 */
function checkout_cart ($external_cart_id) {
    $db = getDb();

    //We use a transaction so that all purchases either succeed
    //or all fail
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