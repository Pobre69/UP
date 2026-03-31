import { useMemo, useState } from "react";
import type { FormEvent } from "react";
import { Link, useLocation, useNavigate } from "react-router-dom";
import { ArrowLeft, AlertCircle, Eye, EyeOff, Loader2, Lock, Mail } from "lucide-react";
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

    if (!password) {
      setError("Por favor, insira sua senha.");
      return;
    }

    setIsLoading(true);

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
      if (err instanceof Error) {
        if (err.name === "AbortError" || err.message.includes("timeout")) {
          setError("Tempo limite de conexão excedido. Tente novamente.");
        } else if (err.message.includes("Failed to fetch")) {
          setError("Erro de conexão. Verifique se o backend está rodando.");
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
    <div className="login-container">
      <Link to="/" className="back-link">
        <ArrowLeft size={16} />
        <span>Voltar</span>
      </Link>

      <section className="login-card">
        <header className="login-header">
          <h1 className="login-title">
            Entrar no <span className="purplegradient">UP</span>
          </h1>
          <p className="login-subtitle">Acesse sua conta para continuar.</p>
        </header>

        <form onSubmit={handleSubmit} className="login-form" noValidate>
          {error && (
            <div className="form-error" role="alert" aria-live="polite">
              <AlertCircle size={18} /> {error}
            </div>
          )}

          <div className="field">
            <label className="label" htmlFor="email">
              <span className="label-row">
                <span className="label-icon"><Mail size={16} /></span>
                E-mail
                <span className="req">*</span>
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
                Senha
                <span className="req">*</span>
              </span>
            </label>
            <div className="input-wrapper">
              <input
                id="password"
                className={`input ${error && !password ? "input-error" : ""}`}
                type={showPassword ? "text" : "password"}
                placeholder="Sua senha"
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

          <button type="submit" className="button" disabled={isLoading}>
            <span className="btn-row">
              {isLoading ? <Loader2 size={18} className="spin" /> : null}
              {isLoading ? "Entrando..." : "Entrar"}
            </span>
          </button>

          <div className="login-subtitle" style={{ textAlign: "center" }}>
            Não tem conta? <Link to="/SignIn">Cadastre-se</Link>
          </div>
        </form>
      </section>
    </div>
  );
}
