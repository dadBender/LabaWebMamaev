<?php
require_once "header.php";

$file = "guestbook.txt";
$counterFile = "counter.txt";
$success = "";
$error = "";

// Счётчик посещений
$visits = 0;
if (file_exists($counterFile)) {
    $visits = (int)file_get_contents($counterFile);
}
$visits++;
file_put_contents($counterFile, $visits);

// Обработка формы
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $message = trim($_POST['message'] ?? '');
    $imagePath = null;

    if ($name && $message) {
        // Обработка изображения
        if (!empty($_FILES['photo']['tmp_name'])) {
            $uploadDir = 'uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
            $imageName = uniqid('img_', true) . '.' . $ext;
            $imagePath = $uploadDir . $imageName;

            if (!move_uploaded_file($_FILES['photo']['tmp_name'], $imagePath)) {
                $error = "Не удалось загрузить изображение.";
            }
        }

        if (!$error) {
            $entry = [
                'name' => $name,
                'message' => $message,
                'date' => date('Y-m-d H:i:s'),
                'image' => $imagePath
            ];

            file_put_contents($file, json_encode($entry) . PHP_EOL, FILE_APPEND);
            $success = "Сообщение добавлено!";
        }
    } else {
        $error = "Пожалуйста, заполните все поля.";
    }
}

// Получение всех записей
$entries = [];
$wordCounts = [];

if (file_exists($file)) {
    $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $data = json_decode($line, true);
        if ($data) {
            $entries[] = $data;

            // Подсчёт слов
            $words = explode(' ', mb_strtolower(strip_tags($data['message'])));
            foreach ($words as $word) {
                $word = preg_replace('/[^а-яa-z0-9]+/iu', '', $word);
                if ($word) {
                    $wordCounts[$word] = ($wordCounts[$word] ?? 0) + 1;
                }
            }
        }
    }
}
?>

<style>
    .guestbook-container {
        max-width: 800px;
        margin: 60px auto;
        padding: 40px;
        background-color: #fff;
        border-radius: 16px;
        box-shadow: 0 12px 24px rgba(0,0,0,0.15);
        font-family: 'Georgia', serif;
    }

    .guestbook-container h2 {
        text-align: center;
        margin-bottom: 30px;
        font-size: 28px;
        color: #333;
    }

    .guestbook-container form {
        display: flex;
        flex-direction: column;
        gap: 15px;
        margin-bottom: 40px;
    }

    .guestbook-container input,
    .guestbook-container textarea {
        padding: 12px 15px;
        border: 1px solid #ccc;
        border-radius: 8px;
        font-size: 16px;
        font-family: inherit;
    }

    .guestbook-container button {
        background-color: #1a1a1a;
        color: white;
        padding: 14px;
        border: none;
        border-radius: 8px;
        font-size: 16px;
        cursor: pointer;
        transition: background-color 0.3s;
    }

    .guestbook-container button:hover {
        background-color: #333;
    }

    .entry {
        margin-bottom: 25px;
        padding: 15px;
        background-color: #f3f3f3;
        border-radius: 10px;
        box-shadow: inset 0 0 4px rgba(0,0,0,0.05);
    }

    .entry strong {
        color: #000;
        font-size: 18px;
    }

    .entry small {
        color: #777;
        font-size: 14px;
    }

    .success, .error {
        text-align: center;
        padding: 10px;
        border-radius: 8px;
        font-weight: bold;
    }

    .success {
        color: #155724;
        background-color: #d4edda;
    }

    .error {
        color: #721c24;
        background-color: #f8d7da;
    }

    .wordcount {
        margin-top: 40px;
        padding: 20px;
        background: #f0f0f0;
        border-radius: 10px;
        font-size: 15px;
    }
</style>

<div class="guestbook-container">
    <h2>Гостевая книга</h2>

    <?php if ($success): ?>
        <p class="success"><?= htmlspecialchars($success) ?></p>
    <?php endif; ?>
    <?php if ($error): ?>
        <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data">
        <input type="text" name="name" placeholder="Ваше имя" required>
        <textarea name="message" placeholder="Ваше сообщение" rows="4" required></textarea>
        <input type="file" name="photo" accept="image/*">
        <button type="submit">Оставить сообщение</button>
    </form>

    <?php if (!empty($entries)): ?>
        <h3>Сообщения:</h3>
        <?php foreach (array_reverse($entries) as $entry): ?>
            <div class="entry">
                <strong><?= htmlspecialchars($entry['name']) ?></strong> —
                <small><?= htmlspecialchars($entry['date']) ?></small>
                <p><?= nl2br(htmlspecialchars($entry['message'])) ?></p>
                <?php if (!empty($entry['image']) && file_exists($entry['image'])): ?>
                    <img src="<?= htmlspecialchars($entry['image']) ?>" alt="Изображение">
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>Пока нет сообщений.</p>
    <?php endif; ?>

    <?php if (!empty($wordCounts)): ?>
        <div class="wordcount">
            <h4>Статистика слов:</h4>
            <?php
            arsort($wordCounts);
            foreach ($wordCounts as $word => $count) {
                echo "<strong>$word</strong>: $count<br>";
            }
            ?>
        </div>
    <?php endif; ?>

    <p style="text-align:center; font-size: 14px; margin-top: 20px;">
        Количество посещений: <?= $visits ?>
    </p>
</div>

<?php require_once "footer.php"; ?>




