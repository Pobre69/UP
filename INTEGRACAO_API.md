# Integração Dashboard - API - Banco de Dados

## Arquitetura

```
Frontend (React/TypeScript)
    ↓
Services (API Calls)
    ↓
Backend Controllers (PHP)
    ↓
Repositories (Database Access)
    ↓
Database (MySQL)
    ↑
Instagram API Service
    ↑
Instagram Graph API
```

## Fluxo de Dados

### 1. Dashboard Page
- **Frontend**: `DashboardPage.tsx`
- **Service**: `dashboardService.ts`
- **Controller**: `DashboardController.php`
- **Repository**: `DashboardRepository.php`
- **Tabelas**: `instagram_metrics`, `instagram_posts`

**Fluxo**:
1. DashboardPage carrega e chama `dashboardService.getDashboardData()`
2. Service faz requisição GET para `/api/dashboard`
3. DashboardController busca dados do usuário autenticado
4. DashboardRepository consulta banco de dados
5. Dados retornam em JSON para o frontend

### 2. Instagram API Integration
- **Service**: `InstagramService.php`
- **Controller**: `InstagramController.php`
- **Repositories**: `InstagramMetricsRepository.php`, `InstagramPostRepository.php`, `InstagramTokenRepository.php`

**Endpoints**:
- `POST /api/instagram/connect` - Conectar conta do Instagram
- `GET /api/instagram/metrics` - Buscar métricas da conta
- `GET /api/instagram/posts` - Buscar posts recentes
- `GET /api/instagram/metrics/history` - Histórico de métricas
- `POST /api/instagram/refresh` - Renovar token de acesso

**Fluxo de Coleta de Dados**:
1. Usuário conecta conta do Instagram via `connectAccount()`
2. Token é salvo no banco de dados
3. `getAccountMetrics()` busca dados da API do Instagram
4. Dados são salvos no banco via repositories
5. Dashboard consulta dados salvos no banco

### 3. Outras Páginas

#### Engagement
- **Service**: `engagementService.ts`
- **Controller**: `EngagementController.php`
- **Repository**: `EngagementRepository.php`

#### Calendar
- **Service**: `calendarService.ts`
- **Controller**: `CalendarController.php`
- **Repository**: `CalendarRepository.php`

#### Ads
- **Service**: `adsService.ts`
- **Controller**: `AdsController.php`
- **Repository**: `AdsRepository.php`

#### Reports
- **Service**: `reportsService.ts`
- **Controller**: `ReportsController.php`
- **Repository**: `ReportsRepository.php`

#### Requests
- **Service**: `requestsService.ts`
- **Controller**: `RequestsController.php`
- **Repository**: `RequestsRepository.php`

#### Service Status
- **Service**: `serviceStatusService.ts`
- **Controller**: `ServiceStatusController.php`
- **Repository**: `ServiceStatusRepository.php`

#### Plan
- **Service**: `planService.ts`
- **Controller**: `PlanController.php`
- **Repository**: `PlanRepository.php`

## Autenticação

Todas as rotas da API usam autenticação de sessão via `Security::checkAuth()`:

```php
Security::checkAuth();
$user = Security::getAuthUser();
$email = $user['email'];
```

No frontend, as requisições incluem `credentials: 'include'` para enviar cookies de sessão.

## Como Usar

### Frontend

```typescript
import { dashboardService } from '../services';

// Em um componente
const [data, setData] = useState(null);

useEffect(() => {
  dashboardService.getDashboardData()
    .then(setData)
    .catch(console.error);
}, []);
```

Ou usando o hook customizado:

```typescript
import { useApiData } from '../utils/useApiData';
import { dashboardService } from '../services';

const { data, loading, error } = useApiData(() => 
  dashboardService.getDashboardData()
);
```

### Backend

Os controllers já estão configurados e conectados às rotas em `Web.php`.

Para adicionar novos endpoints:

1. Criar método no Controller
2. Criar método no Repository (se necessário)
3. Adicionar rota em `Web.php`
4. Criar/atualizar service no frontend

## Variáveis de Ambiente

Configure no arquivo `.env`:

```
INSTAGRAM_APP_ID=seu_app_id
INSTAGRAM_APP_SECRET=seu_app_secret
```

## Banco de Dados

As tabelas principais são:
- `usuarios` - Dados dos usuários
- `instagram_tokens` - Tokens de acesso do Instagram
- `instagram_metrics` - Métricas coletadas da API
- `instagram_posts` - Posts do Instagram
- `planos` - Planos disponíveis
- `usuario_planos` - Relação usuário-plano

## Próximos Passos

1. Implementar coleta automática de dados (cron job)
2. Adicionar cache para reduzir chamadas à API
3. Implementar rate limiting
4. Adicionar testes unitários
5. Melhorar tratamento de erros
