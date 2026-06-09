SET @has_attachment_columns := (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'asset_assignments'
      AND COLUMN_NAME = 'attachment_path'
);

SET @attachment_sql := IF(
    @has_attachment_columns = 0,
    'ALTER TABLE asset_assignments
        ADD COLUMN attachment_path VARCHAR(255) NULL AFTER notes,
        ADD COLUMN attachment_name VARCHAR(255) NULL AFTER attachment_path,
        ADD COLUMN attachment_mime VARCHAR(120) NULL AFTER attachment_name,
        ADD COLUMN attachment_size BIGINT UNSIGNED NULL AFTER attachment_mime',
    'SELECT 1'
);

PREPARE attachment_statement FROM @attachment_sql;
EXECUTE attachment_statement;
DEALLOCATE PREPARE attachment_statement;
