import { useEffect, useState } from "react";
import { DollarSign, MousePointerClick, Users, Megaphone, Plus } from "lucide-react";
import { Card, CardTitle, PageHeader, StatCard } from "../../Components/UI/Cards";
import { adsService } from "../../services";
import { formatNumber } from "../../utils/format";
import "./pages.css";

interface CampaignItem {
  id: number | string;
  campaign_name: string;
  status: string;
  budget: number;
  spent: number;
  clicks: number;
  cpc: number;
  reach: number;
}

interface AdsResponse {
  success: boolean;
  data?: {
    summary?: {
      totalBudget?: number;
      totalSpent?: number;
      totalClicks?: number;
      totalReach?: number;
    };
    campaigns?: CampaignItem[];
  };
}

const money = (value: number) => value.toLocaleString("pt-BR", { style: "currency", currency: "BRL" });

export default function AdsPage() {
  const [data, setData] = useState<AdsResponse["data"] | null>(null);
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [campaignName, setCampaignName] = useState("");
  const [budget, setBudget] = useState("");
  const [startDate, setStartDate] = useState("");

  const load = async () => {
    try {
      setLoading(true);
      const response = (await adsService.getAdsData()) as AdsResponse;
      if (!response.success) throw new Error("Resposta inválida do servidor");
      setData(response.data ?? null);
    } catch (err) {
      setError(err instanceof Error ? err.message : "Erro ao carregar anúncios");
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    void load();
  }, []);

  const handleCreateCampaign = async (event: React.FormEvent) => {
    event.preventDefault();
    if (!campaignName.trim() || !budget || !startDate) {
      setError("Preencha nome, orçamento e data inicial da campanha.");
      return;
    }

    try {
      setSaving(true);
      setError(null);
      await adsService.createCampaign({
        campaign_name: campaignName.trim(),
        budget: Number(budget),
        start_date: startDate,
      });
      setCampaignName("");
      setBudget("");
      setStartDate("");
      await load();
    } catch (err) {
      setError(err instanceof Error ? err.message : "Erro ao criar campanha");
    } finally {
      setSaving(false);
    }
  };

  return (
    <div className="page">
      <PageHeader
        title={<span className="inlineTitle"><Megaphone size={18} /> Anúncios</span>}
        subtitle="Gerencie e acompanhe seus investimentos em tráfego pago."
      />

      {error && <Card className="hintCard">{error}</Card>}

      <div className="gridStats gridStats4">
        <StatCard icon={<DollarSign size={18} />} label="Orçamento Total" value={money(Number(data?.summary?.totalBudget) || 0)} />
        <StatCard icon={<DollarSign size={18} />} label="Total Investido" value={money(Number(data?.summary?.totalSpent) || 0)} />
        <StatCard icon={<MousePointerClick size={18} />} label="Cliques Totais" value={formatNumber(Number(data?.summary?.totalClicks) || 0)} />
        <StatCard icon={<Users size={18} />} label="Alcance Total" value={formatNumber(Number(data?.summary?.totalReach) || 0)} />
      </div>

      <div className="gridTwo" style={{ alignItems: "start" }}>
        <Card className="chartCard">
          <div className="chartHeader">
            <CardTitle>Nova Campanha</CardTitle>
          </div>
          <form className="form" onSubmit={handleCreateCampaign}>
            <input className="input" placeholder="Nome da campanha" value={campaignName} onChange={(e) => setCampaignName(e.target.value)} />
            <div className="row">
              <input className="input" type="number" min="0" step="0.01" placeholder="Orçamento" value={budget} onChange={(e) => setBudget(e.target.value)} />
              <input className="input" type="date" value={startDate} onChange={(e) => setStartDate(e.target.value)} />
            </div>
            <button className="primaryBtn" type="submit" disabled={saving}>
              <span className="inlineTitle"><Plus size={16} /> {saving ? "Criando..." : "Criar campanha"}</span>
            </button>
          </form>
        </Card>

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
                  <tr><td colSpan={7} className="tableEmpty">Carregando campanhas...</td></tr>
                ) : (data?.campaigns ?? []).length === 0 ? (
                  <tr><td colSpan={7} className="tableEmpty">Nenhum anúncio registrado.</td></tr>
                ) : (
                  (data?.campaigns ?? []).map((campaign: CampaignItem) => (
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
    </div>
  );
}
