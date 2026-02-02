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