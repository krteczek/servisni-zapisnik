CREATE TABLE team_memberships (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    user_id INT UNSIGNED NOT NULL,
    team_id INT UNSIGNED NOT NULL,

    role_in_team ENUM('monter','predak','mistr') NOT NULL,

    valid_from DATE NOT NULL,
    valid_to DATE NULL,

    CONSTRAINT fk_tm_user
        FOREIGN KEY (user_id) REFERENCES users(id),

    CONSTRAINT fk_tm_team
        FOREIGN KEY (team_id) REFERENCES teams(id)
) ENGINE=InnoDB;