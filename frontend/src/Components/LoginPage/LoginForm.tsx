import "../../Design/LoginPage/LoginForm.css";
import { useState } from "react";
import { Mail, Lock, Loader2, ArrowLeft } from "lucide-react";
import { useNavigate } from "react-router-dom";
import config from "../../config.json";

type LoginFormState = {
  email: string;
  senha: string;
};

export default function LoginForm() {
  const navigate = useNavigate();
  const [form, setForm] = useState<LoginFormState>({
    email: "",
    senha: "",
  });

  const [touched, setTouched] = useState<Record<string, boolean>>({});
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [submitError, setSubmitError] = useState("");

  function setField<K extends keyof LoginFormState>(
    key: K,
    value: LoginFormState[K]
  ) {
    setForm((p) => ({ ...p, [key]: value }));
  }

  function touch(name: keyof LoginFormState) {
    setTouched((p) => ({ ...p, [name]: true }));
  }

  function validateEmail(email: string) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.trim());
  }

  const errors: Partial<Record<keyof LoginFormState, string>> = {};
  if (!form.email.trim()) errors.email = "Obrigatório.";
  if (!form.senha.trim()) errors.senha = "Obrigatório.";

  const hasErrors = Object.keys(errors).length > 0;

  function onSubmit(e: React.FormEvent) {
    e.preventDefault();
    setSubmitError("");

    setTouched({
      email: true,
      senha: true,
    });

    if (hasErrors) {
      return;
    }

    setIsSubmitting(true);

    fetch(`${config.backRoute}/auth/login`, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      credentials: "include",
      body: JSON.stringify({
        email: form.email.trim(),
        senha: form.senha,
      }),
    })
      .then(async (res) => {
        const text = await res.text();
        
        if (!text) {
          throw new Error("Resposta vazia do servidor");
        }
        
        let data;
        try {
          data = JSON.parse(text);
        } catch (e) {
          throw new Error("Resposta inválida do servidor");
        }
        
        if (res.ok && data.success) {
          localStorage.setItem("userEmail", form.email);
          navigate("/app");
        } else if (data.inativo) {
          localStorage.setItem("userEmail", form.email);
          navigate("/payment-verification");
        } else {
          setSubmitError(data.mensagem || "E-mail ou senha incorretos");
        }
      })
      .catch((error) => {
        console.error("Erro no login:", error);
        setSubmitError("E-mail ou senha incorretos");
      })
      .finally(() => {
        setIsSubmitting(false);
      });
  }

  return (
    <div className="login-container">
      <a href="/" className="back-link reveal" style={{ "--reveal-delay": "20ms" } as any}>
        <ArrowLeft size={18} />
        <span>Voltar ao início</span>
      </a>

      <div className="login-card reveal">
        <div className="login-header reveal" style={{ "--reveal-delay": "80ms" } as any}>
          <h1 className="login-title">
            Bem-vindo de volta à <span className="purplegradient">UP!</span>
          </h1>
          <p className="login-subtitle">
            Faça login para acessar sua conta
          </p>
        </div>

        <form className="login-form reveal" style={{ "--reveal-delay": "140ms" } as any} onSubmit={onSubmit} noValidate>
          <div className="field">
            <label className="label" htmlFor="email">
              <span className="label-row">
                <span className="label-icon">
                  <Mail size={16} />
                </span>
                <span className="label-text">
                  E-mail <span className="req">*</span>
                </span>
              </span>
            </label>

            <input
              className={`input ${
                touched.email && errors.email ? "input-error" : ""
              }`}
              id="email"
              type="email"
              placeholder="seu@email.com"
              value={form.email}
              onChange={(e) => setField("email", e.target.value)}
              onBlur={() => touch("email")}
            />
            {touched.email && errors.email && (
              <div className="error">{errors.email}</div>
            )}
          </div>

          <div className="field">
            <label className="label" htmlFor="senha">
              <span className="label-row">
                <span className="label-icon">
                  <Lock size={16} />
                </span>
                <span className="label-text">
                  Senha <span className="req">*</span>
                </span>
              </span>
            </label>

            <input
              className={`input ${
                touched.senha && errors.senha ? "input-error" : ""
              }`}
              id="senha"
              type="password"
              placeholder="Sua senha"
              value={form.senha}
              onChange={(e) => setField("senha", e.target.value)}
              onBlur={() => touch("senha")}
            />
            {touched.senha && errors.senha && (
              <div className="error">{errors.senha}</div>
            )}
          </div>

          <button
            className="button"
            type="submit"
            disabled={isSubmitting}
          >
            {isSubmitting ? (
              <span className="btn-row">
                <Loader2 className="spin" size={18} />
                Entrando...
              </span>
            ) : (
              "Entrar"
            )}
          </button>

          {submitError && <div className="form-error">{submitError}</div>}
        </form>
      </div>
    </div>
  );
}
