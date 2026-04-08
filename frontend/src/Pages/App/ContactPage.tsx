import { useState } from "react";
import { Mail, Phone, MessageSquare, Send, Loader2 } from "lucide-react";
import { Card, CardTitle, PageHeader } from "../../Components/UI/Cards";
import { contactService } from "../../services/contactService";
import "./pages.css";

export default function ContactPage() {
  const [form, setForm] = useState({ name: "", email: "", subject: "", message: "" });
  const [status, setStatus] = useState<"idle" | "loading" | "success" | "error">("idle");
  const [errorMsg, setErrorMsg] = useState("");

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setStatus("loading");
    setErrorMsg("");
    try {
      await contactService.send(form);
      setStatus("success");
    } catch {
      setErrorMsg("Não foi possível enviar sua mensagem. Tente novamente.");
      setStatus("error");
    }
  };

  return (
    <div className="page">
      <PageHeader
        title={<>Entre em <span className="accentText">Contato</span></>}
        subtitle="Estamos aqui para ajudar. Envie sua mensagem e retornaremos em breve."
      />

      <div className="gridTwo" style={{ marginBottom: 18 }}>
        <Card style={{ padding: 16 }}>
          <CardTitle>Envie uma mensagem</CardTitle>

          {status === "success" ? (
            <div className="emptyBox" style={{ marginTop: 16 }}>
              <Send size={28} style={{ margin: "0 auto 10px", display: "block", color: "var(--accent)" }} />
              Mensagem enviada com sucesso! Em breve entraremos em contato.
            </div>
          ) : (
            <form className="form" onSubmit={handleSubmit}>
              <div className="row">
                <input
                  className="input"
                  placeholder="Seu nome"
                  value={form.name}
                  onChange={(e) => setForm({ ...form, name: e.target.value })}
                  required
                  disabled={status === "loading"}
                />
                <input
                  className="input"
                  type="email"
                  placeholder="Seu e-mail"
                  value={form.email}
                  onChange={(e) => setForm({ ...form, email: e.target.value })}
                  required
                  disabled={status === "loading"}
                />
              </div>
              <input
                className="input"
                placeholder="Assunto"
                value={form.subject}
                onChange={(e) => setForm({ ...form, subject: e.target.value })}
                required
                disabled={status === "loading"}
              />
              <textarea
                className="input textarea"
                placeholder="Descreva sua dúvida ou solicitação..."
                value={form.message}
                onChange={(e) => setForm({ ...form, message: e.target.value })}
                required
                disabled={status === "loading"}
              />
              {status === "error" && (
                <p style={{ color: "#ef4444", fontSize: 13, fontWeight: 700, margin: 0 }}>{errorMsg}</p>
              )}
              <button
                type="submit"
                className="primaryBtn"
                style={{ alignSelf: "flex-end" }}
                disabled={status === "loading"}
              >
                <span style={{ display: "inline-flex", gap: 8, alignItems: "center" }}>
                  {status === "loading" ? <Loader2 size={15} className="spin" /> : <Send size={15} />}
                  {status === "loading" ? "Enviando..." : "Enviar mensagem"}
                </span>
              </button>
            </form>
          )}
        </Card>

        <div style={{ display: "flex", flexDirection: "column", gap: 14 }}>
          {[
            { icon: <Mail size={18} />, label: "E-mail", value: "suporte@upagencia.com.br" },
            { icon: <Phone size={18} />, label: "Telefone", value: "+55 (11) 9 9999-9999" },
            { icon: <MessageSquare size={18} />, label: "Horário de atendimento", value: "Seg–Sex, 9h às 18h" },
          ].map((item) => (
            <Card key={item.label} style={{ padding: 16 }}>
              <div className="profilePerfTop">
                <div className="profilePerfIcon">{item.icon}</div>
                <div className="profilePerfText">
                  <div className="profilePerfTitle">{item.label}</div>
                  <div className="profilePerfSub">{item.value}</div>
                </div>
              </div>
            </Card>
          ))}
        </div>
      </div>
    </div>
  );
}
