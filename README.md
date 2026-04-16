# 📚 ProjectWebProgramming
Scientific Articles Management System (PHP + PostgreSQL)

---

## 👤 Admin Credentials

- Username: admin
- Password: admin123

---

## 🚀 How to Run the Project

### 1. Start PHP server
Open terminal in the project folder and run:

php -S localhost:8000

---

### 2. Run installation script
Open in browser:

http://localhost:8000/install.php

This will:
- Create database tables
- Insert default admin user
- Add sample data

---

### 3. Use the application

Open:

http://localhost:8000

---

## 🗄️ Database

- PostgreSQL
- Auto setup via install.php
- Tables:
  - users
  - authors
  - publications
  - articles

---

## 🔐 Security Features

- Password hashing (bcrypt)
- Prepared statements (SQL Injection protection)
- CSRF protection (forms)
- Token-based REST API authentication
- XSS protection using htmlspecialchars()

---

## 🌐 REST API

### Base URL

http://localhost:8000/api/records.php

---

### GET all articles

curl -X GET http://localhost:8000/api/records.php -H "Authorization: my_super_secret_token_123456"

---

### POST new article (Windows CMD)

curl -X POST http://localhost:8000/api/records.php ^
-H "Authorization: my_super_secret_token_123456" ^
-H "Content-Type: application/json" ^
-d "{\"title\":\"Test Article\",\"author\":\"Ivan Ivanov\",\"publication\":\"Tech Journal\"}"

---


## 📡 External API Integration

This project uses a public REST API (Open Library) to fetch and display scientific books in the web interface.  
The data is processed on the server side using PHP and rendered in HTML.

---

## 📌 Features

- CRUD operations for scientific articles
- Search and filtering
- Pagination
- Favorite article (cookie-based system)
- User authentication (login/logout)
- REST API with token authentication
- External REST API integration
- CSRF protection


