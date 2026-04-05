<?php
$host = "localhost";
$port = "5432";
$superUser = "postgres";
$superPass = "postgres";

$dbName = "webdev_db";
$appUser = "webdev";
$appPass = "webdev123";

try {
    // Свързване като суперпотребител
    $pdo = new PDO("pgsql:host=$host;port=$port;", $superUser, $superPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 1️⃣ Създаване на базата
    $pdo->exec("CREATE DATABASE $dbName");

    // 2️⃣ Създаване на потребителя
    $pdo->exec("
    DO \$\$
    BEGIN
        IF NOT EXISTS (SELECT FROM pg_catalog.pg_roles WHERE rolname = '$appUser') THEN
            CREATE ROLE $appUser LOGIN PASSWORD '$appPass';
        END IF;
    END
    \$\$;
    ");

    // 3️⃣ Дай права на базата
    $pdo->exec("GRANT ALL PRIVILEGES ON DATABASE $dbName TO $appUser;");

    echo "Database and user setup done.<br>";

} catch (PDOException $e) {
    die("Error creating DB or user: " . $e->getMessage());
}

// 4️⃣ Свързване към базата като суперпотребител, за да дадем права на schema
try {
    $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$dbName;", $superUser, $superPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 5️⃣ Дай права на schema public
    $pdo->exec("GRANT ALL PRIVILEGES ON SCHEMA public TO $appUser;");

    echo "Schema privileges granted.<br>";

} catch (PDOException $e) {
    die("Error granting schema privileges: " . $e->getMessage());
}

// 6️⃣ Свързване като приложния потребител и създаване на таблиците
try {
    $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$dbName;", $appUser, $appPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $pdo->exec("
    CREATE TABLE IF NOT EXISTS users (
        id SERIAL PRIMARY KEY,
        username VARCHAR(50) UNIQUE,
        password VARCHAR(255)
    );
    ");

    $pdo->exec("
    CREATE TABLE IF NOT EXISTS authors (
        id SERIAL PRIMARY KEY,
        name VARCHAR(255)
    );
    ");

    $pdo->exec("
    CREATE TABLE IF NOT EXISTS publications (
        id SERIAL PRIMARY KEY,
        title VARCHAR(255)
    );
    ");

    $pdo->exec("
    CREATE TABLE IF NOT EXISTS articles (
        id SERIAL PRIMARY KEY,
        title VARCHAR(255),
        author_id INT REFERENCES authors(id),
        publication_id INT REFERENCES publications(id),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );
    ");

    // 7️⃣ Добавяне на admin user
    $password = password_hash("admin123", PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO users (username,password) VALUES ('admin',:pass) ON CONFLICT (username) DO NOTHING");
    $stmt->execute(['pass' => $password]);

    echo "Tables and admin user created successfully!";

} catch (PDOException $e) {
    die("Error setting up tables or admin user: " . $e->getMessage());
}