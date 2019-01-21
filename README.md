### `marketplace`

This project uses a LAMP stack.

Users may,

+ List all products
+ List all products that are in stock (`inventory_count > 0`)
+ Fetch a single product
+ Purchase one of an item
+ Purchase more than one of an item
+ Create a cart
+ Add/remove items to/from a cart
+ Checkout a cart

### Assumptions

+ All products are in the same currency
+ Prices are in the smallest denomination (cents/won/yen/etc.)

### Improvements

+ Add online sales tax
+ Add taxable/non-taxable products
+ Subtract items from cart (instead of removing completely)
+ Add product images

### Database Installation

1. Log into mysql using `mysql -u root -p` (Or a different username)
2. Create a new database: `CREATE DATABASE marketplace`
3. `source sql/marketplace.sql`

### Database Configuration

1. `cp php/db/db_config.sample.php php/db/db_config.php`
2. `nano php/db/db_config.php`
3. Modify the `DB_PASSWORD` constant and any other constants as needed
4. Save and exit

## Endpoints

The `.htaccess` file only allows access to the endpoints listed below.

### Product Endpoints

+ `GET php/fetch_product?product_id=:int`

  Fetches a single product.

  `404` if product does not exist.

```json
{
    "product_id":1,
    "title":"MUJI Pen",
    "price":300,
    "inventory_count":10
}
```

-----

+ `GET php/fetch_all_products.php`

  Fetches all products.

```json
[
    {
        "product_id": 1,
        "title": "MUJI Pen",
        "price": 300,
        "inventory_count": 10
    },
    {
        "product_id": 2,
        "title": "Sour Keys",
        "price": 700,
        "inventory_count": 1
    },
    {
        "product_id": 3,
        "title": "Jeans",
        "price": 7500,
        "inventory_count": 12
    },
    {
        "product_id": 4,
        "title": "Nail Polish",
        "price": 500,
        "inventory_count": 0
    },
    {
        "product_id": 5,
        "title": "Laptop",
        "price": 100000,
        "inventory_count": 4
    },
    {
        "product_id": 6,
        "title": "Coconut Water",
        "price": 350,
        "inventory_count": 17
    }
]
```

-----

+ `GET php/fetch_all_products.php?in_stock`

  Fetches all products with `inventory_count > 0`.

```json
[
    {
        "product_id": 1,
        "title": "MUJI Pen",
        "price": 300,
        "inventory_count": 10
    },
    {
        "product_id": 2,
        "title": "Sour Keys",
        "price": 700,
        "inventory_count": 1
    },
    {
        "product_id": 3,
        "title": "Jeans",
        "price": 7500,
        "inventory_count": 12
    },
    {
        "product_id": 5,
        "title": "Laptop",
        "price": 100000,
        "inventory_count": 4
    },
    {
        "product_id": 6,
        "title": "Coconut Water",
        "price": 350,
        "inventory_count": 17
    }
]
```

-----

+ `POST php/purchase_product.php`

  Purchases a product.

  `404` if the product does not exist.

  `400` if purchasing more than what is in stock.

  Request Body: `product_id=3&quantity=6`

```json
{
    "product": {
        "product_id": 1,
        "title": "MUJI Pen",
        "price": 300,
        "inventory_count": 4
    },
    "quantity_purchased": "6"
}
```

-----

### Cart Endpoints

+ `POST php/create_cart.php`

  Creates a new cart.

  The `external_cart_id` must be stored by the client.

  Losing the `external_cart_id` means losing access to the cart.

```json
{
    "total":0,
    "products":[],
    "external_cart_id":"13276854085c45580a7f2b58.82049059"
}
```

-----

+ `GET php/fetch_cart.php?external_cart_id=:string`

  Fetches a cart.

  `404` if the cart does not exist.

```json
{
    "total":0,
    "products":[],
    "external_cart_id":"13276854085c45580a7f2b58.82049059"
}
```

-----

+ `POST php/add_to_cart.php`

  Adds a product to a cart.

  `404` if either the cart or product does not exist.

  Request Body: `product_id=:int&quantity=:int&external_cart_id=:string`

```json
{
    "total": 45500,
    "products": [
        {
            "quantity": 6,
            "title": "Jeans",
            "price": 7500,
            "inventory_count": 12,
            "product_id": 3
        },
        {
            "quantity": 1,
            "title": "Nail Polish",
            "price": 500,
            "inventory_count": 0,
            "product_id": 4
        }
    ],
    "external_cart_id": "5956249035c45444f92cc50.53190824"
}
```

-----

+ `POST php/remove_from_cart.php`

  Removes a product from a cart.

  `404` if the cart does not exist.

  Request Body: `product_id=:int&external_cart_id=:string`

```json
{
    "total": 45000,
    "products": [
        {
            "quantity": 6,
            "title": "Jeans",
            "price": 7500,
            "inventory_count": 12,
            "product_id": 3
        }
    ],
    "external_cart_id": "5956249035c45444f92cc50.53190824"
}
```

-----

+ `POST php/checkout_cart.php`

  Checks out a cart.

  `404` if the cart does not exist.

  `400` if the cart is empty.

  `400` if purchasing more than what is in stock.

  Request Body: `external_cart_id=:string`

```json
{
    "total": 45000,
    "products": [
        {
            "quantity": 6,
            "title": "Jeans",
            "price": 7500,
            "inventory_count": 6,
            "product_id": 3
        }
    ],
    "external_cart_id": "5956249035c45444f92cc50.53190824"
}
```