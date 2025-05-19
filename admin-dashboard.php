<?php
session_start();

if (!isset($_SESSION['user_id']) || !$_SESSION['is_admin']) {
    header('Location: login.html');
    exit;
}

require_once 'config.php';

try {
    $pdo = new PDO(DB_DSN, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fetch users
    $usersStmt = $pdo->query('SELECT id, name, email, phone, is_admin, created_at FROM users ORDER BY created_at DESC');
    $users = $usersStmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch products
    $productsStmt = $pdo->query('SELECT id, name, description, price, category, created_at FROM products ORDER BY created_at DESC');
    $products = $productsStmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch consultation requests
    $consultationsStmt = $pdo->query('
        SELECT 
            cr.id,
            cr.name,
            cr.email,
            cr.phone,
            cr.address,
            cr.consultation_type,
            cr.consultation_date,
            cr.consultation_time,
            cr.payment_method,
            cr.transaction_number,
            cr.status,
            cr.price,
            cr.created_at
        FROM consultation_requests cr
        ORDER BY cr.created_at DESC
    ');
    $consultations = $consultationsStmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch Q&A entries
    $qaStmt = $pdo->query('SELECT qa.id, u.name AS user_name, qa.question, qa.answer, qa.created_at FROM qa_entries qa JOIN users u ON qa.user_id = u.id ORDER BY qa.created_at DESC');
    $qaEntries = $qaStmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch orders with user details and items
    $ordersStmt = $pdo->query('
        SELECT o.*, u.name as user_name, u.email as user_email,
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
        JOIN users u ON o.user_id = u.id
        JOIN order_items oi ON o.id = oi.order_id
        JOIN products p ON oi.product_id = p.id
        GROUP BY o.id
        ORDER BY o.created_at DESC
    ');
    $orders = $ordersStmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die('Database error: ' . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Admin Dashboard - Urban Green Haven</title>
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

<body class="bg-gray-100 min-h-screen">
    <div class="flex">
        <!-- Sidebar -->
        <div class="w-64 bg-white shadow-lg h-screen fixed">
            <div class="p-4 border-b">
                <h2 class="text-xl font-bold text-primary-600">Admin Panel</h2>
            </div>
            <nav class="p-4">
                <ul class="space-y-2">
                    <li>
                        <a href="#dashboard" class="flex items-center p-2 text-gray-700 hover:bg-primary-50 hover:text-primary-600 rounded">
                            <i class="fas fa-home w-6"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li>
                        <a href="#users" class="flex items-center p-2 text-gray-700 hover:bg-primary-50 hover:text-primary-600 rounded">
                            <i class="fas fa-users w-6"></i>
                            <span>Users</span>
                        </a>
                    </li>
                    <li>
                        <a href="#orders" class="flex items-center p-2 text-gray-700 hover:bg-primary-50 hover:text-primary-600 rounded">
                            <i class="fas fa-shopping-cart w-6"></i>
                            <span>Orders</span>
                        </a>
                    </li>
                    <li>
                        <a href="#products" class="flex items-center p-2 text-gray-700 hover:bg-primary-50 hover:text-primary-600 rounded">
                            <i class="fas fa-box w-6"></i>
                            <span>Products</span>
                        </a>
                    </li>
                    <li>
                        <a href="#consultations" class="flex items-center p-2 text-gray-700 hover:bg-primary-50 hover:text-primary-600 rounded">
                            <i class="fas fa-comments w-6"></i>
                            <span>Consultations</span>
                        </a>
                    </li>
                    <li>
                        <a href="#qa" class="flex items-center p-2 text-gray-700 hover:bg-primary-50 hover:text-primary-600 rounded">
                            <i class="fas fa-question-circle w-6"></i>
                            <span>Q&A</span>
                        </a>
                    </li>
                    <li>
                        <a href="#blog" class="flex items-center p-2 text-gray-700 hover:bg-primary-50 hover:text-primary-600 rounded">
                            <i class="fas fa-blog w-6"></i>
                            <span>Blog</span>
                        </a>
                    </li>
                    <li>
                        <a href="javascript:void(0);" onclick="handleLogout()" class="flex items-center p-2 text-red-600 hover:bg-red-50 rounded">
                            <i class="fas fa-sign-out-alt w-6"></i>
                            <span>Logout</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="ml-64 flex-1 p-8">
            <!-- Dashboard Overview -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-white p-6 rounded-lg shadow">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-primary-100 text-primary-600">
                            <i class="fas fa-users text-2xl"></i>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-gray-500 text-sm">Total Users</h3>
                            <p class="text-2xl font-semibold"><?= count($users) ?></p>
                        </div>
                    </div>
                </div>
                <div class="bg-white p-6 rounded-lg shadow">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                            <i class="fas fa-shopping-cart text-2xl"></i>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-gray-500 text-sm">Total Orders</h3>
                            <p class="text-2xl font-semibold"><?= isset($orders) ? count($orders) : '0' ?></p>
                        </div>
                    </div>
                </div>
                <div class="bg-white p-6 rounded-lg shadow">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                            <i class="fas fa-comments text-2xl"></i>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-gray-500 text-sm">Consultations</h3>
                            <p class="text-2xl font-semibold"><?= count($consultations) ?></p>
                        </div>
                    </div>
                </div>
                <div class="bg-white p-6 rounded-lg shadow">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                            <i class="fas fa-box text-2xl"></i>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-gray-500 text-sm">Products</h3>
                            <p class="text-2xl font-semibold"><?= count($products) ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Users Section -->
            <section id="users" class="mb-8">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-2xl font-semibold">Users</h2>
                    <button class="bg-primary-600 text-white px-4 py-2 rounded hover:bg-primary-700">
                        <i class="fas fa-plus mr-2"></i>Add User
                    </button>
                </div>
                <div class="bg-white rounded-lg shadow overflow-x-auto">
                    <table class="min-w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="py-3 px-4 text-left text-sm font-medium text-gray-500">ID</th>
                                <th class="py-3 px-4 text-left text-sm font-medium text-gray-500">Name</th>
                                <th class="py-3 px-4 text-left text-sm font-medium text-gray-500">Email</th>
                                <th class="py-3 px-4 text-left text-sm font-medium text-gray-500">Phone</th>
                                <th class="py-3 px-4 text-left text-sm font-medium text-gray-500">Admin</th>
                                <th class="py-3 px-4 text-left text-sm font-medium text-gray-500">Created At</th>
                                <th class="py-3 px-4 text-left text-sm font-medium text-gray-500">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td class="py-3 px-4"><?= htmlspecialchars($user['id']) ?></td>
                                    <td class="py-3 px-4"><?= htmlspecialchars($user['name']) ?></td>
                                    <td class="py-3 px-4"><?= htmlspecialchars($user['email']) ?></td>
                                    <td class="py-3 px-4"><?= htmlspecialchars($user['phone']) ?></td>
                                    <td class="py-3 px-4"><?= $user['is_admin'] ? 'Yes' : 'No' ?></td>
                                    <td class="py-3 px-4"><?= htmlspecialchars($user['created_at']) ?></td>
                                    <td class="py-3 px-4">
                                        <button class="text-blue-600 hover:text-blue-800 mr-2">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="text-red-600 hover:text-red-800">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- Products Section -->
            <section id="products" class="mb-8">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-2xl font-semibold">Products</h2>
                    <button class="bg-primary-600 text-white px-4 py-2 rounded hover:bg-primary-700">
                        <i class="fas fa-plus mr-2"></i>Add Product
                    </button>
                </div>
                <div class="bg-white rounded-lg shadow overflow-x-auto">
                    <table class="min-w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="py-3 px-4 text-left text-sm font-medium text-gray-500">ID</th>
                                <th class="py-3 px-4 text-left text-sm font-medium text-gray-500">Name</th>
                                <th class="py-3 px-4 text-left text-sm font-medium text-gray-500">Description</th>
                                <th class="py-3 px-4 text-left text-sm font-medium text-gray-500">Price</th>
                                <th class="py-3 px-4 text-left text-sm font-medium text-gray-500">Category</th>
                                <th class="py-3 px-4 text-left text-sm font-medium text-gray-500">Created At</th>
                                <th class="py-3 px-4 text-left text-sm font-medium text-gray-500">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php foreach ($products as $product): ?>
                                <tr>
                                    <td class="py-3 px-4"><?= htmlspecialchars($product['id']) ?></td>
                                    <td class="py-3 px-4"><?= htmlspecialchars($product['name']) ?></td>
                                    <td class="py-3 px-4"><?= htmlspecialchars($product['description']) ?></td>
                                    <td class="py-3 px-4">$<?= number_format($product['price'], 2) ?></td>
                                    <td class="py-3 px-4"><?= htmlspecialchars($product['category']) ?></td>
                                    <td class="py-3 px-4"><?= htmlspecialchars($product['created_at']) ?></td>
                                    <td class="py-3 px-4">
                                        <button class="text-blue-600 hover:text-blue-800 mr-2">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="text-red-600 hover:text-red-800">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- Blog Section -->
            <section id="blog" class="mb-8">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-2xl font-semibold">Blog Posts</h2>
                    <button class="bg-primary-600 text-white px-4 py-2 rounded hover:bg-primary-700">
                        <i class="fas fa-plus mr-2"></i>New Blog Post
                    </button>
                </div>
                <div class="bg-white rounded-lg shadow p-6">
                    <form class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Title</label>
                            <input type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Content</label>
                            <textarea rows="6" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"></textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Featured Image</label>
                            <input type="file" class="mt-1 block w-full">
                        </div>
                        <div class="flex justify-end">
                            <button type="submit" class="bg-primary-600 text-white px-4 py-2 rounded hover:bg-primary-700">
                                Publish Post
                            </button>
                        </div>
                    </form>
                </div>
            </section>

            <!-- Consultations Section -->
            <section id="consultations" class="mb-8">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-2xl font-semibold">Consultation Requests</h2>
                </div>
                <div class="bg-white rounded-lg shadow overflow-x-auto">
                    <table class="min-w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="py-3 px-4 text-left text-sm font-medium text-gray-500">ID</th>
                                <th class="py-3 px-4 text-left text-sm font-medium text-gray-500">User</th>
                                <th class="py-3 px-4 text-left text-sm font-medium text-gray-500">Contact Info</th>
                                <th class="py-3 px-4 text-left text-sm font-medium text-gray-500">Consultation Type</th>
                                <th class="py-3 px-4 text-left text-sm font-medium text-gray-500">Date & Time</th>
                                <th class="py-3 px-4 text-left text-sm font-medium text-gray-500">Payment Info</th>
                                <th class="py-3 px-4 text-left text-sm font-medium text-gray-500">Price</th>
                                <th class="py-3 px-4 text-left text-sm font-medium text-gray-500">Status</th>
                                <th class="py-3 px-4 text-left text-sm font-medium text-gray-500">Created At</th>
                                <th class="py-3 px-4 text-left text-sm font-medium text-gray-500">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php foreach ($consultations as $consult): ?>
                                <tr>
                                    <td class="py-3 px-4"><?= htmlspecialchars($consult['id']) ?></td>
                                    <td class="py-3 px-4">
                                        <div class="font-medium"><?= htmlspecialchars($consult['name']) ?></div>
                                        <div class="text-sm text-gray-500"><?= htmlspecialchars($consult['email']) ?></div>
                                    </td>
                                    <td class="py-3 px-4">
                                        <div class="text-sm">
                                            <div>Phone: <?= htmlspecialchars($consult['phone']) ?></div>
                                            <div class="text-gray-500"><?= htmlspecialchars($consult['address']) ?></div>
                                        </div>
                                    </td>
                                    <td class="py-3 px-4"><?= htmlspecialchars($consult['consultation_type']) ?></td>
                                    <td class="py-3 px-4">
                                        <div class="text-sm">
                                            <div><?= date('M d, Y', strtotime($consult['consultation_date'])) ?></div>
                                            <div class="text-gray-500"><?= date('h:i A', strtotime($consult['consultation_time'])) ?></div>
                                        </div>
                                    </td>
                                    <td class="py-3 px-4">
                                        <div class="text-sm">
                                            <div>Method: <?= htmlspecialchars($consult['payment_method']) ?></div>
                                            <div class="text-gray-500">TXN: <?= htmlspecialchars($consult['transaction_number']) ?></div>
                                        </div>
                                    </td>
                                    <td class="py-3 px-4">৳<?= number_format($consult['price'], 2) ?></td>
                                    <td class="py-3 px-4">
                                        <select onchange="updateConsultationStatus(<?= $consult['id'] ?>, this.value)" 
                                                class="rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                                            <option value="pending" <?= $consult['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                                            <option value="accepted" <?= $consult['status'] === 'accepted' ? 'selected' : '' ?>>Accepted</option>
                                            <option value="completed" <?= $consult['status'] === 'completed' ? 'selected' : '' ?>>Completed</option>
                                            <option value="cancelled" <?= $consult['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                        </select>
                                    </td>
                                    <td class="py-3 px-4">
                                        <div class="text-sm">
                                            <?= date('M d, Y', strtotime($consult['created_at'])) ?>
                                            <div class="text-xs text-gray-500">
                                                <?= date('h:i A', strtotime($consult['created_at'])) ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3 px-4">
                                        <button onclick="deleteConsultation(<?= $consult['id'] ?>)" 
                                                class="text-red-600 hover:text-red-800">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- Orders Section -->
            <section id="orders" class="mb-8">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-2xl font-semibold">Orders</h2>
                </div>
                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="overflow-x-auto">
                        <table class="min-w-full">
                            <thead>
                                <tr class="bg-gray-100">
                                    <th class="px-4 py-2 text-left">Order ID</th>
                                    <th class="px-4 py-2 text-left">Customer Details</th>
                                    <th class="px-4 py-2 text-left">Contact Info</th>
                                    <th class="px-4 py-2 text-left">Order Items</th>
                                    <th class="px-4 py-2 text-left">Total Amount</th>
                                    <th class="px-4 py-2 text-left">Shipping Address</th>
                                    <th class="px-4 py-2 text-left">Payment Info</th>
                                    <th class="px-4 py-2 text-left">Status</th>
                                    <th class="px-4 py-2 text-left">Order Date</th>
                                    <th class="px-4 py-2 text-left">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($orders as $order): ?>
                                <tr class="border-b">
                                    <td class="px-4 py-2">#<?php echo $order['id']; ?></td>
                                    <td class="px-4 py-2">
                                        <div class="font-medium"><?php echo htmlspecialchars($order['customer_name']); ?></div>
                                        <div class="text-sm text-gray-500"><?php echo htmlspecialchars($order['customer_email']); ?></div>
                                    </td>
                                    <td class="px-4 py-2">
                                        <div class="text-sm">
                                            <div class="font-medium">Phone:</div>
                                            <div class="text-gray-600"><?php echo htmlspecialchars($order['phone_number']); ?></div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-2">
                                        <div class="text-sm">
                                            <?php echo htmlspecialchars($order['items']); ?>
                                        </div>
                                    </td>
                                    <td class="px-4 py-2">
                                        <div class="font-medium">৳<?php echo number_format($order['total_amount'], 2); ?></div>
                                        <div class="text-xs text-gray-500">Including shipping</div>
                                    </td>
                                    <td class="px-4 py-2">
                                        <div class="text-sm text-gray-600">
                                            <?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?>
                                        </div>
                                    </td>
                                    <td class="px-4 py-2">
                                        <div class="text-sm">
                                            <div class="font-medium">Method:</div>
                                            <div class="text-gray-600"><?php echo htmlspecialchars($order['payment_method']); ?></div>
                                            <div class="font-medium mt-1">Transaction:</div>
                                            <div class="text-gray-600"><?php echo htmlspecialchars($order['transaction_number']); ?></div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-2">
                                        <select onchange="updateOrderStatus(<?php echo $order['id']; ?>, this.value)" 
                                                class="rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                                            <option value="pending" <?php echo $order['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                            <option value="accepted" <?php echo $order['status'] === 'accepted' ? 'selected' : ''; ?>>Accepted</option>
                                            <option value="processing" <?php echo $order['status'] === 'processing' ? 'selected' : ''; ?>>Processing</option>
                                            <option value="completed" <?php echo $order['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                            <option value="cancelled" <?php echo $order['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                        </select>
                                    </td>
                                    <td class="px-4 py-2">
                                        <div class="text-sm">
                                            <?php echo date('M d, Y', strtotime($order['created_at'])); ?>
                                            <div class="text-xs text-gray-500">
                                                <?php echo date('h:i A', strtotime($order['created_at'])); ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-2">
                                        <button onclick="deleteOrder(<?php echo $order['id']; ?>)" 
                                                class="text-red-600 hover:text-red-800">
                                            Delete
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <!-- Q&A Section -->
            <section id="qa" class="bg-white rounded-lg shadow-md p-6 mb-6">
                <h2 class="text-2xl font-semibold mb-6">Questions & Answers</h2>
                
                <div class="space-y-6">
                    <?php
                    // Fetch questions with their answers
                    $stmt = $pdo->query('
                        SELECT q.*, 
                        GROUP_CONCAT(
                            JSON_OBJECT(
                                "id", a.id,
                                "answer", a.answer,
                                "name", a.name,
                                "created_at", a.created_at
                            )
                        ) as answers
                        FROM questions q
                        LEFT JOIN answers a ON q.id = a.question_id
                        GROUP BY q.id
                        ORDER BY q.created_at DESC
                    ');
                    
                    $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    foreach ($questions as $question):
                        // Process answers JSON
                        $answers = $question['answers'] ? json_decode('[' . $question['answers'] . ']', true) : [];
                    ?>
                        <div class="border rounded-lg p-6">
                            <div class="flex justify-between items-start mb-4">
                                <div>
                                    <h3 class="text-lg font-medium">Question #<?php echo $question['id']; ?></h3>
                                    <p class="text-sm text-gray-500">
                                        Asked by <?php echo htmlspecialchars($question['name']); ?> 
                                        (<?php echo htmlspecialchars($question['email']); ?>) on 
                                        <?php echo date('F d, Y', strtotime($question['created_at'])); ?>
                                    </p>
                                </div>
                                <div class="flex space-x-2">
                                    <button onclick="deleteQuestion(<?php echo $question['id']; ?>)" 
                                            class="text-red-600 hover:text-red-700">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <p class="text-gray-800"><?php echo nl2br(htmlspecialchars($question['question'])); ?></p>
                            </div>

                            <!-- Answers Section -->
                            <div class="mt-6">
                                <h4 class="font-medium mb-4">Answers</h4>
                                <?php if (empty($answers)): ?>
                                    <p class="text-gray-500 italic">No answers yet</p>
                                <?php else: ?>
                                    <div class="space-y-4">
                                        <?php foreach ($answers as $answer): ?>
                                            <div class="bg-gray-50 rounded-lg p-4">
                                                <div class="flex justify-between items-start">
                                                    <div>
                                                        <p class="text-gray-800"><?php echo nl2br(htmlspecialchars($answer['answer'])); ?></p>
                                                        <p class="text-sm text-gray-500 mt-2">
                                                            Answered by <?php echo htmlspecialchars($answer['name']); ?> on 
                                                            <?php echo date('F d, Y', strtotime($answer['created_at'])); ?>
                                                        </p>
                                                    </div>
                                                    <button onclick="deleteAnswer(<?php echo $answer['id']; ?>)" 
                                                            class="text-red-600 hover:text-red-700">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
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

        // Order management functions
        function updateOrderStatus(orderId, status) {
            if (!confirm('Are you sure you want to update the order status?')) {
                return;
            }

            const formData = new FormData();
            formData.append('order_id', orderId);
            formData.append('status', status);

            fetch('update-order-status.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Order status updated successfully');
                    location.reload(); // Refresh the page to show updated status
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while updating the order status');
            });
        }

        function deleteOrder(orderId) {
            if (!confirm('Are you sure you want to delete this order?')) {
                return;
            }

            fetch('delete-order.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    order_id: orderId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Error deleting order: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while deleting the order');
            });
        }

        function handleLogout() {
            if (confirm('Are you sure you want to logout?')) {
                window.location.href = 'logout.php';
            }
        }

        function updateConsultationStatus(consultationId, status) {
            if (!confirm('Are you sure you want to update the consultation status?')) {
                return;
            }

            const formData = new FormData();
            formData.append('consultation_id', consultationId);
            formData.append('status', status);

            fetch('update-consultation-status.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Consultation status updated successfully');
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while updating the consultation status');
            });
        }

        function deleteConsultation(consultationId) {
            if (!confirm('Are you sure you want to delete this consultation request?')) {
                return;
            }

            fetch('delete-consultation.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    consultation_id: consultationId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Error deleting consultation: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while deleting the consultation');
            });
        }

        // Delete question
        function deleteQuestion(questionId) {
            if (confirm('Are you sure you want to delete this question? This will also delete all associated answers.')) {
                fetch('admin-process.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=delete_question&question_id=${questionId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert(data.message || 'Error deleting question');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while deleting the question');
                });
            }
        }

        // Delete answer
        function deleteAnswer(answerId) {
            if (confirm('Are you sure you want to delete this answer?')) {
                fetch('admin-process.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=delete_answer&answer_id=${answerId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert(data.message || 'Error deleting answer');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while deleting the answer');
                });
            }
        }
    </script>
</body>

</html>