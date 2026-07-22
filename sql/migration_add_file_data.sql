ALTER TABLE notice_attachments ADD COLUMN IF NOT EXISTS file_data TEXT;
ALTER TABLE notice_attachments ADD COLUMN IF NOT EXISTS file_mime VARCHAR(100);
