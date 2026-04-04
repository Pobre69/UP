import { useEffect, useMemo, useState } from "react";
import { Download, FileText, TrendingDown, TrendingUp } from "lucide-react";
import { Card, CardTitle, PageHeader } from "../../Components/UI/Cards";
import { SimpleLineChart } from "../../Components/UI/Charts";
import { reportsService } from "../../services";
import { formatNumber } from "../../utils/format";
import "./pages.css";

interface ReportsResponse {
  success: boolean;
  data?: {
    evolution?: {
      followers?: Array<{ date: string; followers_count: number }>;
      reach?: Array<{ date: string; reach: number }>;
    };
    last7Days?: Array<{
      date: string;
      followers_count: number;
      reach: number;
      engagement_rate: number;
      trend: string;
      trend_value: number;
    }>;
    summary?: {
      currentFollowers?: number;
      initialFollowers?: number;
      growthPercentage?: number;
      avgReach?: number;
      avgEngagement?: number;
      totalPosts?: number;
    };
  };
}

export default function ReportsPage() {
  const [data, setData] = useState<ReportsResponse["data"] | null>(null);
  const [loading, setLoading] = useState(true);
  const [exporting, setExporting] = useState(false);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    const load = async () => {
      try {
        const response = (await reportsService.getReportsData()) as ReportsResponse;
        if (!response.success) throw new Error("Resposta inválida do servidor");
        setData(response.data ?? null);
      } catch (err) {
        setError(err instanceof Error ? err.message : "Erro ao carregar relatórios");
      } finally {
        setLoading(false);
      }
    };

    void load();
  }, []);

  const chartValues = useMemo(() => {
    const followers = data?.evolution?.followers ?? [];
    return followers.map((item) => Number(item.followers_count) || 0);
  }, [data]);

  const chartDates = (data?.evolution?.followers ?? []).map((item) => item.date);

  const handleExport = async () => {
    try {
      setExporting(true);
      const blob = await reportsService.exportReport("csv");
      const url = window.URL.createObjectURL(blob);
      const link = document.createElement("a");
      link.href = url;
      link.download = `relatorio_${new Date().toISOString().slice(0, 10)}.csv`;
      document.body.appendChild(link);
      link.click();
      link.remove();
      window.URL.revokeObjectURL(url);
    } catch (err) {
      setError(err instanceof Error ? err.message : "Erro ao exportar relatório");
    } finally {
      setExporting(false);
    }
  };

  return (
    <div className="page">
      <PageHeader
        title="Relatórios"
        subtitle="Visão consolidada da evolução do seu perfil."
      />

      {error && <Card className="hintCard">{error}</Card>}

      <Card className="chartCard" style={{ marginBottom: 18 }}>
        <div className="chartHeader">
          <CardTitle>
            <span className="inlineIcon">
              <FileText size={16} />
            </span>
            Evolução de Seguidores e Alcance
          </CardTitle>
          <button className="primaryBtn" type="button" onClick={handleExport} disabled={exporting}>
            <span className="inlineTitle"><Download size={16} /> {exporting ? "Exportando..." : "Exportar CSV"}</span>
          </button>
        </div>
        {loading ? (
          <div className="emptyBox">Carregando relatório...</div>
        ) : chartValues.length === 0 ? (
          <div className="emptyBox">Dados insuficientes para gerar relatório.</div>
        ) : (
          <div className="chartBody">
            <SimpleLineChart points={chartValues} />
            <div className="chartAxis">
              {chartDates.slice(0, 6).map((date) => (
                <span key={date}>{date}</span>
              ))}
            </div>
          </div>
        )}
      </Card>

      <div className="gridStats gridStats4">
        <Card className="statCard">
          <div className="statValue">{formatNumber(Number(data?.summary?.currentFollowers) || 0)}</div>
          <div className="statLabel">Seguidores atuais</div>
        </Card>
        <Card className="statCard">
          <div className="statValue">{Number(data?.summary?.growthPercentage || 0).toFixed(2)}%</div>
          <div className="statLabel">Crescimento</div>
        </Card>
        <Card className="statCard">
          <div className="statValue">{formatNumber(Number(data?.summary?.avgReach) || 0)}</div>
          <div className="statLabel">Alcance médio</div>
        </Card>
        <Card className="statCard">
          <div className="statValue">{Number(data?.summary?.avgEngagement || 0).toFixed(2)}%</div>
          <div className="statLabel">Engajamento médio</div>
        </Card>
      </div>

      <Card className="chartCard">
        <div className="chartHeader">
          <CardTitle>Resumo dos Últimos 7 Dias</CardTitle>
        </div>

        <div className="tableWrap">
          <table className="table">
            <thead>
              <tr>
                <th>Data</th>
                <th>Seguidores</th>
                <th>Alcance</th>
                <th>Engajamento</th>
                <th>Tendência</th>
              </tr>
            </thead>
            <tbody>
              {(data?.last7Days ?? []).length === 0 ? (
                <tr>
                  <td colSpan={5} className="tableEmpty">
                    Sem dados disponíveis.
                  </td>
                </tr>
              ) : (
                (data?.last7Days ?? []).map((row) => (
                  <tr key={`${row.date}-${row.followers_count}`}>
                    <td>{row.date}</td>
                    <td>{formatNumber(Number(row.followers_count) || 0)}</td>
                    <td>{formatNumber(Number(row.reach) || 0)}</td>
                    <td>{Number(row.engagement_rate || 0).toFixed(2)}%</td>
                    <td>
                      <span className="inlineTitle">
                        {row.trend === "up" ? <TrendingUp size={16} /> : <TrendingDown size={16} />}
                        {row.trend === "stable" ? "Estável" : `${row.trend_value > 0 ? "+" : ""}${row.trend_value}`}
                      </span>
                    </td>
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
