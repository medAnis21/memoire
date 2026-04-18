-- ================================================================
--  Sonatrach Trainee Application System — Database Schema
--  Includes: admins table + stagiaires table
-- ================================================================

-- ----------------------------------------------------------------
--  TABLE: admins
--  Default login:  username = admin  |  password = Sonatrach@2024
-- ----------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `admins` (
  `id`            INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `username`      VARCHAR(80)   NOT NULL UNIQUE,
  `password_hash` VARCHAR(255)  NOT NULL   COMMENT 'bcrypt via password_hash()',
  `role`          ENUM('admin') NOT NULL DEFAULT 'admin',
  `created_at`    DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- password = Sonatrach@2024
INSERT INTO `admins` (username, password_hash, role)
VALUES (
  'admin',
  '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
  'admin'
)
ON DUPLICATE KEY UPDATE username = username;

-- ----------------------------------------------------------------
--  TABLE: stagiaires
-- ----------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `stagiaires` (
  `id`               INT UNSIGNED     NOT NULL AUTO_INCREMENT,
  `first_name`       VARCHAR(100)     NOT NULL,
  `last_name`        VARCHAR(100)     NOT NULL,
  `email`            VARCHAR(180)         NULL,
  `phone`            VARCHAR(30)          NULL,
  `specialization`   VARCHAR(150)     NOT NULL,
  `study_year`       VARCHAR(50)      NOT NULL,
  `wilaya`           VARCHAR(100)     NOT NULL,
  `notes`            TEXT                 NULL,
  `image1`           VARCHAR(255)         NULL,
  `image2`           VARCHAR(255)         NULL,
  `image3`           VARCHAR(255)         NULL,
  `status`           ENUM('pending','accepted','rejected','forwarded')
                     NOT NULL DEFAULT 'pending',
  `rejection_reason` TEXT                 NULL,
  `forwarded_at`     DATETIME             NULL,
  `submitted_at`     DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_status`    (`status`),
  INDEX `idx_submitted` (`submitted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sample data (remove in production)
INSERT INTO `stagiaires`
  (first_name, last_name, email, phone, specialization, study_year, wilaya, status, submitted_at)
VALUES
  ('Amira',  'Benali',  'amira.benali@univ.dz',  '0551234567', 'Génie Pétrolier',    '3ème année', 'Alger',       'pending',   NOW() - INTERVAL 2 DAY),
  ('Youcef', 'Hamidi',  'youcef.h@etud.dz',      '0661112233', 'Génie Mécanique',    '2ème année', 'Oran',        'pending',   NOW() - INTERVAL 1 DAY),
  ('Nadia',  'Khelifi', 'nadia.khelifi@mail.dz', '0773334455', 'Informatique',        'Master 1',  'Constantine', 'accepted',  NOW() - INTERVAL 5 DAY),
  ('Karim',  'Meziane', 'karim.m@univ.dz',       '0555667788', 'Génie Civil',         '4ème année','Annaba',      'rejected',  NOW() - INTERVAL 7 DAY),
  ('Sara',   'Ouali',   'sara.ouali@etud.dz',    '0660998877', 'Chimie Industrielle', 'Master 2',  'Skikda',      'forwarded', NOW() - INTERVAL 10 DAY);
