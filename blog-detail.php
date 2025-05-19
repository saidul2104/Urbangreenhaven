<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blog Detail - Urban Green Haven</title>
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
                    },
                    fontFamily: {
                        sans: ['Poppins', 'sans-serif'],
                    },
                }
            }
        }
    </script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

        body {
            font-family: 'Poppins', sans-serif;
        }
    </style>
</head>

<body class="bg-gray-50">
    <?php
    // Database connection
    $db = new mysqli('localhost', 'root', '', 'urban_green_haven');
    if ($db->connect_error) {
        die("Connection failed: " . $db->connect_error);
    }

    // Get blog ID from URL
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

    // Fetch blog post
    $stmt = $db->prepare("SELECT * FROM blog_posts WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $post = $result->fetch_assoc();

    if (!$post) {
        header("Location: blog.html");
        exit();
    }
    ?>

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
                    <a href="index.html" class="text-gray-600 hover:text-primary-500 transition-colors">Home</a>
                    <a href="shop.html" class="text-gray-600 hover:text-primary-500 transition-colors">Shop</a>
                    <a href="blog.html" class="text-primary-700 font-medium hover:text-primary-500 transition-colors">Blog</a>
                    <a href="qa.html" class="text-gray-600 hover:text-primary-500 transition-colors">Q&A</a>
                    <a href="consultation.html" class="text-gray-600 hover:text-primary-500 transition-colors">Consultation</a>
                    <a href="cart.html" class="text-gray-600 hover:text-primary-500 transition-colors relative">
                        <i class="fas fa-shopping-cart"></i>
                        <span class="absolute -top-2 -right-2 bg-primary-500 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs" id="cart-count">0</span>
                    </a>
                    <a href="login.html" class="text-gray-600 hover:text-primary-500 transition-colors">
                        <i class="fas fa-user"></i>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Blog Content -->
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <article class="bg-white rounded-lg shadow-md overflow-hidden">
            <img src="<?php echo htmlspecialchars($post['image_url']); ?>" alt="<?php echo htmlspecialchars($post['title']); ?>" class="w-full h-96 object-cover">
            <div class="p-8">
                <div class="flex items-center text-sm text-gray-500 mb-4">
                    <i class="far fa-calendar-alt mr-2"></i>
                    <span><?php echo date('F j, Y', strtotime($post['created_at'])); ?></span>
                    <span class="mx-2">•</span>
                    <i class="far fa-user mr-2"></i>
                    <span><?php echo htmlspecialchars($post['author']); ?></span>
                    <span class="mx-2">•</span>
                    <i class="far fa-folder mr-2"></i>
                    <span><?php echo htmlspecialchars($post['category']); ?></span>
                </div>
                <h1 class="text-3xl font-bold text-gray-800 mb-6"><?php echo htmlspecialchars($post['title']); ?></h1>
                <div class="prose max-w-none">
                    <?php echo $post['content']; ?>
                </div>
            </div>
        </article>

        <!-- Share Buttons -->
        <div class="mt-8 flex items-center space-x-4">
            <span class="text-gray-600">Share this article:</span>
            <a href="#" class="text-gray-400 hover:text-primary-500 transition-colors">
                <i class="fab fa-facebook-f"></i>
            </a>
            <a href="#" class="text-gray-400 hover:text-primary-500 transition-colors">
                <i class="fab fa-twitter"></i>
            </a>
            <a href="#" class="text-gray-400 hover:text-primary-500 transition-colors">
                <i class="fab fa-linkedin-in"></i>
            </a>
        </div>

        <!-- Back to Blog -->
        <div class="mt-8">
            <a href="blog.html" class="inline-flex items-center text-primary-600 hover:text-primary-700">
                <i class="fas fa-arrow-left mr-2"></i>
                Back to Blog
            </a>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white pt-16 pb-8 mt-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="border-t border-gray-700 pt-8 flex flex-col md:flex-row justify-between items-center">
                <p class="text-gray-400 text-sm mb-4 md:mb-0">
                    &copy; 2025 Urban Green Haven. All rights reserved.
                </p>
            </div>
        </div>
    </footer>

    <script>
        // Cart count update
        document.addEventListener('DOMContentLoaded', function () {
            let cart = JSON.parse(localStorage.getItem('cart')) || [];
            updateCartCount();

            function updateCartCount() {
                const cartCount = document.getElementById('cart-count');
                const totalItems = cart.reduce((total, item) => total + item.quantity, 0);
                cartCount.textContent = totalItems;
            }
        });
    </script>
</body>

</html> 