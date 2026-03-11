# Fluxo de Pagamento Cakto - Documentação Completa

## 📋 Visão Geral

Sistema completo de cadastro, pagamento e ativação de conta integrado com a plataforma Cakto.

## 🔄 Fluxo Completo

### 1. Seleção de Plano (Página Inicial)
- Usuário acessa a página inicial
- Seleciona um dos planos disponíveis:
  - **Básico** (R$ 250/mês)
  - **Premium** (R$ 499/mês)
  - **Completo** (R$ 999/mês)
- É redirecionado para `/signin?plano=NOME_DO_PLANO`

### 2. Cadastro
- Usuário preenche o formulário de cadastro
- Dados são enviados para `/auth/signup`
- Backend cria a conta com:
  - `ativo = FALSE` (conta inativa)
  - `plano_selecionado = NOME_DO_PLANO`
- Backend retorna a URL de pagamento do Cakto
- Frontend redireciona automaticamente para a página de pagamento do Cakto

### 3. Pagamento no Cakto
- Usuário é levado para a página de pagamento do Cakto
- Realiza o pagamento (PIX, cartão, boleto, etc.)
- Cakto processa o pagamento

### 4. Webhook (Ativação Automática)
- Cakto envia notificação para `/payment/webhook`
- Backend recebe o webhook com status `paid` ou `approved`
- Backend ativa a conta: `ativo = TRUE`
- Conta está pronta para uso

### 5. Login e Acesso
- Usuário faz login em `/login`
- Backend verifica se `ativo = TRUE`
  - **Se TRUE**: Redireciona para `/app` (dashboard)
  - **Se FALSE**: Redireciona para `/payment-verification`

### 6. Página de Verificação (se necessário)
- Mostra status "Aguardando pagamento"
- Exibe botão "Realizar Pagamento" (link do Cakto)
- Verifica status a cada 5 segundos
- Quando `ativo = TRUE`, redireciona automaticamente para `/app`

## 🔗 Links de Pagamento por Plano

| Plano | Valor | Link Cakto |
|-------|-------|------------|
| Básico | R$ 250/mês | https://pay.cakto.com.br/qq5rz6e_752674 |
| Premium | R$ 499/mês | https://pay.cakto.com.br/3mz49rp_754011 |
| Completo | R$ 999/mês | https://pay.cakto.com.br/4sgtxw3_754018 |

## 🛠️ Endpoints Implementados

### POST /auth/signup
Cria nova conta de usuário.

**Request:**
```json
{
  "fullName": "Nome Completo",
  "email": "email@exemplo.com",
  "password": "senha123",
  "company": "Empresa",
  "instagram": "@instagram",
  "segment": "restaurante-food-service",
  "city": "São Paulo - SP",
  "mainGoal": "mais-clientes-vendas",
  "competitors": "Concorrente 1, Concorrente 2",
  "driveLink": "https://drive.google.com/...",
  "attendant": "arthur-valentim",
  "planoSelecionado": "premium"
}
```

**Response:**
```json
{
  "success": true,
  "mensagem": "Cadastro realizado com sucesso!",
  "paymentUrl": "https://pay.cakto.com.br/3mz49rp_754011"
}
```

### POST /auth/login
Autentica usuário e retorna status da conta.

**Request:**
```json
{
  "email": "email@exemplo.com",
  "senha": "senha123"
}
```

**Response:**
```json
{
  "success": true,
  "mensagem": "Login realizado com sucesso",
  "ativo": false,
  "planoSelecionado": "premium",
  "usuario": {
    "email": "email@exemplo.com",
    "nome": "Nome Completo",
    "empresa": "Empresa"
  }
}
```

### POST /payment/webhook
Recebe notificações de pagamento do Cakto.

**Request (Cakto):**
```json
{
  "status": "paid",
  "customer": {
    "email": "email@exemplo.com"
  }
}
```

**Response:**
```json
{
  "success": true,
  "mensagem": "Conta ativada com sucesso"
}
```

### GET /payment/verificar
Verifica status de ativação da conta do usuário logado.

**Response:**
```json
{
  "success": true,
  "ativo": true,
  "planoSelecionado": "premium"
}
```

## ⚙️ Configuração do Webhook no Cakto

1. Acesse o painel da Cakto
2. Vá em Configurações > Webhooks
3. Configure a URL do webhook:
   ```
   https://seu-dominio.com/payment/webhook
   ```
4. Selecione os eventos:
   - Pagamento aprovado
   - Pagamento confirmado
5. Salve as configurações

## 🗄️ Estrutura do Banco de Dados

### Tabela: usuario
```sql
CREATE TABLE usuario(
    email VARCHAR(200) PRIMARY KEY,
    nome VARCHAR(120) NOT NULL,
    senha VARCHAR(255),
    empresa TEXT NOT NULL,
    ativo BOOLEAN DEFAULT FALSE,
    plano_selecionado VARCHAR(100) DEFAULT NULL
) ENGINE=INNODB;
```

## 🧪 Testando o Sistema

### Teste Manual do Webhook
Use curl para simular o webhook do Cakto:

```bash
curl -X POST http://localhost/payment/webhook \
  -H "Content-Type: application/json" \
  -d '{
    "status": "paid",
    "customer": {
      "email": "teste@email.com"
    }
  }'
```

### Ativar Conta Manualmente (para testes)
```sql
UPDATE usuario SET ativo = TRUE WHERE email = 'teste@email.com';
```

### Desativar Conta (para testar fluxo novamente)
```sql
UPDATE usuario SET ativo = FALSE WHERE email = 'teste@email.com';
```

## 📝 Logs

Os logs são registrados no error_log do PHP:

- `[SignUp] Erro: ...` - Erros no cadastro
- `[Login] Erro: ...` - Erros no login
- `[Webhook] Dados recebidos: ...` - Dados recebidos do webhook
- `[Webhook] Conta ativada para: ...` - Conta ativada com sucesso
- `[Webhook] Erro: ...` - Erros no webhook

## 🔒 Segurança

### Recomendações Implementadas:
- ✅ Senhas criptografadas com `password_hash()`
- ✅ Validação de email
- ✅ Sessões seguras
- ✅ Logs de todas as operações

### Recomendações Futuras:
- [ ] Configurar HTTPS no servidor
- [ ] Adicionar validação de IP da Cakto no webhook
- [ ] Implementar assinatura de webhook (se disponível)
- [ ] Adicionar rate limiting no endpoint do webhook
- [ ] Implementar CSRF protection

## 🚨 Troubleshooting

### Conta não ativa após pagamento
1. Verifique se o webhook está configurado corretamente na Cakto
2. Verifique os logs do servidor (`error_log`)
3. Confirme que o email no webhook corresponde ao email cadastrado
4. Teste o webhook manualmente com curl

### Usuário não consegue acessar após pagamento
1. Verifique se `ativo = TRUE` no banco de dados
2. Limpe o cache do navegador
3. Faça logout e login novamente
4. Verifique a página `/payment-verification`

### Redirecionamento não funciona após cadastro
1. Verifique se o backend está retornando `paymentUrl`
2. Verifique o console do navegador para erros
3. Confirme que o plano selecionado é válido

## 📊 Fluxograma

```
┌─────────────────┐
│  Página Inicial │
│ (Seleção Plano) │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│    Cadastro     │
│  (/signin)      │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│  Backend Cria   │
│  Conta Inativa  │
│  (ativo=FALSE)  │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│  Redireciona    │
│  para Cakto     │
│  (Pagamento)    │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│  Usuário Paga   │
│   no Cakto      │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│  Cakto Envia    │
│    Webhook      │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│  Backend Ativa  │
│     Conta       │
│  (ativo=TRUE)   │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│  Usuário Faz    │
│     Login       │
└────────┬────────┘
         │
         ▼
    ┌────┴────┐
    │ Ativo?  │
    └────┬────┘
         │
    ┌────┴────┐
    │         │
   SIM       NÃO
    │         │
    ▼         ▼
┌───────┐  ┌──────────────┐
│  /app │  │ /payment-    │
│       │  │ verification │
└───────┘  └──────────────┘
```

## ✅ Checklist de Implementação

- [x] Backend retorna URL de pagamento após cadastro
- [x] Frontend redireciona para Cakto após cadastro
- [x] Webhook recebe notificações do Cakto
- [x] Webhook ativa conta automaticamente
- [x] Login verifica status da conta
- [x] Página de verificação de pagamento
- [x] Verificação automática a cada 5 segundos
- [x] Redirecionamento automático após ativação
- [x] Documentação completa

## 🎯 Próximos Passos (Melhorias Futuras)

1. **Notificações por Email**
   - Enviar email de boas-vindas após cadastro
   - Enviar email de confirmação após pagamento
   - Enviar email com instruções de acesso

2. **Painel Administrativo**
   - Visualizar todas as contas
   - Ativar/desativar contas manualmente
   - Histórico de pagamentos

3. **Renovação Automática**
   - Webhook de renovação
   - Notificações de vencimento
   - Suspensão automática de contas vencidas

4. **Relatórios**
   - Dashboard de conversão
   - Relatório de pagamentos
   - Análise de planos mais vendidos

## 📞 Suporte

Em caso de dúvidas ou problemas:
1. Verifique os logs do servidor
2. Consulte esta documentação
3. Teste o webhook manualmente
4. Entre em contato com o suporte da Cakto
