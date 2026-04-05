<?php
$host = "localhost";
$port = "5432";
$superUser = "postgres";
$superPass = "postgres";

$dbName = "webdev_db";
$appUser = "webdev";
$appPass = "webdev123";

try {
    // Connect as superuser
    $pdo = new PDO("pgsql:host=$host;port=$port;", $superUser, $superPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 1️⃣ Create DB
    $pdo->exec("CREATE DATABASE $dbName");

    // 2️⃣ Create app user if not exists
    $pdo->exec("
    DO \$\$
    BEGIN
        IF NOT EXISTS (SELECT FROM pg_catalog.pg_roles WHERE rolname = '$appUser') THEN
            CREATE ROLE $appUser LOGIN PASSWORD '$appPass';
        END IF;
    END
    \$\$;
    ");

    // 3️⃣ Grant privileges
    $pdo->exec("GRANT ALL PRIVILEGES ON DATABASE $dbName TO $appUser;");
    echo "Database and user setup done.<br>";

    // 4️⃣ Grant schema privileges
    $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$dbName;", $superUser, $superPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("GRANT ALL PRIVILEGES ON SCHEMA public TO $appUser;");
    echo "Schema privileges granted.<br>";

} catch (PDOException $e) {
    die("Error creating DB or user: " . $e->getMessage());
}

// ====== Connect as app user and create tables + insert data ======
try {
    $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$dbName;", $appUser, $appPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Tables
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

    // Admin user
    $password = password_hash("admin123", PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO users (username,password) VALUES ('admin',:pass) ON CONFLICT (username) DO NOTHING");
    $stmt->execute(['pass' => $password]);

    // Example authors
    $authors = ['John Doe','Jane Smith','Alice Johnson'];
    foreach($authors as $name){
        $stmt = $pdo->prepare("INSERT INTO authors (name) VALUES (:name) ON CONFLICT (name) DO NOTHING");
        $stmt->execute(['name'=>$name]);
    }

    // Example publications
    $publications = ['Science Journal','Tech Review','Nature Today'];
    foreach($publications as $title){
        $stmt = $pdo->prepare("INSERT INTO publications (title) VALUES (:title) ON CONFLICT (title) DO NOTHING");
        $stmt->execute(['title'=>$title]);
    }

    // Example articles
    $exampleArticles = [
    ['title'=>'The Future of AI','author'=>'John Doe','publication'=>'Tech Review'],
    ['title'=>'Climate Change Effects','author'=>'Jane Smith','publication'=>'Science Journal'],
    ['title'=>'Quantum Computing Basics','author'=>'Alice Johnson','publication'=>'Nature Today'],
    ['title'=>'Advances in Biotechnology','author'=>'John Doe','publication'=>'Science Journal'],
    ['title'=>'Renewable Energy Sources','author'=>'Jane Smith','publication'=>'Nature Today'],
    ['title'=>'Nanotechnology in Medicine','author'=>'Alice Johnson','publication'=>'Tech Review'],
    ['title'=>'Cybersecurity Trends 2026','author'=>'John Doe','publication'=>'Tech Review']
];

    foreach($exampleArticles as $art){
        $author_id = $pdo->query("SELECT id FROM authors WHERE name='{$art['author']}'")->fetchColumn();
        $pub_id = $pdo->query("SELECT id FROM publications WHERE title='{$art['publication']}'")->fetchColumn();

        $stmt = $pdo->prepare("INSERT INTO articles (title, author_id, publication_id) VALUES (:title,:author_id,:pub_id) ON CONFLICT DO NOTHING");
        $stmt->execute([
            'title'=>$art['title'],
            'author_id'=>$author_id,
            'pub_id'=>$pub_id
        ]);
    }

    echo "✅ Tables, admin user, and example data created successfully!<br>";
    echo "You can now <a href='index.php'>visit the site</a>.";

} catch(PDOException $e){
    die("Error setting up tables or data: ".$e->getMessage());
}