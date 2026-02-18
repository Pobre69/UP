import { Crown, Check } from "lucide-react";
import { Card, PageHeader } from "../../Components/UI/Cards";
import "./pages.css";

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

export default function PlanPage() {
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

      <Card className="profilePerf" style={{ marginBottom: 16 }}>
        <div className="profilePerfTop">
          <div className="profilePerfIcon">
            <Crown size={18} />
          </div>
          <div className="profilePerfText">
            <div className="profilePerfTitle">Seu plano atual</div>
            <div className="profilePerfSub">
              <b>Básico</b>
            </div>
          </div>
          <div className="pill pillUp">Ativo</div>
        </div>
      </Card>

      <div className="planGrid">
        <PlanCard
          title="Básico"
          active
          items={["4 posts/mês", "Stories básicos", "Relatório mensal", "1 rede social"]}
        />
        <PlanCard
          title="Profissional"
          items={[
            "8 posts/mês",
            "Stories + Reels",
            "Relatório semanal",
            "2 redes sociais",
            "Gestão de tráfego básica",
          ]}
        />
        <PlanCard
          title="Premium"
          items={[
            "12 posts/mês",
            "Stories + Reels + Carrosséis",
            "Relatório diário",
            "3 redes sociais",
            "Gestão de tráfego avançada",
            "Planejamento estratégico",
          ]}
        />
      </div>

      <Card className="hintCard">
        Deseja fazer upgrade ou alterar seu plano? Entre em contato com seu consultor
        ou envie uma solicitação na aba <b>Solicitações</b>.
      </Card>
    </div>
  );
}
