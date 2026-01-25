# Configuração do Stripe

## Passos para configurar:

### 1. Criar conta no Stripe
- Acesse https://stripe.com
- Crie uma conta gratuita
- Acesse o Dashboard

### 2. Obter as chaves da API
- No Dashboard do Stripe, vá em "Developers" > "API keys"
- Copie a "Publishable key" (pk_test_...)
- Copie a "Secret key" (sk_test_...)

### 3. Configurar as chaves
- Abra o arquivo `backend/.env`
- Substitua as chaves:
  ```
  STRIPE_SECRET_KEY=sk_test_sua_chave_secreta_aqui
  STRIPE_PUBLISHABLE_KEY=pk_test_sua_chave_publica_aqui
  ```

### 4. Iniciar o backend
```bash
cd backend
npm start
```

### 5. Iniciar o frontend
```bash
cd frontend
npm run dev
```

## Como funciona:
1. Cliente clica em "Quero esse plano"
2. Sistema cria sessão de checkout no Stripe
3. Cliente é redirecionado para página de pagamento do Stripe
4. Após pagamento, volta para página de sucesso
5. Se cancelar, volta para página de cancelamento

## Métodos de pagamento suportados:
- Cartão de crédito/débito
- PIX (automático para clientes brasileiros)

## URLs de retorno:
- Sucesso: `/sucesso`
- Cancelamento: `/cancelado`