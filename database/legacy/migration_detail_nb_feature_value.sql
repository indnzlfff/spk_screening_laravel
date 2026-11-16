-- Naive Bayes features include both numeric categories (1-4) and age categories (A-C).
-- VARCHAR preserves either representation in the screening detail table.
ALTER TABLE detail_screening_naive_bayes
  MODIFY nilai_fitur VARCHAR(20) NULL;
