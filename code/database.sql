-- Create database
CREATE DATABASE IF NOT EXISTS football_review;
USE football_review;

-- Drop tables if they exist to ensure clean slate for new schema
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS likes;
DROP TABLE IF EXISTS event_ratings;
DROP TABLE IF EXISTS moderation;
DROP TABLE IF EXISTS leagues;
DROP TABLE IF EXISTS reactions;
DROP TABLE IF EXISTS comments;
DROP TABLE IF EXISTS review_replies;
DROP TABLE IF EXISTS reviews;
DROP TABLE IF EXISTS support_messages;
DROP TABLE IF EXISTS match_events;
DROP TABLE IF EXISTS matches;
DROP TABLE IF EXISTS players;
DROP TABLE IF EXISTS teams;
DROP TABLE IF EXISTS venues;
DROP TABLE IF EXISTS competitions;
DROP TABLE IF EXISTS user_sessions;
DROP TABLE IF EXISTS user_tokens;
DROP TABLE IF EXISTS user_profiles;
DROP TABLE IF EXISTS password_resets;
DROP TABLE IF EXISTS users;
SET FOREIGN_KEY_CHECKS = 1;

-- Users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50),
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    display_name VARCHAR(50),
    avatar VARCHAR(255) DEFAULT NULL,
    bio TEXT,
    role ENUM('fan', 'admin', 'user') DEFAULT 'fan',
    status ENUM('active', 'inactive', 'banned') DEFAULT 'active',
    login_attempts INT DEFAULT 0,
    last_failed_login TIMESTAMP NULL,
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- User Profiles table
CREATE TABLE user_profiles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    full_name VARCHAR(100),
    phone VARCHAR(20),
    address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- User Tokens table (for Remember Me)
CREATE TABLE user_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    selector VARCHAR(255) NOT NULL,
    token_hash VARCHAR(255) NOT NULL,
    expires_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Password Resets table
CREATE TABLE password_resets (
    email VARCHAR(100) NOT NULL,
    token VARCHAR(255) NOT NULL,
    used BOOLEAN DEFAULT FALSE,
    created_at DATETIME NOT NULL,
    expires_at DATETIME NOT NULL,
    PRIMARY KEY (token),
    INDEX (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Competitions table
CREATE TABLE competitions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    logo VARCHAR(255),
    country VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Venues table
CREATE TABLE venues (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    city VARCHAR(100),
    country VARCHAR(50),
    capacity INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Teams table
CREATE TABLE teams (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    short_name VARCHAR(10),
    logo VARCHAR(255),
    country VARCHAR(50),
    founded INT,
    stadium VARCHAR(100), -- Kept for backward compatibility or simple display
    venue_id INT, -- Link to Detailed Venue
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (venue_id) REFERENCES venues(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Players table
CREATE TABLE players (
    id INT AUTO_INCREMENT PRIMARY KEY,
    team_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    number INT,
    position ENUM('GK', 'DEF', 'MID', 'FWD'),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (team_id) REFERENCES teams(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Matches table
CREATE TABLE matches (
    id INT AUTO_INCREMENT PRIMARY KEY,
    home_team INT NOT NULL, -- Changed from home_team_id to match code usage commonly seen
    away_team INT NOT NULL, -- Changed from away_team_id
    home_team_score INT DEFAULT NULL,
    away_team_score INT DEFAULT NULL,
    home_team_penalties INT DEFAULT NULL,
    away_team_penalties INT DEFAULT NULL,
    match_date DATE NOT NULL,
    match_time TIME NOT NULL,
    competition_id INT,
    venue_id INT,
    round VARCHAR(50),
    status ENUM('scheduled', 'in_play', 'HT', 'FT', 'ET', 'PEN', 'postponed', 'cancelled') DEFAULT 'scheduled',
    highlights_url VARCHAR(255),
    match_report TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (home_team) REFERENCES teams(id) ON DELETE CASCADE,
    FOREIGN KEY (away_team) REFERENCES teams(id) ON DELETE CASCADE,
    FOREIGN KEY (competition_id) REFERENCES competitions(id) ON DELETE SET NULL,
    FOREIGN KEY (venue_id) REFERENCES venues(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Match Events table
CREATE TABLE match_events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    match_id INT NOT NULL,
    team_id INT NOT NULL,
    player_id INT,
    related_player_id INT, -- For substitutions (player coming off)
    event_type ENUM('goal', 'yellow_card', 'red_card', 'substitution', 'penalty', 'own_goal', 'var_decision') NOT NULL,
    minute INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (match_id) REFERENCES matches(id) ON DELETE CASCADE,
    FOREIGN KEY (team_id) REFERENCES teams(id) ON DELETE CASCADE,
    FOREIGN KEY (player_id) REFERENCES players(id) ON DELETE CASCADE,
    FOREIGN KEY (related_player_id) REFERENCES players(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Reviews table
CREATE TABLE reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    match_id INT NOT NULL,
    rating TINYINT NOT NULL CHECK (rating BETWEEN 1 AND 10),
    comment TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (match_id) REFERENCES matches(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Review Replies table
CREATE TABLE review_replies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    review_id INT NOT NULL,
    user_id INT NOT NULL,
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (review_id) REFERENCES reviews(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Reactions table (Likes/Dislikes)
CREATE TABLE reactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    review_id INT NOT NULL,
    user_id INT NOT NULL,
    type ENUM('like', 'dislike') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_user_reaction (review_id, user_id),
    FOREIGN KEY (review_id) REFERENCES reviews(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Support Messages table
CREATE TABLE support_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    admin_id INT DEFAULT NULL,
    message TEXT NOT NULL,
    is_admin TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (admin_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed Data

-- Competitions
INSERT INTO competitions (name, country) VALUES
('Premier League', 'England'),
('La Liga', 'Spain'),
('Champions League', 'Europe'),
('Bundesliga', 'Germany'),
('Serie A', 'Italy'),
('Ligue 1', 'France');

-- Venues
INSERT INTO venues (name, city, capacity, country) VALUES
('Old Trafford', 'Manchester', 74310, 'England'),
('Anfield', 'Liverpool', 53394, 'England'),
('Emirates Stadium', 'London', 60704, 'England'),
('Santiago Bernabéu', 'Madrid', 81044, 'Spain'),
('Camp Nou', 'Barcelona', 99354, 'Spain'),
('Stamford Bridge', 'London', 40341, 'England'),
('Etihad Stadium', 'Manchester', 53400, 'England'),
('Tottenham Hotspur Stadium', 'London', 62850, 'England'),
('Allianz Arena', 'Munich', 75024, 'Germany'),
('Signal Iduna Park', 'Dortmund', 81365, 'Germany'),
('Allianz Stadium', 'Turin', 41507, 'Italy'),
('San Siro', 'Milan', 75817, 'Italy'),
('Parc des Princes', 'Paris', 47929, 'France'),
('Metropolitano', 'Madrid', 70460, 'Spain'),
('Benito Villamarín', 'Seville', 60721, 'Spain'),
('Estadio de la Cerámica', 'Villarreal', 23500, 'Spain'),
('Ramón Sánchez Pizjuán', 'Seville', 43883, 'Spain'),
('Gewiss Stadium', 'Bergamo', 21000, 'Italy'),
('Stadio Olimpico', 'Rome', 70634, 'Italy'),
('Diego Armando Maradona', 'Naples', 54726, 'Italy'),
('BayArena', 'Leverkusen', 30210, 'Germany'),
('Red Bull Arena', 'Leipzig', 47069, 'Germany'),
('Orange Vélodrome', 'Marseille', 67394, 'France'),
('Stade de la Meinau', 'Strasbourg', 26280, 'France'),
('Groupama Stadium', 'Lyon', 59186, 'France');

-- Teams
INSERT INTO teams (name, short_name, logo, country, founded, stadium, venue_id) VALUES
('Manchester United', 'MUN', 'https://upload.wikimedia.org/wikipedia/en/7/7a/Manchester_United_FC_crest.svg', 'England', 1878, 'Old Trafford', 1),
('Liverpool', 'LIV', 'https://upload.wikimedia.org/wikipedia/en/0/0c/Liverpool_FC.svg', 'England', 1892, 'Anfield', 2),
('Arsenal', 'ARS', 'https://upload.wikimedia.org/wikipedia/en/5/53/Arsenal_FC.svg', 'England', 1886, 'Emirates Stadium', 3),
('Real Madrid', 'RMA', 'https://upload.wikimedia.org/wikipedia/en/5/56/Real_Madrid_CF.svg', 'Spain', 1902, 'Santiago Bernabéu', 4),
('Barcelona', 'BAR', 'https://upload.wikimedia.org/wikipedia/en/4/47/FC_Barcelona_%28crest%29.svg', 'Spain', 1899, 'Camp Nou', 5),
('Chelsea', 'CHE', 'https://upload.wikimedia.org/wikipedia/en/c/cc/Chelsea_FC.svg', 'England', 1905, 'Stamford Bridge', 6),
('Manchester City', 'MCI', 'https://upload.wikimedia.org/wikipedia/en/e/eb/Manchester_City_FC_badge.svg', 'England', 1880, 'Etihad Stadium', 7),
('Tottenham Hotspur', 'TOT', 'https://upload.wikimedia.org/wikipedia/en/b/b4/Tottenham_Hotspur.svg', 'England', 1882, 'Tottenham Hotspur Stadium', 8),
('Bayern Munich', 'BAY', 'https://upload.wikimedia.org/wikipedia/commons/1/1b/FC_Bayern_München_logo_%282017%29.svg', 'Germany', 1900, 'Allianz Arena', 9),
('Borussia Dortmund', 'BVB', 'https://upload.wikimedia.org/wikipedia/commons/6/67/Borussia_Dortmund_logo.svg', 'Germany', 1909, 'Signal Iduna Park', 10),
('Juventus', 'JUV', 'https://upload.wikimedia.org/wikipedia/commons/0/0a/Juventus_FC_2017_logo.svg', 'Italy', 1897, 'Allianz Stadium', 11),
('AC Milan', 'ACM', 'https://upload.wikimedia.org/wikipedia/commons/d/d0/Logo_of_AC_Milan.svg', 'Italy', 1899, 'San Siro', 12),
('Inter Milan', 'INT', 'https://upload.wikimedia.org/wikipedia/commons/0/05/FC_Internazionale_Milano_2021.svg', 'Italy', 1908, 'San Siro', 12),
('Paris Saint-Germain', 'PSG', 'https://upload.wikimedia.org/wikipedia/en/a/a7/Paris_Saint-Germain_F.C..svg', 'France', 1970, 'Parc des Princes', 13),
('Atletico Madrid', 'ATM', 'https://upload.wikimedia.org/wikipedia/en/f/f4/Atletico_Madrid_2017_logo.svg', 'Spain', 1903, 'Metropolitano', 14),
('Real Betis', 'BET', 'https://upload.wikimedia.org/wikipedia/en/1/13/Real_betis_logo.svg', 'Spain', 1907, 'Benito Villamarín', 15),
('Villarreal', 'VIL', 'https://upload.wikimedia.org/wikipedia/en/b/b9/Villarreal_CF_logo-17.svg', 'Spain', 1923, 'Estadio de la Cerámica', 16),
('Sevilla', 'SEV', 'https://upload.wikimedia.org/wikipedia/en/3/3b/Sevilla_FC_logo.svg', 'Spain', 1890, 'Ramón Sánchez Pizjuán', 17),
('Atalanta', 'ATA', 'https://upload.wikimedia.org/wikipedia/en/6/66/AtalantaBC.svg', 'Italy', 1907, 'Gewiss Stadium', 18),
('Roma', 'ROM', 'https://upload.wikimedia.org/wikipedia/en/f/f7/AS_Roma_logo_%282017%29.svg', 'Italy', 1927, 'Stadio Olimpico', 19),
('Napoli', 'NAP', 'https://upload.wikimedia.org/wikipedia/commons/0/05/SSC_Napoli_2024.svg', 'Italy', 1926, 'Diego Armando Maradona', 20),
('Bayer Leverkusen', 'LEV', 'https://upload.wikimedia.org/wikipedia/en/5/59/Bayer_04_Leverkusen_logo.svg', 'Germany', 1904, 'BayArena', 21),
('RB Leipzig', 'RBL', 'https://upload.wikimedia.org/wikipedia/en/0/04/RB_Leipzig_2014_logo.svg', 'Germany', 2009, 'Red Bull Arena', 22),
('Marseille', 'OM', 'https://upload.wikimedia.org/wikipedia/commons/d/d8/Olympique_Marseille_logo.svg', 'France', 1899, 'Orange Vélodrome', 23),
('Strasbourg', 'RCS', 'https://upload.wikimedia.org/wikipedia/commons/7/76/Racing_Club_de_Strasbourg_logo.svg', 'France', 1906, 'Stade de la Meinau', 24),
('Lyon', 'OL', 'https://upload.wikimedia.org/wikipedia/en/e/e2/Olympique_Lyonnais_logo.svg', 'France', 1950, 'Groupama Stadium', 25);

-- Players (25-26 Squads)

-- Manchester United (ID: 1)
INSERT INTO players (team_id, name, number, position) VALUES
(1, 'Senne Lammens', 31, 'GK'), (1, 'Altay Bayindir', 1, 'GK'),
(1, 'Diogo Dalot', 20, 'DEF'), (1, 'Lisandro Martinez', 6, 'DEF'), (1, 'Matthijs de Ligt', 4, 'DEF'), (1, 'Luke Shaw', 23, 'DEF'), (1, 'Noussair Mazraoui', 3, 'DEF'), (1, 'Leny Yoro', 15, 'DEF'), (1, 'Harry Maguire', 5, 'DEF'), (1, 'Patrick Dorgu', 13, 'DEF'),
(1, 'Kobbie Mainoo', 37, 'MID'), (1, 'Bruno Fernandes', 8, 'MID'), (1, 'Manuel Ugarte', 25, 'MID'), (1, 'Casemiro', 18, 'MID'), (1, 'Mason Mount', 7, 'MID'),
(1, 'Mattheus Cunha', 10, 'FWD'), (1, 'Bryan Mbeumo', 19, 'FWD'), (1, 'Benjamin Sesko', 30, 'FWD'), (1, 'Joshua Zirkzee', 11, 'FWD'), (1, 'Amad Diallo', 16, 'FWD');

-- Liverpool (ID: 2)
INSERT INTO players (team_id, name, number, position) VALUES
(2, 'Alisson Becker', 1, 'GK'), (2, 'Giogio Marmadashvili', 25, 'GK'),
(2, 'Jeremie Frimpong', 30, 'DEF'), (2, 'Virgil van Dijk', 4, 'DEF'), (2, 'Ibrahima Konate', 5, 'DEF'), (2, 'Andrew Robertson', 26, 'DEF'), (2, 'Milos Kerkez', 6, 'DEF'), (2, 'Joe Gomez', 21, 'DEF'), (2, 'Conor Bradley', 12, 'DEF'),
(2, 'Ryan Gravenberch', 38, 'MID'), (2, 'Alexis Mac Allister', 10, 'MID'), (2, 'Dominik Szoboszlai', 8, 'MID'),
(2, 'Mohamed Salah', 11, 'FWD'), (2, 'Florian Wirtz', 7, 'FWD'), (2, 'Hugo Ekitke', 22, 'FWD'), (2, 'Alexander Isak', 9, 'FWD'), (2, 'Cody Gakpo', 18, 'FWD'), (2, 'Federico Chiesa', 14, 'FWD');

-- Arsenal (ID: 3)
INSERT INTO players (team_id, name, number, position) VALUES
(3, 'David Raya', 1, 'GK'), (3, 'Kepa Arrizabalaga', 13, 'GK'),
(3, 'Ben White', 4, 'DEF'), (3, 'William Saliba', 2, 'DEF'), (3, 'Gabriel Magalhaes', 6, 'DEF'), (3, 'Jurrien Timber', 12, 'DEF'), (3, 'Riccardo Calafiori', 33, 'DEF'), (3, 'Cristhian Mosquera', 3, 'DEF'), (3, 'Piero Hincapié', 5, 'DEF'), (3, 'Myles Lewis-Skelly', 49, 'DEF'),
(3, 'Declan Rice', 41, 'MID'), (3, 'Eberechie Eze', 10, 'MID'), (3, 'Martin Ødegaard', 8, 'MID'), (3, 'Mikel Merino', 23, 'MID'), (3, 'Martin Zubimendi', 36, 'MID'), (3, 'Christian Nørgaard', 21, 'MID'),
(3, 'Bukayo Saka', 7, 'FWD'), (3, 'Kai Havertz', 29, 'FWD'), (3, 'Gabriel Martinelli', 11, 'FWD'), (3, 'Leandro Trossard', 19, 'FWD'), (3, 'Noni Madueke', 20, 'FWD'), (3, 'Gabriel Jesus', 9, 'FWD'), (3, 'Viktor Gyökeres', 14, 'FWD');

-- Real Madrid (ID: 4)
INSERT INTO players (team_id, name, number, position) VALUES
(4, 'Thibaut Courtois', 1, 'GK'), (4, 'Andriy Lunin', 13, 'GK'),
(4, 'Dani Carvajal', 2, 'DEF'), (4, 'Eder Militao', 3, 'DEF'), (4, 'Antonio Rudiger', 22, 'DEF'), (4, 'Ferland Mendy', 23, 'DEF'), (4, 'David Alaba', 4, 'DEF'), (4, 'Trent Alexander-Arnold', 12, 'DEF'), (4, 'Fran Garcia', 20, 'DEF'), (4, 'Álvara Carreras', 18, 'DEF'), (4, 'Dean Hujsen', 24, 'DEF'),
(4, 'Aurelien Tchouameni', 14, 'MID'), (4, 'Federico Valverde', 8, 'MID'), (4, 'Jude Bellingham', 5, 'MID'), (4, 'Eduardo Camavinga', 6, 'MID'), (4, 'Brahim Diaz', 21, 'MID'), (4, 'Arda Guler', 15, 'MID'),
(4, 'Kylian Mbappe', 9, 'FWD'), (4, 'Vinicius Junior', 7, 'FWD'), (4, 'Rodrygo', 11, 'FWD'), (4, 'Endrick', 16, 'FWD'), (4, 'Gonzalo Garcia', 16, 'FWD'), (4, 'Franco Mastantuono', 30, 'FWD');

-- Barcelona (ID: 5)
INSERT INTO players (team_id, name, number, position) VALUES
(5, 'Marc-Andre ter Stegen', 1, 'GK'), (5, 'Joan Garcia', 25, 'GK'), (5, 'Wojciech Szczesny', 25, 'GK'),
(5, 'Jules Kounde', 23, 'DEF'), (5, 'Pau Cubarsi', 2, 'DEF'), (5, 'Eric Garcia', 24, 'DEF'), (5, 'Alejandro Balde', 3, 'DEF'), (5, 'Andreas Christensen', 15, 'DEF'), (5, 'Ronald Araujo', 4, 'DEF'),
(5, 'Marc Casado', 17, 'MID'), (5, 'Pedri', 8, 'MID'), (5, 'Dani Olmo', 20, 'MID'), (5, 'Gavi', 6, 'MID'), (5, 'Frenkie de Jong', 21, 'MID'), (5, 'Fermin Lopez', 16, 'MID'),
(5, 'Lamine Yamal', 10, 'FWD'), (5, 'Robert Lewandowski', 9, 'FWD'), (5, 'Raphinha', 11, 'FWD'), (5, 'Ferran Torres', 7, 'FWD'), (5, 'Marcus Rashford', 14, 'FWD');

-- Chelsea (ID: 6)
INSERT INTO players (team_id, name, number, position) VALUES
(6, 'Robert Sanchez', 1, 'GK'), (6, 'Filip Jörgensen', 12, 'GK'),
(6, 'Reece James', 24, 'DEF'), (6, 'Levi Colwill', 6, 'DEF'), (6, 'Wesley Fofana', 29, 'DEF'), (6, 'Marc Cucurella', 3, 'DEF'), (6, 'Jorrel Hato', 21, 'DEF'), (6, 'Malo Gusto', 27, 'DEF'),
(6, 'Moises Caicedo', 25, 'MID'), (6, 'Enzo Fernandez', 8, 'MID'), (6, 'Cole Palmer', 10, 'MID'), (6, 'Romeo Lavia', 45, 'MID'),
(6, 'Jamie Gittens', 11, 'FWD'), (6, 'Joao Pedro', 20, 'FWD'), (6, 'Liam Delap', 9, 'FWD'), (6, 'Pedro Neto', 7, 'FWD'), (6, 'Alejandro Garnacho', 49, 'FWD'), (6, 'Willian Estevao', 41, 'FWD');

-- Manchester City (ID: 7)
INSERT INTO players (team_id, name, number, position) VALUES
(7, 'Gianluigi Donnarumma', 25, 'GK'), (7, 'Stefan Ortega', 18, 'GK'),
(7, 'Rico Lewis', 82, 'DEF'), (7, 'Ruben Dias', 3, 'DEF'), (7, 'Abdukodir Khusanov', 45, 'DEF'), (7, 'Josko Gvardiol', 24, 'DEF'), (7, 'Nathan Ake', 6, 'DEF'), (7, 'John Stones', 5, 'DEF'),
(7, 'Rodri', 16, 'MID'), (7, 'Bernardo Silva', 20, 'MID'), (7, 'Rayan Cherki', 10, 'MID'), (7, 'Tijjani Reijnders', 4, 'MID'), (7, 'Mateo Kovacic', 8, 'MID'), (7, 'Matheus Nunes', 27, 'MID'), (7, 'Nico O\'Reilly', 33, 'MID'), (7, 'Nico Gonazales', 14, 'MID'),
(7, 'Phil Foden', 47, 'FWD'), (7, 'Erling Haaland', 9, 'FWD'), (7, 'Jeremy Doku', 11, 'FWD'), (7, 'Savinho', 26, 'FWD'), (7, 'Omar Marmoush', 7, 'FWD'), (7, 'Oscar Bobb', 52, 'FWD');

-- Tottenham Hotspur (ID: 8)
INSERT INTO players (team_id, name, number, position) VALUES
(8, 'Guglielmo Vicario', 1, 'GK'), (8, 'Antonin Kinsky', 31, 'GK'),
(8, 'Pedro Porro', 23, 'DEF'), (8, 'Cristian Romero', 17, 'DEF'), (8, 'Micky van de Ven', 37, 'DEF'), (8, 'Destiny Udogie', 13, 'DEF'), (8, 'Radu Dragusin', 6, 'DEF'), (8, 'Djed Spence', 24, 'DEF'),
(8, 'Yves Bissouma', 8, 'MID'), (8, 'James Maddison', 10, 'MID'), (8, 'Pape Matar Sarr', 29, 'MID'), (8, 'Rodrigo Bentancur', 30, 'MID'), (8, 'Lucas Bergvall', 14, 'MID'), (8, 'Archie Gray', 14, 'MID'), (8, 'Xavi Simons', 7, 'MID'),
(8, 'Randal Kolo Muani', 39, 'FWD'), (8, 'Dominic Solanke', 19, 'FWD'), (8, 'Brennan Johnson', 22, 'FWD'), (8, 'Dejan Kulusevski', 21, 'FWD'), (8, 'Mathus Tel', 11, 'FWD'), (8, 'Richarlison', 9, 'FWD');

-- Bayern Munich (ID: 9)
INSERT INTO players (team_id, name, number, position) VALUES
(9, 'Manuel Neuer', 1, 'GK'), (9, 'Sven Ulreich', 26, 'GK'),
(9, 'Raphael Guerreiro', 22, 'DEF'), (9, 'Dayot Upamecano', 2, 'DEF'), (9, 'Kim Min-jae', 3, 'DEF'), (9, 'Alphonso Davies', 19, 'DEF'), (9, 'Jonathan Tah', 4, 'DEF'), (9, 'Sacha Boey', 23, 'DEF'),
(9, 'Joshua Kimmich', 6, 'MID'), (9, 'Aleksandar Pavlovic', 45, 'MID'), (9, 'Joao Palhinha', 16, 'MID'), (9, 'Konrad Laimer', 27, 'MID'), (9, 'Leon Goretzka', 8, 'MID'),
(9, 'Michael Olise', 17, 'FWD'), (9, 'Jamal Musiala', 42, 'MID'), (9, 'Serge Gnabry', 7, 'FWD'), (9, 'Harry Kane', 9, 'FWD'), (9, 'Luis Diaz', 14, 'FWD'), (9, 'Nicolas Jackson', 11, 'FWD'), (9, 'Lenart Karl', 42, 'FWD');

-- Borussia Dortmund (ID: 10)
INSERT INTO players (team_id, name, number, position) VALUES
(10, 'Gregor Kobel', 1, 'GK'), (10, 'Alexander Meyer', 33, 'GK'),
(10, 'Aaron Anselmino', 28, 'DEF'), (10, 'Waldemar Anton', 3, 'DEF'), (10, 'Nico Schlotterbeck', 4, 'DEF'), (10, 'Julian Ryerson', 26, 'DEF'), (10, 'Niklas Sule', 25, 'DEF'),
(10, 'Emre Can', 23, 'MID'), (10, 'Pascal Groß', 13, 'MID'), (10, 'Carney Chukwuemeka', 17, 'MID'), (10, 'Felix Nmecha', 8, 'MID'), (10, 'Yan Couto', 2, 'MID'), (10, 'Julian Brandt', 10, 'MID'), (10, 'Jobe Bellingham', 7, 'GK'), (10, 'Marcel Sabitzer', 20, 'GK'),
(10, 'Serhou Guirassy', 9, 'FWD'), (10, 'Karim Adeyemi', 27, 'FWD'), (10, 'Fabio Silva', 21, 'FWD'), (10, 'Maximilian Beier', 14, 'FWD');

-- Juventus (ID: 11)
INSERT INTO players (team_id, name, number, position) VALUES
(11, 'Michele Di Gregorio', 29, 'GK'), (11, 'Mattia Perin', 1, 'GK'),
(11, 'Llyod Kelly', 6, 'DEF'), (11, 'Federico Gatti', 4, 'DEF'), (11, 'Bremer', 3, 'DEF'), (11, 'Daniele Rugani', 24, 'DEF'), (11, 'Pierre Kalulu', 15, 'DEF'), (11, 'Jonas Rouhi', 40, 'DEF'), (11, 'Juan Cabal', 32, 'DEF'),
(11, 'Manuel Locatelli', 5, 'MID'), (11, 'Khephren Thuram', 19, 'MID'), (11, 'Teun Koopmeiners', 8, 'MID'), (11, 'Filip Kostic', 18, 'MID'), (11, 'Weston McKennie', 16, 'MID'),
(11, 'Kenan Yildiz', 10, 'FWD'), (11, 'Dusan Vlahovic', 9, 'FWD'), (11, 'Francisco Conceicao', 7, 'FWD'), (11, 'Edon Zhegrova', 11, 'FWD'), (11, 'Jonathan David', 30, 'FWD');

-- AC Milan (ID: 12)
INSERT INTO players (team_id, name, number, position) VALUES
(12, 'Mike Maignan', 16, 'GK'), (12, 'Pietro Terracciano', 1, 'GK'),
(12, 'Zachary Athekame', 24, 'DEF'), (12, 'Fikayo Tomori', 23, 'DEF'), (12, 'Strahinja Pavlovic', 31, 'DEF'), (12, 'Pervis Estupiñán', 2, 'DEF'), (12, 'Matteo Gabbia', 46, 'DEF'), (12, 'Koni De Winter', 5, 'DEF'), (12, 'Davide Bartesaghi', 33, 'DEF'),
(12, 'Youssouf Fofana', 29, 'MID'), (12, 'Samuele Ricci', 4, 'MID'), (12, 'Ruben Loftus-Cheek', 8, 'MID'), (12, 'Luka Modric', 14, 'MID'), (12, 'Adrien Rabiot', 12, 'MID'),
(12, 'Christian Pulisic', 11, 'FWD'), (12, 'Christopher Nkunku', 18, 'FWD'), (12, 'Rafael Leao', 10, 'FWD'), (12, 'Santiago Gimenez', 7, 'FWD'), (12, 'Alexis Saelemaekers', 56, 'FWD');

-- Inter Milan (ID: 13)
INSERT INTO players (team_id, name, number, position) VALUES
(13, 'Yann Sommer', 1, 'GK'), (13, 'Josep Martinez', 13, 'GK'),
(13, 'Manuel Akanji', 25, 'DEF'), (13, 'Francesco Acerbi', 15, 'DEF'), (13, 'Alessandro Bastoni', 95, 'DEF'), (13, 'Stefan de Vrij', 6, 'DEF'), (13, 'Yann Bisseck', 31, 'DEF'), (13, 'Denzel Dumfries', 2, 'DEF'), (13, 'Federico Dimarco', 32, 'DEF'), (13, 'Matteo Darmian', 36, 'DEF'),
(13, 'Nicolo Barella', 23, 'MID'), (13, 'Hakan Calhanoglu', 20, 'MID'), (13, 'Henrikh Mkhitaryan', 22, 'MID'), (13, 'Piotr Zielinski', 7, 'MID'), (13, 'Davide Frattesi', 16, 'MID'),
(13, 'Marcus Thuram', 9, 'FWD'), (13, 'Lautaro Martinez', 10, 'FWD'), (13, 'Pio Esposito', 94, 'FWD'), (13, 'Ange-Yoan Bonny', 14, 'FWD');

-- Paris Saint-Germain (ID: 14)
INSERT INTO players (team_id, name, number, position) VALUES
(14, 'Lucas Chevalier', 30, 'GK'), (14, 'Matvey Safonov', 39, 'GK'),
(14, 'Achraf Hakimi', 2, 'DEF'), (14, 'Marquinhos', 5, 'DEF'), (14, 'Willian Pacho', 51, 'DEF'), (14, 'Nuno Mendes', 25, 'DEF'), (14, 'Lucas Beraldo', 35, 'DEF'), (14, 'Lucas Hernandez', 21, 'DEF'), (14, 'Ilya Zabarnyi', 6, 'DEF'),
(14, 'Vitinha', 17, 'MID'), (14, 'Joao Neves', 87, 'MID'), (14, 'Warren Zaire-Emery', 33, 'MID'), (14, 'Fabian Ruiz', 8, 'MID'),
(14, 'Ousmane Dembele', 10, 'FWD'), (14, 'Khvicha Kvaratskhelia', 7, 'FWD'), (14, 'Bradley Barcola', 29, 'FWD'), (14, 'Gonçalo Ramos', 9, 'FWD'), (14, 'Lee Kang-in', 19, 'MID'), (14, 'Desire Doue', 14, 'FWD');

-- Atletico Madrid (ID: 15)
INSERT INTO players (team_id, name, number, position) VALUES
(15, 'Jan Oblak', 13, 'GK'), (15, 'Juan Musso', 1, 'GK'),
(15, 'Nahuel Molina', 16, 'DEF'), (15, 'Robin Le Normand', 24, 'DEF'), (15, 'Jose Maria Gimenez', 2, 'DEF'), (15, 'Ruggeri', 3, 'DEF'), (15, 'Clement Lenglet', 15, 'DEF'), (15, 'David Hancko', 17, 'DEF'),
(15, 'Koke', 6, 'MID'), (15, 'Conor Gallagher', 4, 'MID'), (15, 'Pablo Barrios', 8, 'MID'), (15, 'Alex Baena', 10, 'MID'), (15, 'Marcos Llorente', 14, 'MID'), (15, 'Thiago Almada', 11, 'MID'), (15, 'Javi Galán', 21, 'MID'),
(15, 'Antoine Griezmann', 7, 'FWD'), (15, 'Julian Alvarez', 19, 'FWD'), (15, 'Alexander Sorloth', 9, 'FWD'), (15, 'Giacomo Raspadori', 22, 'FWD'), (15, 'Nico Gonalez', 23, 'FWD'), (15, 'Giuliano Simeone', 20, 'FWD');

-- Real Betis (ID: 16)
INSERT INTO players (team_id, name, number, position) VALUES
(16, 'Álvara Valles', 1, 'GK'), (16, 'Adrian', 13, 'GK'),
(16, 'Hector Bellerin', 2, 'DEF'), (16, 'Marc Bartra', 5, 'DEF'), (16, 'Diego Llorente', 3, 'DEF'), (16, 'Romain Perraud', 15, 'DEF'), (16, 'Ricardo Rodriguez', 12, 'DEF'), (16, 'Natan', 4, 'DEF'), (16, 'Junior Firpo', 23, 'DEF'),
(16, 'Marc Roca', 21, 'MID'), (16, 'Johnny Cardoso', 4, 'MID'), (16, 'Pablo Fornals', 18, 'MID'), (16, 'Giovani Lo Celso', 20, 'MID'), (16, 'Isco', 22, 'MID'), (16, 'William Carvalho', 14, 'MID'),
(16, 'Antony', 7, 'FWD'), (16, 'Abde Ezzalzouli', 10, 'FWD'), (16, 'Vitor Roque', 8, 'FWD'), (16, 'Cedric Bakambu', 11, 'FWD'), (16, 'Chimy Avila', 9, 'FWD');

-- Villarreal (ID: 17)
INSERT INTO players (team_id, name, number, position) VALUES
(17, 'Diego Conde', 13, 'GK'), (17, 'Luiz Junior', 1, 'GK'),
(17, 'Renato Veiga', 12, 'DEF'), (17, 'Willy Kambwala', 5, 'DEF'), (17, 'Rafa Marín', 4, 'DEF'), (17, 'Sergi Cardona', 23, 'DEF'), (17, 'Logan Costa', 2, 'DEF'), (17, 'Juan Foyth', 8, 'DEF'),
(17, 'Dani Parejo', 10, 'MID'), (17, 'Santi Comesana', 14, 'MID'), (17, 'Thomas Partey', 16, 'MID'), (17, 'Pape Gueye', 18, 'MID'),
(17, 'Tajon Buchanan', 17, 'FWD'), (17, 'Ayoze Perez', 22, 'FWD'), (17, 'Georges Mikautadze', 9, 'FWD'), (17, 'Gerard Moreno', 7, 'FWD'), (17, 'Nicolas Pepe', 19, 'FWD');

-- Sevilla (ID: 18)
INSERT INTO players (team_id, name, number, position) VALUES
(18, 'Orjan Nyland', 13, 'GK'), (18, 'Odysseas Vlachodimos', 1, 'GK'),
(18, 'Cesar Azpilicueta', 3, 'DEF'), (18, 'Kike Salas', 4, 'DEF'), (18, 'Tanguy Nianzou', 5, 'DEF'), (18, 'Fabio Cardoso', 15, 'DEF'), (18, 'Juanlu Sanchez', 16, 'DEF'), (18, 'Marcão', 23, 'DEF'),
(18, 'Nemanja Gudelj', 6, 'MID'), (18, 'Lucien Agoumé', 18, 'MID'), (18, 'Djibril Sow', 20, 'MID'), (18, 'Batista Mendy', 19, 'MID'),
(18, 'Alexis Sanchez', 10, 'FWD'), (18, 'Rubén Vargas', 7, 'FWD'), (18, 'Chidera Ejuke', 21, 'FWD'), (18, 'Isaac Romero', 7, 'FWD'), (18, 'Akor Adams', 9, 'MID');

-- Atalanta (ID: 19)
INSERT INTO players (team_id, name, number, position) VALUES
(19, 'Marco Carnesecchi', 29, 'GK'), (19, 'Marco Sportiello', 57, 'GK'),
(19, 'Berat Djimsiti', 19, 'DEF'), (19, 'Isak Hien', 4, 'DEF'), (19, 'Sead Kolasinac', 23, 'DEF'), (19, 'Odilon Kossounou', 3, 'DEF'), (19, 'Davide Zappacosta', 77, 'DEF'), (19, 'Raoul Bellanova', 16, 'DEF'), (19, 'Honest Ahanor', 69, 'DEF'),
(19, 'Marten de Roon', 15, 'MID'), (19, 'Ederson', 13, 'MID'), (19, 'Mario Pasalic', 8, 'MID'), (19, 'Lazar Samardzic', 24, 'MID'), (19, 'Yunus Musah', 6, 'MID'),
(19, 'Charles De Ketelaere', 17, 'FWD'), (19, 'Gianluca Scammaca', 9, 'FWD'), (19, 'Ademola Lookman', 11, 'FWD'), (19, 'Kamaldeen Sulemana', 7, 'FWD');

-- Roma (ID: 20)
INSERT INTO players (team_id, name, number, position) VALUES
(20, 'Mile Svilar', 99, 'GK'), (20, 'Pierluigi Gollini', 95, 'GK'),
(20, 'Zeki Celik', 19, 'DEF'), (20, 'Gianluca Mancini', 23, 'DEF'), (20, 'Evan Ndicka', 5, 'DEF'), (20, 'Angelino', 3, 'DEF'), (20, 'Mario Hermoso', 22, 'DEF'), (20, 'Konstantinos Tsimikas', 12, 'DEF'), (20, 'Devyne Rensch', 2, 'DEF'),
(20, 'Bryan Cristante', 4, 'MID'), (20, 'Manu Kone', 17, 'MID'), (20, 'Lorenzo Pellegrini', 7, 'MID'), (20, 'Niccolo Pisilli', 61, 'MID'), (20, 'Eduardo Bove', 16, 'MID'),
(20, 'Matias Soule', 18, 'FWD'), (20, 'Artem Dovbyk', 11, 'FWD'), (20, 'Paulo Dybala', 21, 'FWD'), (20, 'Stephan El Shaarawy', 92, 'FWD'), (20, 'Leon Bailey', 31, 'FWD');

-- Napoli (ID: 21)
INSERT INTO players (team_id, name, number, position) VALUES
(21, 'Alex Meret', 1, 'GK'), (21, 'Vanja Milinkovic-Savic', 28, 'GK'),
(21, 'Giovani Di Lorenzo', 22, 'DEF'), (21, 'Amir Rrahmani', 13, 'DEF'), (21, 'Alessandro Buongiorno', 4, 'DEF'), (21, 'Mathias Olivera', 17, 'DEF'), (21, 'Leonardo Spinazzola', 37, 'DEF'),
(21, 'Andre-Frank Zambo Anguissa', 99, 'MID'), (21, 'Stanislav Lobotka', 68, 'MID'), (21, 'Scott McTominay', 8, 'MID'), (21, 'Billy Gilmour', 6, 'MID'), (21, 'Kevin De Bruyne', 11, 'MID'),
(21, 'Matteo Politano', 21, 'FWD'), (21, 'Romelu Lukaku', 9, 'FWD'), (21, 'Noa Lang', 70, 'FWD'), (21, 'David Neres', 7, 'FWD'), (21, 'Rasmus Højlund', 19, 'FWD');

-- Bayer Leverkusen (ID: 22)
INSERT INTO players (team_id, name, number, position) VALUES
(22, 'Mark Flekken', 1, 'GK'), (22, 'Niklas Lomb', 36, 'GK'),
(22, 'Edmond Tapsoba', 12, 'DEF'), (22, 'Jarrel Quansah', 4, 'DEF'), (22, 'Loïc Badé', 3, 'DEF'), (22, 'Alejandro Grimaldo', 20, 'DEF'), (22, 'Lucas Vázquez', 21, 'DEF'),
(22, 'Wqui Fernández', 34, 'MID'), (22, 'Aleix Garcia', 24, 'MID'), (22, 'Robert Andrich', 8, 'MID'), (22, 'Claudio Echeverri', 9, 'MID'),
(22, 'Martin Terrier', 11, 'FWD'), (22, 'Ernest Poku', 19, 'FWD'), (22, 'Eliesse Ben Seghir', 17, 'FWD'), (22, 'Patrik Schick', 14, 'FWD');

-- RB Leipzig (ID: 23)
INSERT INTO players (team_id, name, number, position) VALUES
(23, 'Peter Gulacsi', 1, 'GK'), (23, 'Maarten Vandevoordt', 26, 'GK'),
(23, 'El Chadaille Bitshiabu', 5, 'DEF'), (23, 'Willi Orban', 4, 'DEF'), (23, 'Castello Lukeba', 23, 'DEF'), (23, 'David Raum', 22, 'DEF'), (23, 'Benjamin Henrichs', 39, 'DEF'),
(23, 'Amadou Haidara', 8, 'MID'), (23, 'Nicolas Seiwald', 13, 'MID'), (23, 'Assan Ouédraogo', 20, 'MID'), (23, 'Christoph Baumgartner', 14, 'MID'), (23, 'Xaver Schalger', 24, 'MID'), (23, 'Kevin Kampl', 44, 'MID'),
(23, 'Timo Werner', 36, 'FWD'), (23, 'Antonio Nusa', 7, 'FWD'), (23, 'Johan Bakayoko', 9, 'FWD'), (23, 'Conrad Harder', 19, 'FWD');

-- Marseille (ID: 24)
INSERT INTO players (team_id, name, number, position) VALUES
(24, 'Geronimo Rulli', 1, 'GK'), (24, 'Jeffrey de Lange', 12, 'GK'),
(24, 'Nayef Aguerd', 21, 'DEF'), (24, 'Leonardo Balerdi', 5, 'DEF'), (24, 'Benjamin Pavard', 28, 'DEF'), (24, 'Facundo Medina', 32, 'DEF'), (24, 'Emerson', 33, 'DEF'), (24, 'CJ Egan-Riley', 4, 'DEF'),
(24, 'Pierre-Emile Hojbjerg', 23, 'MID'), (24, 'Matt O\'Riley', 17, 'MID'), (24, 'Geoffrey Kondogbia', 19, 'MID'), (24, 'Angel Gomes', 8, 'MID'),
(24, 'Mason Greenwood', 10, 'FWD'), (24, 'Amine Gouiri', 9, 'FWD'), (24, 'Pierre-Emerick Aubameyang', 97, 'FWD'), (24, 'Neal Maupay', 7, 'FWD'), (24, 'Timothy Weah', 22, 'FWD'), (24, 'Robino Vaz', 34, 'FWD');

-- Strasbourg (ID: 25)
INSERT INTO players (team_id, name, number, position) VALUES
(25, 'Mike Penders', 39, 'GK'), (25, 'Karl-Johan Johnsson', 1, 'GK'),
(25, 'Guela Doue', 2, 'DEF'), (25, 'Saidou Sow', 4, 'DEF'), (25, 'Abakar Sylla', 5, 'DEF'), (25, 'Ben Chilwell', 3, 'DEF'), (25, 'Andrew Omobamidele', 2, 'DEF'),
(25, 'Maxi Oyedele', 8, 'MID'), (25, 'Ismael Doukoure', 6, 'MID'), (25, 'Julio Enciso', 19, 'MID'), (25, 'Valentin Barco', 32, 'MID'), (25, 'Kendry Paez', 16, 'MID'), (25, 'Diego Morreira', 7, 'MID'),
(25, 'Joaquin Panichelli', 9, 'FWD'), (25, 'Emanuel Emegha', 10, 'FWD'), (25, 'Abdoul Ouattara', 42, 'FWD'), (25, 'Sekou Mara', 14, 'FWD'), (25, 'Martial Godo', 20, 'FWD'), (25, 'Samuel Amo-Ameyaw', 27, 'FWD');

-- Lyon (ID: 26)
INSERT INTO players (team_id, name, number, position) VALUES
(26, 'Dominik Greif', 1, 'GK'), (26, 'Remy Descamps', 40, 'GK'),
(26, 'Ainsley Maitland-Niles', 98, 'DEF'), (26, 'Ruben Kluivert', 21, 'DEF'), (26, 'Moussa Niakhate', 19, 'DEF'), (26, 'Nicolas Tagliafico', 3, 'DEF'), (26, 'Abner', 16, 'DEF'),
(26, 'Orel Mangala', 5, 'MID'), (26, 'Corentin Tolisso', 8, 'MID'), (26, 'Adam Karabec', 7, 'MID'), (26, 'Mathys De Carvalho', 39, 'MID'), (26, 'Tanner Tessmann', 6, 'MID'),
(26, 'Malick Fofana', 11, 'MID'), (26, 'Afonso Moreira', 17, 'FWD'), (26, 'Ernest Nuamah', 37, 'FWD'), (26, 'Alejandro Gomes Rodriguez', 32, 'FWD'), (26, 'Enzo Molebe', 29, 'FWD'), (26, 'Rachid Ghezzal', 18, 'FWD');

-- Matches
INSERT INTO matches (home_team, away_team, home_team_score, away_team_score, match_date, match_time, competition_id, venue_id, status, round) VALUES
(6, 2, 2, 1, '2023-08-13', '16:30:00', 1, 6, 'FT', 'Matchday 1'), -- Chelsea vs Liverpool
(23, 22, 2, 1, '2023-08-19', '15:30:00', 4, 22, 'FT', 'Matchday 1'), -- Leipzig vs Leverkusen
(1, 7, 2, 0, '2023-10-29', '15:30:00', 1, 1, 'FT', 'Matchday 10'), -- Man Utd vs Man City
(3, 8, 2, 1, '2023-09-24', '14:00:00', 1, 3, 'FT', 'Matchday 6'), -- Arsenal vs Tottenham
(14, 24, 2, 0, '2023-09-24', '20:45:00', 6, 13, 'FT', 'Matchday 6'), -- PSG vs Marseille
(4, 15, 3, 1, '2023-02-25', '18:30:00', 2, 4, 'FT', 'Matchday 23'), -- Real Madrid vs Atletico
(5, 17, 3, 1, '2023-10-20', '21:00:00', 2, 5, 'FT', 'Matchday 10'), -- Barcelona vs Villarreal
(9, 10, 2, 1, '2023-11-04', '18:30:00', 4, 9, 'FT', 'Matchday 10'), -- Bayern vs Dortmund
(26, 25, 3, 0, '2023-10-29', '13:00:00', 6, 25, 'FT', 'Matchday 10'), -- Lyon vs Strasbourg
(1, 2, 2, 1, '2023-08-22', '20:00:00', 1, 1, 'FT', 'Matchday 3'), -- Man Utd vs Liverpool
(3, 1, NULL, NULL, CURDATE() + INTERVAL 2 DAY, '17:30:00', 1, 3, 'scheduled', 'Matchday 4'),
(4, 5, 3, 1, '2023-10-28', '16:15:00', 2, 4, 'FT', 'El Clásico');

-- Match Events (Detailed)

-- Match 1: Chelsea vs Liverpool (2-1)
INSERT INTO match_events (match_id, team_id, player_id, event_type, minute) VALUES
(1, 6, (SELECT id FROM players WHERE name = 'Jamie Gittens' AND team_id = 6), 'goal', 24),
(1, 6, (SELECT id FROM players WHERE name = 'Cole Palmer' AND team_id = 6), 'goal', 56),
(1, 2, (SELECT id FROM players WHERE name = 'Mohamed Salah' AND team_id = 2), 'goal', 80);

-- Match 2: Leipzig vs Leverkusen (2-1)
INSERT INTO match_events (match_id, team_id, player_id, event_type, minute) VALUES
(2, 23, (SELECT id FROM players WHERE name = 'Timo Werner' AND team_id = 23), 'goal', 10),
(2, 23, (SELECT id FROM players WHERE name = 'Timo Werner' AND team_id = 23), 'goal', 35),
(2, 22, (SELECT id FROM players WHERE name = 'Robert Andrich' AND team_id = 22), 'goal', 50);

-- Match 3: Man Utd vs Man City (2-0)
INSERT INTO match_events (match_id, team_id, player_id, event_type, minute) VALUES
(3, 1, (SELECT id FROM players WHERE name = 'Bruno Fernandes' AND team_id = 1), 'goal', 15),
(3, 1, (SELECT id FROM players WHERE name = 'Mattheus Cunha' AND team_id = 1), 'goal', 65),
(3, 7, (SELECT id FROM players WHERE name = 'Rodri' AND team_id = 7), 'yellow_card', 40);

-- Match 4: Arsenal vs Tottenham (2-1)
INSERT INTO match_events (match_id, team_id, player_id, event_type, minute) VALUES
(4, 3, (SELECT id FROM players WHERE name = 'Bukayo Saka' AND team_id = 3), 'goal', 15),
(4, 3, (SELECT id FROM players WHERE name = 'Kai Havertz' AND team_id = 3), 'goal', 35),
(4, 8, (SELECT id FROM players WHERE name = 'Dominic Solanke' AND team_id = 8), 'goal', 75);

-- Match 5: PSG vs Marseille (2-0)
INSERT INTO match_events (match_id, team_id, player_id, event_type, minute) VALUES
(5, 14, (SELECT id FROM players WHERE name = 'Ousmane Dembele' AND team_id = 14), 'goal', 20),
(5, 14, (SELECT id FROM players WHERE name = 'Bradley Barcola' AND team_id = 14), 'goal', 70);

-- Match 6: Real Madrid vs Atletico (3-1)
INSERT INTO match_events (match_id, team_id, player_id, event_type, minute) VALUES
(6, 4, (SELECT id FROM players WHERE name = 'Kylian Mbappe' AND team_id = 4), 'goal', 12),
(6, 4, (SELECT id FROM players WHERE name = 'Vinicius Junior' AND team_id = 4), 'goal', 45),
(6, 4, (SELECT id FROM players WHERE name = 'Jude Bellingham' AND team_id = 4), 'goal', 85),
(6, 15, (SELECT id FROM players WHERE name = 'Antoine Griezmann' AND team_id = 15), 'goal', 60);

-- Match 7: Barcelona vs Villarreal (3-1)
INSERT INTO match_events (match_id, team_id, player_id, event_type, minute) VALUES
(7, 5, (SELECT id FROM players WHERE name = 'Robert Lewandowski' AND team_id = 5), 'goal', 30),
(7, 5, (SELECT id FROM players WHERE name = 'Lamine Yamal' AND team_id = 5), 'goal', 55),
(7, 5, (SELECT id FROM players WHERE name = 'Raphinha' AND team_id = 5), 'goal', 80),
(7, 17, (SELECT id FROM players WHERE name = 'Gerard Moreno' AND team_id = 17), 'goal', 40);

-- Match 8: Bayern vs Dortmund (2-1)
INSERT INTO match_events (match_id, team_id, player_id, event_type, minute) VALUES
(8, 9, (SELECT id FROM players WHERE name = 'Harry Kane' AND team_id = 9), 'goal', 22),
(8, 9, (SELECT id FROM players WHERE name = 'Michael Olise' AND team_id = 9), 'goal', 68),
(8, 10, (SELECT id FROM players WHERE name = 'Serhou Guirassy' AND team_id = 10), 'goal', 45);

-- Match 9: Lyon vs Strasbourg (3-0)
INSERT INTO match_events (match_id, team_id, player_id, event_type, minute) VALUES
(9, 26, (SELECT id FROM players WHERE name = 'Ernest Nuamah' AND team_id = 26), 'goal', 15),
(9, 26, (SELECT id FROM players WHERE name = 'Malick Fofana' AND team_id = 26), 'goal', 50),
(9, 26, (SELECT id FROM players WHERE name = 'Rachid Ghezzal' AND team_id = 26), 'goal', 85);

-- Match 10: Man Utd vs Liverpool (Old)
INSERT INTO match_events (match_id, team_id, player_id, event_type, minute) VALUES
(10, 1, (SELECT id FROM players WHERE name = 'Mattheus Cunha' AND team_id = 1), 'goal', 16),
(10, 1, (SELECT id FROM players WHERE name = 'Bruno Fernandes' AND team_id = 1), 'yellow_card', 27),
(10, 2, (SELECT id FROM players WHERE name = 'Virgil van Dijk' AND team_id = 2), 'goal', 81);

-- Users
INSERT INTO users (email, password_hash, display_name, role) VALUES
('admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin User', 'admin'),
('user@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'John Doe', 'user');

