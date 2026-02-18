import { useState } from "react";
import { Inbox } from "lucide-react";
import { Card, CardTitle, PageHeader } from "../../Components/UI/Cards";
import "./pages.css";

export default function RequestsPage() {
  const [title, setTitle] = useState("");
  const [type, setType] = useState("Alteração");
  const [details, setDetails] = useState("");

  return (
    <div className="page">
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
          <form
            className="form"
            onSubmit={(e) => {
              e.preventDefault();
              // front-end only
              alert("Solicitação enviada (mock).");
              setTitle("");
              setDetails("");
            }}
          >
            <div className="row">
              <input
                className="input"
                placeholder="Título da solicitação"
                value={title}
                onChange={(e) => setTitle(e.target.value)}
              />
              <select
                className="input"
                value={type}
                onChange={(e) => setType(e.target.value)}
              >
                <option>Alteração</option>
                <option>Ideia</option>
                <option>Feedback</option>
              </select>
            </div>

            <textarea
              className="input textarea"
              placeholder="Descreva sua solicitação em detalhes..."
              value={details}
              onChange={(e) => setDetails(e.target.value)}
            />

            <button className="primaryBtn" type="submit">
              Enviar Solicitação
            </button>
          </form>
        </Card>

        <Card className="chartCard">
          <div className="chartHeader">
            <CardTitle>Minhas Solicitações</CardTitle>
          </div>
          <div className="emptyBox">Nenhuma solicitação enviada ainda.</div>
        </Card>
      </div>
    </div>
  );
}
