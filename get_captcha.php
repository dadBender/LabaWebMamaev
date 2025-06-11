<?php
require_once 'db.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

function generateCaptcha($conn) {
    $stmt = $conn->prepare("SELECT id, image_path, answer FROM captcha_images ORDER BY RAND() LIMIT 1");
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $captcha = $result->fetch_assoc();
        $_SESSION['captcha_answer'] = mb_strtolower(trim($captcha['answer']));
        return $captcha;
    }

    $_SESSION['captcha_answer'] = 'default';
    return [
        'image_path' => 'images/captcha/default.jpg',
        'answer' => 'default'
    ];
}

header('Content-Type: application/json');
echo json_encode(generateCaptcha($conn));
?>