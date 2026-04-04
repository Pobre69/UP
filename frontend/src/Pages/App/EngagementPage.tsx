import { useEffect, useMemo, useState } from "react";
import {
  Heart,
  MessageCircle,
  Share2,
  Eye,
  Activity,
  Images,
} from "lucide-react";
import { Card, CardTitle, PageHeader, StatCard } from "../../Components/UI/Cards";
import { DonutChart, SimpleBarChart } from "../../Components/UI/Charts";
import { engagementService } from "../../services";
import { formatNumber } from "../../utils/format";
import "./pages.css";

interface EngagementResponse {
  success: boolean;
  data?: {
    stats?: {
      avgLikes?: number;
      avgComments?: number;
      avgShares?: number;
      avgReach?: number;
      engagementRate?: number;
      bestStoryViews?: number;
    };
    postsPerformance?: Array<{
      id: string | number;
      caption?: string | null;
      media_type?: string;
      total_engagement?: number;
      like_count?: number;
      comments_count?: number;
      shares_count?: number;
      reach?: number;
    }>;
    distribution?: Array<{
      label: string;
      value: number;
      percent?: number;
    }>;
  };
}

const DONUT_COLORS = ["var(--accent)", "#3b82f6", "#f59e0b"];

export default function EngagementPage() {
  const [data, setData] = useState<EngagementResponse["data"] | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    const load = async () => {
      try {
        const response = (await engagementService.getEngagementData()) as EngagementResponse;
        if (!response.success) {
          throw new Error("Resposta inválida do servidor");
        }
        setData(response.data ?? null);
      } catch (err) {
        setError(err instanceof Error ? err.message : "Erro ao carregar engajamento");
      } finally {
        setLoading(false);
      }
    };

    void load();
  }, []);

  const distribution = useMemo(() => {
    const incoming = data?.distribution ?? [];
    return incoming.map((item, index) => ({
      label: item.label,
      value: Number(item.value) || 0,
      color: DONUT_COLORS[index % DONUT_COLORS.length],
    }));
  }, [data]);

  const performanceValues = (data?.postsPerformance ?? []).slice(0, 8).map((post) => Number(post.total_engagement) || 0);

  return (
    <div className="page">
      <PageHeader
        title="Engajamento"
        subtitle="Acompanhe as métricas de engajamento do seu perfil."
      />

      {error && <Card className="hintCard">{error}</Card>}

      <div className="gridStats gridStats6">
        <StatCard icon={<Heart size={18} />} label="Média de Curtidas" value={formatNumber(Number(data?.stats?.avgLikes) || 0)} />
        <StatCard
          icon={<MessageCircle size={18} />}
          label="Média de Comentários"
          value={formatNumber(Number(data?.stats?.avgComments) || 0)}
        />
        <StatCard
          icon={<Share2 size={18} />}
          label="Média de Compartilhamentos"
          value={formatNumber(Number(data?.stats?.avgShares) || 0)}
        />
        <StatCard icon={<Eye size={18} />} label="Alcance por Post" value={formatNumber(Number(data?.stats?.avgReach) || 0)} />
        <StatCard icon={<Activity size={18} />} label="Taxa de Engajamento" value={`${Number(data?.stats?.engagementRate || 0).toFixed(2)}%`} />
        <StatCard icon={<Images size={18} />} label="Melhor Story Views" value={formatNumber(Number(data?.stats?.bestStoryViews) || 0)} />
      </div>

      <div className="gridCharts">
        <Card className="chartCard" style={{ minHeight: 280 }}>
          <div className="chartHeader">
            <CardTitle>Performance por Post</CardTitle>
          </div>
          {loading ? (
            <div className="emptyBox">Carregando dados de engajamento...</div>
          ) : performanceValues.length === 0 ? (
            <div className="emptyBox">Nenhum post publicado ainda.</div>
          ) : (
            <div className="chartBody">
              <SimpleBarChart values={performanceValues} />
            </div>
          )}
        </Card>

        <Card className="chartCard" style={{ minHeight: 280 }}>
          <div className="chartHeader">
            <CardTitle>Distribuição de Engajamento</CardTitle>
          </div>
          {loading ? (
            <div className="emptyBox">Carregando distribuição...</div>
          ) : distribution.length === 0 || distribution.every((segment) => segment.value === 0) ? (
            <div className="emptyBox">Sem dados suficientes para distribuição.</div>
          ) : (
            <div className="chartBody" style={{ paddingTop: 14 }}>
              <DonutChart segments={distribution} />
            </div>
          )}
        </Card>
      </div>

      <Card className="chartCard">
        <div className="chartHeader">
          <CardTitle>Últimos Posts</CardTitle>
        </div>
        <div className="tableWrap">
          <table className="table">
            <thead>
              <tr>
                <th>Legenda</th>
                <th>Tipo</th>
                <th>Engajamento</th>
                <th>Curtidas</th>
                <th>Comentários</th>
                <th>Compart.</th>
                <th>Alcance</th>
              </tr>
            </thead>
            <tbody>
              {(data?.postsPerformance ?? []).length === 0 ? (
                <tr>
                  <td colSpan={7} className="tableEmpty">
                    Nenhum post disponível.
                  </td>
                </tr>
              ) : (
                (data?.postsPerformance ?? []).slice(0, 10).map((post) => (
                  <tr key={post.id}>
                    <td>{post.caption?.trim() ? post.caption.slice(0, 48) : "Sem legenda"}</td>
                    <td>{post.media_type ?? "—"}</td>
                    <td>{formatNumber(Number(post.total_engagement) || 0)}</td>
                    <td>{formatNumber(Number(post.like_count) || 0)}</td>
                    <td>{formatNumber(Number(post.comments_count) || 0)}</td>
                    <td>{formatNumber(Number(post.shares_count) || 0)}</td>
                    <td>{formatNumber(Number(post.reach) || 0)}</td>
                  </tr>
                ))
              )}
            </tbody>
          </table>
        </div>
      </Card>
    </div>
  );
}
