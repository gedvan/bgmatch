
CREATE TABLE users (
    id          SERIAL PRIMARY KEY,
    username    VARCHAR(255) NOT NULL,
    email       VARCHAR(255) NOT NULL,
    fb_id       VARCHAR(50) NOT NULL,
    image       TEXT NULL,
    date_created TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE groups (
    id          SERIAL PRIMARY KEY,
    name        VARCHAR(255) NOT NULL,
    description TEXT NULL,
    image       TEXT NULL
);

CREATE TABLE groups_users (
    group_id    INTEGER NOT NULL
                REFERENCES groups ON DELETE CASCADE ON UPDATE CASCADE,
    user_id     INTEGER NOT NULL
                REFERENCES users ON DELETE CASCADE ON UPDATE CASCADE,
    is_admin    BOOLEAN NOT NULL DEFAULT FALSE
);

CREATE TABLE games (
    id          SERIAL PRIMARY KEY,
    name        VARCHAR(255) NOT NULL,
    description TEXT NULL,
    image       TEXT NULL
);

CREATE TABLE mettings (
    id          SERIAL PRIMARY KEY,
    datetime    TIMESTAMP NOT NULL,
    group_id    INTEGER NOT NULL
                REFERENCES groups ON DELETE CASCADE ON UPDATE CASCADE,
    info        TEXT NULL
);

CREATE TABLE matches (
    id          SERIAL PRIMARY KEY,
    metting_id  INTEGER NOT NULL
                REFERENCES mettings ON DELETE CASCADE ON UPDATE CASCADE,
    game_id     INTEGER NOT NULL
                REFERENCES games ON DELETE RESTRICT ON UPDATE CASCADE,
    info        TEXT NULL
);

CREATE TABLE matches_users (
    match_id    INTEGER NOT NULL
                REFERENCES matches ON DELETE CASCADE ON UPDATE CASCADE,
    user_id     INTEGER NOT NULL
                REFERENCES users ON DELETE CASCADE ON UPDATE CASCADE,
    is_winner   BOOLEAN NOT NULL DEFAULT FALSE
);

