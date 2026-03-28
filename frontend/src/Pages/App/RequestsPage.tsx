import { useState, useEffect } from "react";
import { Inbox, ChevronDown } from "lucide-react";
import { Card, CardTitle, PageHeader } from "../../Components/UI/Cards";
import { requestsService } from "../../services";
import Toast from "../../Components/UI/Toast";
import "./pages.css";

interface RequestItem {
  id: number | string;
  titulo: string;
  tipo: string;
  texto: string;
  status: string;
  created_at: string;
}

export default function RequestsPage() {
  const [title, setTitle] = useState("");
  const [type, setType] = useState("Alteração");
  const [details, setDetails] = useState("");
  const [dropdownOpen, setDropdownOpen] = useState(false);
  const [requests, setRequests] = useState<RequestItem[]>([]);
  const [loading, setLoading] = useState(false);
  const [toast, setToast] = useState<{ message: string; type: "success" | "error" } | null>(null);

  const types = ["Alteração", "Ideia", "Feedback"];

  useEffect(() => {
    void loadRequests();
  }, []);

  const loadRequests = async () => {
    try {
      const response = await requestsService.getRequests();
      if (response.success) {
        setRequests(Array.isArray(response.data) ? response.data : []);
      }
    } catch (error) {
      console.error("Erro ao carregar solicitações:", error);
    }
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!title || !details) {
      setToast({ message: "Preencha todos os campos", type: "error" });
      return;
    }

    setLoading(true);
    try {
      const response = await requestsService.createRequest({
        titulo: title,
        tipo: type,
        texto: details,
      });

      if (response.success) {
        setToast({ message: "Solicitação enviada com sucesso!", type: "success" });
        setTitle("");
        setDetails("");
        await loadRequests();
      } else {
        setToast({ message: response.mensagem || "Erro ao enviar solicitação", type: "error" });
      }
    } catch (error: unknown) {
      const message = error instanceof Error ? error.message : "Erro ao enviar solicitação";
      setToast({ message, type: "error" });
      console.error("Erro completo:", error);
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="page">
      {toast && (
        <Toast
          message={toast.message}
          type={toast.type}
          onClose={() => setToast(null)}
        />
      )}
      <PageHeader
        title={
          <span className="inlineTitle">
            <Inbox size={18} /> Solicitações
          </span>
        }
        subtitle="Envie pedidos de alteração, ideias ou feedbacks."
      />

      <div className="gridTwo">
        <Card className="chartCard">
          <div className="chartHeader">
            <CardTitle>Nova Solicitação</CardTitle>
          </div>
          <form className="form" onSubmit={handleSubmit}>
            <div className="row">
              <input
                className="input"
                placeholder="Título da solicitação"
                value={title}
                onChange={(e) => setTitle(e.target.value)}
              />
              <div className="customSelect">
                <button
                  type="button"
                  className="input selectBtn"
                  onClick={() => setDropdownOpen(!dropdownOpen)}
                >
                  {type}
                  <ChevronDown size={16} />
                </button>
                {dropdownOpen && (
                  <div className="selectDropdown">
                    {types.map((t) => (
                      <div
                        key={t}
                        className={`selectOption ${type === t ? "selectOptionActive" : ""}`}
                        onClick={() => {
                          setType(t);
                          setDropdownOpen(false);
                        }}
                      >
                        {t}
                      </div>
                    ))}
                  </div>
                )}
              </div>
            </div>

            <textarea
              className="input textarea"
              placeholder="Descreva sua solicitação em detalhes..."
              value={details}
              onChange={(e) => setDetails(e.target.value)}
            />

            <button className="primaryBtn" type="submit" disabled={loading}>
              {loading ? "Enviando..." : "Enviar Solicitação"}
            </button>
          </form>
        </Card>

        <Card className="chartCard">
          <div className="chartHeader">
            <CardTitle>Minhas Solicitações</CardTitle>
          </div>
          {requests.length === 0 ? (
            <div className="emptyBox">Nenhuma solicitação enviada ainda.</div>
          ) : (
            <div style={{ display: "flex", flexDirection: "column", gap: "12px" }}>
              {requests.map((req) => (
                <div key={req.id} style={{ padding: "12px", border: "1px solid #e5e7eb", borderRadius: "8px" }}>
                  <div style={{ display: "flex", justifyContent: "space-between", marginBottom: "8px" }}>
                    <strong>{req.titulo}</strong>
                    <span style={{ fontSize: "12px", color: "#6b7280" }}>{req.tipo}</span>
                  </div>
                  <p style={{ fontSize: "14px", color: "#4b5563", marginBottom: "8px" }}>{req.texto}</p>
                  <div style={{ display: "flex", justifyContent: "space-between", fontSize: "12px", color: "#9ca3af" }}>
                    <span>Status: {req.status}</span>
                    <span>{new Date(req.created_at).toLocaleDateString("pt-BR")}</span>
                  </div>
                </div>
              ))}
            </div>
          )}
        </Card>
      </div>
    </div>
  );
}
