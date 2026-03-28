import type React from "react";

import { useMemo, useState, useEffect } from "react";
import {
  BarChart3,
  Users,
  MousePointerClick,
  Eye,
  Sparkles,
  Zap,
  Heart,
  MessageCircle,
  Share2,
  Images,
} from "lucide-react";
import { Card, CardTitle, PageHeader, StatCard } from "../../Components/UI/Cards";
import { SimpleBarChart, SimpleLineChart } from "../../Components/UI/Charts";
import { Segmented } from "../../Components/UI/Tabs";
import { InstagramAlert } from "../../Components/UI/InstagramAlert";
import { InstagramConnectModal } from "../../Components/UI/InstagramConnectModal";
import { ErrorModal } from "../../Components/UI/ErrorModal";
import { dashboardService, type DashboardData } from "../../services/dashboardService";
import { instagramService } from "../../services/instagramService";
import { dashboard as mockDashboard, demoUser } from "../../mock/data";
import { formatNumber } from "../../utils/format";
import "./pages.css";

type InstagramStatusData = { connected?: boolean; username?: string };

function toNumberDelta(value: string | number): number {
  if (typeof value === "number") return value;
  const normalized = Number.parseFloat(value.replace("%", ""));
  return Number.isFinite(normalized) ? normalized : 0;
}


export default function DashboardPage() {
  const [range, setRange] = useState<"diario" | "semanal" | "mensal">("diario");
  const [dashboard, setDashboard] = useState<DashboardData | null>(null);
  const [loading, setLoading] = useState(true);
  const [showInstagramAlert, setShowInstagramAlert] = useState(false);
  const [showConnectModal, setShowConnectModal] = useState(false);
  const [instagramStatus, setInstagramStatus] = useState<InstagramStatusData | null>(null);
  const [errorModal, setErrorModal] = useState<{ show: boolean; title: string; message: string; onRetry?: () => void }>({ 
    show: false, 
    title: '', 
    message: '' 
  });

  useEffect(() => {
    // Verificar status da conexão do Instagram
    instagramService.getConnectionStatus()
      .then((res) => setInstagramStatus((res.data as InstagramStatusData | undefined) ?? null))
      .catch(console.error);

    // Carregar dados do dashboard
    dashboardService.getDashboardData()
      .then(setDashboard)
      .catch((err) => {
        console.error('Erro ao carregar dashboard:', err);
        setShowInstagramAlert(true);
        // Usar dados mockados como fallback
        setDashboard({
          user: demoUser,
          profileGrowthPct: mockDashboard.profileGrowthPct,
          stats: {
            seguidores: {
              value: mockDashboard.stats.seguidores.value,
              delta: toNumberDelta(mockDashboard.stats.seguidores.delta),
            },
            cliquesPerfil: {
              value: mockDashboard.stats.cliquesPerfil.value,
              delta: toNumberDelta(mockDashboard.stats.cliquesPerfil.delta),
            },
            alcanceTotal: {
              value: mockDashboard.stats.alcanceTotal.value,
              delta: toNumberDelta(mockDashboard.stats.alcanceTotal.delta),
            },
            impressoes: {
              value: mockDashboard.stats.impressoes.value,
              delta: toNumberDelta(mockDashboard.stats.impressoes.delta),
            },
            engajamento: {
              value: mockDashboard.stats.engajamento.value,
              delta: toNumberDelta(mockDashboard.stats.engajamento.delta),
            },
          },
          seguidoresSerie: mockDashboard.seguidoresSerie,
          alcanceSerie: mockDashboard.alcanceSerie,
          chartDates: mockDashboard.chartDates,
          engajamentoResumo: mockDashboard.engajamentoResumo
        });
      })
      .finally(() => setLoading(false));
  }, []);

  const handleConnectInstagram = () => {
    setShowConnectModal(true);
  };

  const handleChangeAccount = () => {
    setShowConnectModal(true);
  };

  const handleInstagramConnect = async (accessToken: string) => {
    try {
      await instagramService.connectAccount(accessToken);
      setShowInstagramAlert(false);
      window.location.reload();
    } catch (error: unknown) {
      console.error('Erro ao conectar Instagram:', error);
      const message = error instanceof Error ? error.message : 'Não foi possível conectar sua conta. Verifique se o token está correto e tente novamente.';
      setErrorModal({
        show: true,
        title: 'Erro ao Conectar Instagram',
        message,
        onRetry: () => {
          setErrorModal({ show: false, title: '', message: '' });
          setShowConnectModal(true);
        }
      });
    }
  };

  const handleInstagramDisconnect = async () => {
    try {
      await instagramService.disconnectAccount();
      setShowInstagramAlert(true);
      window.location.reload();
    } catch (error: unknown) {
      console.error('Erro ao desconectar Instagram:', error);
      const message = error instanceof Error ? error.message : 'Não foi possível desconectar sua conta do Instagram. Tente novamente mais tarde.';
      setErrorModal({
        show: true,
        title: 'Erro ao Desconectar',
        message
      });
    }
  };

  const growthWidth = useMemo(() => {
    const pct = Math.min(Math.max(dashboard?.profileGrowthPct ?? 0, 0), 100);
    return `${pct}%`;
  }, [dashboard]);

  if (loading) return <div className="page">Carregando...</div>;
  if (!dashboard) return <div className="page">Erro ao carregar dados</div>;

  const followers = dashboard.seguidoresSerie;
  const reach = dashboard.alcanceSerie;
  const chartDates = dashboard.chartDates || [];
  
  // Determinar nível de desempenho baseado no crescimento
  const performanceLevel = dashboard.profileGrowthPct >= 50 ? 'Alto' : dashboard.profileGrowthPct >= 20 ? 'Médio' : 'Baixo';
  const performancePill = dashboard.profileGrowthPct >= 50 ? 'pillUp' : dashboard.profileGrowthPct >= 20 ? 'pillNeutral' : 'pillDown';

  return (
    <div className="page">
      <ErrorModal
        isOpen={errorModal.show}
        title={errorModal.title}
        message={errorModal.message}
        onClose={() => setErrorModal({ show: false, title: '', message: '' })}
        onRetry={errorModal.onRetry}
      />

      {showInstagramAlert && (
        <InstagramAlert 
          onConnect={handleConnectInstagram}
          onChangeAccount={handleChangeAccount}
          hasAccount={instagramStatus?.connected}
        />
      )}

      <InstagramConnectModal
        isOpen={showConnectModal}
        onClose={() => setShowConnectModal(false)}
        onConnect={handleInstagramConnect}
        onDisconnect={handleInstagramDisconnect}
        currentUsername={instagramStatus?.username}
      />
      <PageHeader
        title={
          <>
            Olá, <span className="accentText">{dashboard.user.handle}</span> 👋
          </>
        }
        subtitle="Aqui está o resumo do seu desempenho."
      />

      <Card className="profilePerf">
        <div className="profilePerfTop">
          <div className="profilePerfIcon">
            <BarChart3 size={18} />
          </div>
          <div className="profilePerfText">
            <div className="profilePerfTitle">
              Desempenho do Perfil <span className={`pill ${performancePill}`}>{performanceLevel}</span>
            </div>
            <div className="profilePerfSub">
              Seu perfil cresceu mais que <b>{dashboard.profileGrowthPct}%</b> dos
              nossos clientes este mês <Sparkles size={14} />
            </div>
          </div>
        </div>
        <div className="progressTrack">
          <div className="progressBar" style={{ width: growthWidth }} />
        </div>
      </Card>

      <div className="gridStats">
        <StatCard
          icon={<Users size={18} />}
          label="Seguidores"
          value={formatNumber(dashboard.stats.seguidores.value)}
          delta={{ value: String(dashboard.stats.seguidores.delta), tone: "up" }}
        />
        <StatCard
          icon={<MousePointerClick size={18} />}
          label="Cliques no Perfil"
          value={formatNumber(dashboard.stats.cliquesPerfil.value)}
          delta={{ value: String(dashboard.stats.cliquesPerfil.delta), tone: "up" }}
        />
        <StatCard
          icon={<Eye size={18} />}
          label="Alcance Total"
          value={formatNumber(dashboard.stats.alcanceTotal.value)}
          delta={{ value: String(dashboard.stats.alcanceTotal.delta), tone: "up" }}
        />
        <StatCard
          icon={<Zap size={18} />}
          label="Impressões"
          value={formatNumber(dashboard.stats.impressoes.value)}
          delta={{ value: String(dashboard.stats.impressoes.delta), tone: "down" }}
        />
        <StatCard
          icon={<Sparkles size={18} />}
          label="Engajamento"
          value={`${dashboard.stats.engajamento.value.toFixed(1)}%`}
          delta={{ value: String(dashboard.stats.engajamento.delta), tone: "up" }}
        />
      </div>

      <div className="gridCharts">
        <Card className="chartCard">
          <div className="chartHeader">
            <CardTitle>Crescimento de Seguidores</CardTitle>
            <Segmented
              value={range}
              onChange={(v) => setRange(v as "diario" | "semanal" | "mensal")}
              options={[
                { value: "diario", label: "Diário" },
                { value: "semanal", label: "Semanal" },
                { value: "mensal", label: "Mensal" },
              ]}
            />
          </div>
          <div className="chartBody">
            <SimpleLineChart points={followers} />
            <div className="chartAxis">
              {chartDates.length > 0 ? (
                chartDates.map((d: string, i: number) => (
                  <span key={i}>{d}</span>
                ))
              ) : (
                <span>Sem dados</span>
              )}
            </div>
          </div>
        </Card>

        <Card className="chartCard">
          <div className="chartHeader">
            <CardTitle>Alcance por Período</CardTitle>
          </div>
          <div className="chartBody">
            <SimpleBarChart values={reach} />
            <div className="chartAxis">
              {chartDates.length > 0 ? (
                chartDates.map((d: string, i: number) => (
                  <span key={i}>{d}</span>
                ))
              ) : (
                <span>Sem dados</span>
              )}
            </div>
          </div>
        </Card>
      </div>

      <Card className="engSummary">
        <div className="engHeader">
          <div className="engTitle">
            <Heart size={16} /> Engajamento
          </div>
        </div>
        <div className="engGrid">
          <MiniStat
            icon={<Heart size={16} />}
            label="Curtidas médias/post"
            value={formatNumber(dashboard.engajamentoResumo.curtidasMedia)}
          />
          <MiniStat
            icon={<MessageCircle size={16} />}
            label="Comentários médios"
            value={formatNumber(dashboard.engajamentoResumo.comentariosMedios)}
          />
          <MiniStat
            icon={<Share2 size={16} />}
            label="Compartilhamentos"
            value={formatNumber(dashboard.engajamentoResumo.compartilhamentos)}
          />
          <MiniStat
            icon={<Eye size={16} />}
            label="Alcance médio/post"
            value={formatNumber(dashboard.engajamentoResumo.alcanceMedio)}
          />
          <MiniStat
            icon={<Images size={16} />}
            label="Melhor Story (views)"
            value={formatNumber(dashboard.engajamentoResumo.melhorStory)}
          />
        </div>
      </Card>
    </div>
  );
}

function MiniStat({
  icon,
  label,
  value,
}: {
  icon: React.ReactNode;
  label: string;
  value: string;
}) {
  return (
    <div className="miniStat reveal">
      <div className="miniIcon">{icon}</div>
      <div className="miniValue">{value}</div>
      <div className="miniLabel">{label}</div>
    </div>
  );
}
