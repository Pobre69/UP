import "../../Design/SignInPage/SignInForm.css";
import React, { useEffect, useMemo, useRef, useState } from "react";
import {
  Rocket,
  Target,
  Users,
  Building2,
  Mail,
  Instagram,
  MapPin,
  Link2,
  UserCheck,
  Loader2,
  CheckCircle,
  Calendar,
  ChevronDown,
} from "lucide-react";
import config from "../../config.json";

type Option = { value: string; label: string };

type FormState = {
  fullName: string;
  company: string;
  email: string;
  instagram: string;
  segment: string;
  city: string;
  mainGoal: string;
  competitors: string;
  driveLink: string;
  attendant: string;
};

function clamp(n: number, min: number, max: number) {
  return Math.max(min, Math.min(n, max));
}

function useOutsideClick(
  refs: React.RefObject<HTMLElement | null>[],
  onClose: () => void,
  enabled: boolean
) {
  useEffect(() => {
    if (!enabled) return;

    function onDown(e: MouseEvent) {
      const t = e.target as Node;
      const inside = refs.some((r) => r.current && r.current.contains(t));
      if (!inside) onClose();
    }

    function onKey(e: KeyboardEvent) {
      if (e.key === "Escape") onClose();
    }

    document.addEventListener("mousedown", onDown);
    document.addEventListener("keydown", onKey);
    return () => {
      document.removeEventListener("mousedown", onDown);
      document.removeEventListener("keydown", onKey);
    };
  }, [enabled, onClose, refs]);
}

function Dropdown({
  id,
  label,
  required,
  placeholder,
  icon,
  value,
  options,
  error,
  touched,
  openId,
  setOpenId,
  onChange,
  onBlur,
}: {
  id: string;
  label: string;
  required?: boolean;
  placeholder: string;
  icon: React.ReactNode;
  value: string;
  options: Option[];
  error?: string;
  touched?: boolean;
  openId: string | null;
  setOpenId: (v: string | null) => void;
  onChange: (v: string) => void;
  onBlur?: () => void;
}) {
  const isOpen = openId === id;

  const wrapRef = useRef<HTMLDivElement>(null);
  const menuRef = useRef<HTMLDivElement>(null);

  const [openUp, setOpenUp] = useState(false);
  const [maxH, setMaxH] = useState(280);

  useOutsideClick([wrapRef], () => setOpenId(null), isOpen);

  const selected = options.find((o) => o.value === value);

  function toggle() {
    setOpenId(isOpen ? null : id);
    onBlur?.();
  }

  function choose(v: string) {
    onChange(v);
    setOpenId(null);
    onBlur?.();
  }

  useEffect(() => {
    if (!isOpen) return;

    const wrap = wrapRef.current;
    if (!wrap) return;

    const card = wrap.closest(".card") as HTMLElement | null;
    const bounds = (card ?? document.body).getBoundingClientRect();
    const r = wrap.getBoundingClientRect();

    const spaceBelow = bounds.bottom - r.bottom - 12;
    const spaceAbove = r.top - bounds.top - 12;

    const preferDown = spaceBelow >= 220 || spaceBelow >= spaceAbove;
    setOpenUp(!preferDown);

    setMaxH(clamp(preferDown ? spaceBelow : spaceAbove, 160, 320));

    function onRecalc() {
      const cardNow = wrapRef.current?.closest(".card") as HTMLElement | null;
      if (!wrapRef.current) return;

      const b = (cardNow ?? document.body).getBoundingClientRect();
      const rr = wrapRef.current.getBoundingClientRect();
      const sb = b.bottom - rr.bottom - 12;
      const sa = rr.top - b.top - 12;
      const down = sb >= 220 || sb >= sa;

      setOpenUp(!down);
      setMaxH(clamp(down ? sb : sa, 160, 320));
    }

    window.addEventListener("resize", onRecalc);
    window.addEventListener("scroll", onRecalc, true);
    return () => {
      window.removeEventListener("resize", onRecalc);
      window.removeEventListener("scroll", onRecalc, true);
    };
  }, [isOpen]);

  return (
    <div className="field" id={`field-${id}`}>
      <label className="label" htmlFor={id}>
        <span className="label-row">
          <span className="label-icon">{icon}</span>
          <span className="label-text">
            {label} {required && <span className="req">*</span>}
          </span>
        </span>
      </label>

      <div
        ref={wrapRef}
        className={`dd ${isOpen ? "dd-open" : ""} ${
          touched && error ? "input-error" : ""
        }`}
        id={id}
        tabIndex={0}
        role="button"
        aria-haspopup="listbox"
        aria-expanded={isOpen}
        onClick={toggle}
        onKeyDown={(e) => {
          if (e.key === "Enter" || e.key === " ") toggle();
          if (e.key === "Escape") setOpenId(null);
        }}
      >
        <div className="dd-value">
          <span className={`dd-text ${selected ? "" : "dd-placeholder"}`}>
            {selected ? selected.label : placeholder}
          </span>
          <span className="dd-chevron">
            <ChevronDown size={18} />
          </span>
        </div>

        {isOpen && (
          <div
            ref={menuRef}
            className={`dd-menu ${openUp ? "dd-menu-up" : "dd-menu-down"}`}
            role="listbox"
            style={{ maxHeight: maxH }}
          >
            {options.map((opt) => {
              const isSel = opt.value === value;
              return (
                <div
                  key={opt.value}
                  className={`dd-item ${
                    isSel ? "dd-item-selected" : ""
                  }`}
                  role="option"
                  aria-selected={isSel}
                  onMouseDown={(e) => e.preventDefault()}
                  onClick={() => choose(opt.value)}
                >
                  {opt.label}
                </div>
              );
            })}
          </div>
        )}
      </div>

      {touched && error && <div className="error">{error}</div>}
    </div>
  );
}

export default function SignInForm() {
  const [openId, setOpenId] = useState<string | null>(null);

  const segmentOptions = useMemo<Option[]>(
    () => [
      { value: "restaurante-food-service", label: "Restaurante / Food Service" },
      { value: "moda-vestuario", label: "Moda / Vestuário" },
      { value: "beleza-estetica", label: "Beleza / Estética" },
      { value: "saude-clinica", label: "Saúde / Clínica" },
      { value: "educacao-cursos", label: "Educação / Cursos" },
      { value: "imobiliario", label: "Imobiliário" },
      { value: "tecnologia-saas", label: "Tecnologia / SaaS" },
      { value: "e-commerce", label: "E-commerce" },
      { value: "servicos-profissionais", label: "Serviços Profissionais" },
      { value: "academia-fitness", label: "Academia / Fitness" },
      { value: "turismo-hotelaria", label: "Turismo / Hotelaria" },
      { value: "outro", label: "Outro" },
    ],
    []
  );

  const goalOptions = useMemo<Option[]>(
    () => [
      { value: "mais-clientes-vendas", label: "Mais clientes / vendas" },
      { value: "mais-seguidores", label: "Mais seguidores" },
      { value: "mais-orcamentos-leads", label: "Mais orçamentos / leads" },
      { value: "fortalecer-marca", label: "Fortalecer a marca" },
      { value: "lancar-produto-servico", label: "Lançar produto/serviço" },
      { value: "aumentar-engajamento", label: "Aumentar engajamento" },
    ],
    []
  );

  const attendantOptions = useMemo<Option[]>(
    () => [
      { value: "arthur-valentim", label: "Arthur Valentim" },
      { value: "arthur-victor", label: "Arthur Victor" },
      { value: "guilherme", label: "Guilherme" },
      { value: "joao-vitor", label: "João Vitor" },
      { value: "marcel", label: "Marcel" },
    ],
    []
  );

  const [form, setForm] = useState<FormState>({
    fullName: "",
    company: "",
    email: "",
    instagram: "",
    segment: "",
    city: "São Paulo - SP",
    mainGoal: "",
    competitors: "",
    driveLink: "",
    attendant: "",
  });

  const [touched, setTouched] = useState<Record<string, boolean>>({});
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [success, setSuccess] = useState(false);
  const [submitError, setSubmitError] = useState("");

  function setField<K extends keyof FormState>(key: K, value: FormState[K]) {
    setForm((p) => ({ ...p, [key]: value }));
  }

  function touch(name: keyof FormState) {
    setTouched((p) => ({ ...p, [name]: true }));
  }

  function validateEmail(email: string) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.trim());
  }

  const errors = useMemo(() => {
    const e: Partial<Record<keyof FormState, string>> = {};
    if (!form.fullName.trim()) e.fullName = "Obrigatório.";
    if (!form.email.trim()) e.email = "Obrigatório.";
    if (form.email && !validateEmail(form.email)) e.email = "E-mail inválido.";
    if (!form.segment) e.segment = "Obrigatório.";
    if (!form.city.trim()) e.city = "Obrigatório.";
    if (!form.mainGoal) e.mainGoal = "Obrigatório.";
    return e;
  }, [form]);

  const hasErrors = Object.keys(errors).length > 0;

  function onSubmit(e: React.FormEvent) {
    e.preventDefault();
    setSubmitError("");

    setTouched({
      fullName: true,
      company: true,
      email: true,
      instagram: true,
      segment: true,
      city: true,
      mainGoal: true,
      competitors: true,
      driveLink: true,
      attendant: true,
    });

    if (hasErrors) {
      setSubmitError("Confira os campos obrigatórios.");
      return;
    }

    setIsSubmitting(true);

    fetch(`${config.backRoute}/api`, {
      method: "POST",
      headers: {
        "Content-Type": "application/json"
      },
      body: JSON.stringify({
        fullName: form.fullName,
        company: form.company,
        email: form.email,
        instagram: form.instagram,
        segment: form.segment,
        city: form.city,
        mainGoal: form.mainGoal,
        competitors: form.competitors,
        driveLink: form.driveLink,
        attendant: form.attendant
      })
    })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          setSuccess(true);
        } else {
          setSubmitError(data.mensagem || "Erro ao enviar cadastro");
        }
      })
      .catch(() => {
        setSubmitError("Não foi possível enviar. Tente novamente.");
      })
      .finally(() => {
        setIsSubmitting(false);
      });
  }

  function reset() {
    setSuccess(false);
    setSubmitError("");
    setTouched({});
    setForm({
      fullName: "",
      company: "",
      email: "",
      instagram: "",
      segment: "",
      city: "São Paulo - SP",
      mainGoal: "",
      competitors: "",
      driveLink: "",
      attendant: "",
    });
  }

    return (
    <div id="boxform">
      <div id="title-icon">
        <Rocket size={38} color="white" strokeWidth={2} />
      </div>

      <div id="title">
        Bem-vindo(a) à <span className="purplegradient">UP!</span>
        <span id="titlePhrase">
          Preencha os dados abaixo para começarmos a trabalhar juntos.
        </span>
      </div>

      <div id="form">
        {!success ? (
          <form id="signupForm" onSubmit={onSubmit} noValidate>
            <div className="card" id="card">
              <div className="section-title" id="section-title">
                <span className="section-icon" id="section-icon">
                  <Target size={18} />
                </span>
                Próximos Passos
              </div>

              <div className="section-subtitle" id="section-subtitle">
                Precisamos de algumas informações para personalizar sua
                estratégia.
              </div>

              <div className="grid-2">
                <div className="field" id="field-fullName">
                  <label className="label" htmlFor="fullName">
                    <span className="label-row">
                      <span className="label-icon">
                        <Users size={16} />
                      </span>
                      <span className="label-text">
                        Nome completo <span className="req">*</span>
                      </span>
                    </span>
                  </label>

                  <input
                    className={`input ${
                      touched.fullName && errors.fullName
                        ? "input-error"
                        : ""
                    }`}
                    id="fullName"
                    placeholder="Seu nome"
                    value={form.fullName}
                    onChange={(e) =>
                      setField("fullName", e.target.value)
                    }
                    onBlur={() => touch("fullName")}
                  />
                  {touched.fullName && errors.fullName && (
                    <div className="error">{errors.fullName}</div>
                  )}
                </div>

                <div className="field" id="field-company">
                  <label className="label" htmlFor="company">
                    <span className="label-row">
                      <span className="label-icon">
                        <Building2 size={16} />
                      </span>
                      <span className="label-text">Empresa</span>
                    </span>
                  </label>

                  <input
                    className="input"
                    id="company"
                    placeholder="Nome da empresa"
                    value={form.company}
                    onChange={(e) =>
                      setField("company", e.target.value)
                    }
                    onBlur={() => touch("company")}
                  />
                </div>
              </div>

              <div className="field" id="field-email">
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
                  placeholder="seu@email.com"
                  value={form.email}
                  onChange={(e) =>
                    setField("email", e.target.value)
                  }
                  onBlur={() => touch("email")}
                />
                {touched.email && errors.email && (
                  <div className="error">{errors.email}</div>
                )}
              </div>

              <div className="field" id="field-instagram">
                <label className="label" htmlFor="instagram">
                  <span className="label-row">
                    <span className="label-icon">
                      <Instagram size={16} />
                    </span>
                    <span className="label-text">Instagram</span>
                  </span>
                </label>

                <input
                  className="input"
                  id="instagram"
                  placeholder="@seuinstagram"
                  value={form.instagram}
                  onChange={(e) =>
                    setField("instagram", e.target.value)
                  }
                  onBlur={() => touch("instagram")}
                />
                <div className="hint">
                  Após o envio, nossa equipe solicitará acesso via Meta
                  Business Suite.
                </div>
              </div>

              <div className="grid-2">
                <Dropdown
                  id="segment"
                  label="Segmento"
                  required
                  placeholder="Selecione o segmento"
                  icon={<Target size={16} />}
                  value={form.segment}
                  options={segmentOptions}
                  error={errors.segment}
                  touched={touched.segment}
                  openId={openId}
                  setOpenId={setOpenId}
                  onChange={(v) => setField("segment", v)}
                  onBlur={() => touch("segment")}
                />

                <div className="field" id="field-city">
                  <label className="label" htmlFor="city">
                    <span className="label-row">
                      <span className="label-icon">
                        <MapPin size={16} />
                      </span>
                      <span className="label-text">
                        Cidade <span className="req">*</span>
                      </span>
                    </span>
                  </label>

                  <input
                    className={`input ${
                      touched.city && errors.city
                        ? "input-error"
                        : ""
                    }`}
                    id="city"
                    placeholder="São Paulo - SP"
                    value={form.city}
                    onChange={(e) =>
                      setField("city", e.target.value)
                    }
                    onBlur={() => touch("city")}
                  />
                  {touched.city && errors.city && (
                    <div className="error">{errors.city}</div>
                  )}
                </div>
              </div>

              <Dropdown
                id="mainGoal"
                label="Objetivo principal"
                required
                placeholder="O que você quer alcançar?"
                icon={<Target size={16} />}
                value={form.mainGoal}
                options={goalOptions}
                error={errors.mainGoal}
                touched={touched.mainGoal}
                openId={openId}
                setOpenId={setOpenId}
                onChange={(v) => setField("mainGoal", v)}
                onBlur={() => touch("mainGoal")}
              />

              <div className="field" id="field-competitors">
                <label className="label" htmlFor="competitors">
                  <span className="label-row">
                    <span className="label-icon">
                      <Users size={16} />
                    </span>
                    <span className="label-text">
                      Concorrentes (2-3 exemplos)
                    </span>
                  </span>
                </label>

                <textarea
                  className="textarea"
                  id="competitors"
                  placeholder="Liste 2 ou 3 concorrentes que você admira ou gostaria de superar..."
                  value={form.competitors}
                  onChange={(e) =>
                    setField("competitors", e.target.value)
                  }
                  onBlur={() => touch("competitors")}
                  rows={3}
                />
              </div>

              <div className="field" id="field-driveLink">
                <label className="label" htmlFor="driveLink">
                  <span className="label-row">
                    <span className="label-icon">
                      <Link2 size={16} />
                    </span>
                    <span className="label-text">
                      Link do Google Drive
                    </span>
                  </span>
                </label>

                <input
                  className="input"
                  id="driveLink"
                  placeholder="https://drive.google.com/..."
                  value={form.driveLink}
                  onChange={(e) =>
                    setField("driveLink", e.target.value)
                  }
                  onBlur={() => touch("driveLink")}
                />
                <div className="hint">
                  Compartilhe logos, fotos e materiais que possamos usar.
                </div>
              </div>

              <Dropdown
                id="attendant"
                label="Quem foi que te atendeu?"
                placeholder="Selecione o atendente"
                icon={<UserCheck size={16} />}
                value={form.attendant}
                options={attendantOptions}
                touched={touched.attendant}
                openId={openId}
                setOpenId={setOpenId}
                onChange={(v) => setField("attendant", v)}
                onBlur={() => touch("attendant")}
              />

              <button
                className="button"
                id="submit"
                type="submit"
                disabled={isSubmitting}
              >
                {isSubmitting ? (
                  <span className="btn-row">
                    <Loader2 className="spin" size={18} />
                    Enviando...
                  </span>
                ) : (
                  "Enviar e começar!"
                )}
              </button>

              {submitError && (
                <div className="form-error">{submitError}</div>
              )}
            </div>
          </form>
        ) : (
          <div className="card success" id="success">
            <div className="success-icon" id="success-icon">
              <CheckCircle size={34} />
            </div>

            <div className="success-title" id="success-title">
              Cadastro enviado com sucesso!
            </div>

            <div className="success-subtitle" id="success-subtitle">
              Agora vamos agendar sua reunião de Kick-off para alinhar
              próximos passos.
            </div>

            <button
              className="button button-secondary"
              id="kickoff"
              type="button"
            >
              <span className="btn-row">
                <Calendar size={18} />
                Agendar Reunião de Kick-off
              </span>
            </button>

            <button
              className="link-btn"
              id="reset"
              type="button"
              onClick={reset}
            >
              Voltar e editar
            </button>
          </div>
        )}
      </div>
    </div>
  );
}
