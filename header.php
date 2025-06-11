<?php
// Отключаем вывод ошибок для пользователей
ob_start();
error_reporting(E_ALL);
//вот это закомментито то раскомментить
ini_set('display_errors', 1);
// Настройки безопасности ДО старта сессии
//ini_set('session.cookie_httponly', 1);
//ini_set('session.cookie_secure', 0);
//ini_set('session.use_strict_mode', 1);
//ini_set('session.cookie_samesite', 'Lax');
// Старт сессии
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
if (session_status() == PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', 0);
    ini_set('session.use_strict_mode', 1);
    ini_set('session.cookie_samesite', 'Lax');
    session_start();
}


// Подключение к БД с обработкой ошибок
require_once 'db.php'; // Файл должен содержать безопасное подключение к БД

/**
 * Безопасное выполнение SQL запроса
 */
function safeQuery($conn, $sql, $params = [], $types = "") {
    try {
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Ошибка подготовки запроса: " . $conn->error);
        }

        if (!empty($params)) {
            if (!$stmt->bind_param($types, ...$params)) {
                throw new Exception("Ошибка привязки параметров: " . $stmt->error);
            }
        }

        if (!$stmt->execute()) {
            throw new Exception("Ошибка выполнения запроса: " . $stmt->error);
        }

        return $stmt;
    } catch (Exception $e) {
        error_log($e->getMessage());
        return false;
    }
}

/**
 * Валидация входных данных
 */
function validateInput($data, $maxLength = 255) {
    if (!isset($data)) {
        return false;
    }

    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');

    if (strlen($data) > $maxLength) {
        return false;
    }

    return $data;
}

/**
 * Генерация CAPTCHA
 */
function generateCaptcha($conn) {
    // Получаем случайную CAPTCHA из базы данных
    $stmt = safeQuery($conn, "SELECT id, image_path, answer FROM captcha_images ORDER BY RAND() LIMIT 1");

    if ($stmt) {
        $result = $stmt->get_result();
        if ($result->num_rows === 1) {
            $captcha = $result->fetch_assoc();

            // Нормализуем ответ: удаляем пробелы и приводим к нижнему регистру
            $cleanAnswer = mb_strtolower(trim($captcha['answer']));

            // Сохраняем в сессии
            $_SESSION['captcha_answer'] = $cleanAnswer;

            // Для отладки (можно включить временно)
            error_log("CAPTCHA generated. Correct answer: " . $_SESSION['captcha_answer']);

            return $captcha;
        }
    }

    // Заглушка на случай ошибки
    $_SESSION['captcha_answer'] = 'default';
    return [
        'image_path' => 'images/captcha/default.jpg',
        'answer' => 'default'
    ];
}

/**
 * Проверка CAPTCHA
 */
function verifyCaptcha($userAnswer) {
    if (empty($_SESSION['captcha_answer']) || empty($userAnswer)) {
        error_log("CAPTCHA check failed: empty data");
        return false;
    }

    // Нормализуем ввод пользователя
    $userInput = mb_strtolower(trim($userAnswer));
    $correctAnswer = $_SESSION['captcha_answer'];

    // Для отладки
    error_log("User input: '$userInput', Correct answer: '$correctAnswer'");

    // Сравниваем
    return $userInput === $correctAnswer;
}

// --- Главная логика ---

$login_error = '';

// Генерируем CAPTCHA только при GET-запросе
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $currentCaptcha = generateCaptcha($conn);
}

// Обработка входа
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login']) && isset($_POST['password'])) {
    // Инициализация попыток входа
    if (!isset($_SESSION['login_attempts'])) {
        $_SESSION['login_attempts'] = 0;
    }

    // Задержка при множественных попытках
    if ($_SESSION['login_attempts'] > 2) {
        sleep(min($_SESSION['login_attempts'], 10)); // Максимум 10 секунд
    }

    // Валидация данных
    $login = validateInput($_POST['login'] ?? '');
    $password = $_POST['password'] ?? '';
    $captchaAnswer = $_POST['captcha_answer'] ?? '';

    // Проверка CAPTCHA
    if (!verifyCaptcha($captchaAnswer)) {
        $login_error = "Неверный код с картинки";
        $currentCaptcha = generateCaptcha($conn); // Генерируем новую CAPTCHA
    } else {
        // CAPTCHA верна, продолжаем обработку
        $stmt = safeQuery($conn,
            "SELECT id, login, name, password FROM users WHERE login = ?",
            [$login],
            "s"
        );

        if ($stmt) {
            $result = $stmt->get_result();

            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();

                // Проверка пароля
                if (password_verify($password, $user['password'])) {
                    // Успешный вход
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_login'] = $user['login'];
                    $_SESSION['user_name'] = $user['name'];
                    $_SESSION['login_attempts'] = 0;

                    session_regenerate_id(true);

                    // Очистка буфера и редирект
                    header("Location: " . filter_var($_SERVER['PHP_SELF'], FILTER_SANITIZE_URL));
                    exit();
                } else {
                    $login_error = "Неверный пароль";
                }
            } else {
                $login_error = "Пользователь не найден";
            }

            $stmt->close();
        } else {
            $login_error = "Ошибка системы. Пожалуйста, попробуйте позже.";
        }

        // После успешной проверки капчи и попытки входа генерируем новую капчу только если была ошибка
        if ($login_error) {
            $currentCaptcha = generateCaptcha($conn);
        }
    }

    // Увеличение счетчика попыток при ошибке
    if ($login_error) {
        $_SESSION['login_attempts']++;
        $logMessage = "Failed login attempt";
        if (!empty($login)) {
            $logMessage .= " for login: " . $login;
        }
        $logMessage .= " from IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown');
        error_log($logMessage);
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Luxury Hotels - Бронирование отелей</title>
    <style>
        body {
            font-family: 'Roboto', Arial, sans-serif;
            margin: 0;
            background-color: #f9f9f9;
        }
        header {
            background-color: #003580;
            color: white;
            padding: 15px 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header-container {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        nav {
            margin-top: 15px;
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 15px;
        }
        nav a {
            color: white;
            text-decoration: none;
            padding: 8px 15px;
            border-radius: 4px;
            transition: all 0.3s ease;
            font-weight: 500;
        }
        nav a:hover {
            background-color: rgba(255,255,255,0.2);
        }
        nav a.login-btn {
            background-color: #ffb700;
            color: #003580;
            font-weight: bold;
        }
        nav a.login-btn:hover {
            background-color: #ffa700;
        }

        /* Модальные окна */
        .modal {
            display: none;
            position: fixed;
            z-index: 100;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }
        .modal-content {
            background-color: #fefefe;
            margin: 10% auto;
            padding: 25px;
            border: 1px solid #ddd;
            width: 320px;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        .close:hover {
            color: #003580;
        }
        input[type="text"],
        input[type="password"],
        input[type="email"],
        input[type="tel"] {
            width: 100%;
            padding: 10px;
            margin: 8px 0;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        input[type="submit"] {
            background: #003580;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
            font-weight: bold;
            margin-top: 10px;
            transition: background 0.3s;
        }
        input[type="submit"]:hover {
            background: #002366;
        }
        .modal-switch {
            font-size: 14px;
            margin-top: 15px;
            text-align: center;
            color: #666;
        }
        .modal-switch a {
            color: #003580;
            text-decoration: none;
            font-weight: bold;
        }
        .modal-switch a:hover {
            text-decoration: underline;
        }
        .error-message {
            color: #d9534f;
            margin-bottom: 15px;
            text-align: center;
        }
        h1 {
            margin: 0;
            font-size: 2.2em;
            color: #ffb700;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.2);
        }
        /* Стили для CAPTCHA */
        .captcha-container {
            margin: 15px 0;
        }
        .captcha-image {
            margin: 10px auto;
            border: 1px solid #ddd;
            padding: 5px;
            background: #f9f9f9;
            text-align: center;
        }
        .captcha-image img {
            max-width: 100%;
            height: auto;
        }
        .captcha-reload {
            color: #003580;
            cursor: pointer;
            font-size: 12px;
            margin-top: 5px;
            text-align: center;
            text-decoration: underline;
        }
    </style>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
</head>
<body>
<div class="wrapper">
    <header>
        <div class="header-container">
            <h1>Luxury Hotels</h1>
            <nav>
                <a href="index.php">Главная</a>
                <a href="gallery.php">Галерея</a>
                <a href="product.php">Номера</a>
                <a href="order.php">Бронирование</a>
                <a href="cart.php">Корзина</a>
                <a href="contacts.php">Контакты</a>
                <a href="guestbook.php">Отзывы</a>
                <?php if (!empty($_SESSION['user_id'])): ?>
                    <a href="profile.php">Личный кабинет</a>
                    <a href="logout.php">Выход</a>
                <?php else: ?>
                    <a href="#" id="openLogin" class="login-btn">Вход</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>

    <!-- Модалка входа -->
    <div id="loginModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('loginModal')">&times;</span>
            <h3 style="text-align: center; color: #003580; margin-top: 0;">Вход в аккаунт</h3>
            <?php if (isset($login_error)): ?>
                <div class="error-message"><?php echo htmlspecialchars($login_error, ENT_QUOTES, 'UTF-8'); ?></div>
            <?php endif; ?>
            <form method="post" action="">
                <input type="text" name="login" placeholder="Логин" required maxlength="50">
                <input type="password" name="password" placeholder="Пароль" required minlength="6">

                <!-- Блок CAPTCHA -->
                <div class="captcha-container">
                    <div class="captcha-image">
                        <img src="<?php echo htmlspecialchars($currentCaptcha['image_path']); ?>" alt="CAPTCHA">
                    </div>
                    <div class="captcha-reload" onclick="reloadCaptcha()">
                        Обновить картинку
                    </div>
                    <input type="text" name="captcha_answer" placeholder="Введите текст с картинки" required>
                </div>

                <input type="submit" value="Войти">
            </form>
            <div class="modal-switch">
                Нет аккаунта? <a href="#" id="openRegisterFromLogin">Зарегистрироваться</a>
            </div>
        </div>
    </div>

    <!-- Модалка регистрации -->
    <div id="registerModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('registerModal')">&times;</span>
            <h3 style="text-align: center; color: #003580; margin-top: 0;">Регистрация</h3>
            <form method="post" action="register.php" id="registerForm">
                <input type="text" name="login" placeholder="Логин" required maxlength="50">
                <input type="text" name="name" placeholder="Ваше имя" required maxlength="100">
                <input type="tel" name="phone" placeholder="Телефон" required pattern="[\+]\d{1}\s[\(]\d{3}[\)]\s\d{3}[\-]\d{2}[\-]\d{2}">
                <input type="email" name="email" placeholder="E-mail" required>
                <input type="password" name="password" placeholder="Пароль" required minlength="8">
                <input type="password" name="confirm_password" placeholder="Повторите пароль" required>

                <!-- Блок CAPTCHA -->
                <div class="captcha-container">
                    <div class="captcha-image">
                        <img src="<?php echo htmlspecialchars($currentCaptcha['image_path']); ?>" alt="CAPTCHA">
                    </div>
                    <div class="captcha-reload" onclick="reloadCaptcha()">
                        Обновить картинку
                    </div>
                    <input type="text" name="captcha_answer" placeholder="Введите текст с картинки" required>
                </div>

                <div style="background-color: #721c24; color: white; font-size: 16px;">
                    error:
                </div>

                <input type="submit" value="Зарегистрироваться">
            </form>
            <div class="modal-switch">
                Уже есть аккаунт? <a href="#" id="openLoginFromRegister">Войти</a>
            </div>
        </div>
    </div>

    <script>
        // Защита от XSS в URL
        function safeRedirect(url) {
            return url.replace(/[^a-z0-9-._~:/?#[\]@!$&'()*+,;=]/gi, '');
        }

        function closeModal(id) {
            document.getElementById(id).style.display = "none";
        }

        // Обновление CAPTCHA
        function reloadCaptcha() {
            fetch('get_captcha.php')
                .then(response => response.json())
                .then(data => {
                    const captchaImages = document.querySelectorAll('.captcha-image img');
                    captchaImages.forEach(img => {
                        img.src = data.image_path + '?t=' + new Date().getTime();
                    });
                })
                .catch(error => console.error('Ошибка при обновлении CAPTCHA:', error));
        }

        document.getElementById("openLogin")?.addEventListener("click", function(e) {
            e.preventDefault();
            document.getElementById("loginModal").style.display = "block";
        });

        document.getElementById("openRegisterFromLogin")?.addEventListener("click", function(e) {
            e.preventDefault();
            closeModal('loginModal');
            document.getElementById("registerModal").style.display = "block";
        });

        document.getElementById("openLoginFromRegister")?.addEventListener("click", function(e) {
            e.preventDefault();
            closeModal('registerModal');
            document.getElementById("loginModal").style.display = "block";
        });

        window.onclick = function(event) {
            if (event.target.classList.contains("modal")) {
                event.target.style.display = "none";
            }
        };

        // Валидация формы регистрации
        document.getElementById("registerForm")?.addEventListener("submit", function(e) {
            const password = this.elements['password'].value;
            const confirmPassword = this.elements['confirm_password'].value;

            if (password !== confirmPassword) {
                alert("Пароли не совпадают!");
                e.preventDefault();
            }

            if (password.length < 8) {
                alert("Пароль должен содержать минимум 8 символов");
                e.preventDefault();
            }
        });

        <?php if (isset($_GET['registered']) && $_GET['registered'] == 1): ?>
        window.addEventListener('DOMContentLoaded', function() {
            document.getElementById('loginModal').style.display = 'block';
        });
        <?php endif; ?>
    </script>
</div>
</body>
</html>