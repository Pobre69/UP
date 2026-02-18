import { DollarSign, MousePointerClick, Users, Megaphone } from "lucide-react";
import { Card, CardTitle, PageHeader, StatCard } from "../../Components/UI/Cards";
import "./pages.css";

export default function AdsPage() {
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

      <div className="gridStats gridStats4">
        <StatCard icon={<DollarSign size={18} />} label="Orçamento Total" value="R$ 0" />
        <StatCard icon={<DollarSign size={18} />} label="Total Investido" value="R$ 0" />
        <StatCard icon={<MousePointerClick size={18} />} label="Cliques Totais" value="0" />
        <StatCard icon={<Users size={18} />} label="Alcance Total" value="0" />
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
              <tr>
                <td colSpan={7} className="tableEmpty">
                  Nenhum anúncio registrado.
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </Card>
    </div>
  );
}
