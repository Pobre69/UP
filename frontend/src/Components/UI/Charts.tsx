import "./ui.css";

export function SimpleLineChart({
  points,
  height = 140,
}: {
  points: number[];
  height?: number;
}) {
  const max = Math.max(...points, 1);
  const min = Math.min(...points, 0);
  const range = Math.max(max - min, 1);
  const w = 560;
  const h = height;
  const pad = 10;
  const step = (w - pad * 2) / Math.max(points.length - 1, 1);

  const d = points
    .map((p, i) => {
      const x = pad + i * step;
      const y = pad + (h - pad * 2) * (1 - (p - min) / range);
      return `${x},${y}`;
    })
    .join(" ");

  return (
    <svg viewBox={`0 0 ${w} ${h}`} className="chartSvg" aria-hidden="true">
      <polyline
        fill="none"
        stroke="var(--accent)"
        strokeWidth="3"
        strokeLinecap="round"
        strokeLinejoin="round"
        points={d}
      />
    </svg>
  );
}

export function SimpleBarChart({
  values,
  height = 170,
}: {
  values: number[];
  height?: number;
}) {
  const max = Math.max(...values, 1);
  const w = 560;
  const h = height;
  const pad = 12;
  const gap = 10;
  const barW = (w - pad * 2 - gap * (values.length - 1)) / values.length;

  return (
    <svg viewBox={`0 0 ${w} ${h}`} className="chartSvg" aria-hidden="true">
      {values.map((v, i) => {
        const barH = ((h - pad * 2) * v) / max;
        const x = pad + i * (barW + gap);
        const y = h - pad - barH;
        return (
          <rect
            key={i}
            x={x}
            y={y}
            width={barW}
            height={barH}
            rx={10}
            fill="var(--accent)"
            opacity={0.75}
          />
        );
      })}
    </svg>
  );
}

export function DonutChart({
  segments,
}: {
  segments: { label: string; value: number; color: string }[];
}) {
  const total = segments.reduce((a, s) => a + s.value, 0) || 1;
  const stops: string[] = [];
  let acc = 0;
  for (const s of segments) {
    const start = (acc / total) * 100;
    acc += s.value;
    const end = (acc / total) * 100;
    stops.push(`${s.color} ${start.toFixed(2)}% ${end.toFixed(2)}%`);
  }
  const bg = `conic-gradient(${stops.join(", ")})`;

  return (
    <div className="donutWrap">
      <div className="donut" style={{ background: bg }} />
      <div className="donutLegend">
        {segments.map((s) => (
          <div className="legendRow" key={s.label}>
            <span className="legendDot" style={{ background: s.color }} />
            <span className="legendLabel">{s.label}</span>
            <span className="legendValue">
              {Math.round((s.value / total) * 100)}%
            </span>
          </div>
        ))}
      </div>
    </div>
  );
}
