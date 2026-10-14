-- Run this once against the lms database after backing it up.
-- It enforces the relationships used by the application.

ALTER TABLE users
    ADD UNIQUE INDEX uq_users_member_id (member_id),
    ADD CONSTRAINT fk_users_member
        FOREIGN KEY (member_id) REFERENCES members(member_id)
        ON UPDATE CASCADE ON DELETE SET NULL;

ALTER TABLE borrowings
    ADD COLUMN issued_by_user_id INT NULL AFTER status,
    ADD COLUMN returned_by_user_id INT NULL AFTER issued_by_user_id,
    ADD INDEX idx_borrowings_issued_by_user_id (issued_by_user_id),
    ADD INDEX idx_borrowings_returned_by_user_id (returned_by_user_id),
    ADD CONSTRAINT fk_borrowings_issued_by_user
        FOREIGN KEY (issued_by_user_id) REFERENCES users(id)
        ON UPDATE CASCADE ON DELETE SET NULL,
    ADD CONSTRAINT fk_borrowings_returned_by_user
        FOREIGN KEY (returned_by_user_id) REFERENCES users(id)
        ON UPDATE CASCADE ON DELETE SET NULL;
