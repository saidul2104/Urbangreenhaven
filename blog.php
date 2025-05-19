<?php
// Database connection
$db = new mysqli('localhost', 'root', '', 'urban_green_haven');
if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}

// Get all blog posts
$stmt = $db->prepare("SELECT * FROM blog_posts ORDER BY created_at DESC");
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blog - Urban Green Haven</title>
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
    <!-- Navigation -->
    <nav class="bg-white shadow-md fixed top-0 left-0 right-0 z-50">
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
                    <a href="blog.php" class="text-primary-700 font-medium hover:text-primary-500 transition-colors">Blog</a>
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

    <!-- Add padding to body to account for fixed header -->
    <div class="pt-16">
        <!-- Blog Header -->
        <div class="bg-primary-600 py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <h1 class="text-3xl font-bold text-white">Blog</h1>
                <p class="mt-2 text-primary-100">Tips, tricks, and insights for your rooftop garden</p>
            </div>
        </div>

        <!-- Blog Content -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="flex flex-col lg:flex-row gap-8">
                <!-- Main Content -->
                <div class="lg:w-2/3">
                    <?php
                    $first = true;
                    while ($post = $result->fetch_assoc()):
                        if ($first):
                            // Featured post
                    ?>
                    <div class="bg-white rounded-lg shadow-md overflow-hidden mb-10">
                        <img src="<?php echo htmlspecialchars($post['image_url']); ?>" alt="<?php echo htmlspecialchars($post['title']); ?>" class="w-full h-80 object-cover">
                        <div class="p-6">
                            <div class="flex items-center text-sm text-gray-500 mb-2">
                                <i class="far fa-calendar-alt mr-2"></i>
                                <span><?php echo date('F j, Y', strtotime($post['created_at'])); ?></span>
                                <span class="mx-2">•</span>
                                <i class="far fa-user mr-2"></i>
                                <span><?php echo htmlspecialchars($post['author']); ?></span>
                                <span class="mx-2">•</span>
                                <i class="far fa-folder mr-2"></i>
                                <span><?php echo htmlspecialchars($post['category']); ?></span>
                            </div>
                            <h2 class="text-2xl font-bold text-gray-800 mb-4"><?php echo htmlspecialchars($post['title']); ?></h2>
                            <p class="text-gray-600 mb-6"><?php echo substr(strip_tags($post['content']), 0, 200) . '...'; ?></p>
                            <a href="blog-detail.php?id=<?php echo $post['id']; ?>" class="inline-block bg-primary-600 hover:bg-primary-700 text-white font-medium py-2 px-4 rounded-md transition-colors duration-300">
                                Read Full Article
                            </a>
                        </div>
                    </div>
                    <?php
                            $first = false;
                        else:
                    ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div class="bg-white rounded-lg shadow-md overflow-hidden transition-transform duration-300 hover:transform hover:scale-105">
                            <img src="<?php echo htmlspecialchars($post['image_url']); ?>" alt="<?php echo htmlspecialchars($post['title']); ?>" class="w-full h-48 object-cover">
                            <div class="p-6">
                                <div class="flex items-center text-xs text-gray-500 mb-2">
                                    <i class="far fa-calendar-alt mr-2"></i>
                                    <span><?php echo date('F j, Y', strtotime($post['created_at'])); ?></span>
                                </div>
                                <h3 class="text-xl font-semibold text-gray-800 mb-3"><?php echo htmlspecialchars($post['title']); ?></h3>
                                <p class="text-gray-600 text-sm mb-4"><?php echo substr(strip_tags($post['content']), 0, 150) . '...'; ?></p>
                                <a href="blog-detail.php?id=<?php echo $post['id']; ?>" class="text-primary-600 hover:text-primary-700 font-medium text-sm">Read More →</a>
                            </div>
                        </div>
                    </div>
                    <?php
                        endif;
                    endwhile;
                    ?>
                </div>

                <!-- Sidebar -->
                <div class="lg:w-1/3">
                    <!-- Search -->
                    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Search</h3>
                        <form action="blog-search.php" method="GET" class="flex">
                            <input type="text" name="query" placeholder="Search articles..."
                                class="flex-grow px-4 py-2 border border-gray-300 rounded-l-md focus:outline-none focus:ring-primary-500 focus:border-primary-500">
                            <button type="submit"
                                class="bg-primary-600 hover:bg-primary-700 text-white px-4 py-2 rounded-r-md transition-colors">
                                <i class="fas fa-search"></i>
                            </button>
                        </form>
                    </div>

                    <!-- Categories -->
                    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Categories</h3>
                        <ul class="space-y-2">
                            <?php
                            $categories = array();
                            $result->data_seek(0);
                            while ($post = $result->fetch_assoc()) {
                                if (!isset($categories[$post['category']])) {
                                    $categories[$post['category']] = 0;
                                }
                                $categories[$post['category']]++;
                            }
                            foreach ($categories as $category => $count):
                            ?>
                            <li>
                                <a href="blog-search.php?query=<?php echo urlencode($category); ?>"
                                    class="flex justify-between items-center text-gray-600 hover:text-primary-600 transition-colors">
                                    <span><?php echo htmlspecialchars($category); ?></span>
                                    <span class="bg-primary-100 text-primary-800 text-xs font-medium px-2 py-1 rounded-full"><?php echo $count; ?></span>
                                </a>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>

                    <!-- Popular Posts -->
                    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Popular Posts</h3>
                        <div class="space-y-4">
                            <?php
                            $result->data_seek(0);
                            $count = 0;
                            while ($post = $result->fetch_assoc() && $count < 3):
                                $count++;
                            ?>
                            <div class="flex items-center">
                                <img src="<?php echo htmlspecialchars($post['image_url']); ?>" alt="<?php echo htmlspecialchars($post['title']); ?>"
                                    class="w-16 h-16 object-cover rounded-md mr-3">
                                <div>
                                    <h4 class="text-sm font-medium text-gray-800"><?php echo htmlspecialchars($post['title']); ?></h4>
                                    <p class="text-xs text-gray-500 mt-1"><?php echo date('F j, Y', strtotime($post['created_at'])); ?></p>
                                </div>
                            </div>
                            <?php endwhile; ?>
                        </div>
                    </div>

                    <!-- Newsletter Signup -->
                    <div class="bg-primary-50 rounded-lg shadow-md p-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-2">Subscribe to Newsletter</h3>
                        <p class="text-gray-600 text-sm mb-4">Get the latest gardening tips and updates delivered to your inbox.</p>
                        <form action="newsletter-signup.php" method="POST" class="space-y-3">
                            <input type="email" name="email" placeholder="Your email address"
                                class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-primary-500 focus:border-primary-500">
                            <button type="submit"
                                class="w-full bg-primary-600 hover:bg-primary-700 text-white font-medium py-2 px-4 rounded-md transition-colors duration-300">
                                Subscribe
                            </button>
                        </form>
                    </div>
                </div>
            </div>
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