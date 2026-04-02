import { useEffect, useMemo, useState } from "react";
import { Link, useLocation, useNavigate } from "react-router-dom";
import { AlertCircle, ArrowLeft, CheckCircle2, CreditCard, Loader2, RefreshCcw } from "lucide-react";
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

export default function PaymentVerificationPage() {
  const navigate = useNavigate();
  const location = useLocation();
  const routeState = (location.state as PaymentState | null) ?? null;
  const [status, setStatus] = useState<PaymentStatus>("checking");
  const [message, setMessage] = useState("Verificando pagamento...");
  const [planoSelecionado, setPlanoSelecionado] = useState<string | null>(routeState?.planoSelecionado ?? sessionStorage.getItem("pendingPlan"));

  const paymentUrl = useMemo(() => {
    return routeState?.paymentUrl ?? sessionStorage.getItem("pendingPaymentUrl") ?? (planoSelecionado ? paymentLinks[planoSelecionado] ?? null : null);
  }, [routeState?.paymentUrl, planoSelecionado]);

  useEffect(() => {
    let isMounted = true;

    const checkPayment = async () => {
      try {
        const response = await fetchWithRetry(`${API_BASE_URL}/payment/verificar`, {
          method: "GET",
          headers: {
            "Content-Type": "application/json",
          },
        });

        const data = await response.json();

        if (!isMounted) {
          return;
        }

        if (response.ok && data.success && data.ativo) {
          setStatus("approved");
          setMessage("Pagamento confirmado! Redirecionando para sua conta...");
          sessionStorage.removeItem("pendingPaymentUrl");
          sessionStorage.removeItem("pendingPlan");
          window.setTimeout(() => {
            navigate("/app/dashboard", { replace: true });
          }, 1400);
          return;
        }

        setStatus("pending");
        setPlanoSelecionado(data.planoSelecionado ?? planoSelecionado ?? null);
        setMessage(data.mensagem || "Aguardando confirmação do pagamento...");
      } catch {
        if (!isMounted) {
          return;
        }
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

  return (
    <div className="login-container">
      <Link to="/Login" className="back-link">
        <ArrowLeft size={16} />
        <span>Voltar para o login</span>
      </Link>

      <section className="login-card">
        <header className="login-header">
          <h1 className="login-title">
            Status do <span className="purplegradient">pagamento</span>
          </h1>
          <p className="login-subtitle">A ativação da sua conta acontece automaticamente após a confirmação.</p>
        </header>

        <div className="login-form" style={{ gap: "18px" }}>
          {status === "checking" && (
            <div className="form-error" style={{ background: "rgba(124, 58, 237, 0.12)", borderColor: "rgba(124, 58, 237, 0.2)", color: "#c4b5fd" }}>
              <Loader2 size={18} className="spin" /> {message}
            </div>
          )}

          {status === "approved" && (
            <div className="form-error" style={{ background: "rgba(16, 185, 129, 0.14)", borderColor: "rgba(16, 185, 129, 0.24)", color: "#86efac" }}>
              <CheckCircle2 size={18} /> {message}
            </div>
          )}

          {status === "pending" && (
            <div className="form-error" style={{ background: "rgba(245, 158, 11, 0.14)", borderColor: "rgba(245, 158, 11, 0.24)", color: "#fde68a" }}>
              <CreditCard size={18} /> {message}
            </div>
          )}

          {status === "error" && (
            <div className="form-error">
              <AlertCircle size={18} /> {message}
            </div>
          )}

          <div className="field">
            <label className="label">Próximos passos</label>
            <div className="login-subtitle" style={{ textAlign: "left" }}>
              1. Abra o link de pagamento do seu plano.<br />
              2. Conclua o pagamento.<br />
              3. Esta página verifica a ativação automaticamente.
            </div>
          </div>

          {paymentUrl && (
            <a href={paymentUrl} target="_blank" rel="noopener noreferrer" className="submit-button" style={{ textDecoration: "none" }}>
              Realizar pagamento
            </a>
          )}

          <button type="button" className="submit-button secondary" onClick={() => window.location.reload()}>
            <RefreshCcw size={18} /> Verificar novamente
          </button>
        </div>
      </section>
    </div>
  );
}
