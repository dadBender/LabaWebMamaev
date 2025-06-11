<?php
require_once "db.php";
require_once "header.php";
?>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=Open+Sans&display=swap');

    body {
        font-family: 'Open Sans', sans-serif;
        background-color: #f8f5f0;
        margin: 0;
        padding: 0;
    }

    h2 {
        font-family: 'Playfair Display', serif;
        font-size: 36px;
        text-align: center;
        margin-top: 50px;
        margin-bottom: 40px;
        color: #2e2e2e;
    }

    .slider-container {
        max-width: 1000px;
        margin: 0 auto 60px auto;
        position: relative;
        overflow: hidden;
        border-radius: 16px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        background-color: #fff;
    }

    .slider-track {
        display: flex;
        transition: transform 0.6s ease-in-out;
    }

    .slider-card {
        min-width: 100%;
        box-sizing: border-box;
        padding: 40px 30px;
        text-align: center;
    }

    .slider-card img {
        width: 100%;
        height: 420px;
        object-fit: cover;
        border-radius: 12px;
        margin-bottom: 20px;
        box-shadow: 0 6px 18px rgba(0, 0, 0, 0.1);
    }

    .slider-card h3 {
        font-family: 'Playfair Display', serif;
        font-size: 26px;
        color: #3a3a3a;
        margin-bottom: 12px;
    }

    .slider-card p {
        font-size: 16px;
        color: #555;
        margin-bottom: 18px;
    }

    .slider-price {
        font-size: 20px;
        font-weight: bold;
        color: #bfa05a;
        font-family: 'Playfair Display', serif;
    }

    .slider-btn {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        background: #bfa05a;
        color: white;
        border: none;
        padding: 14px 18px;
        cursor: pointer;
        font-size: 24px;
        border-radius: 50%;
        box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        transition: background 0.3s ease;
        z-index: 2;
    }

    .slider-btn:hover {
        background: #d5bc8c;
    }

    .slider-btn.left {
        left: 20px;
    }

    .slider-btn.right {
        right: 20px;
    }
</style>

<h2>Номера класса люкс</h2>

<div class="slider-container">
    <div class="slider-track" id="sliderTrack">
        <?php
        $stmt = $conn->query("SELECT * FROM products");

        while ($row = $stmt->fetch_assoc()) {
            echo '<div class="slider-card">';
            if (!empty($row['image'])) {
                echo '<img src="uploads/' . htmlspecialchars($row['image']) . '" alt="Номер">';
            } else {
                echo '<img src="placeholder.jpg" alt="Нет изображения">';
            }
            echo '<h3>' . htmlspecialchars($row['title']) . '</h3>';
            echo '<p>' . htmlspecialchars($row['description']) . '</p>';
            echo '<div class="slider-price">' . htmlspecialchars($row['price']) . ' ₽ / ночь</div>';
            echo '</div>';
        }
        ?>
    </div>

    <button class="slider-btn left" onclick="prevSlide()">‹</button>
    <button class="slider-btn right" onclick="nextSlide()">›</button>
</div>

<script>
    let currentSlide = 0;
    const track = document.getElementById('sliderTrack');
    const totalSlides = track.children.length;

    function updateSliderPosition() {
        track.style.transform = `translateX(-${currentSlide * 100}%)`;
    }

    function prevSlide() {
        currentSlide = (currentSlide - 1 + totalSlides) % totalSlides;
        updateSliderPosition();
    }

    function nextSlide() {
        currentSlide = (currentSlide + 1) % totalSlides;
        updateSliderPosition();
    }
</script>

<?php require_once "footer.php"; ?>
