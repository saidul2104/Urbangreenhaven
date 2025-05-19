<?php
// Include database configuration
require_once 'config.php';

// Override password as per user update
define('DB_PASS', '123');

try {
    $pdo = new PDO(DB_DSN, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html>

<head>
    <title>Database Tables</title>
</head>

<body>
    <h1>Tables in Database '<?php echo DB_NAME; ?>'</h1>
    <?php if (!empty($tables)): ?>
        <ul>
            <?php foreach ($tables as $table): ?>
                <li><?php echo htmlspecialchars($table); ?></li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>No tables found in the database.</p>
    <?php endif; ?>
</body>

</html>