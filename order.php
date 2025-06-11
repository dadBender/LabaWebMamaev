<?php
require_once "db.php";
require_once "header.php";

// Проверка авторизации
if (!isset($_SESSION['user_id'])) {
    echo "<p style='text-align:center; margin-top: 40px;'>Пожалуйста, <a href='#' onclick=\"document.getElementById('loginModal').style.display='block'\">войдите</a>, чтобы сделать заказ.</p>";
    require_once "footer.php";
    exit();
}

// Получение всех доступных продуктов
$products = $conn->query("SELECT id, title FROM products");

// Обработка формы
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productId = (int)($_POST['product_id'] ?? 0);
    $startDate = trim($_POST['start_date'] ?? '');
    $endDate = trim($_POST['end_date'] ?? '');
    $people = (int)($_POST['people'] ?? 0);

    if ($productId && $startDate && $endDate && $people > 0) {
        $cartItem = [
            'product_id' => $productId,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'people' => $people
        ];

        $_SESSION['cart'][] = $cartItem;

        echo "<p style='text-align:center; color:green; margin-top:20px;'>Товар добавлен в корзину.</p>";
    } else {
        echo "<p style='text-align:center; color:red; margin-top:20px;'>Заполните все поля корректно.</p>";
    }
}
?>
<style>
    .order-container {
        max-width: 600px;
        margin: 60px auto;
        padding: 40px 50px;
        background: linear-gradient(145deg, #1e1e1e, #2a2a2a);
        border-radius: 20px;
        box-shadow: 0 0 25px rgba(255, 215, 0, 0.1);
        font-family: 'Georgia', serif;
        color: #f1f1f1;
        border: 1px solid #444;
    }

    .order-container h2 {
        text-align: center;
        margin-bottom: 30px;
        font-size: 26px;
        color: #ffd700;
        letter-spacing: 1px;
    }

    .order-container form {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    .order-container label {
        font-weight: 600;
        font-size: 15px;
        color: #ccc;
    }

    .order-container input,
    .order-container select {
        padding: 12px 14px;
        border: 1px solid #666;
        border-radius: 10px;
        font-size: 16px;
        background-color: #2e2e2e;
        color: #fff;
        transition: border-color 0.3s, box-shadow 0.3s;
    }

    .order-container input:focus,
    .order-container select:focus {
        border-color: #ffd700;
        box-shadow: 0 0 8px rgba(255, 215, 0, 0.5);
        outline: none;
    }

    .order-container button {
        background-color: #ffd700;
        color: #1e1e1e;
        padding: 14px;
        border: none;
        border-radius: 12px;
        font-size: 17px;
        font-weight: bold;
        cursor: pointer;
        transition: background-color 0.3s, transform 0.2s;
    }

    .order-container button:hover {
        background-color: #e6c200;
        transform: translateY(-2px);
    }

    @media (max-width: 600px) {
        .order-container {
            padding: 25px;
        }

        .order-container h2 {
            font-size: 22px;
        }
    }
</style>


<div class="order-container">
    <h2>Добавить в корзину</h2>
    <form method="post">
        <label for="product_id">Номер:</label>
        <select name="product_id" id="product_id" required>
            <option value="">-- Выберите --</option>
            <?php while ($row = $products->fetch_assoc()): ?>
                <option value="<?= htmlspecialchars($row['id']) ?>"><?= htmlspecialchars($row['title']) ?></option>
            <?php endwhile; ?>
        </select>

        <label for="start_date">Дата заезда:</label>
        <input type="date" name="start_date" required>

        <label for="end_date">Дата выезда:</label>
        <input type="date" name="end_date" required>

        <label for="people">Количество людей:</label>
        <input type="number" name="people" min="1" required>

        <button type="submit">Добавить в корзину</button>
    </form>
</div>

<?php require_once "footer.php"; ?>
