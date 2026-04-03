import { useMemo, useState } from "react";
import type { FormEvent } from "react";
import { Link, useLocation, useNavigate } from "react-router-dom";
import { ArrowLeft, AlertCircle, Eye, EyeOff, Loader2, Lock, Mail, LogIn } from "lucide-react";
import { API_BASE_URL, fetchWithRetry } from "../../config/api";
import "../../Design/LoginPage/LoginForm.css";

interface LoginFormProps {
  onLoginSuccess?: () => void;
}

interface LoginResponse {
  success?: boolean;
  mensagem?: string;
  planoSelecionado?: string | null;
  paymentUrl?: string | null;
  inativo?: boolean;
  usuario?: {
    email?: string;
    nome?: string | null;
    empresa?: string | null;
    id?: number | null;
  };
}

export function LoginForm({ onLoginSuccess }: LoginFormProps) {
  const navigate = useNavigate();
  const location = useLocation();
  const redirectTo = useMemo(() => {
    const state = location.state as { from?: { pathname?: string } } | null;
    return state?.from?.pathname || "/app/dashboard";
  }, [location.state]);

  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");
  const [error, setError] = useState<string | null>(null);
  const [isLoading, setIsLoading] = useState(false);
  const [showPassword, setShowPassword] = useState(false);

  const validateEmail = (value: string): boolean => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value);

  const handleSubmit = async (event: FormEvent<HTMLFormElement>) => {
    event.preventDefault();
    setError(null);

    const normalizedEmail = email.trim().toLowerCase();

    if (!normalizedEmail) { setError("Por favor, insira seu e-mail."); return; }
    if (!validateEmail(normalizedEmail)) { setError("Por favor, insira um e-mail válido."); return; }
    if (!password) { setError("Por favor, insira sua senha."); return; }

    setIsLoading(true);

    try {
      const response = await fetchWithRetry(`${API_BASE_URL}/auth/login`, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ email: normalizedEmail, senha: password }),
      });

      const rawText = await response.text();
      let data: LoginResponse = {};
      if (rawText) {
        try { data = JSON.parse(rawText) as LoginResponse; }
        catch { data = { mensagem: "Resposta inválida do servidor." }; }
      }

      if (response.status === 403 && data.inativo) {
        if (data.paymentUrl) sessionStorage.setItem("pendingPaymentUrl", data.paymentUrl);
        if (data.planoSelecionado) sessionStorage.setItem("pendingPlan", data.planoSelecionado);
        navigate("/payment-verification", {
          replace: true,
          state: { paymentUrl: data.paymentUrl, planoSelecionado: data.planoSelecionado },
        });
        return;
      }

      if (!response.ok || !data.success) {
        if (response.status === 429) setError("Muitas tentativas de login. Tente novamente em alguns minutos.");
        else if (response.status === 401) setError("E-mail ou senha incorretos.");
        else setError(data.mensagem || "Não foi possível entrar no momento.");
        return;
      }

      if (data.usuario) localStorage.setItem("user", JSON.stringify(data.usuario));
      sessionStorage.setItem("just_logged_in", "1");
      onLoginSuccess?.();
      navigate(redirectTo, { replace: true });
    } catch (err) {
      if (err instanceof Error) {
        if (err.name === "AbortError" || err.message.includes("timeout")) setError("Tempo limite de conexão excedido. Tente novamente.");
        else if (err.message.includes("Failed to fetch")) setError("Erro de conexão. Verifique se o backend está rodando.");
        else setError(err.message);
      } else {
        setError("Erro inesperado ao fazer login.");
      }
    } finally {
      setIsLoading(false);
    }
  };

  return (
    <div id="login-box">
      <Link to="/" className="back-link reveal" style={{ "--reveal-delay": "20ms" } as React.CSSProperties}>
        <ArrowLeft size={18} />
        <span>Voltar ao início</span>
      </Link>

      <div id="login-icon" className="reveal" style={{ "--reveal-delay": "40ms" } as React.CSSProperties}>
        <LogIn size={34} color="white" strokeWidth={2} />
      </div>

      <div id="login-title" className="reveal" style={{ "--reveal-delay": "90ms" } as React.CSSProperties}>
        Entrar na <span className="purplegradient">UP</span>
        <span id="login-phrase">Acesse sua conta para continuar.</span>
      </div>

      <div id="login-form-wrap">
        <form id="loginForm" onSubmit={handleSubmit} noValidate className="reveal" style={{ "--reveal-delay": "140ms" } as React.CSSProperties}>
          <div className="card">
            <div className="section-title">
              <span className="section-icon"><Lock size={16} /></span>
              Acesso à conta
            </div>
            <div className="section-subtitle">
              Insira suas credenciais para acessar o painel.
            </div>

            {error && (
              <div className="form-error" role="alert" aria-live="polite">
                <AlertCircle size={15} /> {error}
              </div>
            )}

            <div className="field">
              <label className="label" htmlFor="email">
                <span className="label-row">
                  <span className="label-icon"><Mail size={16} /></span>
                  <span className="label-text">E-mail <span className="req">*</span></span>
                </span>
              </label>
              <input
                id="email"
                className={`input${error && !email ? " input-error" : ""}`}
                type="email"
                placeholder="seu@email.com"
                value={email}
                onChange={(e) => { setEmail(e.target.value); if (error) setError(null); }}
                autoComplete="email"
                disabled={isLoading}
                required
              />
            </div>

            <div className="field">
              <label className="label" htmlFor="password">
                <span className="label-row">
                  <span className="label-icon"><Lock size={16} /></span>
                  <span className="label-text">Senha <span className="req">*</span></span>
                </span>
              </label>
              <div className="password-wrap">
                <input
                  id="password"
                  className={`input${error && !password ? " input-error" : ""}`}
                  type={showPassword ? "text" : "password"}
                  placeholder="Sua senha"
                  value={password}
                  onChange={(e) => { setPassword(e.target.value); if (error) setError(null); }}
                  autoComplete="current-password"
                  disabled={isLoading}
                  required
                />
                <button
                  type="button"
                  className="eye-btn"
                  onClick={() => setShowPassword((v) => !v)}
                  aria-label={showPassword ? "Ocultar senha" : "Mostrar senha"}
                  disabled={isLoading}
                >
                  {showPassword ? <EyeOff size={17} /> : <Eye size={17} />}
                </button>
              </div>
            </div>

            <button className="button" id="login-submit" type="submit" disabled={isLoading}>
              {isLoading ? (
                <span className="btn-row">
                  <Loader2 className="spin" size={18} /> Entrando...
                </span>
              ) : "Entrar"}
            </button>

            <div className="login-footer">
              <span>Ainda não tem conta?</span>
              <Link to="/SignIn" className="footer-link">Cadastre-se</Link>
            </div>
          </div>
        </form>
      </div>
    </div>
  );
}
