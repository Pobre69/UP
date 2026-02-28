# Integração com API do Instagram

## Instalação

### 1. Instalar Composer
Baixe e instale o Composer: https://getcomposer.org/download/

### 2. Instalar Dependências
```bash
cd backend
composer install
```

### 3. Configurar Credenciais
Copie o arquivo `.env.example` para `.env` e configure suas credenciais:
```bash
cp .env.example .env
```

Edite o arquivo `.env` com suas credenciais do Instagram:
```
INSTAGRAM_APP_ID=seu_app_id
INSTAGRAM_APP_SECRET=seu_app_secret
INSTAGRAM_REDIRECT_URI=http://localhost:5173/instagram/callback
```

### 4. Estrutura de Pastas
A pasta `storage/` foi criada para armazenar:
- `storage/instagram/` - Cache e dados do Instagram
- `storage/cache/` - Cache geral
- `storage/logs/` - Logs de requisições

### 5. Banco de Dados
As tabelas do Instagram foram adicionadas ao arquivo `Tables.sql`:
- `instagram_tokens` - Tokens de acesso
- `instagram_metrics` - Métricas da conta
- `instagram_posts` - Posts do Instagram
- `instagram_post_insights` - Insights dos posts

## Como Obter Credenciais do Instagram

1. Acesse: https://developers.facebook.com/
2. Crie um novo App
3. Adicione o produto "Instagram Basic Display"
4. Configure o OAuth Redirect URI
5. Copie o App ID e App Secret

## Endpoints da API

### 1. Conectar Conta do Instagram
```
POST /api/instagram/connect
Body: {
  "email": "usuario@email.com",
  "access_token": "token_do_instagram"
}
```

### 2. Obter Métricas da Conta
```
GET /api/instagram/metrics?email=usuario@email.com
```

### 3. Obter Posts Recentes
```
GET /api/instagram/posts?email=usuario@email.com&limit=20
```

### 4. Obter Histórico de Métricas
```
GET /api/instagram/metrics/history?email=usuario@email.com
```

### 5. Renovar Token de Acesso
```
POST /api/instagram/refresh
Body: {
  "email": "usuario@email.com"
}
```

## Estrutura de Dados

### Tabelas Criadas:
- `instagram_tokens` - Armazena tokens de acesso
- `instagram_metrics` - Armazena métricas da conta (seguidores, engajamento)
- `instagram_posts` - Armazena posts do Instagram
- `instagram_post_insights` - Armazena insights dos posts

## Arquivos Criados:

### Models:
- `app/models/InstagramToken.php`
- `app/models/InstagramMetrics.php`

### Repositories:
- `app/repository/InstagramTokenRepository.php`
- `app/repository/InstagramMetricsRepository.php`
- `app/repository/InstagramPostRepository.php`

### Services:
- `app/services/InstagramService.php`

### Controllers:
- `app/controllers/InstagramController.php`

## Fluxo de Uso

1. Usuário autoriza o app no Instagram
2. Frontend recebe o access_token
3. Frontend envia token para `/api/instagram/connect`
4. Sistema salva token e busca dados do usuário
5. Sistema pode buscar métricas e posts periodicamente
6. Token é renovado automaticamente a cada 60 dias
