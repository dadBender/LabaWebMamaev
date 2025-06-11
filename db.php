<?php

$host = "localhost";
$user = "root";
$password = "2468";
$database = "hotel_booking"; // Новое имя БД

try {
    $conn = new mysqli($host, $user, $password, $database);

    if ($conn->connect_error) {
        throw new Exception("Ошибка подключения к базе данных: " . $conn->connect_error);
    }

    $conn->set_charset("utf8");
} catch (Exception $e) {
    error_log($e->getMessage());
    die("Произошла ошибка при подключении к системе. Пожалуйста, попробуйте позже.");
}
?>