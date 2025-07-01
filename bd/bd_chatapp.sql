-- Crear la base de datos (si no está creada aún)
CREATE DATABASE IF NOT EXISTS bd_chatapp;
USE bd_chatapp;

-- -----------------------------------------------------------------------------------------------------------------------------------------------
-- TABLA users (usuarios)
-- -----------------------------------------------------------------------------------------------------------------------------------------------
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(100) NOT NULL,
    avatar VARCHAR(255) DEFAULT NULL,
    bio TEXT,
    location VARCHAR(100),
    website VARCHAR(255),
    email_verified_at TIMESTAMP NULL,
    remember_token VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- -----------------------------------------------------------------------------------------------------------------------------------------------
-- TABLA password_reset_tokens (tokens para resetear contraseña)
-- -----------------------------------------------------------------------------------------------------------------------------------------------
CREATE TABLE password_reset_tokens (
    email VARCHAR(255) PRIMARY KEY,
    token VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- -----------------------------------------------------------------------------------------------------------------------------------------------
-- TABLA personal_access_tokens (tokens de acceso personal)
-- -----------------------------------------------------------------------------------------------------------------------------------------------
CREATE TABLE personal_access_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tokenable_type VARCHAR(255) NOT NULL,
    tokenable_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    token VARCHAR(64) NOT NULL,
    abilities TEXT,
    last_used_at TIMESTAMP NULL,
    expires_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY personal_access_tokens_token_unique (token),
    KEY personal_access_tokens_tokenable_type_tokenable_id_index (tokenable_type, tokenable_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- *****************************************************************************************************************************
-- ****************** CHAT ***********************************************************************************
-- *****************************************************************************************************************************

-- Tabla chats: representa conversación entre 2 usuarios
CREATE TABLE chats (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_one_id INT NOT NULL,
    user_two_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_chat_between_users (user_one_id, user_two_id),
    FOREIGN KEY (user_one_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (user_two_id) REFERENCES users(id) ON DELETE CASCADE,
    CHECK (user_one_id < user_two_id) -- Aseguramos que user_one_id siempre sea el menor
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabla mensajes
CREATE TABLE messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    chat_id INT NOT NULL,
    sender_id INT NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (chat_id) REFERENCES chats(id) ON DELETE CASCADE,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_chat_id (chat_id),
    INDEX idx_sender_id (sender_id),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE message_reads (
    id INT AUTO_INCREMENT PRIMARY KEY,
    message_id INT NOT NULL,
    user_id INT NOT NULL,
    read_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (message_id) REFERENCES messages(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_message_user (message_id, user_id)
);

-- Tabla invitaciones
CREATE TABLE chat_invitations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    inviter_id INT NOT NULL,
    invitee_email VARCHAR(255) NOT NULL,
    chat_id INT NULL,
    accepted TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (inviter_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (chat_id) REFERENCES chats(id) ON DELETE SET NULL,
    INDEX idx_invitee_email (invitee_email),
    INDEX idx_inviter_id (inviter_id),
    INDEX idx_chat_id (chat_id),
    INDEX idx_accepted (accepted)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;




-- *****************************************************************************************************************************
-- ****************** INSERCIONES INICIALES ***********************************************************************************
-- *****************************************************************************************************************************

-- Insertar usuarios de prueba
INSERT INTO users (username, email, password, name, created_at) VALUES
('John', 'usuario1@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'John', NOW()),
('Katerine', 'usuario2@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Katerine', NOW()),
('Brian', 'usuario3@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Brian', NOW());

-- Insertar posts de prueba
INSERT INTO posts (user_id, content, created_at) VALUES
(1, '¡Hola a todos! Este es mi primer post en la red social.', NOW()),
(2, 'Probando esta nueva red social. ¡Se ve genial!', NOW()),
(3, '¿Alguien quiere charlar sobre desarrollo web?', NOW());

-- Insertar relaciones de seguidores
INSERT INTO followers (follower_id, followed_id, created_at) VALUES
(1, 2, NOW()),
(2, 1, NOW()),
(3, 1, NOW());

-- Insertar likes
INSERT INTO likes (user_id, post_id, created_at) VALUES
(2, 1, NOW()),
(3, 1, NOW()),
(1, 2, NOW());




-- *****************************************************************************************************************************
-- ****************** INSERCIONES PARA EL SISTEMA DE CHAT *********************************************************************
-- *****************************************************************************************************************************

-- Chat entre usuario1 (id 1) y usuario2 (id 2) - user_one_id debe ser el menor
INSERT INTO chats (user_one_id, user_two_id) VALUES (1, 2);
-- Chat entre usuario1 (id 1) y usuario3 (id 3) - user_one_id es el menor
INSERT INTO chats (user_one_id, user_two_id) VALUES (1, 3);


-- Mensajes en el chat 1 (usuario1 y usuario2)
INSERT INTO messages (chat_id, sender_id, message) VALUES
(1, 1, 'Hola Katerine, ¿cómo estás?'),
(1, 2, 'Hola John, estoy bien ¿y tú?'),
(1, 1, 'Todo bien por aquí, gracias por preguntar.');
-- Mensajes en el chat 2 (usuario1 y usuario3)
INSERT INTO messages (chat_id, sender_id, message) VALUES
(2, 1, 'Hola Brian, ¿recibiste mi informe?'),
(2, 3, 'Sí, la recibí. Estoy revisando los detalles.'),
(2, 3, 'Cuando lo tenga listo te envio un mensaje.');

-- Invitación aceptada (que resultó en el chat 1)
INSERT INTO chat_invitations (inviter_id, invitee_email, chat_id, accepted) VALUES
(1, 'usuario2@example.com', 1, 1);
-- Invitación aceptada (que resultó en el chat 2)
INSERT INTO chat_invitations (inviter_id, invitee_email, chat_id, accepted) VALUES
(1, 'usuario3@example.com', 2, 1);
-- Invitación pendiente de usuario2 a usuario3
INSERT INTO chat_invitations (inviter_id, invitee_email, accepted) VALUES
(2, 'usuario3@example.com', 0);
-- Invitación rechazada (no generó chat)
INSERT INTO chat_invitations (inviter_id, invitee_email, accepted) VALUES
(3, 'usuario1@example.com', 0);