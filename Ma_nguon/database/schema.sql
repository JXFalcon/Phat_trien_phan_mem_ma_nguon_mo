-- Chạy trong database lab riêng; không dùng database production.
CREATE TABLE IF NOT EXISTS events (
    id integer PRIMARY KEY,
    title varchar(160) NOT NULL,
    category varchar(30) NOT NULL,
    capacity integer NOT NULL CHECK (capacity > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS registrations (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    event_id integer NOT NULL,
    name varchar(120) NOT NULL CHECK (length(trim(name)) > 0),
    email varchar(200) NOT NULL CHECK (email = lower(trim(email)) COLLATE utf8mb4_bin),
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    CONSTRAINT fk_reg_event FOREIGN KEY (event_id) REFERENCES events(id),
    UNIQUE (event_id, email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT INTO events (id,title,category,capacity) VALUES (1,'PHP Open Day','tech',10),(2,'Design Workshop','design',5),(3,'Git Clinic','tech',20) ON DUPLICATE KEY UPDATE id=id;
