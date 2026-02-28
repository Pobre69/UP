-- Script para corrigir a validação do nome no procedimento CREATE_USUARIO
-- Execute este script no MySQL para aplicar a correção

DROP PROCEDURE IF EXISTS CREATE_USUARIO;

DELIMITER $$

CREATE PROCEDURE CREATE_USUARIO(
    IN param_email VARCHAR(200),
    IN param_nome VARCHAR(120),
    IN param_senha VARCHAR(60),
    IN param_empresa TEXT
)
BEGIN
    IF param_email IS NULL OR NOT (param_email REGEXP '^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\\.[A-Za-z]{2,}$') THEN
        SELECT 'Email inválido' AS RESULTADO;
    ELSEIF param_senha IS NOT NULL AND NOT (param_senha REGEXP '^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[^A-Za-z0-9])[^\\s]{8,}$') THEN
        SELECT 'Senha inválida. A senha deve ter no mínimo 8 caracteres, incluindo letras maiúsculas, minúsculas, números e caracteres especiais.' AS RESULTADO;
    ELSEIF param_nome IS NULL OR LENGTH(TRIM(param_nome)) < 2 OR LENGTH(TRIM(param_nome)) > 120 THEN
        SELECT 'Nome inválido. O nome deve ter entre 2 e 120 caracteres.' AS RESULTADO;
    ELSEIF EXISTS (SELECT 1 FROM usuario WHERE email = param_email) THEN
        SELECT 'Email já cadastrado' AS RESULTADO;
    ELSE
        INSERT INTO usuario(email, nome, senha, empresa) VALUES
            (param_email, param_nome, param_senha, param_empresa);
        SELECT 'Usuário criado com sucesso' AS RESULTADO;
    END IF;
END$$

DELIMITER ;
