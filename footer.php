<style>
    .big-footer {
        background-color: #2e2e2e;
        color: #f0f0f0;
        padding: 40px 20px;
        box-sizing: border-box;
        display: flex;
        flex-direction: column;
        justify-content: center;
        font-family: 'Segoe UI', sans-serif;
    }

    .footer-columns {
        display: flex;
        justify-content: space-between;
        gap: 40px;
        flex-wrap: wrap;
        max-width: 1200px;
        margin: 0 auto;
    }

    .footer-column {
        flex: 1;
        min-width: 220px;
    }

    .footer-column h3 {
        margin-bottom: 18px;
        font-size: 18px;
        color: #aadccc;
        letter-spacing: 0.5px;
    }

    .footer-column ul {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .footer-column ul li {
        margin-bottom: 10px;
    }

    .footer-column a,
    .footer-column p {
        color: #ccc;
        text-decoration: none;
        font-size: 15px;
        transition: color 0.3s ease;
    }

    .footer-column a:hover {
        color: #fff;
        text-decoration: underline;
    }

    .map-placeholder {
        width: 100%;
        height: 140px;
        background-color: #444;
        border-radius: 10px;
        overflow: hidden;
        position: relative;
        background-image: url("uploads/card2.jpg");
        background-size: cover;
        background-position: center;
        display: flex;
        align-items: flex-end;
        justify-content: flex-start;
        padding: 10px;
    }

    .map-placeholder p {
        margin: 0;
        font-size: 13px;
        background: rgba(0, 0, 0, 0.5);
        padding: 4px 8px;
        border-radius: 6px;
    }

    .footer-bottom {
        text-align: center;
        margin-top: 40px;
        font-size: 14px;
        color: #aaa;
        border-top: 1px solid #444;
        padding-top: 20px;
    }

    @media (max-width: 768px) {
        .footer-columns {
            flex-direction: column;
            align-items: center;
            text-align: center;
        }

        .footer-column {
            margin-bottom: 30px;
        }

        .map-placeholder {
            height: 120px;
        }
    }
</style>

<footer class="big-footer">
    <div class="footer-columns">
        <div class="footer-column">
            <h3>Навигация</h3>
            <ul>
                <li><a href="index.php">Главная</a></li>
                <li><a href="catalog.php">Наши дома</a></li>
                <li><a href="cart.php">Корзина</a></li>
                <li><a href="profile.php">Личный кабинет</a></li>
                <li><a href="guestbook.php">Отзывы</a></li>
                <li><a href="contacts.php">Контакты</a></li>
            </ul>
        </div>

        <div class="footer-column">
            <h3>Контакты</h3>
            <p>📍 Россия, Экопарк «Зелёный лес»</p>
            <p>📞 +7 (999) 123-45-67</p>
            <p>📧 info@ecodomiki.ru</p>
            <p>🕒 Пн–Вс: 9:00 – 21:00</p>
        </div>

        <div class="footer-column">
            <h3>Как нас найти</h3>
            <div class="map-placeholder">
                <p>Экопарк на карте</p>
            </div>
        </div>

        <div class="footer-column">
            <h3>Мы в соцсетях</h3>
            <p><a href="#">VK</a> | <a href="#">Telegram</a> | <a href="#">YouTube</a></p>
            <p style="margin-top: 20px;">Следите за акциями и новинками!</p>
        </div>
    </div>

    <div class="footer-bottom">
        © <?= date('Y') ?> Luxury Hotels. Все права защищены.
    </div>
</footer>
