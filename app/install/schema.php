<?php
/**
 * Schéma de la base. Idempotent : peut être rejoué sans risque.
 */
function install_schema(PDO $pdo): void
{
    $pdo->exec(<<<SQL
CREATE TABLE IF NOT EXISTS settings (
    key   TEXT PRIMARY KEY,
    value TEXT
);

CREATE TABLE IF NOT EXISTS users (
    id            INTEGER PRIMARY KEY AUTOINCREMENT,
    name          TEXT NOT NULL,
    email         TEXT NOT NULL UNIQUE,
    password_hash TEXT NOT NULL,
    role          TEXT NOT NULL DEFAULT 'editor',
    last_login    TEXT,
    created_at    TEXT DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS login_attempts (
    ip           TEXT,
    attempted_at INTEGER
);

CREATE TABLE IF NOT EXISTS programs (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    tag         TEXT,
    title       TEXT NOT NULL,
    description TEXT,
    image       TEXT,
    link_label  TEXT,
    link_url    TEXT,
    sort_order  INTEGER DEFAULT 0,
    published   INTEGER DEFAULT 1,
    created_at  TEXT DEFAULT CURRENT_TIMESTAMP,
    updated_at  TEXT DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS stats (
    id         INTEGER PRIMARY KEY AUTOINCREMENT,
    grp        TEXT NOT NULL,
    value      TEXT NOT NULL,
    label      TEXT NOT NULL,
    detail     TEXT,
    sort_order INTEGER DEFAULT 0,
    published  INTEGER DEFAULT 1,
    created_at TEXT DEFAULT CURRENT_TIMESTAMP,
    updated_at TEXT DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS events (
    id         INTEGER PRIMARY KEY AUTOINCREMENT,
    slug       TEXT UNIQUE,
    title      TEXT NOT NULL,
    date_label TEXT,
    event_date TEXT,
    location   TEXT,
    type       TEXT DEFAULT 'autre',
    summary    TEXT,
    content    TEXT,
    cover      TEXT,
    in_history INTEGER DEFAULT 1,
    in_events  INTEGER DEFAULT 1,
    featured   INTEGER DEFAULT 0,
    published  INTEGER DEFAULT 1,
    sort_order INTEGER DEFAULT 0,
    created_at TEXT DEFAULT CURRENT_TIMESTAMP,
    updated_at TEXT DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS event_media (
    id         INTEGER PRIMARY KEY AUTOINCREMENT,
    event_id   INTEGER NOT NULL REFERENCES events(id) ON DELETE CASCADE,
    kind       TEXT NOT NULL DEFAULT 'image',
    path       TEXT,
    video_url  TEXT,
    caption    TEXT,
    sort_order INTEGER DEFAULT 0,
    created_at TEXT DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS publications (
    id           INTEGER PRIMARY KEY AUTOINCREMENT,
    title        TEXT NOT NULL,
    authors      TEXT,
    collection   TEXT,
    summary      TEXT,
    cover        TEXT,
    release_date TEXT,
    publisher    TEXT,
    sort_order   INTEGER DEFAULT 0,
    published    INTEGER DEFAULT 1,
    created_at   TEXT DEFAULT CURRENT_TIMESTAMP,
    updated_at   TEXT DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS figures (
    id         INTEGER PRIMARY KEY AUTOINCREMENT,
    name       TEXT NOT NULL,
    role       TEXT,
    bio        TEXT,
    photo      TEXT,
    sort_order INTEGER DEFAULT 0,
    published  INTEGER DEFAULT 1,
    created_at TEXT DEFAULT CURRENT_TIMESTAMP,
    updated_at TEXT DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS member_categories (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    title       TEXT NOT NULL,
    description TEXT,
    note        TEXT,
    sort_order  INTEGER DEFAULT 0,
    published   INTEGER DEFAULT 1,
    created_at  TEXT DEFAULT CURRENT_TIMESTAMP,
    updated_at  TEXT DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS links (
    id         INTEGER PRIMARY KEY AUTOINCREMENT,
    label      TEXT NOT NULL,
    url        TEXT NOT NULL,
    kind       TEXT DEFAULT 'blog',
    sort_order INTEGER DEFAULT 0,
    published  INTEGER DEFAULT 1,
    created_at TEXT DEFAULT CURRENT_TIMESTAMP,
    updated_at TEXT DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS messages (
    id         INTEGER PRIMARY KEY AUTOINCREMENT,
    kind       TEXT DEFAULT 'contact',
    name       TEXT,
    email      TEXT,
    phone      TEXT,
    subject    TEXT,
    body       TEXT,
    ip         TEXT,
    is_read    INTEGER DEFAULT 0,
    created_at TEXT DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS media (
    id            INTEGER PRIMARY KEY AUTOINCREMENT,
    path          TEXT NOT NULL,
    original_name TEXT,
    mime          TEXT,
    size          INTEGER,
    width         INTEGER,
    height        INTEGER,
    created_at    TEXT DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_events_date ON events(event_date);
CREATE INDEX IF NOT EXISTS idx_media_event ON event_media(event_id);
SQL);
}
