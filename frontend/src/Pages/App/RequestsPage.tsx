import { useState } from "react";
import { Inbox, ChevronDown } from "lucide-react";
import { Card, CardTitle, PageHeader } from "../../Components/UI/Cards";
import "./pages.css";

export default function RequestsPage() {
  const [title, setTitle] = useState("");
  const [type, setType] = useState("Alteração");
  const [details, setDetails] = useState("");
  const [dropdownOpen, setDropdownOpen] = useState(false);

  const types = ["Alteração", "Ideia", "Feedback"];

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
