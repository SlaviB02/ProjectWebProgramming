    <?php
    $host = getenv('DB_HOST');
    $db   = getenv('DB_NAME');
    $user = getenv('DB_APPUSER');
    $pass = getenv('DB_APPPASS');

    $dsn = "pgsql:host=$host;port=5432;dbname=$db;";

try {
    $pdo = new PDO($dsn, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    
} catch (PDOException $e) {
     http_response_code(500);

    // Optional: log error (DO NOT show in production)
    error_log("DB connection failed: " . $e->getMessage());

    // Safe message to user
    die("Internal Server Error");
}