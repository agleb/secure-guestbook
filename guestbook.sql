-- phpMyAdmin SQL Dump
-- version 4.8.5
-- https://www.phpmyadmin.net/
--
-- Хост: localhost
-- Время создания: Мар 18 2019 г., 15:19
-- Версия сервера: 10.3.12-MariaDB
-- Версия PHP: 7.3.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `secure_guestbook`
--

-- --------------------------------------------------------

--
-- Структура таблицы `banned_ips`
--

CREATE TABLE `banned_ips` (
  `IPBanID` int(11) NOT NULL,
  `IPBanAddress` varchar(15) NOT NULL,
  `IPBanDateTime` datetime NOT NULL,
  `IPBanExpirationDateTime` datetime NOT NULL,
  `IPBanReason` varchar(255) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

-- --------------------------------------------------------

--
-- Структура таблицы `banned_users`
--

CREATE TABLE `banned_users` (
  `UserBanID` int(11) NOT NULL,
  `UserBanUserID` int(11) NOT NULL,
  `UserBanDateTime` datetime NOT NULL,
  `UserBanExpiration` datetime NOT NULL,
  `UserBanReason` varchar(255) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `events`
--

CREATE TABLE `events` (
  `EventID` int(11) NOT NULL,
  `EventUserID` int(11) NOT NULL,
  `EventIP` varchar(15) NOT NULL,
  `EventDateTime` datetime NOT NULL,
  `EventSeverity` tinyint(4) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

-- --------------------------------------------------------

--
-- Структура таблицы `posts`
--

CREATE TABLE `posts` (
  `PostID` varchar(50) NOT NULL,
  `PostMessage` text NOT NULL,
  `PostUserID` int(11) NOT NULL,
  `PostDateTime` datetime NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `replies`
--

CREATE TABLE `replies` (
  `ReplyID` int(11) NOT NULL,
  `ReplyMessage` text NOT NULL,
  `ReplyDateTime` datetime NOT NULL,
  `ReplyUserID` int(11) NOT NULL,
  `ReplyPostID` varchar(50) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `users`
--

CREATE TABLE `users` (
  `UserID` int(11) NOT NULL,
  `UserName` varchar(100) NOT NULL,
  `UserLogin` varchar(100) NOT NULL,
  `UserPassword` varchar(100) NOT NULL,
  `UserDateTime` datetime NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `user_tokens`
--

CREATE TABLE `user_tokens` (
  `UserToken` varchar(100) NOT NULL,
  `UserID` int(11) NOT NULL,
  `ExpirationDateTime` datetime NOT NULL,
  `IPAddress` varchar(15) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `banned_ips`
--
ALTER TABLE `banned_ips`
  ADD PRIMARY KEY (`IPBanID`),
  ADD KEY `check_ban` (`IPBanAddress`,`IPBanExpirationDateTime`);

--
-- Индексы таблицы `banned_users`
--
ALTER TABLE `banned_users`
  ADD PRIMARY KEY (`UserBanID`);

--
-- Индексы таблицы `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`EventID`);

--
-- Индексы таблицы `posts`
--
ALTER TABLE `posts`
  ADD PRIMARY KEY (`PostID`);

--
-- Индексы таблицы `replies`
--
ALTER TABLE `replies`
  ADD PRIMARY KEY (`ReplyID`),
  ADD KEY `replies_to_posts` (`ReplyPostID`);

--
-- Индексы таблицы `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`UserID`) USING BTREE,
  ADD UNIQUE KEY `UserLogin` (`UserLogin`),
  ADD KEY `auth_by_credentials` (`UserLogin`,`UserPassword`);

--
-- Индексы таблицы `user_tokens`
--
ALTER TABLE `user_tokens`
  ADD PRIMARY KEY (`UserToken`),
  ADD KEY `auth_by_token` (`UserToken`,`ExpirationDateTime`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `banned_ips`
--
ALTER TABLE `banned_ips`
  MODIFY `IPBanID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `banned_users`
--
ALTER TABLE `banned_users`
  MODIFY `UserBanID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `events`
--
ALTER TABLE `events`
  MODIFY `EventID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `replies`
--
ALTER TABLE `replies`
  MODIFY `ReplyID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `users`
--
ALTER TABLE `users`
  MODIFY `UserID` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
