<?php
require_once "db.php";
require_once "exception.php";

function fetch_product ($product_id) {
    $db = getDb();
    $statement = $db->prepare("
        SELECT
            *
        FROM
            product
        WHERE
            product_id = :product_id
    ");

    $statement->execute(["product_id"=>$product_id]);
    $result = $statement->fetchObject();
    if (!$result) {
        throw new RowNotFoundException("Product not found");
    }
    return $result;
}

function fetch_all_products ($in_stock) {
    $db = getDb();
    if ($in_stock) {
        $statement = $db->prepare("
            SELECT
                *
            FROM
                product
            WHERE
                inventory_count > 0
        ");
    } else {
        $statement = $db->prepare("
            SELECT
                *
            FROM
                product
        ");
    }
    $statement->execute();
    $result = $statement->fetchAll(PDO::FETCH_OBJ);
    return $result;
}

function assert_product_exists ($product_id) {
    $db = getDb();
    $statement = $db->prepare("
        SELECT EXISTS(
            SELECT
                *
            FROM
                product
            WHERE
                product_id = :product_id
        ) AS `exists`
    ");

    $statement->execute(["product_id"=>$product_id]);
    $result = $statement->fetchObject();
    if (!$result || !$result->exists) {
        throw new RowNotFoundException("Product not found");
    }
}

function purchase_product ($product_id, $quantity) {
    if ($quantity <= 0) {
        throw new InvalidOperationException("Cannot purchase {$quantity} of {$product->title} (Min: 1)");
    }
    $db = getDb();

    $product = fetch_product($product_id);
    if ($product->inventory_count < $quantity) {
        throw new InvalidOperationException("Cannot purchase {$quantity} of {$product->title} (Max: {$product->inventory_count})");
    }

    $statement = $db->prepare("
        UPDATE
            product
        SET
            inventory_count = inventory_count - :quantity
        WHERE
            product_id = :product_id AND
            inventory_count >= :quantity2
    ");
    $executed = $statement->execute([
        "product_id"=>$product_id,
        "quantity"=>$quantity,
        "quantity2"=>$quantity,
    ]);
    if (!$executed) {
        throw new Exception("An unexpected error occurred; could not purchase product");
    }
    if ($statement->rowCount() == 0) {
        throw new InvalidOperationException("Coult not purchase {$quantity} of {$product->title}");
    }
}
?>