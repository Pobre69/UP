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
import { formatNumber } from "../../utils/format";
import "./pages.css";

type InstagramStatusData = { connected?: boolean; username?: string };


export default function DashboardPage() {
  const [range, setRange] = useState<"diario" | "semanal" | "mensal">("mensal");
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
    dashboardService.getDashboardData(range)
      .then(setDashboard)
      .catch((err) => {
        console.error('Erro ao carregar dashboard:', err);
        setShowInstagramAlert(true);
        setDashboard({
          user: { handle: 'Usuário', email: '' },
          profileGrowthPct: 0,
          stats: {
            seguidores: { value: 0, delta: 0 },
            cliquesPerfil: { value: 0, delta: 0 },
            alcanceTotal: { value: 0, delta: 0 },
            impressoes: { value: 0, delta: 0 },
            engajamento: { value: 0, delta: 0 },
          },
          seguidoresSerie: [],
          alcanceSerie: [],
          chartDates: [],
          engajamentoResumo: {
            curtidasMedia: 0,
            comentariosMedios: 0,
            compartilhamentos: 0,
            alcanceMedio: 0,
            melhorStory: 0,
          }
        });
      })
      .finally(() => setLoading(false));
  }, [range]);

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
              Crescimento do período: <b>{dashboard.profileGrowthPct}%</b> em relação ao seu histórico recente <Sparkles size={14} />
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
          delta={{ value: String(dashboard.stats.seguidores.delta), tone: dashboard.stats.seguidores.delta >= 0 ? "up" : "down" }}
        />
        <StatCard
          icon={<MousePointerClick size={18} />}
          label="Cliques no Perfil"
          value={formatNumber(dashboard.stats.cliquesPerfil.value)}
          delta={{ value: String(dashboard.stats.cliquesPerfil.delta), tone: dashboard.stats.cliquesPerfil.delta >= 0 ? "up" : "down" }}
        />
        <StatCard
          icon={<Eye size={18} />}
          label="Alcance Total"
          value={formatNumber(dashboard.stats.alcanceTotal.value)}
          delta={{ value: String(dashboard.stats.alcanceTotal.delta), tone: dashboard.stats.alcanceTotal.delta >= 0 ? "up" : "down" }}
        />
        <StatCard
          icon={<Zap size={18} />}
          label="Impressões"
          value={formatNumber(dashboard.stats.impressoes.value)}
          delta={{ value: String(dashboard.stats.impressoes.delta), tone: dashboard.stats.impressoes.delta >= 0 ? "up" : "down" }}
        />
        <StatCard
          icon={<Sparkles size={18} />}
          label="Engajamento"
          value={`${dashboard.stats.engajamento.value.toFixed(1)}%`}
          delta={{ value: String(dashboard.stats.engajamento.delta), tone: dashboard.stats.engajamento.delta >= 0 ? "up" : "down" }}
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
