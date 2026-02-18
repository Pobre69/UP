import { useMemo, useState } from "react";
import { ChevronLeft, ChevronRight, CalendarDays } from "lucide-react";
import { Card, PageHeader } from "../../Components/UI/Cards";
import "./pages.css";

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

export default function CalendarPage() {
  const [cursor, setCursor] = useState(() => new Date(2026, 1, 1));

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
            const isSelected = inMonth && d.getDate() === 18;
            return (
              <div
                key={idx}
                className={`calCell ${inMonth ? "" : "calCellMuted"} ${
                  isSelected ? "calCellSelected" : ""
                }`}
              >
                <div className="calNum">{d.getDate()}</div>
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
