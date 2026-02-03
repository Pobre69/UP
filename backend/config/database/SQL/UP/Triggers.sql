DELIMITER %

/* ============================================= */
/* TRIGGERS PARA TABELA USUARIO                 */
/* ============================================= */

CREATE TRIGGER INSERINDO_Usuario
AFTER INSERT ON usuario
FOR EACH ROW
BEGIN
    DECLARE VAR_TEXTO TEXT DEFAULT '';
    DECLARE VAR_NOME_TABELA VARCHAR(30) DEFAULT 'usuario';
    
    SET VAR_TEXTO = CONCAT(
        'Novo usuário inserido:\n',
        'Email: ', NEW.email, '\n',
        'Nome: ', NEW.nome, '\n',
        'Empresa: ', NEW.empresa
    );
    
    INSERT INTO auditoria (tabela, descricao, data) 
    VALUES (VAR_NOME_TABELA, VAR_TEXTO, CURRENT_TIMESTAMP);
END%

CREATE TRIGGER ALTERANDO_Usuario
BEFORE UPDATE ON usuario
FOR EACH ROW
BEGIN
    DECLARE VAR_TEXTO TEXT DEFAULT '';
    DECLARE VAR_QTDE_ALTERACOES INT DEFAULT 0;
    DECLARE VAR_NOME_TABELA VARCHAR(30) DEFAULT 'usuario';
    
    IF NOT (NEW.nome <=> OLD.nome) THEN
        SET VAR_TEXTO = CONCAT(VAR_TEXTO, '\nNOME: Antigo - ', OLD.nome, ', Novo - ', NEW.nome);
        SET VAR_QTDE_ALTERACOES = VAR_QTDE_ALTERACOES + 1;
    END IF;
    IF NOT (NEW.senha <=> OLD.senha) THEN
        SET VAR_TEXTO = CONCAT(VAR_TEXTO, '\nSENHA: Alterada');
        SET VAR_QTDE_ALTERACOES = VAR_QTDE_ALTERACOES + 1;
    END IF;
    IF NOT (NEW.empresa <=> OLD.empresa) THEN
        SET VAR_TEXTO = CONCAT(VAR_TEXTO, '\nEMPRESA: Antigo - ', OLD.empresa, ', Novo - ', NEW.empresa);
        SET VAR_QTDE_ALTERACOES = VAR_QTDE_ALTERACOES + 1;
    END IF;
    
    IF (VAR_TEXTO != '') THEN
        SET VAR_TEXTO = CONCAT('Houve ', VAR_QTDE_ALTERACOES, ' MODIFICAÇÃO(ÕES) no usuário ', OLD.nome, ' (Email: ', OLD.email, ') com as seguintes ALTERAÇÕES:', VAR_TEXTO);
        INSERT INTO auditoria (tabela, descricao, data) 
        VALUES ('usuario', VAR_TEXTO, CURRENT_TIMESTAMP);
    ELSE
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Nenhuma informação alterada';
    END IF;
END%

CREATE TRIGGER EXCLUINDO_Usuario
BEFORE DELETE ON usuario
FOR EACH ROW
BEGIN
    DECLARE VAR_TEXTO TEXT DEFAULT '';
    DECLARE VAR_NOME_TABELA VARCHAR(30) DEFAULT 'usuario';
    
    SET VAR_TEXTO = CONCAT(
        'Usuário deletado:\n',
        'Email: ', OLD.email, '\n',
        'Nome: ', OLD.nome, '\n',
        'Empresa: ', OLD.empresa
    );
    
    INSERT INTO auditoria (tabela, descricao, data) 
    VALUES (VAR_NOME_TABELA, VAR_TEXTO, CURRENT_TIMESTAMP);
END%

/* ============================================= */
/* TRIGGERS PARA TABELA PLANO                   */
/* ============================================= */

CREATE TRIGGER INSERINDO_Plano
AFTER INSERT ON plano
FOR EACH ROW
BEGIN
    DECLARE VAR_TEXTO TEXT DEFAULT '';
    DECLARE VAR_NOME_TABELA VARCHAR(30) DEFAULT 'plano';
    
    SET VAR_TEXTO = CONCAT(
        'Novo plano inserido:\n',
        'ID: ', NEW.id, '\n',
        'Nome: ', NEW.nome, '\n',
        'Valor: ', NEW.valor
    );
    
    INSERT INTO auditoria (tabela, descricao, data) 
    VALUES (VAR_NOME_TABELA, VAR_TEXTO, CURRENT_TIMESTAMP);
END%

CREATE TRIGGER ALTERANDO_Plano
BEFORE UPDATE ON plano
FOR EACH ROW
BEGIN
    DECLARE VAR_TEXTO TEXT DEFAULT '';
    DECLARE VAR_QTDE_ALTERACOES INT DEFAULT 0;
    DECLARE VAR_NOME_TABELA VARCHAR(30) DEFAULT 'plano';
    
    IF NOT (NEW.nome <=> OLD.nome) THEN
        SET VAR_TEXTO = CONCAT(VAR_TEXTO, '\nNOME: Antigo - ', OLD.nome, ', Novo - ', NEW.nome);
        SET VAR_QTDE_ALTERACOES = VAR_QTDE_ALTERACOES + 1;
    END IF;
    IF NOT (NEW.valor <=> OLD.valor) THEN
        SET VAR_TEXTO = CONCAT(VAR_TEXTO, '\nVALOR: Antigo - ', OLD.valor, ', Novo - ', NEW.valor);
        SET VAR_QTDE_ALTERACOES = VAR_QTDE_ALTERACOES + 1;
    END IF;
    
    IF (VAR_TEXTO != '') THEN
        SET VAR_TEXTO = CONCAT('Houve ', VAR_QTDE_ALTERACOES, ' MODIFICAÇÃO(ÕES) no plano ', OLD.nome, ' (ID: ', OLD.id, ') com as seguintes ALTERAÇÕES:', VAR_TEXTO);
        INSERT INTO auditoria (tabela, descricao, data) 
        VALUES (VAR_NOME_TABELA, VAR_TEXTO, CURRENT_TIMESTAMP);
    ELSE
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Nenhuma informação alterada';
    END IF;
END%

CREATE TRIGGER EXCLUINDO_Plano
BEFORE DELETE ON plano
FOR EACH ROW
BEGIN
    DECLARE VAR_TEXTO TEXT DEFAULT '';
    DECLARE VAR_NOME_TABELA VARCHAR(30) DEFAULT 'plano';
    
    SET VAR_TEXTO = CONCAT(
        'Plano deletado:\n',
        'ID: ', OLD.id, '\n',
        'Nome: ', OLD.nome, '\n',
        'Valor: ', OLD.valor
    );
    
    INSERT INTO auditoria (tabela, descricao, data) 
    VALUES (VAR_NOME_TABELA, VAR_TEXTO, CURRENT_TIMESTAMP);
END%

/* ============================================= */
/* TRIGGERS PARA TABELA USUARIO_PLANO           */
/* ============================================= */

CREATE TRIGGER INSERINDO_UsuarioPlano
AFTER INSERT ON usuario_plano
FOR EACH ROW
BEGIN
    DECLARE VAR_TEXTO TEXT DEFAULT '';
    DECLARE VAR_NOME_TABELA VARCHAR(30) DEFAULT 'usuario_plano';
    
    SET VAR_TEXTO = CONCAT(
        'Nova associação usuário-plano inserida:\n',
        'Email do Usuário: ', NEW.usuario_email, '\n',
        'ID do Plano: ', NEW.plano_id, '\n',
        'Nome do Plano: ', NEW.plano_nome, '\n',
        'Data Inicial: ', NEW.data_inicial, '\n',
        'Data Final: ', NEW.data_final
    );
    
    INSERT INTO auditoria (tabela, descricao, data) 
    VALUES (VAR_NOME_TABELA, VAR_TEXTO, CURRENT_TIMESTAMP);
END%

CREATE TRIGGER ALTERANDO_UsuarioPlano
BEFORE UPDATE ON usuario_plano
FOR EACH ROW
BEGIN
    DECLARE VAR_TEXTO TEXT DEFAULT '';
    DECLARE VAR_QTDE_ALTERACOES INT DEFAULT 0;
    DECLARE VAR_NOME_TABELA VARCHAR(30) DEFAULT 'usuario_plano';
    
    IF NOT (NEW.data_inicial <=> OLD.data_inicial) THEN
        SET VAR_TEXTO = CONCAT(VAR_TEXTO, '\nDATA INICIAL: Antigo - ', OLD.data_inicial, ', Novo - ', NEW.data_inicial);
        SET VAR_QTDE_ALTERACOES = VAR_QTDE_ALTERACOES + 1;
    END IF;
    IF NOT (NEW.data_final <=> OLD.data_final) THEN
        SET VAR_TEXTO = CONCAT(VAR_TEXTO, '\nDATA FINAL: Antigo - ', OLD.data_final, ', Novo - ', NEW.data_final);
        SET VAR_QTDE_ALTERACOES = VAR_QTDE_ALTERACOES + 1;
    END IF;
    
    IF (VAR_TEXTO != '') THEN
        SET VAR_TEXTO = CONCAT('Houve ', VAR_QTDE_ALTERACOES, ' MODIFICAÇÃO(ÕES) na associação do usuário ', OLD.usuario_email, ' com o plano ', OLD.plano_nome, ' com as seguintes ALTERAÇÕES:', VAR_TEXTO);
        INSERT INTO auditoria (tabela, descricao, data) 
        VALUES (VAR_NOME_TABELA, VAR_TEXTO, CURRENT_TIMESTAMP);
    ELSE
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Nenhuma informação alterada';
    END IF;
END%

CREATE TRIGGER EXCLUINDO_UsuarioPlano
BEFORE DELETE ON usuario_plano
FOR EACH ROW
BEGIN
    DECLARE VAR_TEXTO TEXT DEFAULT '';
    DECLARE VAR_NOME_TABELA VARCHAR(30) DEFAULT 'usuario_plano';
    
    SET VAR_TEXTO = CONCAT(
        'Associação usuário-plano deletada:\n',
        'Email do Usuário: ', OLD.usuario_email, '\n',
        'ID do Plano: ', OLD.plano_id, '\n',
        'Nome do Plano: ', OLD.plano_nome, '\n',
        'Data Inicial: ', OLD.data_inicial, '\n',
        'Data Final: ', OLD.data_final
    );
    
    INSERT INTO auditoria (tabela, descricao, data) 
    VALUES (VAR_NOME_TABELA, VAR_TEXTO, CURRENT_TIMESTAMP);
END%

/* ============================================= */
/* TRIGGERS PARA TABELA USUARIO_DETALHES        */
/* ============================================= */

CREATE TRIGGER INSERINDO_UsuarioDetalhes
AFTER INSERT ON usuario_detalhes
FOR EACH ROW
BEGIN
    DECLARE VAR_TEXTO TEXT DEFAULT '';
    DECLARE VAR_NOME_TABELA VARCHAR(30) DEFAULT 'usuario_detalhes';
    
    SET VAR_TEXTO = CONCAT(
        'Novos detalhes de usuário inseridos:\n',
        'Email: ', NEW.email, '\n',
        'Objetivo: ', NEW.objetivo, '\n',
        'Segmento: ', NEW.segmento, '\n',
        'Instagram: ', NEW.instagram, '\n',
        'Ajudante: ', NEW.ajudante
    );
    
    INSERT INTO auditoria (tabela, descricao, data) 
    VALUES (VAR_NOME_TABELA, VAR_TEXTO, CURRENT_TIMESTAMP);
END%

CREATE TRIGGER ALTERANDO_UsuarioDetalhes
BEFORE UPDATE ON usuario_detalhes
FOR EACH ROW
BEGIN
    DECLARE VAR_TEXTO TEXT DEFAULT '';
    DECLARE VAR_QTDE_ALTERACOES INT DEFAULT 0;
    DECLARE VAR_NOME_TABELA VARCHAR(30) DEFAULT 'usuario_detalhes';
    
    IF NOT (NEW.objetivo <=> OLD.objetivo) THEN
        SET VAR_TEXTO = CONCAT(VAR_TEXTO, '\nOBJETIVO: Antigo - ', OLD.objetivo, ', Novo - ', NEW.objetivo);
        SET VAR_QTDE_ALTERACOES = VAR_QTDE_ALTERACOES + 1;
    END IF;
    IF NOT (NEW.google_drive <=> OLD.google_drive) THEN
        SET VAR_TEXTO = CONCAT(VAR_TEXTO, '\nGOOGLE DRIVE: Antigo - ', OLD.google_drive, ', Novo - ', NEW.google_drive);
        SET VAR_QTDE_ALTERACOES = VAR_QTDE_ALTERACOES + 1;
    END IF;
    IF NOT (NEW.segmento <=> OLD.segmento) THEN
        SET VAR_TEXTO = CONCAT(VAR_TEXTO, '\nSEGMENTO: Antigo - ', OLD.segmento, ', Novo - ', NEW.segmento);
        SET VAR_QTDE_ALTERACOES = VAR_QTDE_ALTERACOES + 1;
    END IF;
    IF NOT (NEW.instagram <=> OLD.instagram) THEN
        SET VAR_TEXTO = CONCAT(VAR_TEXTO, '\nINSTAGRAM: Antigo - ', OLD.instagram, ', Novo - ', NEW.instagram);
        SET VAR_QTDE_ALTERACOES = VAR_QTDE_ALTERACOES + 1;
    END IF;
    IF NOT (NEW.ajudante <=> OLD.ajudante) THEN
        SET VAR_TEXTO = CONCAT(VAR_TEXTO, '\nAJUDANTE: Antigo - ', OLD.ajudante, ', Novo - ', NEW.ajudante);
        SET VAR_QTDE_ALTERACOES = VAR_QTDE_ALTERACOES + 1;
    END IF;
    IF NOT (NEW.localizacao <=> OLD.localizacao) THEN
        SET VAR_TEXTO = CONCAT(VAR_TEXTO, '\nLOCALIZAÇÃO: Alterada');
        SET VAR_QTDE_ALTERACOES = VAR_QTDE_ALTERACOES + 1;
    END IF;
    
    IF (VAR_TEXTO != '') THEN
        SET VAR_TEXTO = CONCAT('Houve ', VAR_QTDE_ALTERACOES, ' MODIFICAÇÃO(ÕES) nos detalhes do usuário ', OLD.email, ' com as seguintes ALTERAÇÕES:', VAR_TEXTO);
        INSERT INTO auditoria (tabela, descricao, data) 
        VALUES (VAR_NOME_TABELA, VAR_TEXTO, CURRENT_TIMESTAMP);
    ELSE
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Nenhuma informação alterada';
    END IF;
END%

CREATE TRIGGER EXCLUINDO_UsuarioDetalhes
BEFORE DELETE ON usuario_detalhes
FOR EACH ROW
BEGIN
    DECLARE VAR_TEXTO TEXT DEFAULT '';
    DECLARE VAR_NOME_TABELA VARCHAR(30) DEFAULT 'usuario_detalhes';
    
    SET VAR_TEXTO = CONCAT(
        'Detalhes do usuário deletados:\n',
        'Email: ', OLD.email, '\n',
        'Objetivo: ', OLD.objetivo, '\n',
        'Segmento: ', OLD.segmento, '\n',
        'Instagram: ', OLD.instagram, '\n',
        'Ajudante: ', OLD.ajudante
    );
    
    INSERT INTO auditoria (tabela, descricao, data) 
    VALUES (VAR_NOME_TABELA, VAR_TEXTO, CURRENT_TIMESTAMP);
END%

DELIMITER ;
