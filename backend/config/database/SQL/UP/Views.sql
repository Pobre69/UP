CREATE OR REPLACE VIEW vw_usuario AS
SELECT 
    email AS user_email,
    nome AS full_name,
    senha AS user_senha,
    empresa AS company_name
FROM 
    usuario;

CREATE OR REPLACE VIEW vw_plano AS
SELECT 
    id AS plan_id,
    nome AS plan_name,
    valor AS plan_valor
FROM 
    plano;

CREATE OR REPLACE VIEW vw_concorrente AS
SELECT 
    email AS competitor_email,
    descricao AS competitor_descricao
FROM 
    concorrente;

CREATE OR REPLACE VIEW vw_usuario_plano AS
SELECT 
    usuario_email AS user_email,
    plano_id AS plan_id,
    plano_nome AS plan_name,
    data_inicial AS start_date,
    data_final AS end_date
FROM 
    usuario_plano;

CREATE OR REPLACE VIEW vw_usuario_detalhes AS
SELECT 
    email AS user_email,
    objetivo AS user_objetivo,
    google_drive AS drive_link,
    segmento AS market_segmento,
    instagram AS insta_handle,
    ajudante AS helper_info,
    localizacao AS user_localizacao
FROM 
    usuario_detalhes;

CREATE OR REPLACE VIEW vw_usuario_posts AS
SELECT 
    email AS user_email,
    post AS post_content,
    data_postada AS posted_date
FROM 
    usuario_posts;

CREATE OR REPLACE VIEW vw_instagram_seguidores AS
SELECT 
    email AS user_email,
    seguidores AS followers_count
FROM 
    instagram_seguidores;

CREATE OR REPLACE VIEW vw_ajudante AS
SELECT 
    id AS helper_id,
    nome AS helper_name
FROM 
    ajudante;

CREATE OR REPLACE VIEW vw_usuario_ajudante AS
SELECT 
    email_usuario AS user_email,
    id_ajudante AS helper_id,
    status AS helper_status
FROM 
    usuario_ajudante;

CREATE OR REPLACE VIEW vw_feedback AS
SELECT 
    id AS feedback_id,
    usuario_email AS user_email,
    titulo AS feedback_title,
    tipo AS feedback_type,
    texto AS feedback_text
FROM 
    feedBack;