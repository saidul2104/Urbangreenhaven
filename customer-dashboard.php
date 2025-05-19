<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.html');
    exit;
}

require_once 'config.php';

try {
    $pdo = new PDO(DB_DSN, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Get user information
    $stmt = $pdo->prepare('SELECT * FROM users WHERE id = :user_id');
    $stmt->execute(['user_id' => $_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Get user's orders
    $stmt = $pdo->prepare('
        SELECT o.*, 
        GROUP_CONCAT(
            CONCAT(
                p.name, 
                " (", 
                oi.quantity, 
                " x ৳", 
                oi.price,
                ")"
            ) 
            SEPARATOR ", "
        ) as items
        FROM orders o
        JOIN order_items oi ON o.id = oi.order_id
        JOIN products p ON oi.product_id = p.id
        WHERE o.user_id = :user_id
        GROUP BY o.id
        ORDER BY o.created_at DESC
    ');
    $stmt->execute(['user_id' => $_SESSION['user_id']]);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die('Database error: ' . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Dashboard - Urban Green Haven</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#f0fdf4',
                            100: '#dcfce7',
                            200: '#bbf7d0',
                            300: '#86efac',
                            400: '#4ade80',
                            500: '#22c55e',
                            600: '#16a34a',
                            700: '#15803d',
                            800: '#166534',
                            900: '#14532d',
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white shadow-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="index.html" class="flex-shrink-0 flex items-center">
                        <i class="fas fa-leaf text-primary-600 text-2xl mr-2"></i>
                        <span class="font-bold text-xl text-primary-700">Urban Green Haven</span>
                    </a>
                </div>
                <div class="hidden md:flex items-center space-x-8">
                    <a href="index.html" class="text-gray-600 hover:text-primary-500">Home</a>
                    <a href="shop.html" class="text-gray-600 hover:text-primary-500">Shop</a>
                    <a href="blog.html" class="text-gray-600 hover:text-primary-500">Blog</a>
                    <a href="qa.html" class="text-gray-600 hover:text-primary-500">Q&A</a>
                    <a href="consultation.html" class="text-gray-600 hover:text-primary-500">Consultation</a>
                    <a href="cart.html" class="text-gray-600 hover:text-primary-500">
                        <i class="fas fa-shopping-cart"></i>
                    </a>
                    <a href="javascript:void(0);" onclick="handleLogout()" class="text-gray-600 hover:text-primary-500">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Dashboard Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <!-- Sidebar -->
            <div class="md:col-span-1">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="text-center mb-6">
                        <div class="w-24 h-24 bg-primary-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-user text-4xl text-primary-600"></i>
                        </div>
                        <h2 class="text-xl font-semibold"><?php echo htmlspecialchars($user['name']); ?></h2>
                        <p class="text-gray-500"><?php echo htmlspecialchars($user['email']); ?></p>
                    </div>
                    <nav class="space-y-2">
                        <a href="#orders" class="block px-4 py-2 text-gray-700 hover:bg-primary-50 hover:text-primary-600 rounded">
                            <i class="fas fa-shopping-bag mr-2"></i> My Orders
                        </a>
                        <a href="#profile" class="block px-4 py-2 text-gray-700 hover:bg-primary-50 hover:text-primary-600 rounded">
                            <i class="fas fa-user mr-2"></i> Profile
                        </a>
                        <a href="#addresses" class="block px-4 py-2 text-gray-700 hover:bg-primary-50 hover:text-primary-600 rounded">
                            <i class="fas fa-map-marker-alt mr-2"></i> Addresses
                        </a>
                    </nav>
                </div>
            </div>

            <!-- Main Content -->
            <div class="md:col-span-3">
                <!-- Orders Section -->
                <section id="orders" class="bg-white rounded-lg shadow-md p-6 mb-6">
                    <h2 class="text-2xl font-semibold mb-6">My Orders</h2>
                    <?php if (empty($orders)): ?>
                        <div class="text-center py-8">
                            <i class="fas fa-shopping-bag text-gray-300 text-5xl mb-4"></i>
                            <h3 class="text-xl font-medium text-gray-700 mb-2">No orders yet</h3>
                            <p class="text-gray-500 mb-6">Start shopping to see your orders here.</p>
                            <a href="shop.html" class="inline-block bg-primary-600 hover:bg-primary-700 text-white font-medium py-2 px-6 rounded-md transition-colors duration-300">
                                Start Shopping
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="space-y-6">
                            <?php foreach ($orders as $order): ?>
                                <div class="border rounded-lg p-6">
                                    <div class="flex justify-between items-start mb-4">
                                        <div>
                                            <h3 class="text-lg font-medium">Order #<?php echo $order['id']; ?></h3>
                                            <p class="text-sm text-gray-500">
                                                Placed on <?php echo date('F d, Y', strtotime($order['created_at'])); ?>
                                            </p>
                                        </div>
                                        <span class="px-3 py-1 rounded-full text-sm
                                            <?php
                                            switch($order['status']) {
                                                case 'pending':
                                                    echo 'bg-yellow-100 text-yellow-800';
                                                    break;
                                                case 'accepted':
                                                    echo 'bg-green-100 text-green-800';
                                                    break;
                                                case 'processing':
                                                    echo 'bg-blue-100 text-blue-800';
                                                    break;
                                                case 'shipped':
                                                    echo 'bg-purple-100 text-purple-800';
                                                    break;
                                                case 'delivered':
                                                    echo 'bg-green-100 text-green-800';
                                                    break;
                                                case 'cancelled':
                                                    echo 'bg-red-100 text-red-800';
                                                    break;
                                            }
                                            ?>">
                                            <?php echo ucfirst($order['status']); ?>
                                        </span>
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                        <div>
                                            <h4 class="font-medium mb-2">Order Details</h4>
                                            <p class="text-sm text-gray-600"><?php echo htmlspecialchars($order['items']); ?></p>
                                        </div>
                                        <div>
                                            <h4 class="font-medium mb-2">Shipping Information</h4>
                                            <p class="text-sm text-gray-600">
                                                Address: <?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?><br>
                                                Phone: <?php echo htmlspecialchars($order['phone_number']); ?>
                                            </p>
                                        </div>
                                    </div>
                                    <div class="border-t pt-4">
                                        <div class="flex justify-between items-center">
                                            <span class="text-gray-600">Total Amount:</span>
                                            <span class="text-lg font-semibold">৳<?php echo number_format($order['total_amount'], 2); ?></span>
                                        </div>
                                        <div class="mt-2">
                                            <div class="text-sm text-gray-600">
                                                <span class="font-medium">Payment Method:</span> <?php echo htmlspecialchars($order['payment_method']); ?><br>
                                                <span class="font-medium">Transaction Number:</span> <?php echo htmlspecialchars($order['transaction_number']); ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="mt-6 text-center">
                            <a href="shop.html" class="inline-block bg-primary-600 hover:bg-primary-700 text-white font-medium py-2 px-6 rounded-md transition-colors duration-300">
                                Continue Shopping
                            </a>
                        </div>
                    <?php endif; ?>
                </section>

                <!-- Profile Section -->
                <section id="profile" class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-2xl font-semibold mb-6">Profile Information</h2>
                    <form class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Name</label>
                            <input type="text" value="<?php echo htmlspecialchars($user['name']); ?>" 
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Email</label>
                            <input type="email" value="<?php echo htmlspecialchars($user['email']); ?>" 
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Phone</label>
                            <input type="tel" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" 
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                        </div>
                        <div class="flex justify-end">
                            <button type="submit" class="bg-primary-600 text-white px-4 py-2 rounded hover:bg-primary-700">
                                Update Profile
                            </button>
                        </div>
                    </form>
                </section>
            </div>
        </div>
    </div>

    <script>
        // Add smooth scrolling for navigation
        document.querySelectorAll('nav a').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                const targetId = this.getAttribute('href').substring(1);
                const targetElement = document.getElementById(targetId);
                if (targetElement) {
                    targetElement.scrollIntoView({ behavior: 'smooth' });
                }
            });
        });

        // Handle logout
        function handleLogout() {
            if (confirm('Are you sure you want to logout?')) {
                window.location.href = 'logout.php';
            }
        }
    </script>
</body>
</html> 