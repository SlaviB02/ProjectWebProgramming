<?php
session_start();

function isLogged() {
    return isset($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLogged()) {
        header("Location: /login.php");
        exit;
    }
}