-- Optional data source for the Recent Fights panel on app/player.php
-- The redesigned profile page works without this table and shows a clean empty state.
-- MineacleCore (or another exporter) must insert/update fight rows for live history.

CREATE TABLE IF NOT EXISTS mineacle_web_fights (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    player_uuid CHAR(32) NOT NULL,
    player_username VARCHAR(32) NOT NULL,
    opponent_uuid CHAR(32) DEFAULT NULL,
    opponent_username VARCHAR(32) NOT NULL,
    result ENUM('WIN', 'LOSS') NOT NULL,
    change_cents BIGINT NOT NULL DEFAULT 0,
    created_at BIGINT NOT NULL,
    PRIMARY KEY (id),
    KEY idx_mineacle_web_fights_player_time (player_uuid, created_at),
    KEY idx_mineacle_web_fights_player_name_time (player_username, created_at),
    KEY idx_mineacle_web_fights_opponent_name (opponent_username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
