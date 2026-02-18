import { FileText } from "lucide-react";
import { Card, CardTitle, PageHeader } from "../../Components/UI/Cards";
import "./pages.css";

export default function ReportsPage() {
  return (
    <div className="page">
      <PageHeader
        title="Relatórios"
        subtitle="Visão consolidada da evolução do seu perfil."
      />

      <Card className="chartCard" style={{ marginBottom: 18 }}>
        <div className="chartHeader">
          <CardTitle>
            <span className="inlineIcon">
              <FileText size={16} />
            </span>
            Evolução de Seguidores e Alcance
          </CardTitle>
        </div>
        <div className="emptyBox">Dados insuficientes para gerar relatório.</div>
      </Card>

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
              <tr>
                <td colSpan={5} className="tableEmpty">
                  Sem dados disponíveis.
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </Card>
    </div>
  );
}
