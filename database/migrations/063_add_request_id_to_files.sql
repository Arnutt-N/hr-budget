-- Add request_id to files table for budget request attachments
ALTER TABLE files ADD COLUMN request_id INT NULL AFTER folder_id;
ALTER TABLE files ADD CONSTRAINT fk_files_request
    FOREIGN KEY (request_id) REFERENCES budget_requests(id) ON DELETE CASCADE;
CREATE INDEX idx_files_request ON files(request_id);
