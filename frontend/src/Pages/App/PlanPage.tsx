import { useEffect, useState } from "react";
import { Crown, Check } from "lucide-react";
import { Card, PageHeader } from "../../Components/UI/Cards";
import { planService } from "../../services";
import "./pages.css";

interface PlanDataResponse {
  success: boolean;
  data?: {
    currentPlan?: {
      name: string;
      value: number;
      startDate: string;
      endDate: string;
      status: string;
    } | null;
    availablePlans?: Array<{
      id: number;
      nome: string;
      valor: number;
      features?: string[];
      active?: boolean;
    }>;
    history?: Array<{
      plano_nome: string;
      data_inicial: string;
      data_final: string;
      valor: number;
    }>;
  };
}

function PlanCard({
  title,
  active,
  items,
}: {
  title: string;
  active?: boolean;
  items: string[];
}) {
  return (
    <Card className={`planCard ${active ? "planCardActive" : ""}`}>
      <div className="planHead">
        <div className="planName">
          {title} {active ? <span className="pill pillUp">Ativo</span> : null}
        </div>
      </div>
      <ul className="planList">
        {items.map((it) => (
          <li key={it}>
            <Check size={16} />
            {it}
          </li>
        ))}
      </ul>
    </Card>
  );
}

const money = (value: number) => value.toLocaleString("pt-BR", { style: "currency", currency: "BRL" });

export default function PlanPage() {
  const [data, setData] = useState<PlanDataResponse["data"] | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    const load = async () => {
      try {
        const response = (await planService.getPlanData()) as PlanDataResponse;
        if (!response.success) throw new Error("Resposta inválida do servidor");
        setData(response.data ?? null);
      } catch (err) {
        setError(err instanceof Error ? err.message : "Erro ao carregar plano");
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
            <Crown size={18} /> Meu Plano
          </span>
        }
        subtitle="Veja seu plano atual e compare as opções disponíveis."
      />

      {error && <Card className="hintCard">{error}</Card>}

      <Card className="profilePerf" style={{ marginBottom: 16 }}>
        <div className="profilePerfTop">
          <div className="profilePerfIcon">
            <Crown size={18} />
          </div>
          <div className="profilePerfText">
            <div className="profilePerfTitle">Seu plano atual</div>
            <div className="profilePerfSub">
              {loading
                ? "Carregando informações do plano..."
                : data?.currentPlan
                ? `${data.currentPlan.name} • ${money(Number(data.currentPlan.value) || 0)} • até ${new Date(data.currentPlan.endDate).toLocaleDateString("pt-BR")}`
                : "Nenhum plano ativo encontrado."}
            </div>
          </div>
        </div>
      </Card>

      <div className="planGrid">
        {(data?.availablePlans ?? []).map((plan) => (
          <PlanCard
            key={`${plan.id}-${plan.nome}`}
            title={`${plan.nome} • ${money(Number(plan.valor) || 0)}`}
            active={Boolean(plan.active)}
            items={plan.features ?? []}
          />
        ))}
      </div>

      <Card className="chartCard">
        <div className="chartHeader">
          <strong>Histórico de planos</strong>
        </div>
        <div className="tableWrap">
          <table className="table">
            <thead>
              <tr>
                <th>Plano</th>
                <th>Início</th>
                <th>Fim</th>
                <th>Valor</th>
              </tr>
            </thead>
            <tbody>
              {(data?.history ?? []).length === 0 ? (
                <tr>
                  <td colSpan={4} className="tableEmpty">Nenhum histórico disponível.</td>
                </tr>
              ) : (
                (data?.history ?? []).map((item, index) => (
                  <tr key={`${item.plano_nome}-${index}`}>
                    <td>{item.plano_nome}</td>
                    <td>{new Date(item.data_inicial).toLocaleDateString("pt-BR")}</td>
                    <td>{new Date(item.data_final).toLocaleDateString("pt-BR")}</td>
                    <td>{money(Number(item.valor) || 0)}</td>
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
