# Fluxo de Autenticação e Sincronização de Dados

## 1. Cadastro do Usuário (SignUp)
```
POST /api/
Body: { email, fullName, company, instagram, ... }
```
- Cria usuário no banco
- Salva informações básicas

## 2. Conectar Conta do Instagram (Primeira vez)
```
POST /api/instagram/connect
Body: { email, access_token }
```
- Usuário autoriza o app no Instagram
- Frontend recebe access_token
- Backend salva token na tabela `instagram_tokens`
- Busca e salva username e instagram_user_id

## 3. Login do Usuário
```
POST /auth/login
Body: { email, senha }
```

**Fluxo automático após login:**
1. Valida credenciais
2. Cria sessão ($_SESSION['user_email'])
3. **Sincroniza dados do Instagram automaticamente:**
   - Verifica se usuário tem token salvo
   - Se sim, busca da API do Instagram:
     - Métricas da conta (seguidores, alcance, impressões)
     - Posts recentes (últimos 20)
     - Insights (profile_views, reach, impressions)
   - Salva tudo no banco de dados
   - Salva backup em `storage/instagram/`

## 4. Dashboard carrega dados do banco
```
GET /api/dashboard
Headers: Cookie com sessão
```
- Valida autenticação via sessão
- Busca dados do banco (não da API)
- Retorna dados agregados e séries temporais

## Dados Armazenados no Banco

### instagram_tokens
- access_token (para futuras requisições)
- instagram_user_id
- instagram_username
- expires_at

### instagram_metrics (histórico)
- followers_count
- following_count
- media_count
- profile_views
- reach
- impressions
- engagement_rate
- collected_at (timestamp)

### instagram_posts
- id, caption, media_type, media_url
- like_count, comments_count
- timestamp

## Renovação de Token
Tokens do Instagram expiram em 60 dias.
```
POST /api/instagram/refresh
Body: { email }
```
- Renova token automaticamente
- Atualiza expires_at no banco

## Segurança
- Todas as rotas /api/* validam sessão via Security::checkAuth()
- Email vem da sessão, não do request
- Tokens armazenados criptografados no banco
- CORS configurado para frontend específico
