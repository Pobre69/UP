# Correções Realizadas no Sistema de Cadastro

## Problemas Identificados e Soluções

### 1. URL Incorreta no Frontend
**Problema:** O arquivo `frontend/src/config.json` estava apontando para `http://localhost/Sites/backend/public` ao invés de `http://localhost/Sites/UP/backend/public`.

**Solução:** Corrigido o caminho da URL no arquivo de configuração.

### 2. Validação de Nome Muito Restritiva
**Problema:** O procedimento `CREATE_USUARIO` no banco de dados validava o nome com uma regex que aceitava apenas letras, impedindo nomes com números ou caracteres especiais.

**Solução:** Criado script SQL (`fix_create_usuario.sql`) que substitui a validação por uma verificação de comprimento (2-120 caracteres).

### 3. Tratamento de Valores Nulos
**Problema:** O controller não tratava corretamente valores opcionais (instagram, driveLink, attendant) que poderiam ser strings vazias.

**Solução:** Modificado o `SignUpController.php` para converter strings vazias em NULL antes de enviar ao banco.

### 4. Validação de Resultado da Inserção
**Problema:** O controller não verificava se a inserção do usuário foi bem-sucedida antes de tentar inserir os detalhes.

**Solução:** Adicionada validação do resultado retornado pela procedure `USUARIO_CONTROLLER`.

## Arquivos Modificados

1. **frontend/src/config.json**
   - Corrigida URL do backend

2. **backend/app/controllers/SignUpController.php**
   - Adicionado tratamento de valores nulos
   - Adicionada validação do resultado da criação do usuário
   - Melhorado tratamento de erros

3. **backend/config/database/SQL/UP/fix_create_usuario.sql** (NOVO)
   - Script para corrigir a validação do nome no banco de dados

## Como Aplicar as Correções

### Passo 1: Atualizar o Banco de Dados
Execute o script SQL no MySQL:

```bash
mysql -u root -p UP < backend/config/database/SQL/UP/fix_create_usuario.sql
```

Ou execute manualmente no phpMyAdmin/MySQL Workbench:
1. Abra o arquivo `fix_create_usuario.sql`
2. Copie todo o conteúdo
3. Execute no banco de dados `UP`

### Passo 2: Reiniciar o Servidor Backend
Se estiver usando XAMPP, reinicie o Apache.

### Passo 3: Reiniciar o Frontend
No terminal do frontend:
```bash
npm run dev
```

## Como Testar

1. Acesse a página de cadastro no frontend
2. Preencha o formulário com os seguintes dados de teste:
   - Nome completo: "João Silva"
   - Empresa: "Empresa Teste" (opcional)
   - E-mail: "joao.silva@teste.com"
   - Instagram: "@joaosilva" (opcional)
   - Segmento: Selecione qualquer opção
   - Cidade: "São Paulo - SP"
   - Objetivo principal: Selecione qualquer opção
   - Concorrentes: "Concorrente 1, Concorrente 2" (opcional)
   - Link do Google Drive: (opcional)
   - Atendente: Selecione qualquer opção (opcional)

3. Clique em "Enviar e começar!"

4. Verifique se:
   - A mensagem de sucesso aparece
   - Os dados foram salvos no banco de dados
   - Não há erros no console do navegador (F12)

## Verificar no Banco de Dados

Execute as seguintes queries para verificar se os dados foram salvos:

```sql
-- Verificar usuário
SELECT * FROM usuario WHERE email = 'joao.silva@teste.com';

-- Verificar detalhes
SELECT * FROM usuario_detalhes WHERE email = 'joao.silva@teste.com';

-- Verificar concorrentes (se preenchido)
SELECT * FROM concorrente WHERE email = 'joao.silva@teste.com';
```

## Estrutura de Dados Enviados

O frontend envia os seguintes dados para o endpoint `POST /api`:

```json
{
  "fullName": "Nome do usuário",
  "company": "Nome da empresa",
  "email": "email@exemplo.com",
  "instagram": "@usuario",
  "segment": "segmento-selecionado",
  "city": "Cidade - UF",
  "mainGoal": "objetivo-selecionado",
  "competitors": "Lista de concorrentes",
  "driveLink": "https://drive.google.com/...",
  "attendant": "nome-atendente"
}
```

## Fluxo de Dados

1. **Frontend** → Envia JSON via POST para `/api`
2. **SignUpController** → Valida e processa os dados
3. **UsuarioRepository** → Chama `USUARIO_CONTROLLER('add', ...)`
4. **CREATE_USUARIO** → Valida e insere na tabela `usuario`
5. **UsuarioDetalhesRepository** → Chama `USUARIO_DETALHES_CONTROLLER('add', ...)`
6. **CREATE_USUARIO_DETALHES** → Insere na tabela `usuario_detalhes`
7. **ConcorrenteRepository** → (Se houver) Insere na tabela `concorrente`
8. **SignUpController** → Retorna resposta JSON de sucesso/erro

## Possíveis Erros e Soluções

### Erro: "Email já cadastrado"
**Causa:** O e-mail já existe no banco de dados.
**Solução:** Use um e-mail diferente ou delete o registro existente.

### Erro: "Campos obrigatórios não preenchidos"
**Causa:** Algum campo obrigatório está vazio.
**Solução:** Verifique se preencheu: Nome, E-mail, Segmento, Cidade e Objetivo.

### Erro: "Não foi possível enviar"
**Causa:** Problema de conexão com o backend.
**Solução:** Verifique se o XAMPP está rodando e se a URL está correta.

### Erro: "Usuário não existente" ao inserir detalhes
**Causa:** A inserção do usuário falhou mas o código tentou inserir os detalhes.
**Solução:** Já corrigido no SignUpController - agora valida antes de prosseguir.

## Melhorias Futuras Sugeridas

1. Adicionar transações no banco de dados para garantir atomicidade
2. Implementar hash de senha quando o campo for utilizado
3. Adicionar logs de auditoria para rastreamento
4. Implementar validação de duplicidade de e-mail no frontend
5. Adicionar feedback visual durante o envio (loading state)
