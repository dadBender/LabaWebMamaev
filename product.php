<?php
session_start();
require_once "db.php";
require_once "header.php";
?>

<style>
    main {
        padding: 40px;
        max-width: 1200px;
        margin: auto;
        background-color: #1e1e1e;
        color: #f1f1f1;
        font-family: 'Georgia', serif;
    }

    h2 {
        text-align: center;
        color: #ffd700;
        font-size: 36px;
        margin-bottom: 40px;
        letter-spacing: 2px;
    }

    form {
        margin-bottom: 40px;
        display: flex;
        gap: 20px;
        flex-wrap: wrap;
        justify-content: center;
    }

    input, button {
        padding: 12px 16px;
        border-radius: 8px;
        border: 1px solid #444;
        font-size: 16px;
        background-color: #2e2e2e;
        color: #fff;
    }

    input:focus, button:focus {
        outline: none;
        border-color: #ffd700;
    }

    button {
        background-color: #ffd700;
        color: #1e1e1e;
        cursor: pointer;
        transition: background-color 0.3s, transform 0.2s;
    }

    button:hover {
        background-color: #e6c200;
        transform: translateY(-2px);
    }

    .product-container {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 30px;
    }

    .product-card {
        background-color: #2e2e2e;
        border-radius: 15px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        overflow: hidden;
        color: #fff;
        text-align: center;
        transition: transform 0.3s, box-shadow 0.3s;
    }

    .product-card img {
        width: 100%;
        height: 250px;
        object-fit: cover;
        transition: transform 0.3s;
    }

    .product-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.3);
    }

    .product-card:hover img {
        transform: scale(1.05);
    }

    .product-card .card-body {
        padding: 20px;
    }

    .product-card h3 {
        color: #ffd700;
        font-size: 22px;
        margin-bottom: 10px;
    }

    .product-card p {
        font-size: 14px;
        color: #bbb;
        margin-bottom: 10px;
    }

    .product-card .price {
        font-size: 18px;
        font-weight: bold;
        color: #ffd700;
    }

    @media (max-width: 768px) {
        .product-container {
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
        }
    }
</style>


<main style="padding: 40px; max-width: 1200px; margin: auto; background-color: #1e1e1e; color: #f1f1f1;">
    <h2>Фильтрация экодомиков</h2>

    <form method="GET" style="margin-bottom: 40px; display: flex; gap: 20px; flex-wrap: wrap; justify-content: center;">
        <input type="text" name="title" placeholder="Поиск по названию" value="<?= htmlspecialchars($_GET['title'] ?? '') ?>" style="padding: 12px 16px; border-radius: 8px; border: 1px solid #444; font-size: 16px; background-color: #2e2e2e; color: #fff;">
        <input type="number" name="min_price" placeholder="Мин. цена" value="<?= htmlspecialchars($_GET['min_price'] ?? '') ?>" style="padding: 12px 16px; border-radius: 8px; border: 1px solid #444; font-size: 16px; background-color: #2e2e2e; color: #fff;">
        <input type="number" name="max_price" placeholder="Макс. цена" value="<?= htmlspecialchars($_GET['max_price'] ?? '') ?>" style="padding: 12px 16px; border-radius: 8px; border: 1px solid #444; font-size: 16px; background-color: #2e2e2e; color: #fff;">
        <button type="submit" style="padding: 12px 16px; background-color: #ffd700; color: #1e1e1e; font-size: 16px; cursor: pointer;">Фильтровать</button>
    </form>

    <div class="product-container" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 30px;">
        <?php
        $title = $_GET['title'] ?? '';
        $min_price = $_GET['min_price'] ?? '';
        $max_price = $_GET['max_price'] ?? '';

        $query = "SELECT * FROM products WHERE 1";
        if (!empty($title)) {
            $title = $conn->real_escape_string($title);
            $query .= " AND title LIKE '%$title%'";
        }
        if (!empty($min_price)) {
            $query .= " AND price >= " . floatval($min_price);
        }
        if (!empty($max_price)) {
            $query .= " AND price <= " . floatval($max_price);
        }

        $result = $conn->query($query);

        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo '<div class="product-card" style="background-color: #2e2e2e; border-radius: 15px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2); overflow: hidden; color: #fff; text-align: center;">';
                echo '<img src="uploads/' . htmlspecialchars($row['image']) . '" alt="' . htmlspecialchars($row['title']) . '" style="width: 100%; height: 250px; object-fit: cover; transition: transform 0.3s;">';
                echo '<div class="card-body" style="padding: 20px;">';
                echo '<h3 style="color: #ffd700; font-size: 22px; margin-bottom: 10px;">' . htmlspecialchars($row['title']) . '</h3>';
                echo '<p style="font-size: 14px; color: #bbb; margin-bottom: 10px;">' . htmlspecialchars($row['description']) . '</p>';
                echo '<p><strong class="price" style="font-size: 18px; font-weight: bold; color: #ffd700;">' . $row['price'] . ' ₽/ночь</strong></p>';
                echo '</div>';
                echo '</div>';
            }
        } else {
            echo "<p style='color: #fff;'>Ничего не найдено по заданным параметрам.</p>";
        }
        ?>
    </div>
</main>

<?php require_once "footer.php"; ?>