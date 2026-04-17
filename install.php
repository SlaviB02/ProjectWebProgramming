<?php

// ====== ENV VARIABLES ======
$host      = getenv('DB_HOST');
$port      = getenv('DB_PORT');

$superUser = getenv('DB_SUPERUSER');
$superPass = getenv('DB_SUPERPASS');

$dbName    = getenv('DB_NAME');
$appUser   = getenv('DB_APPUSER');
$appPass   = getenv('DB_APPPASS');

try {
    // ====== CONNECT AS SUPERUSER (postgres DB) ======
    $pdo = new PDO("pgsql:host=$host;port=$port;dbname=postgres;", $superUser, $superPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // ====== CREATE DATABASE ======
    $result = $pdo->query("SELECT 1 FROM pg_database WHERE datname = '$dbName'");
if (!$result->fetch()) {
    $pdo->exec("CREATE DATABASE $dbName");
}

    // ====== CREATE ROLE (APP USER) ======
    $stmt = $pdo->prepare("
        DO $$
        BEGIN
            IF NOT EXISTS (
                SELECT FROM pg_catalog.pg_roles WHERE rolname = :user
            ) THEN
                CREATE ROLE $appUser LOGIN PASSWORD :pass;
            END IF;
        END
        $$;
    ");

    // NOTE: PDO cannot bind inside DO $$ safely, so we execute directly (safe if trusted input)
    $pdo->exec("
        DO $$
        BEGIN
            IF NOT EXISTS (
                SELECT FROM pg_catalog.pg_roles WHERE rolname = '$appUser'
            ) THEN
                CREATE ROLE $appUser LOGIN PASSWORD '$appPass';
            END IF;
        END
        $$;
    ");

// Connect to target DB as superuser
$pdo = new PDO("pgsql:host=$host;port=$port;dbname=$dbName;", $superUser, $superPass);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// ====== IMPORTANT: allow schema creation ======
$pdo->exec("GRANT USAGE, CREATE ON SCHEMA public TO $appUser;");

// ====== TABLE PRIVILEGES ======
$pdo->exec("
    GRANT SELECT, INSERT, UPDATE, DELETE
    ON ALL TABLES IN SCHEMA public
    TO $appUser;
");

// ====== SEQUENCES (SERIAL support) ======
$pdo->exec("
    GRANT USAGE, SELECT
    ON ALL SEQUENCES IN SCHEMA public
    TO $appUser;
");

// ====== FUTURE TABLES ======
$pdo->exec("
    ALTER DEFAULT PRIVILEGES IN SCHEMA public
    GRANT SELECT, INSERT, UPDATE, DELETE ON TABLES TO $appUser;

    ALTER DEFAULT PRIVILEGES IN SCHEMA public
    GRANT USAGE, SELECT ON SEQUENCES TO $appUser;
");
} catch (PDOException $e) {
    die("Error creating DB or user: " . $e->getMessage());
}

// ======================================================
// ====== APP USER CONNECTS AND CREATES TABLES =========
// ======================================================

try {
    $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$dbName;", $appUser, $appPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // ====== TABLES ======
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
            id SERIAL PRIMARY KEY,
            username VARCHAR(50) UNIQUE,
            password VARCHAR(255)
        );

        CREATE TABLE IF NOT EXISTS authors (
            id SERIAL PRIMARY KEY,
            name VARCHAR(255) UNIQUE
        );

        CREATE TABLE IF NOT EXISTS publications (
            id SERIAL PRIMARY KEY,
            title VARCHAR(255) UNIQUE
        );

        CREATE TABLE IF NOT EXISTS articles (
            id SERIAL PRIMARY KEY,
            title VARCHAR(255),
            author_id INT REFERENCES authors(id),
            publication_id INT REFERENCES publications(id),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );
    ");

    // ====== ADMIN USER ======
    $password = password_hash("admin123", PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("
        INSERT INTO users (username, password)
        VALUES ('admin', :pass)
        ON CONFLICT (username) DO NOTHING
    ");
    $stmt->execute(['pass' => $password]);

    // ====== AUTHORS ======
    $authors = ['John Doe','Jane Smith','Alice Johnson'];
    foreach ($authors as $name) {
        $stmt = $pdo->prepare("
            INSERT INTO authors (name)
            VALUES (:name)
            ON CONFLICT (name) DO NOTHING
        ");
        $stmt->execute(['name' => $name]);
    }

    // ====== PUBLICATIONS ======
    $publications = ['Science Journal','Tech Review','Nature Today'];
    foreach ($publications as $title) {
        $stmt = $pdo->prepare("
            INSERT INTO publications (title)
            VALUES (:title)
            ON CONFLICT (title) DO NOTHING
        ");
        $stmt->execute(['title' => $title]);
    }

    // ====== ARTICLES ======
    $exampleArticles = [
        ['title'=>'The Future of AI','author'=>'John Doe','publication'=>'Tech Review'],
        ['title'=>'Climate Change Effects','author'=>'Jane Smith','publication'=>'Science Journal'],
        ['title'=>'Quantum Computing Basics','author'=>'Alice Johnson','publication'=>'Nature Today'],
        ['title'=>'Advances in Biotechnology','author'=>'John Doe','publication'=>'Science Journal'],
        ['title'=>'Renewable Energy Sources','author'=>'Jane Smith','publication'=>'Nature Today'],
        ['title'=>'Nanotechnology in Medicine','author'=>'Alice Johnson','publication'=>'Tech Review'],
        ['title'=>'Cybersecurity Trends 2026','author'=>'John Doe','publication'=>'Tech Review']
    ];

    foreach ($exampleArticles as $art) {

        $author_id = $pdo->query("
            SELECT id FROM authors WHERE name = " . $pdo->quote($art['author'])
        )->fetchColumn();

        $pub_id = $pdo->query("
            SELECT id FROM publications WHERE title = " . $pdo->quote($art['publication'])
        )->fetchColumn();

        $stmt = $pdo->prepare("
            INSERT INTO articles (title, author_id, publication_id)
            VALUES (:title, :author_id, :pub_id)
            ON CONFLICT DO NOTHING
        ");

        $stmt->execute([
            'title' => $art['title'],
            'author_id' => $author_id,
            'pub_id' => $pub_id
        ]);
    }

    echo "✅ Database, tables, users, and sample data created successfully!<br>";
    echo "👉 Go to <a href='index.php'>index.php</a>";

} catch (PDOException $e) {
    die("Error setting up tables or data: " . $e->getMessage());
}