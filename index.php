<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once 'db.php';
include 'header.php';
?>

<style>
    body {
        background-color: #fdfaf6;
        font-family: 'Georgia', serif;
        color: #333;
    }

    h1, h2, h3 {
        color: #7b5e57;
    }

    main {
        padding: 40px 20px;
        max-width: 1100px;
        margin: auto;
    }

    .section-title {
        text-align: center;
        font-size: 36px;
        margin-bottom: 40px;
        color: #7b5e57;
    }

    .lux-card {
        background: #fff;
        border: 1px solid #e5d8ce;
        border-radius: 12px;
        box-shadow: 0 4px 8px rgba(0,0,0,0.05);
        overflow: hidden;
        transition: transform 0.3s;
    }

    .lux-card:hover {
        transform: translateY(-5px);
    }

    .lux-card img {
        width: 100%;
        height: 200px;
        object-fit: cover;
    }

    .lux-card-content {
        padding: 20px;
    }

    .lux-card-content h3 {
        margin: 0 0 10px;
        font-size: 20px;
        color: #a87c6a;
    }

    .lux-card-content p {
        margin-bottom: 10px;
    }

    .lux-card-content .price {
        font-weight: bold;
        color: #6c4f3d;
        font-size: 18px;
    }

    .icon-section {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 30px;
        text-align: center;
        margin-bottom: 60px;
    }

    .icon-section div {
        padding: 20px;
        border-radius: 12px;
        background: #fff;
        border: 1px solid #e7d9cc;
    }

    .icon-section img {
        margin-bottom: 10px;
    }
</style>

<main>
    <h1 class="section-title">Добро пожаловать в наш бутик-отель</h1>

    <section style="font-size: 18px; line-height: 1.8; margin-bottom: 60px;">
        <p>Погрузитесь в атмосферу элегантности и уюта. Наш бутик-отель — это гармония современного комфорта и изысканного дизайна. Мы предлагаем роскошные апартаменты с панорамными видами, персонализированное обслуживание и уникальную атмосферу для истинных ценителей качественного отдыха.</p>
    </section>

    <h2 class="section-title">Как это работает?</h2>
    <section class="icon-section">
        <div>
            <img src="https://cdn-icons-png.flaticon.com/512/3652/3652191.png" width="60" alt="Выбор номера">
            <h3>Выберите апартаменты</h3>
            <p>Подберите идеальный номер по стилю, виду и комфорту.</p>
        </div>
        <div>
            <img src="https://cdn-icons-png.flaticon.com/512/3063/3063792.png" width="60" alt="Бронирование">
            <h3>Забронируйте онлайн</h3>
            <p>Легко и удобно — выберите даты и оформите бронирование за пару кликов.</p>
        </div>
        <div>
            <img src="https://cdn-icons-png.flaticon.com/512/808/808439.png" width="60" alt="Приезд">
            <h3>Приезжайте и отдыхайте</h3>
            <p>Расслабьтесь, наслаждайтесь атмосферой и сервисом премиум-класса.</p>
        </div>
    </section>

    <h2 class="section-title">Наши апартаменты</h2>
    <section style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 30px;">
        <?php
        $result = $conn->query("SELECT * FROM products LIMIT 6");
        while ($row = $result->fetch_assoc()):
            ?>
            <div class="lux-card">
                <img src="uploads/<?= htmlspecialchars($row['image']) ?>" alt="<?= htmlspecialchars($row['title']) ?>">
                <div class="lux-card-content">
                    <h3><?= htmlspecialchars($row['title']) ?></h3>
                    <p><?= htmlspecialchars($row['description']) ?></p>
                    <p class="price"><?= $row['price'] ?> ₽/ночь</p>
                </div>
            </div>
        <?php endwhile; ?>
    </section>
</main>

<?php include 'footer.php'; ?>
