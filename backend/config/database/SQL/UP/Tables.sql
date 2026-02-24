CREATE TABLE IF NOT EXISTS usuario(
	email VARCHAR(200) PRIMARY KEY,
    nome VARCHAR(120) NOT NULL,
    senha VARCHAR(60),
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

CREATE TABLE IF NOT EXISTS usuario_posts(
	email VARCHAR(200) PRIMARY KEY,
    post JSON,
    data_postada DATETIME NOT NULL,
    FOREIGN KEY (email) REFERENCES usuario(email)
		ON UPDATE CASCADE
)ENGINE=INNODB;

CREATE TABLE IF NOT EXISTS instagram_seguidores(
	email VARCHAR(200) PRIMARY KEY,
    seguidores INT NOT NULL,
    FOREIGN KEY (email) REFERENCES usuario(email)
		ON UPDATE CASCADE
)ENGINE=INNODB;

CREATE TABLE IF NOT EXISTS ajudante(
	id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL
)ENGINE=INNODB;

CREATE TABLE IF NOT EXISTS usuario_ajudante(
	email_usuario VARCHAR(200) NOT NULL,
    id_ajudante INT NOT NULL,
    status TEXT NOT NULL,
    FOREIGN KEY (email_usuario) REFERENCES usuario(email)
		ON UPDATE CASCADE,
	FOREIGN KEY (id_ajudante) REFERENCES ajudante(id)
		ON UPDATE CASCADE
)ENGINE=INNODB;

CREATE TABLE IF NOT EXISTS feedBack(
	id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_email VARCHAR(200) NOT NULL,
    titulo VARCHAR(200) NOT NULL,
    tipo VARCHAR(60) DEFAULT 'Feedback',
    texto TEXT NOT NULL,
    status VARCHAR(20) DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_email) REFERENCES usuario(email)
		ON UPDATE CASCADE
        ON DELETE CASCADE
)ENGINE=INNODB;

CREATE TABLE IF NOT EXISTS auditoria(
	id INT AUTO_INCREMENT PRIMARY KEY,
    tabela VARCHAR(30) NOT NULL,
    descricao TEXT,
    data TIMESTAMP NOT NULL
)ENGINE=INNODB;

CREATE TABLE IF NOT EXISTS instagram_tokens(
    email VARCHAR(200) PRIMARY KEY,
    access_token TEXT NOT NULL,
    token_type VARCHAR(50) DEFAULT 'long_lived',
    expires_at DATETIME,
    instagram_user_id VARCHAR(100),
    instagram_username VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (email) REFERENCES usuario(email)
        ON UPDATE CASCADE
        ON DELETE CASCADE
)ENGINE=INNODB;

CREATE TABLE IF NOT EXISTS instagram_metrics(
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(200) NOT NULL,
    followers_count INT DEFAULT 0,
    following_count INT DEFAULT 0,
    media_count INT DEFAULT 0,
    profile_views INT DEFAULT 0,
    reach INT DEFAULT 0,
    impressions INT DEFAULT 0,
    engagement_rate DECIMAL(5,2) DEFAULT 0.00,
    collected_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (email) REFERENCES usuario(email)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
    INDEX idx_email_date (email, collected_at)
)ENGINE=INNODB;

CREATE TABLE IF NOT EXISTS instagram_posts(
    id VARCHAR(100) PRIMARY KEY,
    email VARCHAR(200) NOT NULL,
    caption TEXT,
    media_type VARCHAR(50),
    media_url TEXT,
    permalink TEXT,
    timestamp DATETIME,
    like_count INT DEFAULT 0,
    comments_count INT DEFAULT 0,
    shares_count INT DEFAULT 0,
    saved_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (email) REFERENCES usuario(email)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
    INDEX idx_email (email)
)ENGINE=INNODB;

CREATE TABLE IF NOT EXISTS instagram_post_insights(
    id INT AUTO_INCREMENT PRIMARY KEY,
    post_id VARCHAR(100) NOT NULL,
    impressions INT DEFAULT 0,
    reach INT DEFAULT 0,
    saved INT DEFAULT 0,
    collected_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (post_id) REFERENCES instagram_posts(id)
        ON UPDATE CASCADE
        ON DELETE CASCADE
)ENGINE=INNODB;

CREATE TABLE IF NOT EXISTS instagram_stories(
    id VARCHAR(100) PRIMARY KEY,
    email VARCHAR(200) NOT NULL,
    media_type VARCHAR(50),
    media_url TEXT,
    timestamp DATETIME,
    impressions INT DEFAULT 0,
    reach INT DEFAULT 0,
    replies INT DEFAULT 0,
    exits INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (email) REFERENCES usuario(email)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
    INDEX idx_email (email)
)ENGINE=INNODB;

CREATE TABLE IF NOT EXISTS scheduled_posts(
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(200) NOT NULL,
    content_type VARCHAR(50) NOT NULL,
    caption TEXT,
    media_url TEXT,
    scheduled_date DATETIME NOT NULL,
    status VARCHAR(20) DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (email) REFERENCES usuario(email)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
    INDEX idx_email_date (email, scheduled_date)
)ENGINE=INNODB;

CREATE TABLE IF NOT EXISTS ads_campaigns(
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(200) NOT NULL,
    campaign_name VARCHAR(200) NOT NULL,
    status VARCHAR(20) DEFAULT 'active',
    budget DECIMAL(10,2) DEFAULT 0.00,
    spent DECIMAL(10,2) DEFAULT 0.00,
    clicks INT DEFAULT 0,
    impressions INT DEFAULT 0,
    reach INT DEFAULT 0,
    start_date DATE,
    end_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (email) REFERENCES usuario(email)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
    INDEX idx_email (email)
)ENGINE=INNODB;

CREATE TABLE IF NOT EXISTS content_pipeline(
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(200) NOT NULL,
    title VARCHAR(200) NOT NULL,
    content_type VARCHAR(50) NOT NULL,
    description TEXT,
    status VARCHAR(20) DEFAULT 'planned',
    scheduled_date DATETIME,
    published_date DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (email) REFERENCES usuario(email)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
    INDEX idx_email_status (email, status)
)ENGINE=INNODB;