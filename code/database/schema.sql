-- sql/database.sql
CREATE DATABASE IF NOT EXISTS football_review CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE football_review;

-- USERS
CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(255) NOT NULL UNIQUE,
  display_name VARCHAR(100) NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('fan','admin') DEFAULT 'fan',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- TEAMS
CREATE TABLE IF NOT EXISTS teams (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  short_name VARCHAR(20),
  crest_url VARCHAR(500),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- MATCHES
CREATE TABLE IF NOT EXISTS matches (
  id INT AUTO_INCREMENT PRIMARY KEY,
  home_team INT,
  away_team INT,
  match_date DATE NOT NULL,
  competition VARCHAR(255),
  venue VARCHAR(255),
  score_home INT DEFAULT 0,
  score_away INT DEFAULT 0,
  highlights_url VARCHAR(500),
  created_by INT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (home_team) REFERENCES teams(id) ON DELETE SET NULL,
  FOREIGN KEY (away_team) REFERENCES teams(id) ON DELETE SET NULL,
  FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- MATCH EVENTS
CREATE TABLE IF NOT EXISTS match_events (
  id INT AUTO_INCREMENT PRIMARY KEY,
  match_id INT NOT NULL,
  minute INT NOT NULL,
  event_type VARCHAR(50) NOT NULL,
  description TEXT,
  media_url VARCHAR(500),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (match_id) REFERENCES matches(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- REVIEWS
CREATE TABLE IF NOT EXISTS reviews (
  id INT AUTO_INCREMENT PRIMARY KEY,
  match_id INT NOT NULL,
  user_id INT,
  rating TINYINT NOT NULL,
  comment TEXT,
  sentiment_score FLOAT DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (match_id) REFERENCES matches(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- REACTIONS (emoji) tied to review
CREATE TABLE IF NOT EXISTS reactions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT,
  review_id INT,
  emoji VARCHAR(50) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT uniq_user_review_emoji UNIQUE(user_id, review_id, emoji),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
  FOREIGN KEY (review_id) REFERENCES reviews(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- EVENT RATINGS (excitement / controversial)
CREATE TABLE IF NOT EXISTS event_ratings (
  id INT AUTO_INCREMENT PRIMARY KEY,
  event_id INT NOT NULL,
  user_id INT,
  excitement TINYINT,
  controversial BOOLEAN DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT uniq_event_user UNIQUE(event_id, user_id),
  FOREIGN KEY (event_id) REFERENCES match_events(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Simple moderation table
CREATE TABLE IF NOT EXISTS moderation (
  id INT AUTO_INCREMENT PRIMARY KEY,
  review_id INT,
  moderator_id INT,
  action VARCHAR(50),
  note TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (review_id) REFERENCES reviews(id) ON DELETE CASCADE,
  FOREIGN KEY (moderator_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- seed admin and teams and a match
INSERT INTO users (email, display_name, password_hash, role)
VALUES ('admin@football.local','Admin', '$2y$10$thisisaplaceholderhash12chars', 'admin')
ON DUPLICATE KEY UPDATE email=email;

INSERT INTO users (email, display_name, password_hash, role)
VALUES ('fan1@example.com','Fan One', '$2y$10$anotherplaceholderhash000000000', 'fan')
ON DUPLICATE KEY UPDATE email=email;

INSERT INTO teams (name, short_name) VALUES
  ('Accra United', 'ACU'),
  ('Kumasi Rovers','KRO')
ON DUPLICATE KEY UPDATE name=name;

-- Add an example match if not present
INSERT INTO matches (home_team, away_team, match_date, competition, venue, score_home, score_away, highlights_url, created_by)
SELECT 1,2,'2025-11-30','English Premier League','White Hart Lane',2,1,'uploads/highlight_demo.mp4',1
WHERE NOT EXISTS (SELECT 1 FROM matches WHERE highlights_url='uploads/highlight_demo.mp4');

-- Users table
CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(255) NOT NULL UNIQUE,
  display_name VARCHAR(100) NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('fan','admin') DEFAULT 'fan',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Teams table
CREATE TABLE IF NOT EXISTS teams (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  short_name VARCHAR(20),
  crest_url VARCHAR(500),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Leagues table
CREATE TABLE IF NOT EXISTS leagues (
    league_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    country VARCHAR(50),
    logo_url VARCHAR(255),
    season VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Matches table
CREATE TABLE IF NOT EXISTS matches (
  id INT AUTO_INCREMENT PRIMARY KEY,
  home_team INT,
  away_team INT,
  match_date DATE NOT NULL,
  competition VARCHAR(255),
  venue VARCHAR(255),
  score_home INT DEFAULT 0,
  score_away INT DEFAULT 0,
  highlights_url VARCHAR(500),
  created_by INT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (home_team) REFERENCES teams(id) ON DELETE SET NULL,
  FOREIGN KEY (away_team) REFERENCES teams(id) ON DELETE SET NULL,
  FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Match Events table
CREATE TABLE IF NOT EXISTS match_events (
  id INT AUTO_INCREMENT PRIMARY KEY,
  match_id INT NOT NULL,
  minute INT NOT NULL,
  event_type VARCHAR(50) NOT NULL,
  description TEXT,
  media_url VARCHAR(500),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (match_id) REFERENCES matches(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Reviews table
CREATE TABLE IF NOT EXISTS reviews (
  id INT AUTO_INCREMENT PRIMARY KEY,
  match_id INT NOT NULL,
  user_id INT,
  rating TINYINT NOT NULL,
  comment TEXT,
  sentiment_score FLOAT DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (match_id) REFERENCES matches(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Reactions (emoji) tied to review
CREATE TABLE IF NOT EXISTS reactions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT,
  review_id INT,
  emoji VARCHAR(50) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT uniq_user_review_emoji UNIQUE(user_id, review_id, emoji),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
  FOREIGN KEY (review_id) REFERENCES reviews(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Event Ratings (excitement / controversial)
CREATE TABLE IF NOT EXISTS event_ratings (
  id INT AUTO_INCREMENT PRIMARY KEY,
  event_id INT NOT NULL,
  user_id INT,
  excitement TINYINT,
  controversial BOOLEAN DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT uniq_event_user UNIQUE(event_id, user_id),
  FOREIGN KEY (event_id) REFERENCES match_events(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Simple moderation table
CREATE TABLE IF NOT EXISTS moderation (
  id INT AUTO_INCREMENT PRIMARY KEY,
  review_id INT,
  moderator_id INT,
  action VARCHAR(50),
  note TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (review_id) REFERENCES reviews(id) ON DELETE CASCADE,
  FOREIGN KEY (moderator_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert admin and test user
INSERT INTO users (email, display_name, password_hash, role)
VALUES ('admin@football.local','Admin', '$2y$10$thisisaplaceholderhash12chars', 'admin')
ON DUPLICATE KEY UPDATE email=email;

INSERT INTO users (email, display_name, password_hash, role)
VALUES ('fan1@example.com','Fan One', '$2y$10$anotherplaceholderhash000000000', 'fan')
ON DUPLICATE KEY UPDATE email=email;

-- Insert example teams
INSERT INTO teams (name, short_name) VALUES
  ('Accra United', 'ACU'),
  ('Kumasi Rovers','KRO')
ON DUPLICATE KEY UPDATE name=name;

-- Add an example match if not present
INSERT INTO matches (home_team, away_team, match_date, competition, venue, score_home, score_away, highlights_url, created_by)
SELECT 1,2,'2025-11-30','Ghana Premier League','Ohene Djan Stadium',2,1,'uploads/highlight_demo.mp4',1
WHERE NOT EXISTS (SELECT 1 FROM matches WHERE highlights_url='uploads/highlight_demo.mp4');
