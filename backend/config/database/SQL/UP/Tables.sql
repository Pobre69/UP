CREATE TABLE IF NOT EXISTS usuario(
	email VARCHAR(200) PRIMARY KEY,
    nome VARCHAR(120) NOT NULL,
    senha VARCHAR(60) NOT NULL,
    empresa TEXT NOT NULL
)ENGINE=INNODB;

CREATE TABLE IF NOT EXISTS plano(
	id INT NOT NULL AUTO_INCREMENT,
    nome VARCHAR(60) NOT NULL,
    valor INT NOT NULL,
    PRIMARY KEY (id, nome)
)ENGINE=INNODB;

CREATE TABLE IF NOT EXISTS concorrente(
	email VARCHAR(200) PRIMARY KEY,
    descricao TEXT,
    FOREIGN KEY (email) REFERENCES usuario(email)
		ON UPDATE CASCADE
        ON DELETE CASCADE
)ENGINE=INNODB;

CREATE TABLE IF NOT EXISTS usuario_plano(
	usuario_email VARCHAR(200) NOT NULL,
    plano_id INT NOT NULL,
    plano_nome VARCHAR(60) NOT NULL,
    data_inicial DATE NOT NULL,
    data_final DATE NOT NULL,
    FOREIGN KEY (usuario_email) REFERENCES usuario(email)
		ON UPDATE CASCADE,
	FOREIGN KEY (plano_id, plano_nome) REFERENCES plano(id, nome)
		ON UPDATE CASCADE
)ENGINE=INNODB;

CREATE TABLE IF NOT EXISTS usuario_detalhes(
	email VARCHAR(200) PRIMARY KEY,
    objetivo TEXT NOT NULL,
    google_drive TEXT,
    segmento TEXT NOT NULL,
    instagram VARCHAR(120),
    ajudante TEXT,
    localizacao JSON,
    FOREIGN KEY (email) REFERENCES usuario(email)
		ON UPDATE CASCADE
)ENGINE=INNODB;

CREATE TABLE IF NOT EXISTS auditoria(
	id INT AUTO_INCREMENT PRIMARY KEY,
    tabela VARCHAR(30) NOT NULL,
    descricao TEXT,
    data TIMESTAMP NOT NULL
)ENGINE=INNODB;