# Configuração do Sistema de Pagamento e Ativação de Conta

## Visão Geral
Este sistema implementa um fluxo completo de ativação de conta baseado em pagamento:

1. Usuário seleciona um plano na página inicial
2. Usuário se cadastra (conta criada com `ativo = FALSE`)
3. Após login, usuário é redirecionado para página de pagamento
4. Após pagamento, webhook ativa a conta automaticamente
5. Usuário pode acessar a aplicação

## Configuração do Webhook na Cakto

### URL do Webhook
Configure o webhook na plataforma Cakto para apontar para:
```
https://seu-dominio.com/payment/webhook
```

### Formato Esperado do Webhook
O sistema espera receber um JSON com a seguinte estrutura:

```json
{
  "status": "paid",
  "customer": {
    "email": "usuario@email.com"
  }
}
```

### Status Aceitos
- `paid` - Pagamento confirmado (ativa a conta)
- `approved` - Pagamento aprovado (ativa a conta)

## Links de Pagamento por Plano

### Plano Básico (R$ 250/mês)
- Link: https://pay.cakto.com.br/qq5rz6e_752674
- ID: `basico`

### Plano Premium (R$ 499/mês)
- Link: https://pay.cakto.com.br/3mz49rp_754011
- ID: `premium`

### Plano Completo (R$ 999/mês)
- Link: https://pay.cakto.com.br/4sgtxw3_754018
- ID: `completo`

## Endpoints Criados

### POST /payment/webhook
Recebe notificações de pagamento da Cakto e ativa contas automaticamente.

**Request Body:**
```json
{
  "status": "paid",
  "customer": {
    "email": "usuario@email.com"
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
Verifica o status de ativação da conta do usuário logado.

**Response:**
```json
{
  "success": true,
  "ativo": true,
  "planoSelecionado": "premium"
}
```

## Fluxo de Ativação

1. **Cadastro**: Usuário se cadastra com plano selecionado
   - Campo `ativo` = FALSE
   - Campo `plano_selecionado` = "basico" | "premium" | "completo"

2. **Login**: Usuário faz login
   - Sistema verifica se `ativo = FALSE`
   - Redireciona para `/payment-verification`

3. **Página de Verificação**: 
   - Mostra mensagem "Aguardando pagamento"
   - Verifica status a cada 5 segundos
   - Quando `ativo = TRUE`, redireciona para `/app`

4. **Webhook**: Cakto envia notificação de pagamento
   - Sistema recebe webhook
   - Atualiza `ativo = TRUE` no banco
   - Usuário é automaticamente redirecionado

## Estrutura do Banco de Dados

### Tabela: usuario
```sql
CREATE TABLE usuario(
    email VARCHAR(200) PRIMARY KEY,
    nome VARCHAR(120) NOT NULL,
    senha VARCHAR(255),
    empresa TEXT NOT NULL,
    ativo BOOLEAN DEFAULT FALSE,
    plano_selecionado VARCHAR(100) DEFAULT NULL
)ENGINE=INNODB;
```

## Testando o Sistema

### Teste Manual do Webhook
Use curl ou Postman para simular o webhook:

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

## Logs
Os logs do webhook são salvos no error_log do PHP. Verifique:
- `[Webhook] Dados recebidos: ...`
- `[Webhook] Conta ativada para: ...`
- `[Webhook] Erro: ...`

## Segurança

### Recomendações:
1. Configure HTTPS no servidor
2. Adicione validação de IP da Cakto no webhook
3. Implemente assinatura de webhook (se disponível na Cakto)
4. Adicione rate limiting no endpoint do webhook

### Exemplo de Validação de IP:
```php
$allowedIPs = ['IP_DA_CAKTO_1', 'IP_DA_CAKTO_2'];
$clientIP = $_SERVER['REMOTE_ADDR'];

if (!in_array($clientIP, $allowedIPs)) {
    http_response_code(403);
    exit('Forbidden');
}
```

## Troubleshooting

### Conta não ativa após pagamento
1. Verifique se o webhook está configurado corretamente na Cakto
2. Verifique os logs do servidor
3. Confirme que o email no webhook corresponde ao email cadastrado
4. Teste o webhook manualmente

### Usuário não consegue acessar após pagamento
1. Verifique se `ativo = TRUE` no banco de dados
2. Limpe o cache do navegador
3. Faça logout e login novamente
4. Verifique a página `/payment-verification`

## Próximos Passos (Melhorias Futuras)

1. Adicionar notificação por email quando conta for ativada
2. Implementar sistema de renovação automática
3. Adicionar histórico de pagamentos
4. Criar painel admin para gerenciar ativações
5. Implementar webhook de cancelamento/expiração
