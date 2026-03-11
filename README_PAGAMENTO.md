# 🚀 Fluxo de Pagamento Cakto - Guia Rápido

## ✅ O que foi implementado?

Sistema completo de cadastro → pagamento → ativação automática de conta.

## 🔄 Como funciona?

1. **Usuário se cadastra** → Conta criada como INATIVA
2. **Redirecionado para Cakto** → Realiza o pagamento
3. **Cakto envia webhook** → Conta ativada AUTOMATICAMENTE
4. **Usuário faz login** → Acessa o sistema

## 🔗 Links de Pagamento

- **Básico**: https://pay.cakto.com.br/qq5rz6e_752674
- **Premium**: https://pay.cakto.com.br/3mz49rp_754011
- **Completo**: https://pay.cakto.com.br/4sgtxw3_754018

## ⚙️ Configurar Webhook no Cakto

URL do webhook:
```
https://seu-dominio.com/payment/webhook
```

## 🧪 Testar Localmente

### Ativar conta manualmente (para testes):
```sql
UPDATE usuario SET ativo = TRUE WHERE email = 'seu@email.com';
```

### Testar webhook com curl:
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

## 📁 Arquivos Modificados

### Backend:
- ✅ `SignUpController.php` - Retorna URL de pagamento
- ✅ `PaymentWebhookController.php` - Recebe webhook e ativa conta
- ✅ `LoginController.php` - Verifica status da conta
- ✅ `UsuarioRepository.php` - Método para ativar conta

### Frontend:
- ✅ `SignInForm.tsx` - Redireciona para Cakto após cadastro
- ✅ `LoginForm.tsx` - Redireciona baseado no status da conta
- ✅ `PaymentVerificationPage.tsx` - Página de verificação de pagamento

## 📚 Documentação Completa

Veja o arquivo `FLUXO_PAGAMENTO_CAKTO.md` para documentação detalhada.

## 🎯 Status

✅ **IMPLEMENTAÇÃO COMPLETA E FUNCIONAL**

Todos os 5 passos foram concluídos com sucesso!
