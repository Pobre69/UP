import { useMemo, useState } from "react";
import type { FormEvent } from "react";
import { Link, useLocation, useNavigate } from "react-router-dom";
import {
  AlertCircle,
  ArrowLeft,
  Eye,
  EyeOff,
  Loader2,
  Lock,
  LogIn,
  Mail,
  ShieldCheck,
} from "lucide-react";
import { API_BASE_URL, fetchWithRetry } from "../../config/api";
import "../../Design/LoginPage/LoginForm.css";

interface LoginFormProps {
  onLoginSuccess?: () => void;
}

interface LoginResponse {
  success?: boolean;
  mensagem?: string;
  planoSelecionado?: string | null;
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

  const clearStoredUser = () => {
    localStorage.removeItem("user");
    localStorage.removeItem("userEmail");
  };

  const handleSubmit = async (event: FormEvent<HTMLFormElement>) => {
    event.preventDefault();
    setError(null);

    const normalizedEmail = email.trim().toLowerCase();

    if (!normalizedEmail) {
      setError("Por favor, insira seu e-mail.");
      return;
    }

    if (!validateEmail(normalizedEmail)) {
      setError("Por favor, insira um e-mail válido.");
      return;
    }

    if (!password.trim()) {
      setError("Por favor, insira sua senha.");
      return;
    }

    setIsLoading(true);
    clearStoredUser();

    try {
      const response = await fetchWithRetry(`${API_BASE_URL}/auth/login`, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({
          email: normalizedEmail,
          senha: password,
        }),
      });

      const rawText = await response.text();
      let data: LoginResponse = {};

      if (rawText) {
        try {
          data = JSON.parse(rawText) as LoginResponse;
        } catch {
          data = { mensagem: "Resposta inválida do servidor." };
        }
      }

      if (!response.ok || !data.success) {
        clearStoredUser();

        if (response.status === 429) {
          setError("Muitas tentativas de login. Tente novamente em alguns minutos.");
        } else if (response.status === 403) {
          setError(data.mensagem || "Sua conta ainda não foi ativada.");
        } else if (response.status === 401) {
          setError("E-mail ou senha incorretos.");
        } else {
          setError(data.mensagem || "Não foi possível entrar no momento.");
        }
        return;
      }

      if (data.usuario) {
        localStorage.setItem("user", JSON.stringify(data.usuario));
        if (data.usuario.email) {
          localStorage.setItem("userEmail", data.usuario.email);
        }
      }

      onLoginSuccess?.();
      navigate(redirectTo, { replace: true });
    } catch (err) {
      clearStoredUser();

      if (err instanceof Error) {
        if (err.name === "AbortError" || err.message.includes("timeout")) {
          setError("Tempo limite de conexão excedido. Tente novamente.");
        } else if (err.message.includes("Failed to fetch")) {
          setError("Erro de conexão. Verifique se o backend está rodando e se a rota do config.json está correta.");
        } else {
          setError(err.message);
        }
      } else {
        setError("Erro inesperado ao fazer login.");
      }
    } finally {
      setIsLoading(false);
    }
  };

  return (
    <div className="login-shell">
      <div className="login-background login-background-left" />
      <div className="login-background login-background-right" />

      <div className="login-layout">
        <div className="login-hero">
          <Link to="/" className="login-back-link">
            <ArrowLeft size={16} />
            <span>Voltar para o início</span>
          </Link>

          <div className="login-badge">
            <ShieldCheck size={16} />
            <span>Acesso seguro</span>
          </div>

          <div className="login-hero-copy">
            <div className="login-title-icon">
              <LogIn size={30} />
            </div>
            <h1 className="login-title">
              Entre no <span className="purplegradient">UP</span>
            </h1>
            <p className="login-subtitle">
              Faça login para acessar seu painel, acompanhar seus serviços e continuar o seu atendimento.
            </p>
          </div>

          <div className="login-benefits">
            <div className="login-benefit-card">
              <strong>Painel completo</strong>
              <span>Visualize dados, calendário, relatórios e solicitações em um só lugar.</span>
            </div>
            <div className="login-benefit-card">
              <strong>Sessão protegida</strong>
              <span>Seu acesso continua validado no backend com sessão autenticada.</span>
            </div>
          </div>
        </div>

        <section className="login-card" aria-label="Formulário de login">
          <div className="login-card-header">
            <h2 className="login-card-title">Acessar conta</h2>
            <p className="login-card-subtitle">Use o mesmo padrão visual da tela de cadastro, mas focado no login.</p>
          </div>

          <form onSubmit={handleSubmit} className="login-form" noValidate>
            {error && (
              <div className="form-error login-form-error" role="alert" aria-live="polite">
                <AlertCircle size={18} />
                <span>{error}</span>
              </div>
            )}

            <div className="field">
              <label className="label" htmlFor="email">
                <span className="label-row">
                  <span className="label-icon"><Mail size={16} /></span>
                  <span className="label-text">
                    E-mail <span className="req">*</span>
                  </span>
                </span>
              </label>
              <input
                id="email"
                className={`input ${error && !email ? "input-error" : ""}`}
                type="email"
                placeholder="seu@email.com"
                value={email}
                onChange={(e) => {
                  setEmail(e.target.value);
                  if (error) setError(null);
                }}
                autoComplete="email"
                disabled={isLoading}
                required
              />
            </div>

            <div className="field">
              <label className="label" htmlFor="password">
                <span className="label-row">
                  <span className="label-icon"><Lock size={16} /></span>
                  <span className="label-text">
                    Senha <span className="req">*</span>
                  </span>
                </span>
              </label>
              <div className="login-input-wrapper">
                <input
                  id="password"
                  className={`input ${error && !password ? "input-error" : ""}`}
                  type={showPassword ? "text" : "password"}
                  placeholder="Digite sua senha"
                  value={password}
                  onChange={(e) => {
                    setPassword(e.target.value);
                    if (error) setError(null);
                  }}
                  autoComplete="current-password"
                  disabled={isLoading}
                  required
                />
                <button
                  type="button"
                  className="password-toggle"
                  onClick={() => setShowPassword((current) => !current)}
                  aria-label={showPassword ? "Ocultar senha" : "Mostrar senha"}
                  disabled={isLoading}
                >
                  {showPassword ? <EyeOff size={18} /> : <Eye size={18} />}
                </button>
              </div>
            </div>

            <button type="submit" className="login-submit" disabled={isLoading}>
              <span className="btn-row">
                {isLoading ? <Loader2 size={18} className="spin" /> : <LogIn size={18} />}
                {isLoading ? "Entrando..." : "Entrar"}
              </span>
            </button>

            <div className="login-footer-text">
              Não tem conta? <Link to="/SignIn">Cadastre-se</Link>
            </div>
          </form>
        </section>
      </div>
    </div>
  );
}
