import { useEffect, useMemo, useState } from "react";
import { Link, useLocation, useNavigate } from "react-router-dom";
import {
  AlertCircle,
  ArrowLeft,
  CheckCircle2,
  CreditCard,
  ExternalLink,
  Loader2,
  RefreshCcw,
  Clock,
} from "lucide-react";
import { API_BASE_URL, fetchWithRetry } from "../config/api";
import "../Design/LoginPage/LoginForm.css";

type PaymentStatus = "checking" | "approved" | "pending" | "error";

interface PaymentState {
  paymentUrl?: string | null;
  planoSelecionado?: string | null;
}

const paymentLinks: Record<string, string> = {
  basico: "https://pay.cakto.com.br/nvtso3j_754042",
  premium: "https://pay.cakto.com.br/3mz49rp_754011",
  completo: "https://pay.cakto.com.br/4sgtxw3_754018",
};

const planLabels: Record<string, string> = {
  basico: "Básico",
  premium: "Premium",
  completo: "Completo",
};

const steps = [
  { label: "Abra o link de pagamento do seu plano" },
  { label: "Conclua o pagamento na plataforma" },
  { label: "Esta página confirma a ativação automaticamente" },
];

export default function PaymentVerificationPage() {
  const navigate = useNavigate();
  const location = useLocation();
  const routeState = (location.state as PaymentState | null) ?? null;
  const [status, setStatus] = useState<PaymentStatus>("checking");
  const [message, setMessage] = useState("Verificando pagamento...");
  const [planoSelecionado, setPlanoSelecionado] = useState<string | null>(
    routeState?.planoSelecionado ?? sessionStorage.getItem("pendingPlan")
  );

  const paymentUrl = useMemo(() => {
    return (
      routeState?.paymentUrl ??
      sessionStorage.getItem("pendingPaymentUrl") ??
      (planoSelecionado ? (paymentLinks[planoSelecionado] ?? null) : null)
    );
  }, [routeState?.paymentUrl, planoSelecionado]);

  useEffect(() => {
    let isMounted = true;

    const checkPayment = async () => {
      try {
        const response = await fetchWithRetry(`${API_BASE_URL}/payment/verificar`, {
          method: "GET",
          headers: { "Content-Type": "application/json" },
        });

        const data = await response.json();
        if (!isMounted) return;

        if (response.ok && data.success && data.ativo) {
          setStatus("approved");
          setMessage("Pagamento confirmado! Redirecionando para sua conta...");
          sessionStorage.removeItem("pendingPaymentUrl");
          sessionStorage.removeItem("pendingPlan");
          window.setTimeout(() => navigate("/app/dashboard", { replace: true }), 1400);
          return;
        }

        setStatus("pending");
        setPlanoSelecionado(data.planoSelecionado ?? planoSelecionado ?? null);
        setMessage(data.mensagem || "Aguardando confirmação do pagamento...");
      } catch {
        if (!isMounted) return;
        setStatus("error");
        setMessage("Não foi possível verificar o pagamento agora. Tente novamente.");
      }
    };

    void checkPayment();
    const interval = window.setInterval(checkPayment, 5000);
    return () => {
      isMounted = false;
      window.clearInterval(interval);
    };
  }, [navigate, planoSelecionado]);

  const statusConfig = {
    checking: {
      icon: <Loader2 size={20} className="spin" />,
      bg: "rgba(124,58,237,0.10)",
      border: "rgba(124,58,237,0.22)",
      color: "#a78bfa",
    },
    approved: {
      icon: <CheckCircle2 size={20} />,
      bg: "rgba(16,185,129,0.10)",
      border: "rgba(16,185,129,0.24)",
      color: "#34d399",
    },
    pending: {
      icon: <Clock size={20} />,
      bg: "rgba(245,158,11,0.10)",
      border: "rgba(245,158,11,0.24)",
      color: "#fbbf24",
    },
    error: {
      icon: <AlertCircle size={20} />,
      bg: "rgba(217,43,43,0.08)",
      border: "rgba(217,43,43,0.22)",
      color: "#f87171",
    },
  }[status];

  return (
    <div id="login-box">
      <Link
        to="/Login"
        className="back-link reveal"
        style={{ "--reveal-delay": "20ms" } as React.CSSProperties}
      >
        <ArrowLeft size={18} />
        <span>Voltar para o login</span>
      </Link>

      <div
        id="login-icon"
        className="reveal"
        style={{ "--reveal-delay": "40ms" } as React.CSSProperties}
      >
        <CreditCard size={34} color="white" strokeWidth={2} />
      </div>

      <div
        id="login-title"
        className="reveal"
        style={{ "--reveal-delay": "90ms" } as React.CSSProperties}
      >
        Status do <span className="purplegradient">pagamento</span>
        <span id="login-phrase">
          A ativação da sua conta acontece automaticamente após a confirmação.
        </span>
      </div>

      <div id="login-form-wrap">
        <div
          id="loginForm"
          className="reveal"
          style={{ "--reveal-delay": "140ms" } as React.CSSProperties}
        >
          {/* Status card */}
          <div className="card" style={{ marginBottom: "12px" }}>
            <div className="section-title">
              <span className="section-icon">
                <CreditCard size={16} />
              </span>
              Verificação de pagamento
            </div>
            {planoSelecionado && (
              <div className="section-subtitle">
                Plano selecionado:{" "}
                <strong style={{ color: "#6d28d9" }}>
                  {planLabels[planoSelecionado] ?? planoSelecionado}
                </strong>
              </div>
            )}

            <div
              className="form-error"
              style={{
                background: statusConfig.bg,
                borderColor: statusConfig.border,
                color: statusConfig.color,
                marginTop: "16px",
              }}
            >
              {statusConfig.icon}
              {message}
            </div>

            {paymentUrl && status !== "approved" && (
              <a
                href={paymentUrl}
                target="_blank"
                rel="noopener noreferrer"
                className="button"
                style={{ textDecoration: "none", display: "flex", alignItems: "center", justifyContent: "center", gap: "8px", marginTop: "16px" }}
              >
                <ExternalLink size={16} />
                Realizar pagamento
              </a>
            )}

            {status !== "approved" && (
              <button
                type="button"
                className="button"
                style={{ background: "transparent", border: "1px solid #ececf5", color: "#6b6b6b", marginTop: "10px" }}
                onClick={() => window.location.reload()}
              >
                <span className="btn-row">
                  <RefreshCcw size={16} />
                  Verificar novamente
                </span>
              </button>
            )}
          </div>

          {/* Steps card */}
          <div className="card">
            <div className="section-title">
              <span className="section-icon">
                <Clock size={16} />
              </span>
              Como funciona
            </div>
            <div style={{ marginTop: "14px", display: "flex", flexDirection: "column", gap: "10px" }}>
              {steps.map((step, i) => (
                <div
                  key={i}
                  style={{ display: "flex", alignItems: "flex-start", gap: "12px" }}
                >
                  <span
                    style={{
                      minWidth: "24px",
                      height: "24px",
                      borderRadius: "50%",
                      background: i === 2 && status === "approved"
                        ? "rgba(16,185,129,0.15)"
                        : "rgba(109,40,217,0.10)",
                      color: i === 2 && status === "approved" ? "#34d399" : "#6d28d9",
                      display: "flex",
                      alignItems: "center",
                      justifyContent: "center",
                      fontSize: "12px",
                      fontWeight: 800,
                    }}
                  >
                    {i === 2 && status === "approved" ? <CheckCircle2 size={14} /> : i + 1}
                  </span>
                  <span style={{ fontSize: "13px", color: "#3a3a48", paddingTop: "3px" }}>
                    {step.label}
                  </span>
                </div>
              ))}
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
