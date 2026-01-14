SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- --------------------------------------------------------

--
-- Структура таблицы `tmt_answer`
--

CREATE TABLE IF NOT EXISTS `tmt_answer` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `question_id` int NOT NULL,
  `value` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`,`question_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `tmt_user`
--

CREATE TABLE IF NOT EXISTS `tmt_user` (
  `id` int NOT NULL AUTO_INCREMENT,
  `chat_id` bigint NOT NULL,
  `lang` enum('ru','en') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `question_id` int DEFAULT NULL,
  `phone` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `referrer` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `envelope_id` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `envelope_send` tinyint(1) NOT NULL DEFAULT '0',
  `envelope_sign` tinyint(1) NOT NULL DEFAULT '0',
  `pipedrive_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `chat_id` (`chat_id`),
  UNIQUE KEY `envelope_id` (`envelope_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `tmt_user_log`
--

CREATE TABLE IF NOT EXISTS `tmt_user_log` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `change_date` datetime NOT NULL,
  `prop_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `old_value` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `new_value` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `ip` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Ограничения внешнего ключа сохраненных таблиц
--

--
-- Ограничения внешнего ключа таблицы `tmt_answer`
--
ALTER TABLE `tmt_answer`
  ADD CONSTRAINT `tmt_answer_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `tmt_user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `tmt_user_log`
--
ALTER TABLE `tmt_user_log`
  ADD CONSTRAINT `tmt_user_log_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `tmt_user` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;
COMMIT;
