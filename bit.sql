-- phpMyAdmin SQL Dump
-- version 4.6.2
-- https://www.phpmyadmin.net/
--
-- Хост: localhost
-- Время создания: Авг 08 2017 г., 09:58
-- Версия сервера: 5.5.57-0ubuntu0.14.04.1-log
-- Версия PHP: 7.1.8-2+ubuntu14.04.1+deb.sury.org+4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `bit`
--

-- --------------------------------------------------------

--
-- Структура таблицы `user`
--

CREATE TABLE `user` (
  `id` int(10) UNSIGNED NOT NULL,
  `email` varchar(255) NOT NULL,
  `passwordHash` varchar(255) NOT NULL,
  `balance` int(11) NOT NULL DEFAULT '0',
  `balanceUpdateDatetime` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `user`
--

INSERT INTO `user` (`id`, `email`, `passwordHash`, `balance`, `balanceUpdateDatetime`) VALUES
(1, 'e@e.ee', '$2y$10$OK1Qc7ebORhkmLdAmsw1BOJCYThOkDthVFDSjkMTX.EQbfD3.XdlW', 989980, '2017-08-08 09:56:50');

-- --------------------------------------------------------

--
-- Структура таблицы `userBalanceChange`
--

CREATE TABLE `userBalanceChange` (
  `id` int(10) UNSIGNED NOT NULL,
  `userId` int(10) UNSIGNED NOT NULL,
  `amount` int(11) NOT NULL,
  `datetime` datetime NOT NULL,
  `serviceId` int(10) UNSIGNED NOT NULL,
  `executed` tinyint(3) UNSIGNED NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `userBalanceChange`
--

INSERT INTO `userBalanceChange` (`id`, `userId`, `amount`, `datetime`, `serviceId`, `executed`) VALUES
(1, 1, -1, '2017-08-08 09:46:25', 7, 0),
(2, 1, -10, '2017-08-08 09:46:29', 7, 0),
(3, 1, -666, '2017-08-08 09:46:33', 7, 0),
(4, 1, -333, '2017-08-08 09:46:37', 7, 0),
(5, 1, -8990, '2017-08-08 09:51:07', 7, 0),
(6, 1, -1, '2017-08-08 09:52:29', 7, 0),
(7, 1, -1, '2017-08-08 09:52:33', 7, 0),
(8, 1, -1, '2017-08-08 09:52:51', 7, 0),
(9, 1, -10, '2017-08-08 09:56:47', 7, 0),
(10, 1, -7, '2017-08-08 09:56:50', 7, 0);

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Индексы таблицы `userBalanceChange`
--
ALTER TABLE `userBalanceChange`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `user`
--
ALTER TABLE `user`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT для таблицы `userBalanceChange`
--
ALTER TABLE `userBalanceChange`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
