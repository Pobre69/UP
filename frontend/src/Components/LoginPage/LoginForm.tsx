import { useState } from "react";
import { useNavigate } from "react-router-dom";
import { Mail, Lock, AlertCircle, Loader } from "lucide-react";
import { API_BASE_URL, fetchWithRetry } from "../../config/api";
import "../../Design/LoginPage/LoginForm.css";

interface LoginFormProps {
  onLoginSuccess?: () => void;
}

export function LoginForm({ onLoginSuccess }: LoginFormProps) {
  const navigate = useNavigate();
  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");
  const [error, setError] = useState<string | null>(null);
  const [isLoading, setIsLoading] = useState(false);
  const [showPassword, setShowPassword] = useState(false);

  const validateEmail = (email: string): boolean => {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setError(null);

    // Validação de entrada
    if (!email.trim()) {
      setError("Por favor, insira seu e-mail");
      return;
    }

    if (!validateEmail(email)) {
      setError("Por favor, insira um e-mail válido");
      return;
    }

    if (!password) {
      setError("Por favor, insira sua senha");
      return;
    }

    if (password.length < 6) {
      setError("Senha deve ter no mínimo 6 caracteres");
      return;
    }

    setIsLoading(true);

    try {
      const response = await fetchWithRetry(
        `${API_BASE_URL}/auth/login`,
        {
          method: "POST",
          credentials: "include",
          headers: {
            "Content-Type": "application/json",
          },
          body: JSON.stringify({
            email: email.toLowerCase().trim(),
            senha: password,
          }),
        }
      );

      const data = await response.json();

      if (!response.ok) {
        if (response.status === 429) {
          setError(
            "Muitas tentativas de login. Por favor, tente novamente em alguns minutos."
          );
        } else if (response.status === 403) {
          // Conta não ativada
          setError(
            data.mensagem ||
            "Sua conta ainda não foi ativada. Por favor, conclua o pagamento."
          );
          // Redirecionar para página de pagamento se necessário
          if (data.planoSelecionado) {
            // Aqui você pode redirecionar para a página de pagamento
            console.log("Plano selecionado:", data.planoSelecionado);
          }
        } else if (response.status === 401) {
          setError("E-mail ou senha incorretos");
        } else {
          setError(
            data.mensagem || "Erro ao fazer login. Por favor, tente novamente."
          );
        }
        return;
      }

      if (!data.success) {
        setError(data.mensagem || "Erro ao fazer login");
        return;
      }

      // Login bem-sucedido
      localStorage.setItem("userEmail", data.usuario.email);
      localStorage.setItem("user", JSON.stringify(data.usuario));

      if (onLoginSuccess) {
        onLoginSuccess();
      }

      // Redirecionar para /app
      navigate("/app/dashboard", { replace: true });
    } catch (err) {
      console.error("Erro ao fazer login:", err);

      if (err instanceof Error) {
        if (err.message.includes("timeout")) {
          setError("Tempo limite de conexão excedido. Tente novamente.");
        } else if (err.message.includes("Failed to fetch")) {
          setError(
            "Erro de conexão. Verifique se o servidor está disponível."
          );
        } else {
          setError(err.message);
        }
      } else {
        setError("Erro desconhecido ao fazer login. Tente novamente.");
      }
    } finally {
      setIsLoading(false);
    }
  };

  return (
    <form onSubmit={handleSubmit} className="loginForm">
      {error && (
        <div className="errorAlert">
          <AlertCircle size={20} />
          <span>{error}</span>
        </div>
      )}

      <div className="formGroup">
        <label htmlFor="email">E-mail</label>
        <div className="inputWrapper">
          <Mail size={18} />
          <input
            id="email"
            type="email"
            placeholder="seu@email.com"
            value={email}
            onChange={(e) => {
              setEmail(e.target.value);
              setError(null);
            }}
            disabled={isLoading}
            required
            autoComplete="email"
          />
        </div>
      </div>

      <div className="formGroup">
        <label htmlFor="password">Senha</label>
        <div className="inputWrapper">
          <Lock size={18} />
          <input
            id="password"
            type={showPassword ? "text" : "password"}
            placeholder="Sua senha"
            value={password}
            onChange={(e) => {
              setPassword(e.target.value);
              setError(null);
            }}
            disabled={isLoading}
            required
            autoComplete="current-password"
          />
          <button
            type="button"
            className="togglePassword"
            onClick={() => setShowPassword(!showPassword)}
            disabled={isLoading}
            tabIndex={-1}
          >
            {showPassword ? "Ocultar" : "Mostrar"}
          </button>
        </div>
      </div>

      <button
        type="submit"
        className="submitButton"
        disabled={isLoading}
      >
        {isLoading ? (
          <>
            <Loader size={18} className="spinner" />
            Entrando...
          </>
        ) : (
          "Entrar"
        )}
      </button>

      <div className="formFooter">
        <p>
          Não tem conta?{" "}
          <a href="/SignIn" onClick={(e) => {
            e.preventDefault();
            navigate("/SignIn");
          }}>
            Cadastre-se
          </a>
        </p>
      </div>
    </form>
  );
}
