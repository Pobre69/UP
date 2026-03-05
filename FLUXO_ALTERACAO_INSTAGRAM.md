# Fluxo de Alteração de Conta do Instagram

## Visão Geral

Sistema completo para conectar, alterar e desconectar contas do Instagram, com atualização automática no banco de dados.

## Componentes

### 1. Backend

#### Endpoints Criados
- `POST /api/instagram/connect` - Conectar/Reconectar conta
- `POST /api/instagram/disconnect` - Desconectar conta
- `GET /api/instagram/status` - Verificar status da conexão

#### InstagramController
```php
// Conectar conta (cria ou atualiza)
connectAccount() - Salva novo token no banco

// Desconectar conta
disconnectAccount() - Remove token do banco

// Verificar status
getConnectionStatus() - Retorna se está conectado e username
```

#### InstagramTokenRepository
```php
save() - INSERT ou UPDATE (ON DUPLICATE KEY)
delete() - Remove token do usuário
getByEmail() - Busca token atual
```

### 2. Frontend

#### InstagramAlert (Alerta)
- Detecta automaticamente se há conta conectada
- Mostra mensagem diferente para cada situação:
  - **Sem conta**: "Instagram não conectado"
  - **Com conta mas com erro**: "Erro ao buscar dados do Instagram"
- Botões:
  - **Conectar/Reconectar**: Abre modal
  - **Alterar Conta**: Abre modal com opção de desconectar

#### InstagramConnectModal (Modal)
- **Modo Conectar**: Apenas campo de token
- **Modo Alterar**: 
  - Mostra conta atual (@username)
  - Campo para novo token
  - Botão "Desconectar Conta" (vermelho)
  - Botão "Alterar Conta" (gradiente)

## Fluxo de Uso

### Cenário 1: Primeira Conexão
```
1. Usuário entra no dashboard
2. Não há dados do Instagram
3. InstagramAlert aparece: "Instagram não conectado"
4. Usuário clica "Conectar Instagram"
5. Modal abre
6. Usuário cola access token
7. Clica "Conectar Conta"
8. Token salvo no banco
9. Página recarrega com dados reais
```

### Cenário 2: Alterar Conta
```
1. Usuário tem conta conectada (@usuario1)
2. Quer trocar para @usuario2
3. Clica "Alterar Conta" no alerta (se houver erro)
   OU
   Acessa configurações (futuro)
4. Modal abre mostrando "Conta atual: @usuario1"
5. Usuário cola novo token
6. Clica "Alterar Conta"
7. Token atualizado no banco (mesmo email, novo token)
8. Página recarrega com dados da nova conta
```

### Cenário 3: Desconectar Conta
```
1. Usuário abre modal de alteração
2. Clica "Desconectar Conta" (botão vermelho)
3. Confirma ação
4. Token removido do banco
5. Página recarrega
6. Volta ao estado "Instagram não conectado"
```

### Cenário 4: Token Expirado
```
1. Token expira (60 dias)
2. API retorna erro ao buscar dados
3. InstagramAlert aparece: "Erro ao buscar dados"
4. Mostra conta atual mas com problema
5. Botões: "Reconectar" e "Alterar Conta"
6. Usuário escolhe:
   - Reconectar: Novo token da mesma conta
   - Alterar: Token de outra conta
```

## Banco de Dados

### Tabela: instagram_tokens
```sql
- email (PK) - Email do usuário
- access_token - Token de acesso
- expires_at - Data de expiração
- instagram_user_id - ID do Instagram
- instagram_username - Username (@usuario)
- created_at - Data de criação
- updated_at - Data de atualização
```

### Operações
- **INSERT**: Primeira conexão
- **UPDATE**: Alterar conta (ON DUPLICATE KEY)
- **DELETE**: Desconectar conta

## Estados da Interface

### InstagramAlert
1. **Não conectado**: 
   - Ícone de alerta
   - Mensagem: "Instagram não conectado"
   - Botão: "Conectar Instagram"

2. **Conectado com erro**:
   - Ícone de alerta
   - Mensagem: "Erro ao buscar dados"
   - Botões: "Reconectar" + "Alterar Conta"

### InstagramConnectModal
1. **Modo Conectar**:
   - Título: "Conectar Instagram"
   - Campo: Access Token
   - Botão: "Conectar Conta"

2. **Modo Alterar**:
   - Título: "Alterar Conta do Instagram"
   - Badge: "Conta atual: @username"
   - Campo: Access Token
   - Botões: "Desconectar Conta" + "Alterar Conta"

## Segurança

- Todas as rotas requerem autenticação (`Security::checkAuth()`)
- Token associado ao email do usuário logado
- Não é possível alterar token de outro usuário
- Confirmação antes de desconectar

## Próximos Passos

1. ✅ Conectar conta
2. ✅ Alterar conta
3. ✅ Desconectar conta
4. ✅ Verificar status
5. 🔄 Renovar token automaticamente
6. 🔄 Notificar quando token expirar
7. 🔄 Página de configurações dedicada
8. 🔄 Histórico de contas conectadas
