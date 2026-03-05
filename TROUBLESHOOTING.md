# Troubleshooting - Dashboard Carregando Infinitamente

## Problema Resolvido ✅

O dashboard agora usa **fallback para dados mockados** quando a API falha, evitando carregamento infinito.

## Como Verificar o Problema Real

### 1. Abrir Console do Navegador (F12)

Procure por erros como:
- `Failed to fetch`
- `404 Not Found`
- `500 Internal Server Error`
- `CORS error`

### 2. Verificar Network Tab

1. Abra DevTools (F12)
2. Vá para aba "Network"
3. Recarregue a página
4. Procure pela requisição para `/api/dashboard`
5. Verifique:
   - Status Code (deve ser 200)
   - Response (deve ter JSON com dados)

### 3. Possíveis Causas

#### A. Backend não está rodando
**Solução**: Inicie o servidor PHP
```bash
cd backend/public
php -S localhost:8000
```

#### B. URL da API incorreta
**Verificar**: `frontend/src/config/api.ts`
```typescript
export const API_BASE_URL = "http://localhost/Sites/UP/backend/public/index.php";
```

**Deve apontar para onde seu backend está rodando**

#### C. Usuário não está autenticado
**Solução**: Fazer login primeiro
- A rota `/api/dashboard` requer autenticação
- Verifique se `Security::checkAuth()` está passando

#### D. Banco de dados vazio
**Solução**: Popular banco com dados de teste
```sql
-- Inserir métricas de teste
INSERT INTO instagram_metrics (email, followers_count, profile_views, reach, impressions, engagement_rate)
VALUES ('seu@email.com', 1000, 500, 5000, 10000, 5.5);
```

#### E. CORS bloqueando requisições
**Solução**: Verificar `backend/config/cors.php`
```php
header('Access-Control-Allow-Origin: http://localhost:5173');
header('Access-Control-Allow-Credentials: true');
```

### 4. Teste Manual da API

Abra o navegador e acesse diretamente:
```
http://localhost/Sites/UP/backend/public/index.php/api/dashboard
```

Deve retornar JSON ou erro específico.

### 5. Verificar Logs do PHP

Verifique erros no console do PHP ou em:
```
backend/storage/logs/
```

## Solução Atual

O código agora:
1. ✅ Tenta buscar dados da API
2. ✅ Se falhar, mostra aviso amarelo
3. ✅ Usa dados mockados como fallback
4. ✅ Nunca fica em loading infinito

## Próximos Passos

1. Verificar console do navegador para ver erro específico
2. Corrigir a causa raiz (backend, autenticação, banco, etc)
3. Quando API funcionar, dados reais serão exibidos automaticamente
