import {
  Heart,
  MessageCircle,
  Share2,
  Eye,
  Activity,
  Images,
} from "lucide-react";
import { Card, CardTitle, PageHeader, StatCard } from "../../Components/UI/Cards";
import { DonutChart } from "../../Components/UI/Charts";
import { engagement } from "../../mock/data";
import "./pages.css";

export default function EngagementPage() {
  return (
    <div className="page">
      <PageHeader
        title="Engajamento"
        subtitle="Acompanhe as métricas de engajamento do seu perfil."
      />

      <div className="gridStats gridStats6">
        <StatCard icon={<Heart size={18} />} label="Média de Curtidas" value="0" />
        <StatCard
          icon={<MessageCircle size={18} />}
          label="Média de Comentários"
          value="0"
        />
        <StatCard
          icon={<Share2 size={18} />}
          label="Média de Compartilhamentos"
          value="0"
        />
        <StatCard icon={<Eye size={18} />} label="Alcance por Post" value="0" />
        <StatCard icon={<Activity size={18} />} label="Taxa de Engajamento" value="0%" />
        <StatCard icon={<Images size={18} />} label="Melhor Story Views" value="0" />
      </div>

      <div className="gridCharts">
        <Card className="chartCard" style={{ minHeight: 280 }}>
          <div className="chartHeader">
            <CardTitle>Performance por Post</CardTitle>
          </div>
          <div className="emptyBox">Nenhum post publicado ainda.</div>
        </Card>

        <Card className="chartCard" style={{ minHeight: 280 }}>
          <div className="chartHeader">
            <CardTitle>Distribuição de Engajamento</CardTitle>
          </div>
          <div className="chartBody" style={{ paddingTop: 14 }}>
            <DonutChart segments={engagement.distrib} />
          </div>
        </Card>
      </div>
    </div>
  );
}
