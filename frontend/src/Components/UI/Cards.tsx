import type React from "react";

import "./ui.css";

export function PageHeader({
  title,
  subtitle,
}: {
  title: React.ReactNode;
  subtitle?: string;
}) {
  return (
    <div className="pageHeader reveal">
      <h1 className="pageTitle">{title}</h1>
      {subtitle ? <p className="pageSubtitle">{subtitle}</p> : null}
    </div>
  );
}

export function Card({
  children,
  className = "",
  ...rest
}: React.PropsWithChildren<{ className?: string } & React.HTMLAttributes<HTMLElement>>) {
  return (
    <section className={`card reveal ${className}`} {...rest}>
      {children}
    </section>
  );
}

export function CardTitle({ children }: { children: React.ReactNode }) {
  return <div className="cardTitle">{children}</div>;
}

export function StatCard({
  icon,
  label,
  value,
  delta,
}: {
  icon: React.ReactNode;
  label: string;
  value: string;
  delta?: { value: string; tone: "up" | "down" | "flat" };
}) {
  return (
    <Card className="statCard">
      <div className="statTop">
        <div className="statIcon">{icon}</div>
        {delta ? (
          <span
            className={`pill ${
              delta.tone === "up"
                ? "pillUp"
                : delta.tone === "down"
                ? "pillDown"
                : "pillFlat"
            }`}
          >
            {delta.value}
          </span>
        ) : null}
      </div>
      <div className="statValue">{value}</div>
      <div className="statLabel">{label}</div>
    </Card>
  );
}
