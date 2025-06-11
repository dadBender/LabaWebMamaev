<?php
session_start();
require_once "db.php";
require_once "header.php";

// Проверка, авторизован ли пользователь
if (!isset($_SESSION['user_id'])) {
    echo "<p style='text-align:center; margin-top: 40px;'>Пожалуйста, <a href='#' onclick=\"document.getElementById('loginModal').style.display='block'\">войдите</a>, чтобы просмотреть личный кабинет.</p>";
    require_once "footer.php";
    exit();
}

$userId = $_SESSION['user_id'];

// Получение информации о пользователе
$stmt = $conn->prepare("SELECT login, name, phone, email FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Получение заказов пользователя
$stmt = $conn->prepare("
    SELECT o.start_date, o.end_date, o.order_date, o.people, p.title 
    FROM orders o
    JOIN products p ON o.product_id = p.id
    WHERE o.user_id = ?
    ORDER BY o.order_date DESC
");
$stmt->bind_param("i", $userId);
$stmt->execute();
$orders = $stmt->get_result();
$stmt->close();
?>

<style>
    .profile-container {
        max-width: 900px;
        margin: 40px auto;
        padding: 40px;
        background-color: #2a2a2a;
        color: #fff;
        border-radius: 15px;
        box-shadow: 0 8px 25px rgba(0,0,0,0.3);
        font-family: 'Georgia', serif;
    }

    .profile-container h2 {
        text-align: center;
        font-size: 36px;
        color: #ffd700;
        margin-bottom: 30px;
        letter-spacing: 2px;
    }

    .profile-info {
        margin-bottom: 40px;
    }

    .profile-info p {
        font-size: 18px;
        margin: 12px 0;
        line-height: 1.6;
        font-weight: 300;
        color: #bbb;
    }

    .profile-info strong {
        color: #ffd700;
    }

    .orders-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 30px;
    }

    .orders-table th,
    .orders-table td {
        padding: 15px;
        border: 1px solid #444;
        text-align: center;
        font-size: 16px;
        font-weight: normal;
    }

    .orders-table th {
        background-color: #333;
        color: #ffd700;
    }

    .orders-table td {
        background-color: #1e1e1e;
    }

    .orders-table tr:hover {
        background-color: #444;
    }

    .no-orders {
        text-align: center;
        font-style: italic;
        color: #bbb;
        margin-top: 20px;
    }

    @media (max-width: 768px) {
        .profile-container {
            padding: 20px;
        }

        .orders-table th,
        .orders-table td {
            font-size: 14px;
        }

        .profile-info p {
            font-size: 16px;
        }
    }
</style>

<div class="profile-container">
    <h2>Личный кабинет</h2>

    <div class="profile-info">
        <p><strong>Логин:</strong> <?= htmlspecialchars($user['login']) ?></p>
        <p><strong>Имя:</strong> <?= htmlspecialchars($user['name']) ?></p>
        <p><strong>Телефон:</strong> <?= htmlspecialchars($user['phone']) ?></p>
        <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
    </div>

    <h3>Ваши заказы</h3>

    <?php if ($orders->num_rows > 0): ?>
        <table class="orders-table">
            <tr>
                <th>Домик</th>
                <th>Дата заезда</th>
                <th>Дата выезда</th>
                <th>Дата заказа</th>
                <th>Кол-во человек</th>
            </tr>
            <?php while ($order = $orders->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($order['title']) ?></td>
                    <td><?= htmlspecialchars($order['start_date']) ?></td>
                    <td><?= htmlspecialchars($order['end_date']) ?></td>
                    <td><?= htmlspecialchars($order['order_date']) ?></td>
                    <td><?= htmlspecialchars($order['people']) ?></td>
                </tr>
            <?php endwhile; ?>
        </table>
    <?php else: ?>
        <p class="no-orders">У вас пока нет заказов.</p>
    <?php endif; ?>
</div>

<?php require_once "footer.php"; ?>
