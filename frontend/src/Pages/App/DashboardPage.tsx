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
import { dashboardService, type DashboardData } from "../../services/dashboardService";
import { instagramService } from "../../services/instagramService";
import { dashboard as mockDashboard, demoUser } from "../../mock/data";
import { formatNumber } from "../../utils/format";
import "./pages.css";

export default function DashboardPage() {
  const [range, setRange] = useState<"diario" | "semanal" | "mensal">("diario");
  const [dashboard, setDashboard] = useState<any>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [showInstagramAlert, setShowInstagramAlert] = useState(false);
  const [showConnectModal, setShowConnectModal] = useState(false);
  const [instagramStatus, setInstagramStatus] = useState<any>(null);

  useEffect(() => {
    // Verificar status da conexão do Instagram
    instagramService.getConnectionStatus()
      .then(res => setInstagramStatus(res.data))
      .catch(console.error);

    // Carregar dados do dashboard
    dashboardService.getDashboardData()
      .then(setDashboard)
      .catch((err) => {
        console.error('Erro ao carregar dashboard:', err);
        setError(err.message);
        setShowInstagramAlert(true);
        // Usar dados mockados como fallback
        setDashboard({
          user: demoUser,
          profileGrowthPct: mockDashboard.profileGrowthPct,
          stats: mockDashboard.stats,
          seguidoresSerie: mockDashboard.seguidoresSerie,
          alcanceSerie: mockDashboard.alcanceSerie,
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
    } catch (error) {
      console.error('Erro ao conectar Instagram:', error);
      alert('Erro ao conectar Instagram. Verifique o token e tente novamente.');
    }
  };

  const handleInstagramDisconnect = async () => {
    try {
      await instagramService.disconnectAccount();
      setShowInstagramAlert(true);
      window.location.reload();
    } catch (error) {
      console.error('Erro ao desconectar Instagram:', error);
      alert('Erro ao desconectar Instagram.');
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

  return (
    <div className="page">
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
              Desempenho do Perfil <span className="pill pillUp">Alto</span>
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
          delta={{ value: dashboard.stats.seguidores.delta, tone: "up" }}
        />
        <StatCard
          icon={<MousePointerClick size={18} />}
          label="Cliques no Perfil"
          value={formatNumber(dashboard.stats.cliquesPerfil.value)}
          delta={{ value: dashboard.stats.cliquesPerfil.delta, tone: "up" }}
        />
        <StatCard
          icon={<Eye size={18} />}
          label="Alcance Total"
          value={formatNumber(dashboard.stats.alcanceTotal.value)}
          delta={{ value: dashboard.stats.alcanceTotal.delta, tone: "up" }}
        />
        <StatCard
          icon={<Zap size={18} />}
          label="Impressões"
          value={formatNumber(dashboard.stats.impressoes.value)}
          delta={{ value: dashboard.stats.impressoes.delta, tone: "down" }}
        />
        <StatCard
          icon={<Sparkles size={18} />}
          label="Engajamento"
          value={`${dashboard.stats.engajamento.value.toFixed(1)}%`}
          delta={{ value: dashboard.stats.engajamento.delta, tone: "up" }}
        />
      </div>

      <div className="gridCharts">
        <Card className="chartCard">
          <div className="chartHeader">
            <CardTitle>Crescimento de Seguidores</CardTitle>
            <Segmented
              value={range}
              onChange={(v) => setRange(v as any)}
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
              {["01/01", "05/01", "10/01", "15/01", "20/01", "25/01", "30/01"].map(
                (d) => (
                  <span key={d}>{d}</span>
                )
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
              {["01/01", "05/01", "10/01", "15/01", "20/01", "25/01", "30/01"].map(
                (d) => (
                  <span key={d}>{d}</span>
                )
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
