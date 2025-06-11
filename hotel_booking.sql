-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Хост: localhost
-- Время создания: Июн 11 2025 г., 19:20
-- Версия сервера: 9.2.0
-- Версия PHP: 8.4.5

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `hotel_booking`
--

-- --------------------------------------------------------

--
-- Структура таблицы `captcha_images`
--

CREATE TABLE `captcha_images` (
  `id` int NOT NULL,
  `image_path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `answer` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `captcha_images`
--

INSERT INTO `captcha_images` (`id`, `image_path`, `answer`) VALUES
(1, 'images/captcha/1.png', '28ivw'),
(2, 'images/captcha/2.png', 'FH2DE'),
(3, 'images/captcha/3.png', 'gwprp'),
(4, 'images/captcha/4.png', '4D7YS'),
(5, 'images/captcha/5.png', 'xmqki'),
(6, 'images/captcha/6.png', 'e5hb'),
(7, 'images/captcha/7.png', 'q98p'),
(8, 'images/captcha/8.png', 'XDHYN');

-- --------------------------------------------------------

--
-- Структура таблицы `orders`
--

CREATE TABLE `orders` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `product_id` int NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `order_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `people` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `product_id`, `start_date`, `end_date`, `order_date`, `people`) VALUES
(1, 1, 4, '2025-04-16', '2025-04-27', '2025-04-23 18:15:28', 1),
(2, 3, 4, '2025-05-08', '2025-05-31', '2025-05-06 11:11:44', 8),
(3, 3, 4, '2025-05-08', '2025-05-24', '2025-05-06 11:18:04', 4),
(4, 3, 3, '2025-05-09', '2025-05-11', '2025-05-06 11:19:33', 2),
(5, 3, 5, '2025-05-09', '2025-05-11', '2025-05-06 11:19:33', 5),
(6, 5, 5, '2025-05-12', '2025-05-31', '2025-05-27 06:15:07', 5),
(7, 3, 5, '2025-06-09', '2025-06-29', '2025-06-06 22:51:08', 4),
(8, 3, 1, '2025-06-12', '2025-06-21', '2025-06-11 08:45:56', 4),
(9, 3, 3, '2025-06-20', '2025-06-27', '2025-06-11 08:45:56', 5);

-- --------------------------------------------------------

--
-- Структура таблицы `products`
--

CREATE TABLE `products` (
  `id` int NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `price` int NOT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `products`
--

INSERT INTO `products` (`id`, `title`, `description`, `price`, `image`) VALUES
(1, 'Люкс с видом на океанc', 'Просторный номер с балконом, джакузи и видом на fghj', 40000, 'lux-ocean.jpg'),
(2, 'Премиум люкс в горах', 'Номер с панорамными окнами, камином и видом на ', 40000, 'mountain-premium.jpg'),
(3, 'Королевский люкс', 'Изысканный интерьер, отдельная гостиная зона и приватный бассейн', 30000, 'royal-suite.jpg'),
(4, 'Спа-номер с сауной', 'Уютный номер с собственной сауной и массажным душем', 30000, 'spa-sauna.jpg'),
(5, 'Бунгало у озера', 'Изолированный домик у озера с террасой и камином', 30000, 'lake-bungalow.jpg'),
(6, 'Современный номер с террасой', 'Минималистичный стиль, терраса с лежаками и видом на сад', 30000, 'modern-terrace.jpg'),
(7, 'Номер для двоих с джакузи', 'Идеально для пары: романтическая обстановка, большая кровать и джакузи', 30000, 'couple-jacuzzi.jpg');

-- --------------------------------------------------------

--
-- Структура таблицы `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `login` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `users`
--

INSERT INTO `users` (`id`, `login`, `name`, `phone`, `email`, `password`, `created_at`) VALUES
(1, 'a', 'a', 'a', 'a@a', '$2y$12$td6rjxz4wG5YHCNZDTfE2.c4EXgG66bwi5ZxALCsfB.yXi6Bxni4W', '2025-04-23 17:39:42'),
(3, 'Bogdaaaaaaaan', 'Bogdan', '89246214400', 'ntnefimov@gmail.com', '$2y$12$KtTsj6B250mBdCazVUcsiOeCLBUhJirAIvqjFGZgGFHY0xicris5S', '2025-05-06 11:11:12'),
(5, 'AloAloAloAlo', 'AloAloAloAlo', '+7 (924) 621-44-00', 'ntnef@gmail.com', '$2y$12$vzhcPohtzUN7eZ7V7nML/.czo3ntmN6.x8b88PwCGbn5GBblvhgi6', '2025-05-27 06:14:17');

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `captcha_images`
--
ALTER TABLE `captcha_images`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Индексы таблицы `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `login` (`login`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `captcha_images`
--
ALTER TABLE `captcha_images`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT для таблицы `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT для таблицы `products`
--
ALTER TABLE `products`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT для таблицы `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Ограничения внешнего ключа сохраненных таблиц
--

--
-- Ограничения внешнего ключа таблицы `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
