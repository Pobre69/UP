import type React from "react";

import { useMemo, useState } from "react";
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
import { dashboard, demoUser } from "../../mock/data";
import { formatNumber } from "../../utils/format";
import "./pages.css";

export default function DashboardPage() {
  const [range, setRange] = useState<"diario" | "semanal" | "mensal">("diario");

  const growthWidth = useMemo(() => {
    const pct = Math.min(Math.max(dashboard.profileGrowthPct, 0), 100);
    return `${pct}%`;
  }, []);

  const followers = dashboard.seguidoresSerie;
  const reach = dashboard.alcanceSerie;

  return (
    <div className="page">
      <PageHeader
        title={
          <>
            Olá, <span className="accentText">{demoUser.handle}</span> 👋
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
    <div className="miniStat">
      <div className="miniIcon">{icon}</div>
      <div className="miniValue">{value}</div>
      <div className="miniLabel">{label}</div>
    </div>
  );
}
