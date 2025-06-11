<?php
require_once 'db.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $login = $_POST['login'];
    $name = $_POST['name'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $registration_date = date('Y-m-d');

    $stmt = $conn->prepare("INSERT INTO users (login, name, phone, email, password) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $login, $name, $phone, $email, $password);


    if ($stmt->execute()) {
        header("Location: " . $_SERVER['HTTP_REFERER'] . "?registered=1");
        exit();
    } else {
        echo "Ошибка регистрации: " . $stmt->error;
    }
}
?>
