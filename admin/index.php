<?php
session_start();

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Location: /lab4Bogdan/login.php');
    exit;
}

header('Location: /lab4Bogdan/admin/dashboard.php');
exit;
