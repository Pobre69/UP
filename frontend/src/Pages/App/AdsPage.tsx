import { useEffect, useState } from "react";
import { DollarSign, MousePointerClick, Users, Megaphone } from "lucide-react";
import { Card, CardTitle, PageHeader, StatCard } from "../../Components/UI/Cards";
import { adsService } from "../../services";
import { formatNumber } from "../../utils/format";
import "./pages.css";

interface AdsResponse {
  success: boolean;
  data?: {
    summary?: {
      totalBudget?: number;
      totalSpent?: number;
      totalClicks?: number;
      totalReach?: number;
    };
    campaigns?: Array<{
      id: number | string;
      campaign_name: string;
      status: string;
      budget: number;
      spent: number;
      clicks: number;
      cpc: number;
      reach: number;
    }>;
  };
}

const money = (value: number) => value.toLocaleString("pt-BR", { style: "currency", currency: "BRL" });

export default function AdsPage() {
  const [data, setData] = useState<AdsResponse["data"] | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    const load = async () => {
      try {
        const response = (await adsService.getAdsData()) as AdsResponse;
        if (!response.success) throw new Error("Resposta inválida do servidor");
        setData(response.data ?? null);
      } catch (err) {
        setError(err instanceof Error ? err.message : "Erro ao carregar anúncios");
      } finally {
        setLoading(false);
      }
    };

    void load();
  }, []);

  return (
    <div className="page">
      <PageHeader
        title={
          <span className="inlineTitle">
            <Megaphone size={18} /> Anúncios
          </span>
        }
        subtitle="Gerencie e acompanhe seus investimentos em tráfego pago."
      />

      {error && <Card className="hintCard">{error}</Card>}

      <div className="gridStats gridStats4">
        <StatCard icon={<DollarSign size={18} />} label="Orçamento Total" value={money(Number(data?.summary?.totalBudget) || 0)} />
        <StatCard icon={<DollarSign size={18} />} label="Total Investido" value={money(Number(data?.summary?.totalSpent) || 0)} />
        <StatCard icon={<MousePointerClick size={18} />} label="Cliques Totais" value={formatNumber(Number(data?.summary?.totalClicks) || 0)} />
        <StatCard icon={<Users size={18} />} label="Alcance Total" value={formatNumber(Number(data?.summary?.totalReach) || 0)} />
      </div>

      <Card className="chartCard">
        <div className="chartHeader">
          <CardTitle>Todos os Anúncios</CardTitle>
        </div>
        <div className="tableWrap">
          <table className="table">
            <thead>
              <tr>
                <th>Nome</th>
                <th>Status</th>
                <th>Orçamento</th>
                <th>Investido</th>
                <th>Cliques</th>
                <th>CPC</th>
                <th>Alcance</th>
              </tr>
            </thead>
            <tbody>
              {loading ? (
                <tr>
                  <td colSpan={7} className="tableEmpty">Carregando campanhas...</td>
                </tr>
              ) : (data?.campaigns ?? []).length === 0 ? (
                <tr>
                  <td colSpan={7} className="tableEmpty">
                    Nenhum anúncio registrado.
                  </td>
                </tr>
              ) : (
                (data?.campaigns ?? []).map((campaign) => (
                  <tr key={campaign.id}>
                    <td>{campaign.campaign_name}</td>
                    <td>{campaign.status}</td>
                    <td>{money(Number(campaign.budget) || 0)}</td>
                    <td>{money(Number(campaign.spent) || 0)}</td>
                    <td>{formatNumber(Number(campaign.clicks) || 0)}</td>
                    <td>{money(Number(campaign.cpc) || 0)}</td>
                    <td>{formatNumber(Number(campaign.reach) || 0)}</td>
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
