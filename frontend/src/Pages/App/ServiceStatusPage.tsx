import { CheckCircle2, Clock, FileEdit, Send } from "lucide-react";
import { Card, PageHeader, StatCard } from "../../Components/UI/Cards";
import "./pages.css";

export default function ServiceStatusPage() {
  return (
    <div className="page">
      <PageHeader
        title="Status do Serviço"
        subtitle="Acompanhe o andamento de todas as suas peças e conteúdos."
      />

      <div className="gridStats gridStats4">
        <StatCard icon={<FileEdit size={18} />} label="Em Revisão" value="0" />
        <StatCard icon={<Clock size={18} />} label="Planejado" value="0" />
        <StatCard icon={<Send size={18} />} label="Agendado" value="0" />
        <StatCard icon={<CheckCircle2 size={18} />} label="Publicado" value="0" />
      </div>

      <Card className="chartCard" style={{ minHeight: 180 }}>
        <div className="emptyBox">Nenhum conteúdo registrado ainda.</div>
      </Card>
    </div>
  );
}
