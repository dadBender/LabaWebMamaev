<?php
require_once "header.php";

$successMessage = "";
$errorMessage = "";

// Обработка формы
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if ($name && $email && $message) {
        $to = "youremail@example.com"; // Замените на вашу почту
        $subject = "Сообщение с сайта от $name";
        $body = "Имя: $name\nEmail: $email\n\nСообщение:\n$message";
        $headers = "From: $email";

        if (mail($to, $subject, $body, $headers)) {
            $successMessage = "Сообщение успешно отправлено!";
        } else {
            $errorMessage = "Ошибка при отправке сообщения. Попробуйте позже.";
        }
    } else {
        $errorMessage = "Пожалуйста, заполните все поля.";
    }
}
?>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;700&family=Open+Sans&display=swap');

    body {
        font-family: 'Open Sans', sans-serif;
        background-color: #f4f1ed;
        margin: 0;
        padding: 0;
    }

    .contact-container {
        max-width: 700px;
        margin: 60px auto;
        padding: 40px;
        background-color: #ffffff;
        border-radius: 16px;
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.15);
        border: 1px solid #e0dcd2;
    }

    .contact-container h2 {
        font-family: 'Playfair Display', serif;
        font-size: 32px;
        color: #2e2e2e;
        text-align: center;
        margin-bottom: 30px;
    }

    .contact-container form {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    .contact-container input,
    .contact-container textarea {
        padding: 14px;
        border: 1px solid #cfcfcf;
        border-radius: 10px;
        font-size: 16px;
        transition: border-color 0.3s ease;
    }

    .contact-container input:focus,
    .contact-container textarea:focus {
        border-color: #bfa05a;
        outline: none;
    }

    .contact-container button {
        background: linear-gradient(135deg, #bfa05a, #d5bc8c);
        color: #fff;
        padding: 14px;
        border: none;
        border-radius: 10px;
        font-size: 18px;
        font-weight: bold;
        cursor: pointer;
        transition: background 0.3s ease;
        font-family: 'Playfair Display', serif;
    }

    .contact-container button:hover {
        background: linear-gradient(135deg, #d5bc8c, #bfa05a);
    }

    .success-message,
    .error-message {
        text-align: center;
        font-size: 16px;
        margin-bottom: 20px;
        padding: 10px;
        border-radius: 8px;
    }

    .success-message {
        background-color: #e6f5e9;
        color: #3c763d;
        border: 1px solid #c6e9c9;
    }

    .error-message {
        background-color: #fce4e4;
        color: #a94442;
        border: 1px solid #f5c6cb;
    }
</style>

<div class="contact-container">
    <h2>Свяжитесь с нами</h2>

    <?php if ($successMessage): ?>
        <p class="success-message"><?= htmlspecialchars($successMessage) ?></p>
    <?php endif; ?>

    <?php if ($errorMessage): ?>
        <p class="error-message"><?= htmlspecialchars($errorMessage) ?></p>
    <?php endif; ?>

    <form method="post">
        <input type="text" name="name" placeholder="Ваше имя" required>
        <input type="email" name="email" placeholder="Ваш email" required>
        <textarea name="message" rows="6" placeholder="Ваше сообщение" required></textarea>
        <button type="submit">Отправить сообщение</button>
    </form>
</div>

<?php require_once "footer.php"; ?>
