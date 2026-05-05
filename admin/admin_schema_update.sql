-- ── SCHEMA UPDATES FOR ADMIN PANEL ──────────────────────────────────────────

-- 1. Add status & admin notes to stagaires
ALTER TABLE `stagaires`
  ADD COLUMN `status` ENUM('pending','accepted','rejected') NOT NULL DEFAULT 'pending' AFTER `phone`,
  ADD COLUMN `admin_notes` TEXT DEFAULT NULL AFTER `status`;

-- 2. Branch quotas table — one row per branch, controls max accepted stagaires
CREATE TABLE IF NOT EXISTS `branch_quotas` (
  `branch`      VARCHAR(16) NOT NULL,
  `max_quota`   INT UNSIGNED NOT NULL DEFAULT 10,
  `manager_email` VARCHAR(150) DEFAULT NULL,
  `manager_name`  VARCHAR(200) DEFAULT NULL,
  PRIMARY KEY (`branch`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Seed default quotas for all known branches
INSERT INTO `branch_quotas` (branch, max_quota, manager_email, manager_name) VALUES
  ('CP2K', 10, 'manager.cp2k@sonatrach.dz', 'Directeur CP2K'),
  ('CP1K', 10, 'manager.cp1k@sonatrach.dz', 'Directeur CP1K'),
  ('GL1K', 10, 'manager.gl1k@sonatrach.dz', 'Directeur GL1K'),
  ('RA1K', 10, 'manager.ra1k@sonatrach.dz', 'Directeur RA1K'),
  ('RA2K', 10, 'manager.ra2k@sonatrach.dz', 'Directeur RA2K'),
  ('GL1Z', 10, 'manager.gl1z@sonatrach.dz', 'Directeur GL1Z'),
  ('GL2Z', 10, 'manager.gl2z@sonatrach.dz', 'Directeur GL2Z'),
  ('GP1Z', 10, 'manager.gp1z@sonatrach.dz', 'Directeur GP1Z'),
  ('GNL',  10, 'manager.gnl@sonatrach.dz',  'Directeur GNL')
ON DUPLICATE KEY UPDATE branch = branch;
