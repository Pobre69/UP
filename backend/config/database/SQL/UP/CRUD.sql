DELIMITER %

/* USUARIO */

CREATE PROCEDURE CREATE_USUARIO(
    IN param_email VARCHAR(200),
    IN param_nome VARCHAR(120),
    IN param_senha VARCHAR(255),
    IN param_empresa TEXT
)
BEGIN
    IF param_email IS NULL OR NOT (param_email REGEXP '^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\\.[A-Za-z]{2,}$') THEN
        SELECT 'Email inválido' AS RESULTADO;
    ELSEIF param_nome IS NULL OR LENGTH(TRIM(param_nome)) < 2 OR LENGTH(TRIM(param_nome)) > 120 THEN
        SELECT 'Nome inválido. O nome deve ter entre 2 e 120 caracteres.' AS RESULTADO;
    ELSEIF EXISTS (SELECT 1 FROM usuario WHERE email = param_email) THEN
        SELECT 'Email já cadastrado' AS RESULTADO;
    ELSE
        INSERT INTO usuario(email, nome, senha, empresa) VALUES
            (param_email, param_nome, param_senha, param_empresa);
        SELECT 'Usuário criado com sucesso' AS RESULTADO;
    END IF;
END%

CREATE PROCEDURE UPDATE_USUARIO(
    IN param_email VARCHAR(200),
    IN param_nome VARCHAR(120),
    IN param_senha VARCHAR(255),
    IN param_empresa TEXT
)
BEGIN
    DECLARE VAR_VERIFICAR INT DEFAULT 0;
    
    SELECT COUNT(email) INTO VAR_VERIFICAR FROM usuario WHERE email = param_email;
    
    IF (VAR_VERIFICAR > 0) THEN
        IF param_senha IS NOT NULL THEN
            UPDATE usuario
            SET senha = param_senha
            WHERE email = param_email;
        END IF;
        
        IF param_nome IS NOT NULL AND (param_nome REGEXP '^[A-Za-zÀ-ÖØ-öø-ÿ ]{2,100}$') THEN
            UPDATE usuario
            SET nome = param_nome
            WHERE email = param_email;
        END IF;
        
        IF param_empresa IS NOT NULL THEN
            UPDATE usuario
            SET empresa = param_empresa
            WHERE email = param_email;
        END IF;
    ELSE
        SELECT 'Usuário não existente' AS RESULTADO;
    END IF;
END%

CREATE PROCEDURE DELETE_USUARIO(IN param_email VARCHAR(200))
BEGIN
    DECLARE VAR_VERIFICAR INT DEFAULT 0;
    SELECT COUNT(email) INTO VAR_VERIFICAR FROM usuario WHERE email = param_email;
    
    IF (VAR_VERIFICAR > 0) THEN
        DELETE FROM usuario WHERE email = param_email;
    ELSE
        SELECT 'Usuário não existente' AS RESULTADO;
    END IF;
END%

/* PLANO */

CREATE PROCEDURE CREATE_PLANO(
    IN param_id INT,
    IN param_nome VARCHAR(60),
    IN param_valor INT
)
BEGIN
    IF param_id IS NULL OR param_nome IS NULL OR param_valor IS NULL THEN
        SELECT 'Todos os campos (id, nome, valor) são obrigatórios' AS RESULTADO;
    ELSEIF EXISTS (SELECT 1 FROM plano WHERE id = param_id AND nome = param_nome) THEN
        SELECT 'Plano com este ID e Nome já existe' AS RESULTADO;
    ELSE
        INSERT INTO plano(id, nome, valor) VALUES
            (param_id, param_nome, param_valor);
    END IF;
END%

CREATE PROCEDURE UPDATE_PLANO(
    IN param_id INT,
    IN param_nome VARCHAR(60),
    IN param_novo_valor INT
)
BEGIN
    DECLARE VAR_VERIFICAR INT DEFAULT 0;
    
    SELECT COUNT(*) INTO VAR_VERIFICAR FROM plano WHERE id = param_id AND nome = param_nome;
    
    IF (VAR_VERIFICAR > 0) THEN
        IF param_novo_valor IS NOT NULL THEN
            UPDATE plano
            SET valor = param_novo_valor
            WHERE id = param_id AND nome = param_nome;
        END IF;
    ELSE
        SELECT 'Plano não existente' AS RESULTADO;
    END IF;
END%

CREATE PROCEDURE DELETE_PLANO(IN param_id INT, IN param_nome VARCHAR(60))
BEGIN
    DECLARE VAR_VERIFICAR INT DEFAULT 0;
    SELECT COUNT(*) INTO VAR_VERIFICAR FROM plano WHERE id = param_id AND nome = param_nome;
    
    IF (VAR_VERIFICAR > 0) THEN
        DELETE FROM plano WHERE id = param_id AND nome = param_nome;
    ELSE
        SELECT 'Plano não existente' AS RESULTADO;
    END IF;
END%

/* CONCORRENTE */

CREATE PROCEDURE CREATE_CONCORRENTE(
    IN param_email VARCHAR(200),
    IN param_descricao TEXT
)
BEGIN
    IF param_email IS NULL OR NOT (param_email REGEXP '^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\\.[A-Za-z]{2,}$') THEN
        SELECT 'Email inválido' AS RESULTADO;
    ELSEIF NOT EXISTS (SELECT 1 FROM usuario WHERE email = param_email) THEN
        SELECT 'Usuário não existente. Não é possível adicionar concorrente a um usuário que não existe.' AS RESULTADO;
    ELSEIF EXISTS (SELECT 1 FROM concorrente WHERE email = param_email) THEN
        SELECT 'Concorrente para este email já existe' AS RESULTADO;
    ELSE
        INSERT INTO concorrente(email, descricao) VALUES
            (param_email, param_descricao);
    END IF;
END%

CREATE PROCEDURE UPDATE_CONCORRENTE(
    IN param_email VARCHAR(200),
    IN param_descricao TEXT
)
BEGIN
    DECLARE VAR_VERIFICAR INT DEFAULT 0;
    
    SELECT COUNT(email) INTO VAR_VERIFICAR FROM concorrente WHERE email = param_email;
    
    IF (VAR_VERIFICAR > 0) THEN
        UPDATE concorrente
        SET descricao = param_descricao
        WHERE email = param_email;
    ELSE
        SELECT 'Concorrente não existente' AS RESULTADO;
    END IF;
END%

CREATE PROCEDURE DELETE_CONCORRENTE(IN param_email VARCHAR(200))
BEGIN
    DECLARE VAR_VERIFICAR INT DEFAULT 0;
    SELECT COUNT(email) INTO VAR_VERIFICAR FROM concorrente WHERE email = param_email;
    
    IF (VAR_VERIFICAR > 0) THEN
        DELETE FROM concorrente WHERE email = param_email;
    ELSE
        SELECT 'Concorrente não existente' AS RESULTADO;
    END IF;
END%

/* USUARIO_PLANO */

CREATE PROCEDURE CREATE_USUARIO_PLANO(
    IN param_usuario_email VARCHAR(200),
    IN param_plano_id INT,
    IN param_plano_nome VARCHAR(60),
    IN param_data_inicial DATE,
    IN param_data_final DATE
)
BEGIN
    IF param_usuario_email IS NULL OR param_plano_id IS NULL OR param_plano_nome IS NULL OR param_data_inicial IS NULL OR param_data_final IS NULL THEN
        SELECT 'Todos os campos são obrigatórios' AS RESULTADO;
    ELSEIF NOT EXISTS (SELECT 1 FROM usuario WHERE email = param_usuario_email) THEN
        SELECT 'Usuário não existente' AS RESULTADO;
    ELSEIF NOT EXISTS (SELECT 1 FROM plano WHERE id = param_plano_id AND nome = param_plano_nome) THEN
        SELECT 'Plano não existente' AS RESULTADO;
    ELSEIF EXISTS (SELECT 1 FROM usuario_plano WHERE usuario_email = param_usuario_email AND plano_id = param_plano_id AND plano_nome = param_plano_nome) THEN
        SELECT 'Associação de usuário e plano já existe' AS RESULTADO;
    ELSE
        INSERT INTO usuario_plano(usuario_email, plano_id, plano_nome, data_inicial, data_final) VALUES
            (param_usuario_email, param_plano_id, param_plano_nome, param_data_inicial, param_data_final);
    END IF;
END%

CREATE PROCEDURE UPDATE_USUARIO_PLANO(
    IN param_usuario_email VARCHAR(200),
    IN param_plano_id INT,
    IN param_plano_nome VARCHAR(60),
    IN param_nova_data_inicial DATE,
    IN param_nova_data_final DATE
)
BEGIN
    DECLARE VAR_VERIFICAR INT DEFAULT 0;
    
    SELECT COUNT(*) INTO VAR_VERIFICAR FROM usuario_plano WHERE usuario_email = param_usuario_email AND plano_id = param_plano_id AND plano_nome = param_plano_nome;
    
    IF (VAR_VERIFICAR > 0) THEN
        IF param_nova_data_inicial IS NOT NULL THEN
            UPDATE usuario_plano
            SET data_inicial = param_nova_data_inicial
            WHERE usuario_email = param_usuario_email AND plano_id = param_plano_id AND plano_nome = param_plano_nome;
        END IF;
        IF param_nova_data_final IS NOT NULL THEN
            UPDATE usuario_plano
            SET data_final = param_nova_data_final
            WHERE usuario_email = param_usuario_email AND plano_id = param_plano_id AND plano_nome = param_plano_nome;
        END IF;
    ELSE
        SELECT 'Associação de usuário e plano não existente' AS RESULTADO;
    END IF;
END%

CREATE PROCEDURE DELETE_USUARIO_PLANO(IN param_usuario_email VARCHAR(200), IN param_plano_id INT, IN param_plano_nome VARCHAR(60))
BEGIN
    DECLARE VAR_VERIFICAR INT DEFAULT 0;
    SELECT COUNT(*) INTO VAR_VERIFICAR FROM usuario_plano WHERE usuario_email = param_usuario_email AND plano_id = param_plano_id AND plano_nome = param_plano_nome;
    
    IF (VAR_VERIFICAR > 0) THEN
        DELETE FROM usuario_plano WHERE usuario_email = param_usuario_email AND plano_id = param_plano_id AND plano_nome = param_plano_nome;
    ELSE
        SELECT 'Associação de usuário e plano não existente' AS RESULTADO;
    END IF;
END%

/* USUARIO_DETALHES */

CREATE PROCEDURE CREATE_USUARIO_DETALHES(
    IN param_email VARCHAR(200),
    IN param_objetivo TEXT,
    IN param_google_drive TEXT,
    IN param_segmento TEXT,
    IN param_instagram VARCHAR(120),
    IN param_ajudante TEXT,
    IN param_localizacao JSON
)
BEGIN
    IF param_email IS NULL OR NOT (param_email REGEXP '^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\\.[A-Za-z]{2,}$') THEN
        SELECT 'Email inválido' AS RESULTADO;
    ELSEIF NOT EXISTS (SELECT 1 FROM usuario WHERE email = param_email) THEN
        SELECT 'Usuário não existente. Não é possível adicionar detalhes a um usuário que não existe.' AS RESULTADO;
    ELSEIF EXISTS (SELECT 1 FROM usuario_detalhes WHERE email = param_email) THEN
        SELECT 'Detalhes para este email já existem' AS RESULTADO;
    ELSE
        INSERT INTO usuario_detalhes(email, objetivo, google_drive, segmento, instagram, ajudante, localizacao) VALUES
            (param_email, param_objetivo, param_google_drive, param_segmento, param_instagram, param_ajudante, param_localizacao);
    END IF;
END%

CREATE PROCEDURE UPDATE_USUARIO_DETALHES(
    IN param_email VARCHAR(200),
    IN param_objetivo TEXT,
    IN param_google_drive TEXT,
    IN param_segmento TEXT,
    IN param_instagram VARCHAR(120),
    IN param_ajudante TEXT,
    IN param_localizacao JSON
)
BEGIN
    DECLARE VAR_VERIFICAR INT DEFAULT 0;
    
    SELECT COUNT(email) INTO VAR_VERIFICAR FROM usuario_detalhes WHERE email = param_email;
    
    IF (VAR_VERIFICAR > 0) THEN
        UPDATE usuario_detalhes
        SET 
            objetivo = COALESCE(param_objetivo, objetivo),
            google_drive = COALESCE(param_google_drive, google_drive),
            segmento = COALESCE(param_segmento, segmento),
            instagram = COALESCE(param_instagram, instagram),
            ajudante = COALESCE(param_ajudante, ajudante),
            localizacao = COALESCE(param_localizacao, localizacao)
        WHERE email = param_email;
    ELSE
        SELECT 'Detalhes do usuário não existentes' AS RESULTADO;
    END IF;
END%

CREATE PROCEDURE DELETE_USUARIO_DETALHES(IN param_email VARCHAR(200))
BEGIN
    DECLARE VAR_VERIFICAR INT DEFAULT 0;
    SELECT COUNT(email) INTO VAR_VERIFICAR FROM usuario_detalhes WHERE email = param_email;
    
    IF (VAR_VERIFICAR > 0) THEN
        DELETE FROM usuario_detalhes WHERE email = param_email;
    ELSE
        SELECT 'Detalhes do usuário não existentes' AS RESULTADO;
    END IF;
END%

/* USUARIO_POSTS */

CREATE PROCEDURE CREATE_USUARIO_POSTS(
    IN param_email VARCHAR(200),
    IN param_post JSON,
    IN param_data_postada DATETIME
)
BEGIN
    IF NOT EXISTS (SELECT 1 FROM usuario WHERE email = param_email) THEN
        SELECT 'Usuário não existente' AS RESULTADO;
    ELSEIF EXISTS (SELECT 1 FROM usuario_posts WHERE email = param_email) THEN
        SELECT 'Post para este email já existe' AS RESULTADO;
    ELSE
        INSERT INTO usuario_posts(email, post, data_postada) VALUES
            (param_email, param_post, param_data_postada);
    END IF;
END%

CREATE PROCEDURE UPDATE_USUARIO_POSTS(
    IN param_email VARCHAR(200),
    IN param_post JSON,
    IN param_data_postada DATETIME
)
BEGIN
    DECLARE VAR_VERIFICAR INT DEFAULT 0;
    SELECT COUNT(email) INTO VAR_VERIFICAR FROM usuario_posts WHERE email = param_email;
    
    IF (VAR_VERIFICAR > 0) THEN
        UPDATE usuario_posts
        SET 
            post = COALESCE(param_post, post),
            data_postada = COALESCE(param_data_postada, data_postada)
        WHERE email = param_email;
    ELSE
        SELECT 'Post do usuário não existente' AS RESULTADO;
    END IF;
END%

CREATE PROCEDURE DELETE_USUARIO_POSTS(IN param_email VARCHAR(200))
BEGIN
    DECLARE VAR_VERIFICAR INT DEFAULT 0;
    SELECT COUNT(email) INTO VAR_VERIFICAR FROM usuario_posts WHERE email = param_email;
    
    IF (VAR_VERIFICAR > 0) THEN
        DELETE FROM usuario_posts WHERE email = param_email;
    ELSE
        SELECT 'Post do usuário não existente' AS RESULTADO;
    END IF;
END%

/* INSTAGRAM_SEGUIDORES */

CREATE PROCEDURE CREATE_INSTAGRAM_SEGUIDORES(
    IN param_email VARCHAR(200),
    IN param_seguidores INT
)
BEGIN
    IF NOT EXISTS (SELECT 1 FROM usuario WHERE email = param_email) THEN
        SELECT 'Usuário não existente' AS RESULTADO;
    ELSEIF EXISTS (SELECT 1 FROM instagram_seguidores WHERE email = param_email) THEN
        SELECT 'Registro de seguidores para este email já existe' AS RESULTADO;
    ELSE
        INSERT INTO instagram_seguidores(email, seguidores) VALUES
            (param_email, param_seguidores);
    END IF;
END%

CREATE PROCEDURE UPDATE_INSTAGRAM_SEGUIDORES(
    IN param_email VARCHAR(200),
    IN param_seguidores INT
)
BEGIN
    DECLARE VAR_VERIFICAR INT DEFAULT 0;
    SELECT COUNT(email) INTO VAR_VERIFICAR FROM instagram_seguidores WHERE email = param_email;
    
    IF (VAR_VERIFICAR > 0) THEN
        UPDATE instagram_seguidores
        SET seguidores = param_seguidores
        WHERE email = param_email;
    ELSE
        SELECT 'Registro de seguidores não existente' AS RESULTADO;
    END IF;
END%

CREATE PROCEDURE DELETE_INSTAGRAM_SEGUIDORES(IN param_email VARCHAR(200))
BEGIN
    DECLARE VAR_VERIFICAR INT DEFAULT 0;
    SELECT COUNT(email) INTO VAR_VERIFICAR FROM instagram_seguidores WHERE email = param_email;
    
    IF (VAR_VERIFICAR > 0) THEN
        DELETE FROM instagram_seguidores WHERE email = param_email;
    ELSE
        SELECT 'Registro de seguidores não existente' AS RESULTADO;
    END IF;
END%

/* AJUDANTE */

CREATE PROCEDURE CREATE_AJUDANTE(
    IN param_id INT,
    IN param_nome VARCHAR(100)
)
BEGIN
    IF param_nome IS NULL THEN
        SELECT 'Nome é obrigatório' AS RESULTADO;
    ELSE
        INSERT INTO ajudante(id, nome) VALUES
            (param_id, param_nome);
    END IF;
END%

CREATE PROCEDURE UPDATE_AJUDANTE(
    IN param_id INT,
    IN param_nome VARCHAR(100)
)
BEGIN
    DECLARE VAR_VERIFICAR INT DEFAULT 0;
    SELECT COUNT(id) INTO VAR_VERIFICAR FROM ajudante WHERE id = param_id;
    
    IF (VAR_VERIFICAR > 0) THEN
        UPDATE ajudante
        SET nome = param_nome
        WHERE id = param_id;
    ELSE
        SELECT 'Ajudante não existente' AS RESULTADO;
    END IF;
END%

CREATE PROCEDURE DELETE_AJUDANTE(IN param_id INT)
BEGIN
    DECLARE VAR_VERIFICAR INT DEFAULT 0;
    SELECT COUNT(id) INTO VAR_VERIFICAR FROM ajudante WHERE id = param_id;
    
    IF (VAR_VERIFICAR > 0) THEN
        DELETE FROM ajudante WHERE id = param_id;
    ELSE
        SELECT 'Ajudante não existente' AS RESULTADO;
    END IF;
END%

/* USUARIO_AJUDANTE */

CREATE PROCEDURE CREATE_USUARIO_AJUDANTE(
    IN param_email_usuario VARCHAR(200),
    IN param_id_ajudante INT,
    IN param_status TEXT
)
BEGIN
    IF NOT EXISTS (SELECT 1 FROM usuario WHERE email = param_email_usuario) THEN
        SELECT 'Usuário não existente' AS RESULTADO;
    ELSEIF NOT EXISTS (SELECT 1 FROM ajudante WHERE id = param_id_ajudante) THEN
        SELECT 'Ajudante não existente' AS RESULTADO;
    ELSE
        INSERT INTO usuario_ajudante(email_usuario, id_ajudante, status) VALUES
            (param_email_usuario, param_id_ajudante, param_status);
    END IF;
END%

CREATE PROCEDURE UPDATE_USUARIO_AJUDANTE(
    IN param_email_usuario VARCHAR(200),
    IN param_id_ajudante INT,
    IN param_status TEXT
)
BEGIN
    DECLARE VAR_VERIFICAR INT DEFAULT 0;
    SELECT COUNT(*) INTO VAR_VERIFICAR FROM usuario_ajudante WHERE email_usuario = param_email_usuario AND id_ajudante = param_id_ajudante;
    
    IF (VAR_VERIFICAR > 0) THEN
        UPDATE usuario_ajudante
        SET status = param_status
        WHERE email_usuario = param_email_usuario AND id_ajudante = param_id_ajudante;
    ELSE
        SELECT 'Associação de usuário e ajudante não existente' AS RESULTADO;
    END IF;
END%

CREATE PROCEDURE DELETE_USUARIO_AJUDANTE(IN param_email_usuario VARCHAR(200), IN param_id_ajudante INT)
BEGIN
    DECLARE VAR_VERIFICAR INT DEFAULT 0;
    SELECT COUNT(*) INTO VAR_VERIFICAR FROM usuario_ajudante WHERE email_usuario = param_email_usuario AND id_ajudante = param_id_ajudante;
    
    IF (VAR_VERIFICAR > 0) THEN
        DELETE FROM usuario_ajudante WHERE email_usuario = param_email_usuario AND id_ajudante = param_id_ajudante;
    ELSE
        SELECT 'Associação de usuário e ajudante não existente' AS RESULTADO;
    END IF;
END%

/* FEEDBACK */

CREATE PROCEDURE CREATE_FEEDBACK(
    IN param_id INT,
    IN param_usuario_email VARCHAR(200),
    IN param_titulo VARCHAR(40),
    IN param_tipo VARCHAR(60),
    IN param_texto TEXT
)
BEGIN
    IF NOT EXISTS (SELECT 1 FROM usuario WHERE email = param_usuario_email) THEN
        SELECT 'Usuário não existente' AS RESULTADO;
    ELSE
        INSERT INTO feedBack(id, usuario_email, titulo, tipo, texto) VALUES
            (param_id, param_usuario_email, param_titulo, param_tipo, param_texto);
    END IF;
END%

CREATE PROCEDURE UPDATE_FEEDBACK(
    IN param_id INT,
    IN param_titulo VARCHAR(40),
    IN param_tipo VARCHAR(60),
    IN param_texto TEXT
)
BEGIN
    DECLARE VAR_VERIFICAR INT DEFAULT 0;
    SELECT COUNT(id) INTO VAR_VERIFICAR FROM feedBack WHERE id = param_id;
    
    IF (VAR_VERIFICAR > 0) THEN
        UPDATE feedBack
        SET 
            titulo = COALESCE(param_titulo, titulo),
            tipo = COALESCE(param_tipo, tipo),
            texto = COALESCE(param_texto, texto)
        WHERE id = param_id;
    ELSE
        SELECT 'Feedback não existente' AS RESULTADO;
    END IF;
END%

CREATE PROCEDURE DELETE_FEEDBACK(IN param_id INT)
BEGIN
    DECLARE VAR_VERIFICAR INT DEFAULT 0;
    SELECT COUNT(id) INTO VAR_VERIFICAR FROM feedBack WHERE id = param_id;
    
    IF (VAR_VERIFICAR > 0) THEN
        DELETE FROM feedBack WHERE id = param_id;
    ELSE
        SELECT 'Feedback não existente' AS RESULTADO;
    END IF;
END%
DELIMITER ;objetivo TEXT,
    IN param_google_drive TEXT,
    IN param_segmento TEXT,
    IN param_instagram VARCHAR(120),
    IN param_ajudante TEXT,
    IN param_localizacao JSON
)
BEGIN
    IF param_email IS NULL OR NOT (param_email REGEXP '^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\\.[A-Za-z]{2,}$') THEN
        SELECT 'Email inválido' AS RESULTADO;
    ELSEIF NOT EXISTS (SELECT 1 FROM usuario WHERE email = param_email) THEN
        SELECT 'Usuário não existente. Não é possível adicionar detalhes a um usuário que não existe.' AS RESULTADO;
    ELSEIF EXISTS (SELECT 1 FROM usuario_detalhes WHERE email = param_email) THEN
        SELECT 'Detalhes para este email já existem' AS RESULTADO;
    ELSE
        INSERT INTO usuario_detalhes(email, objetivo, google_drive, segmento, instagram, ajudante, localizacao) VALUES
            (param_email, param_objetivo, param_google_drive, param_segmento, param_instagram, param_ajudante, param_localizacao);
    END IF;
END%

CREATE PROCEDURE UPDATE_USUARIO_DETALHES(
    IN param_email VARCHAR(200),
    IN param_objetivo TEXT,
    IN param_google_drive TEXT,
    IN param_segmento TEXT,
    IN param_instagram VARCHAR(120),
    IN param_ajudante TEXT,
    IN param_localizacao JSON
)
BEGIN
    DECLARE VAR_VERIFICAR INT DEFAULT 0;
    
    SELECT COUNT(email) INTO VAR_VERIFICAR FROM usuario_detalhes WHERE email = param_email;
    
    IF (VAR_VERIFICAR > 0) THEN
        UPDATE usuario_detalhes
        SET 
            objetivo = COALESCE(param_objetivo, objetivo),
            google_drive = COALESCE(param_google_drive, google_drive),
            segmento = COALESCE(param_segmento, segmento),
            instagram = COALESCE(param_instagram, instagram),
            ajudante = COALESCE(param_ajudante, ajudante),
            localizacao = COALESCE(param_localizacao, localizacao)
        WHERE email = param_email;
    ELSE
        SELECT 'Detalhes do usuário não existentes' AS RESULTADO;
    END IF;
END%

CREATE PROCEDURE DELETE_USUARIO_DETALHES(IN param_email VARCHAR(200))
BEGIN
    DECLARE VAR_VERIFICAR INT DEFAULT 0;
    SELECT COUNT(email) INTO VAR_VERIFICAR FROM usuario_detalhes WHERE email = param_email;
    
    IF (VAR_VERIFICAR > 0) THEN
        DELETE FROM usuario_detalhes WHERE email = param_email;
    ELSE
        SELECT 'Detalhes do usuário não existentes' AS RESULTADO;
    END IF;
END%

/* USUARIO_POSTS */

CREATE PROCEDURE CREATE_USUARIO_POSTS(
    IN param_email VARCHAR(200),
    IN param_post JSON,
    IN param_data_postada DATETIME
)
BEGIN
    IF NOT EXISTS (SELECT 1 FROM usuario WHERE email = param_email) THEN
        SELECT 'Usuário não existente' AS RESULTADO;
    ELSEIF EXISTS (SELECT 1 FROM usuario_posts WHERE email = param_email) THEN
        SELECT 'Post para este email já existe' AS RESULTADO;
    ELSE
        INSERT INTO usuario_posts(email, post, data_postada) VALUES
            (param_email, param_post, param_data_postada);
    END IF;
END%

CREATE PROCEDURE UPDATE_USUARIO_POSTS(
    IN param_email VARCHAR(200),
    IN param_post JSON,
    IN param_data_postada DATETIME
)
BEGIN
    DECLARE VAR_VERIFICAR INT DEFAULT 0;
    SELECT COUNT(email) INTO VAR_VERIFICAR FROM usuario_posts WHERE email = param_email;
    
    IF (VAR_VERIFICAR > 0) THEN
        UPDATE usuario_posts
        SET 
            post = COALESCE(param_post, post),
            data_postada = COALESCE(param_data_postada, data_postada)
        WHERE email = param_email;
    ELSE
        SELECT 'Post do usuário não existente' AS RESULTADO;
    END IF;
END%

CREATE PROCEDURE DELETE_USUARIO_POSTS(IN param_email VARCHAR(200))
BEGIN
    DECLARE VAR_VERIFICAR INT DEFAULT 0;
    SELECT COUNT(email) INTO VAR_VERIFICAR FROM usuario_posts WHERE email = param_email;
    
    IF (VAR_VERIFICAR > 0) THEN
        DELETE FROM usuario_posts WHERE email = param_email;
    ELSE
        SELECT 'Post do usuário não existente' AS RESULTADO;
    END IF;
END%

/* INSTAGRAM_SEGUIDORES */

CREATE PROCEDURE CREATE_INSTAGRAM_SEGUIDORES(
    IN param_email VARCHAR(200),
    IN param_seguidores INT
)
BEGIN
    IF NOT EXISTS (SELECT 1 FROM usuario WHERE email = param_email) THEN
        SELECT 'Usuário não existente' AS RESULTADO;
    ELSEIF EXISTS (SELECT 1 FROM instagram_seguidores WHERE email = param_email) THEN
        SELECT 'Registro de seguidores para este email já existe' AS RESULTADO;
    ELSE
        INSERT INTO instagram_seguidores(email, seguidores) VALUES
            (param_email, param_seguidores);
    END IF;
END%

CREATE PROCEDURE UPDATE_INSTAGRAM_SEGUIDORES(
    IN param_email VARCHAR(200),
    IN param_seguidores INT
)
BEGIN
    DECLARE VAR_VERIFICAR INT DEFAULT 0;
    SELECT COUNT(email) INTO VAR_VERIFICAR FROM instagram_seguidores WHERE email = param_email;
    
    IF (VAR_VERIFICAR > 0) THEN
        UPDATE instagram_seguidores
        SET seguidores = param_seguidores
        WHERE email = param_email;
    ELSE
        SELECT 'Registro de seguidores não existente' AS RESULTADO;
    END IF;
END%

CREATE PROCEDURE DELETE_INSTAGRAM_SEGUIDORES(IN param_email VARCHAR(200))
BEGIN
    DECLARE VAR_VERIFICAR INT DEFAULT 0;
    SELECT COUNT(email) INTO VAR_VERIFICAR FROM instagram_seguidores WHERE email = param_email;
    
    IF (VAR_VERIFICAR > 0) THEN
        DELETE FROM instagram_seguidores WHERE email = param_email;
    ELSE
        SELECT 'Registro de seguidores não existente' AS RESULTADO;
    END IF;
END%

/* AJUDANTE */

CREATE PROCEDURE CREATE_AJUDANTE(
    IN param_id INT,
    IN param_nome VARCHAR(100)
)
BEGIN
    IF param_nome IS NULL THEN
        SELECT 'Nome é obrigatório' AS RESULTADO;
    ELSE
        INSERT INTO ajudante(id, nome) VALUES
            (param_id, param_nome);
    END IF;
END%

CREATE PROCEDURE UPDATE_AJUDANTE(
    IN param_id INT,
    IN param_nome VARCHAR(100)
)
BEGIN
    DECLARE VAR_VERIFICAR INT DEFAULT 0;
    SELECT COUNT(id) INTO VAR_VERIFICAR FROM ajudante WHERE id = param_id;
    
    IF (VAR_VERIFICAR > 0) THEN
        UPDATE ajudante
        SET nome = param_nome
        WHERE id = param_id;
    ELSE
        SELECT 'Ajudante não existente' AS RESULTADO;
    END IF;
END%

CREATE PROCEDURE DELETE_AJUDANTE(IN param_id INT)
BEGIN
    DECLARE VAR_VERIFICAR INT DEFAULT 0;
    SELECT COUNT(id) INTO VAR_VERIFICAR FROM ajudante WHERE id = param_id;
    
    IF (VAR_VERIFICAR > 0) THEN
        DELETE FROM ajudante WHERE id = param_id;
    ELSE
        SELECT 'Ajudante não existente' AS RESULTADO;
    END IF;
END%

/* USUARIO_AJUDANTE */

CREATE PROCEDURE CREATE_USUARIO_AJUDANTE(
    IN param_email_usuario VARCHAR(200),
    IN param_id_ajudante INT,
    IN param_status TEXT
)
BEGIN
    IF NOT EXISTS (SELECT 1 FROM usuario WHERE email = param_email_usuario) THEN
        SELECT 'Usuário não existente' AS RESULTADO;
    ELSEIF NOT EXISTS (SELECT 1 FROM ajudante WHERE id = param_id_ajudante) THEN
        SELECT 'Ajudante não existente' AS RESULTADO;
    ELSE
        INSERT INTO usuario_ajudante(email_usuario, id_ajudante, status) VALUES
            (param_email_usuario, param_id_ajudante, param_status);
    END IF;
END%

CREATE PROCEDURE UPDATE_USUARIO_AJUDANTE(
    IN param_email_usuario VARCHAR(200),
    IN param_id_ajudante INT,
    IN param_status TEXT
)
BEGIN
    DECLARE VAR_VERIFICAR INT DEFAULT 0;
    SELECT COUNT(*) INTO VAR_VERIFICAR FROM usuario_ajudante WHERE email_usuario = param_email_usuario AND id_ajudante = param_id_ajudante;
    
    IF (VAR_VERIFICAR > 0) THEN
        UPDATE usuario_ajudante
        SET status = param_status
        WHERE email_usuario = param_email_usuario AND id_ajudante = param_id_ajudante;
    ELSE
        SELECT 'Associação de usuário e ajudante não existente' AS RESULTADO;
    END IF;
END%

CREATE PROCEDURE DELETE_USUARIO_AJUDANTE(IN param_email_usuario VARCHAR(200), IN param_id_ajudante INT)
BEGIN
    DECLARE VAR_VERIFICAR INT DEFAULT 0;
    SELECT COUNT(*) INTO VAR_VERIFICAR FROM usuario_ajudante WHERE email_usuario = param_email_usuario AND id_ajudante = param_id_ajudante;
    
    IF (VAR_VERIFICAR > 0) THEN
        DELETE FROM usuario_ajudante WHERE email_usuario = param_email_usuario AND id_ajudante = param_id_ajudante;
    ELSE
        SELECT 'Associação de usuário e ajudante não existente' AS RESULTADO;
    END IF;
END%

/* FEEDBACK */

CREATE PROCEDURE CREATE_FEEDBACK(
    IN param_id INT,
    IN param_usuario_email VARCHAR(20),
    IN param_titulo VARCHAR(40),
    IN param_tipo VARCHAR(60),
    IN param_texto TEXT
)
BEGIN
    IF NOT EXISTS (SELECT 1 FROM usuario WHERE email = param_usuario_email) THEN
        SELECT 'Usuário não existente' AS RESULTADO;
    ELSE
        INSERT INTO feedBack(id, usuario_email, titulo, tipo, texto) VALUES
            (param_id, param_usuario_email, param_titulo, param_tipo, param_texto);
    END IF;
END%

CREATE PROCEDURE UPDATE_FEEDBACK(
    IN param_id INT,
    IN param_titulo VARCHAR(40),
    IN param_tipo VARCHAR(60),
    IN param_texto TEXT
)
BEGIN
    DECLARE VAR_VERIFICAR INT DEFAULT 0;
    SELECT COUNT(id) INTO VAR_VERIFICAR FROM feedBack WHERE id = param_id;
    
    IF (VAR_VERIFICAR > 0) THEN
        UPDATE feedBack
        SET 
            titulo = COALESCE(param_titulo, titulo),
            tipo = COALESCE(param_tipo, tipo),
            texto = COALESCE(param_texto, texto)
        WHERE id = param_id;
    ELSE
        SELECT 'Feedback não existente' AS RESULTADO;
    END IF;
END%

CREATE PROCEDURE DELETE_FEEDBACK(IN param_id INT)
BEGIN
    DECLARE VAR_VERIFICAR INT DEFAULT 0;
    SELECT COUNT(id) INTO VAR_VERIFICAR FROM feedBack WHERE id = param_id;
    
    IF (VAR_VERIFICAR > 0) THEN
        DELETE FROM feedBack WHERE id = param_id;
    ELSE
        SELECT 'Feedback não existente' AS RESULTADO;
    END IF;
END%
DELIMITER ;