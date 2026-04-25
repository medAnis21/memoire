CREATE TABLE IF NOT EXISTS `users` (
  `id`         INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `username`   VARCHAR(100)  NOT NULL UNIQUE,
  `email`      VARCHAR(150)  NOT NULL UNIQUE,
  `password`   VARCHAR(255)  NOT NULL,
  `role`       ENUM('admin','user') NOT NULL DEFAULT 'user',
  `created_at` DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Optional: seed a default admin account (password: Admin@1234)
INSERT INTO `users` (username, email, password, role) VALUES (
  'admin',
  'admin@sonatrach.dz',
  '$2y$10$FciMif9aoxDY1IXqo/aryecLpuubpQ9Kn9RflhHLp8e0t3hClLFzq',
  'admin'
) ON DUPLICATE KEY UPDATE username = username;

-- Stagaires table to store applicants' information and uploaded document paths
CREATE TABLE IF NOT EXISTS `stagaires` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NULL,
  `first_name` VARCHAR(120) NOT NULL,
  `last_name` VARCHAR(120) NOT NULL,
  `branch` VARCHAR(16) NOT NULL,
  `university` VARCHAR(200) NOT NULL,
  `speciality` VARCHAR(200) NOT NULL,
  `email` VARCHAR(150) NOT NULL,
  `phone` VARCHAR(40) NOT NULL,
  `national_id_card_path` VARCHAR(512) DEFAULT NULL,
  `self_photo_path` VARCHAR(512) DEFAULT NULL,
  `birth_certificate_path` VARCHAR(512) DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_idx` (`user_id`),
  CONSTRAINT `stagaires_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;