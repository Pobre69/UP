DELIMITER %
CREATE PROCEDURE USUARIO_CONTROLLER(
	IN acao TEXT,
    IN param_email VARCHAR(200),
    IN param_nome VARCHAR(120),
    IN param_senha VARCHAR(60),
    IN param_empresa TEXT)
BEGIN
	IF acao = 'add' THEN
		CALL CREATE_USUARIO(param_email,param_nome,param_senha,param_empresa);
	ELSEIF acao = 'update' THEN
		CALL UPDATE_USUARIO(param_email,param_nome,param_senha,param_empresa);
  ELSEIF acao = 'read' THEN
		SELECT * FROM vw_usuario;
  ELSEIF acao = 'delete' THEN
		CALL DELETE_USUARIO(param_email);
  ELSE
		SELECT 'Ação não indentificada' AS RESULTADO;
  END IF;
END%

CREATE PROCEDURE PLANO_CONTROLLER(
	IN acao TEXT,
	IN param_id INT,
	IN param_nome VARCHAR(60),
	IN param_valor INT)
BEGIN
	IF acao = 'add' THEN
		CALL CREATE_PLANO(param_id,param_nome,param_valor);
	ELSEIF acao = 'update' THEN
		CALL UPDATE_PLANO(param_id,param_nome,param_valor);
	ELSEIF acao = 'read' THEN
		SELECT * FROM vw_plano;
	ELSEIF acao = 'delete' THEN
		CALL DELETE_PLANO(param_id,param_nome);
	ELSE
		SELECT 'Ação não indentificada' AS RESULTADO;
	END IF;
END%

CREATE PROCEDURE CONCORRENTE_CONTROLLER(
	IN acao TEXT,
	IN param_email VARCHAR(200),
	IN param_descricao TEXT)
BEGIN
	IF acao = 'add' THEN
		CALL CREATE_CONCORRENTE(param_email,param_descricao);
	ELSEIF acao = 'update' THEN
		CALL UPDATE_CONCORRENTE(param_email,param_descricao);
	ELSEIF acao = 'read' THEN
		SELECT * FROM vw_concorrente;
	ELSEIF acao = 'delete' THEN
		CALL DELETE_CONCORRENTE(param_email);
	ELSE
		SELECT 'Ação não indentificada' AS RESULTADO;
	END IF;
END%

CREATE PROCEDURE USUARIO_PLANO_CONTROLLER(
	IN acao TEXT,
	IN param_usuario_email VARCHAR(200),
	IN param_plano_id INT,
	IN param_plano_nome VARCHAR(60),
	IN param_data_inicial DATE,
	IN param_data_final DATE)
BEGIN
	IF acao = 'add' THEN
		CALL CREATE_USUARIO_PLANO(param_usuario_email,param_plano_id,param_plano_nome,param_data_inicial,param_data_final);
	ELSEIF acao = 'update' THEN
		CALL UPDATE_USUARIO_PLANO(param_usuario_email,param_plano_id,param_plano_nome,param_data_inicial,param_data_final);
	ELSEIF acao = 'read' THEN
		SELECT * FROM vw_usuario_plano;
	ELSEIF acao = 'delete' THEN
		CALL DELETE_USUARIO_PLANO(param_usuario_email,param_plano_id,param_plano_nome);
	ELSE
		SELECT 'Ação não indentificada' AS RESULTADO;
	END IF;
END%

CREATE PROCEDURE USUARIO_DETALHES_CONTROLLER(
	IN acao TEXT,
	IN param_email VARCHAR(200),
	IN param_objetivo TEXT,
	IN param_google_drive TEXT,
	IN param_segmento TEXT,
	IN param_instagram VARCHAR(120),
	IN param_ajudante TEXT,
	IN param_localizacao JSON)
BEGIN
	IF acao = 'add' THEN
		CALL CREATE_USUARIO_DETALHES(param_email,param_objetivo,param_google_drive,param_segmento,param_instagram,param_ajudante,param_localizacao);
	ELSEIF acao = 'update' THEN
		CALL UPDATE_USUARIO_DETALHES(param_email,param_objetivo,param_google_drive,param_segmento,param_instagram,param_ajudante,param_localizacao);
	ELSEIF acao = 'read' THEN
		SELECT * FROM vw_usuario_detalhes;
	ELSEIF acao = 'delete' THEN
		CALL DELETE_USUARIO_DETALHES(param_email);
	ELSE
		SELECT 'Ação não indentificada' AS RESULTADO;
	END IF;
END%
DELIMITER ;