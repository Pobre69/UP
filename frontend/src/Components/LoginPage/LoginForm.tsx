import "../../Design/LoginPage/LoginForm.css";
import { useState } from "react";
import { Mail, Lock, Loader2 } from "lucide-react";
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
  if (form.email && !validateEmail(form.email)) errors.email = "E-mail inválido.";
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
      setSubmitError("Confira os campos obrigatórios.");
      return;
    }

    setIsSubmitting(true);

    fetch(`${config.backRoute}/auth/login`, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({
        email: form.email,
        senha: form.senha,
      }),
    })
      .then((res) => res.json())
      .then((data) => {
        if (data.success) {
          localStorage.setItem("userEmail", form.email);
          navigate("/app");
        } else {
          setSubmitError(data.mensagem || "Credenciais inválidas");
        }
      })
      .catch(() => {
        setSubmitError("Não foi possível fazer login. Tente novamente.");
      })
      .finally(() => {
        setIsSubmitting(false);
      });
  }

  return (
    <div className="login-container">
      <div className="login-card">
        <div className="login-header">
          <h1 className="login-title">
            Bem-vindo de volta à <span className="purplegradient">UP!</span>
          </h1>
          <p className="login-subtitle">
            Faça login para acessar sua conta
          </p>
        </div>

        <form className="login-form" onSubmit={onSubmit} noValidate>
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
