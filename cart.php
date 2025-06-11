<?php
require_once "db.php";
require_once "header.php";

if (!isset($_SESSION['user_id'])) {
    echo "<p style='text-align:center; margin-top: 40px;'>Пожалуйста, <a href='#' onclick=\"document.getElementById('loginModal').style.display='block'\">войдите</a>, чтобы просматривать корзину.</p>";
    require_once "footer.php";
    exit();
}

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Удаление одного элемента
if (isset($_GET['remove']) && is_numeric($_GET['remove'])) {
    unset($_SESSION['cart'][$_GET['remove']]);
    $_SESSION['cart'] = array_values($_SESSION['cart']);
}

// Оформление заказа
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    $userId = $_SESSION['user_id'];

    $stmt = $conn->prepare("INSERT INTO orders (user_id, product_id, start_date, end_date, people, order_date) VALUES (?, ?, ?, ?, ?, NOW())");

    foreach ($_SESSION['cart'] as $item) {
        $stmt->bind_param("iissi", $userId, $item['product_id'], $item['start_date'], $item['end_date'], $item['people']);
        $stmt->execute();
    }

    $stmt->close();
    $_SESSION['cart'] = [];
    echo "<p style='text-align:center; color:#4e8f5b; margin-top:20px;'>Ваш заказ успешно оформлен! Мы с вами свяжемся в ближайшее время.</p>";
}
?>

<style>
    body {
        background-color: #fdfaf6;
        font-family: 'Georgia', serif;
    }

    .order-container {
        max-width: 800px;
        margin: 60px auto;
        padding: 40px;
        background-color: #ffffff;
        border: 1px solid #e5d8ce;
        border-radius: 18px;
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.06);
    }

    .order-container h2 {
        text-align: center;
        font-size: 32px;
        margin-bottom: 30px;
        color: #7b5e57;
    }

    .lux-item {
        background-color: #fcf9f6;
        border: 1px solid #e8dbd0;
        border-radius: 14px;
        padding: 20px;
        margin-bottom: 20px;
        transition: 0.3s ease;
    }

    .lux-item:hover {
        background-color: #faf3ed;
    }

    .lux-item strong {
        display: inline-block;
        width: 150px;
        color: #5c4a3b;
    }

    .lux-item a {
        color: #c4453c;
        font-weight: bold;
        text-decoration: none;
        display: inline-block;
        margin-top: 10px;
    }

    .lux-item a:hover {
        text-decoration: underline;
    }

    .place-order-btn {
        width: 100%;
        background-color: #a87c6a;
        color: #fff;
        padding: 15px;
        border: none;
        border-radius: 12px;
        font-size: 18px;
        font-weight: bold;
        cursor: pointer;
        transition: background-color 0.3s ease;
        margin-top: 20px;
    }

    .place-order-btn:hover {
        background-color: #8e6453;
    }

    .empty-cart {
        text-align: center;
        font-size: 18px;
        color: #7c6a5d;
    }
</style>

<div class="order-container">
    <h2>Ваша корзина</h2>

    <?php if (empty($_SESSION['cart'])): ?>
        <p class="empty-cart">Корзина пуста. Добавьте апартаменты для бронирования.</p>
    <?php else: ?>
        <?php foreach ($_SESSION['cart'] as $index => $item): ?>
            <div class="lux-item">
                <p><strong>Номер:</strong> <?= $item['product_id'] ?></p>
                <p><strong>Дата заезда:</strong> <?= htmlspecialchars($item['start_date']) ?></p>
                <p><strong>Дата выезда:</strong> <?= htmlspecialchars($item['end_date']) ?></p>
                <p><strong>Гостей:</strong> <?= htmlspecialchars($item['people']) ?></p>
                <a href="?remove=<?= $index ?>">Удалить</a>
            </div>
        <?php endforeach; ?>

        <form method="post">
            <button type="submit" name="place_order" class="place-order-btn">Оформить бронирование</button>
        </form>
    <?php endif; ?>
</div>

<?php require_once "footer.php"; ?>
