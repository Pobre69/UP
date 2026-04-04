import { useEffect, useMemo, useState } from "react";
import { ChevronLeft, ChevronRight, CalendarDays } from "lucide-react";
import { Card, PageHeader } from "../../Components/UI/Cards";
import { calendarService } from "../../services";
import "./pages.css";

interface CalendarItem {
  id: number | string;
  date: string;
  content_type?: string;
  status?: string;
  caption?: string | null;
}

interface CalendarResponse {
  success: boolean;
  data?: {
    year: number;
    month: number;
    content?: Record<string, CalendarItem[]>;
  };
}

function monthNamePT(m: number) {
  const months = [
    "Janeiro",
    "Fevereiro",
    "Março",
    "Abril",
    "Maio",
    "Junho",
    "Julho",
    "Agosto",
    "Setembro",
    "Outubro",
    "Novembro",
    "Dezembro",
  ];
  return months[m];
}

const legendColors: Record<string, string> = {
  POST: "var(--accent)",
  REEL: "#3b82f6",
  STORY: "#f59e0b",
  CAROUSEL: "#22c55e",
};

export default function CalendarPage() {
  const [cursor, setCursor] = useState(() => new Date());
  const [content, setContent] = useState<Record<string, CalendarItem[]>>({});
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    const load = async () => {
      try {
        setLoading(true);
        setError(null);
        const year = cursor.getFullYear();
        const month = cursor.getMonth() + 1;
        const response = (await calendarService.getCalendarData(year, month)) as CalendarResponse;
        if (!response.success) throw new Error("Resposta inválida do servidor");
        setContent(response.data?.content ?? {});
      } catch (err) {
        setError(err instanceof Error ? err.message : "Erro ao carregar calendário");
        setContent({});
      } finally {
        setLoading(false);
      }
    };

    void load();
  }, [cursor]);

  const grid = useMemo(() => {
    const year = cursor.getFullYear();
    const month = cursor.getMonth();
    const first = new Date(year, month, 1);
    const startDow = first.getDay();
    const start = new Date(year, month, 1 - startDow);
    const days: Date[] = [];
    for (let i = 0; i < 35; i++) {
      days.push(new Date(start.getFullYear(), start.getMonth(), start.getDate() + i));
    }
    return { year, month, days };
  }, [cursor]);

  return (
    <div className="page">
      <PageHeader
        title={
          <span className="inlineTitle">
            <CalendarDays size={18} /> Calendário de Conteúdo
          </span>
        }
        subtitle="Visualize seus conteúdos agendados e publicados."
      />

      {error && <Card className="hintCard">{error}</Card>}

      <Card className="calendarCard">
        <div className="calendarTop">
          <button className="iconBtn" onClick={() => setCursor(new Date(grid.year, grid.month - 1, 1))}>
            <ChevronLeft size={18} />
          </button>
          <div className="calendarTitle">{monthNamePT(grid.month)} {grid.year}</div>
          <button className="iconBtn" onClick={() => setCursor(new Date(grid.year, grid.month + 1, 1))}>
            <ChevronRight size={18} />
          </button>
        </div>

        <div className="calendarGridHeader">
          {["Dom", "Seg", "Ter", "Qua", "Qui", "Sex", "Sáb"].map((d) => (
            <div key={d} className="calDow">{d}</div>
          ))}
        </div>

        <div className="calendarGrid">
          {grid.days.map((d, idx) => {
            const inMonth = d.getMonth() === grid.month;
            const key = d.toISOString().slice(0, 10);
            const items = content[key] ?? [];
            return (
              <div key={idx} className={`calCell ${inMonth ? "" : "calCellMuted"}`}>
                <div className="calNum">{d.getDate()}</div>
                <div style={{ display: "flex", gap: 4, marginTop: 6, flexWrap: "wrap" }}>
                  {items.slice(0, 3).map((item) => (
                    <span
                      key={item.id}
                      title={`${item.content_type ?? "Conteúdo"} - ${item.status ?? ""}`}
                      style={{
                        width: 8,
                        height: 8,
                        borderRadius: 999,
                        background: legendColors[item.content_type ?? "POST"] ?? "var(--accent)",
                        display: "inline-block",
                      }}
                    />
                  ))}
                </div>
              </div>
            );
          })}
        </div>

        <div className="calendarLegend">
          <LegendDot color="var(--accent)" label="Feed" />
          <LegendDot color="#3b82f6" label="Reels" />
          <LegendDot color="#f59e0b" label="Stories" />
          <LegendDot color="#22c55e" label="Carousel" />
        </div>

        {loading ? (
          <div className="emptyBox" style={{ marginTop: 14 }}>Carregando calendário...</div>
        ) : Object.keys(content).length === 0 ? (
          <div className="emptyBox" style={{ marginTop: 14 }}>Nenhum conteúdo encontrado neste mês.</div>
        ) : null}
      </Card>
    </div>
  );
}

function LegendDot({ color, label }: { color: string; label: string }) {
  return (
    <div className="legendChip">
      <span className="legendDot" style={{ background: color }} />
      <span>{label}</span>
    </div>
  );
}
