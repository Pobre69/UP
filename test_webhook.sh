#!/bin/bash

# Script de Teste do Fluxo de Pagamento Cakto
# Execute este script para testar o webhook manualmente

echo "======================================"
echo "Teste do Webhook Cakto"
echo "======================================"
echo ""

# Configurações
BASE_URL="http://localhost"
TEST_EMAIL="teste@email.com"

echo "1. Testando webhook com status 'paid'..."
echo ""

curl -X POST "${BASE_URL}/payment/webhook" \
  -H "Content-Type: application/json" \
  -d "{
    \"status\": \"paid\",
    \"customer\": {
      \"email\": \"${TEST_EMAIL}\"
    }
  }"

echo ""
echo ""
echo "======================================"
echo "2. Verificando status da conta..."
echo ""

curl -X GET "${BASE_URL}/payment/verificar" \
  -H "Content-Type: application/json" \
  --cookie-jar cookies.txt \
  --cookie cookies.txt

echo ""
echo ""
echo "======================================"
echo "Teste concluído!"
echo "======================================"
echo ""
echo "Próximos passos:"
echo "1. Verifique os logs do servidor"
echo "2. Confirme que a conta foi ativada no banco de dados"
echo "3. Tente fazer login com a conta de teste"
echo ""
