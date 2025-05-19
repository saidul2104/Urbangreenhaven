<?php
session_start();
header('Content-Type: application/json');

require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in.']);
    exit;
}

$user_id = $_SESSION['user_id'];
$action = $_GET['action'] ?? $_POST['action'] ?? '';

try {
    $pdo = new PDO(DB_DSN, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    switch ($action) {
        case 'add':
            $product_id = intval($_POST['product_id'] ?? 0);
            $quantity = intval($_POST['quantity'] ?? 1);
            
            if ($product_id <= 0 || $quantity <= 0) {
                throw new Exception('Invalid product or quantity.');
            }

            // Check if product exists
            $stmt = $pdo->prepare('SELECT id FROM products WHERE id = :product_id');
            $stmt->execute(['product_id' => $product_id]);
            if (!$stmt->fetch()) {
                throw new Exception('Product not found.');
            }

            // Check if product already in cart
            $stmt = $pdo->prepare('SELECT id, quantity FROM cart WHERE user_id = :user_id AND product_id = :product_id');
            $stmt->execute(['user_id' => $user_id, 'product_id' => $product_id]);
            $cart_item = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($cart_item) {
                // Update quantity
                $new_quantity = $cart_item['quantity'] + $quantity;
                $stmt = $pdo->prepare('UPDATE cart SET quantity = :quantity WHERE id = :id');
                $stmt->execute(['quantity' => $new_quantity, 'id' => $cart_item['id']]);
            } else {
                // Insert new cart item
                $stmt = $pdo->prepare('INSERT INTO cart (user_id, product_id, quantity) VALUES (:user_id, :product_id, :quantity)');
                $stmt->execute(['user_id' => $user_id, 'product_id' => $product_id, 'quantity' => $quantity]);
            }

            echo json_encode(['success' => true, 'message' => 'Product added to cart.']);
            break;

        case 'update':
            $product_id = intval($_POST['product_id'] ?? 0);
            $is_increase = $_POST['increase'] === '1';
            
            if ($product_id <= 0) {
                throw new Exception('Invalid product.');
            }

            // Get current quantity
            $stmt = $pdo->prepare('SELECT id, quantity FROM cart WHERE user_id = :user_id AND product_id = :product_id');
            $stmt->execute(['user_id' => $user_id, 'product_id' => $product_id]);
            $cart_item = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$cart_item) {
                throw new Exception('Item not found in cart.');
            }

            $new_quantity = $is_increase ? $cart_item['quantity'] + 1 : $cart_item['quantity'] - 1;
            
            if ($new_quantity <= 0) {
                // Remove item if quantity becomes 0
                $stmt = $pdo->prepare('DELETE FROM cart WHERE id = :id');
                $stmt->execute(['id' => $cart_item['id']]);
            } else {
                // Update quantity
                $stmt = $pdo->prepare('UPDATE cart SET quantity = :quantity WHERE id = :id');
                $stmt->execute(['quantity' => $new_quantity, 'id' => $cart_item['id']]);
            }

            echo json_encode(['success' => true, 'message' => 'Cart updated.']);
            break;

        case 'remove':
            $product_id = intval($_POST['product_id'] ?? 0);
            
            if ($product_id <= 0) {
                throw new Exception('Invalid product.');
            }

            $stmt = $pdo->prepare('DELETE FROM cart WHERE user_id = :user_id AND product_id = :product_id');
            $stmt->execute(['user_id' => $user_id, 'product_id' => $product_id]);

            echo json_encode(['success' => true, 'message' => 'Item removed from cart.']);
            break;

        case 'clear':
            $stmt = $pdo->prepare('DELETE FROM cart WHERE user_id = :user_id');
            $stmt->execute(['user_id' => $user_id]);

            echo json_encode(['success' => true, 'message' => 'Cart cleared.']);
            break;

        case 'get':
            $stmt = $pdo->prepare('
                SELECT c.id, c.product_id, c.quantity, p.name, p.description, p.price, p.image_url
                FROM cart c
                JOIN products p ON c.product_id = p.id
                WHERE c.user_id = :user_id
            ');
            $stmt->execute(['user_id' => $user_id]);
            $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode(['success' => true, 'items' => $items]);
            break;

        case 'count':
            // Get cart item count for the user
            $stmt = $pdo->prepare('SELECT SUM(quantity) as cart_count FROM cart WHERE user_id = ?');
            $stmt->execute([$user_id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $cart_count = (int)($row['cart_count'] ?? 0);
            echo json_encode(['success' => true, 'cart_count' => $cart_count]);
            exit;

        default:
            throw new Exception('Invalid action.');
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>