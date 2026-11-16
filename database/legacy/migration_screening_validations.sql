-- Additive table used to control when a screening result is shown to a parent.
-- This does not alter applicant, questionnaire, training, or screening rows.
CREATE TABLE IF NOT EXISTS screening_validations (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  screening_id INT UNSIGNED NOT NULL,
  status ENUM('menunggu','dikonfirmasi') NOT NULL DEFAULT 'menunggu',
  catatan_admin TEXT NULL,
  validated_by INT UNSIGNED NULL,
  validated_at TIMESTAMP NULL DEFAULT NULL,
  created_at TIMESTAMP NULL DEFAULT NULL,
  updated_at TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_screening_validations_screening (screening_id),
  KEY idx_screening_validations_status (status),
  CONSTRAINT fk_screening_validations_screening FOREIGN KEY (screening_id)
    REFERENCES screening (id) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_screening_validations_user FOREIGN KEY (validated_by)
    REFERENCES users (id) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
