import "../../Design/StarterPage/Box4.css";

export default function Box4() {
    return (
        <div id="box4">
            <div id="box4_part1">
                <p style={{ marginBottom: "30px" }}>Por que escolher a <span className="text_Purple">UP</span>?</p>
                <h3 id="box4_margin20">A UP não trata seu negócio como mais um perfil.</h3>
                <h3>A gente cria estratégia, mantém constância e acompanha de perto pra transformar presença digital em resultado real. <br />Sem enrolação, sem abandono — crescimento de verdade.</h3>
                <div id="box4_check">
                    <p>Atendimento personalizado 24/7</p>
                    <p>Resultados comprovados e mensuráveis</p>
                    <p>Ferramentas de ponta e inovação constante</p>
                    <p>Acompanhamento quando precisar</p>
                </div>
            </div>
            <div id="box4_part2">
                <div className="box4_boxes">
                    <img src="" alt="Confiança" />
                    <div>
                        <p>Confiança</p>
                        <h3>Transparência em cada etapa — você sempre sabe o que está sendo feito e por quê</h3>
                    </div>
                </div>
                <div className="box4_boxes">
                    <img src="" alt="Excelência" />
                    <div>
                        <p>Excelência</p>
                        <h3>Equipe especializada e certificada nas melhores práticas.</h3>
                    </div>
                </div>
                <div className="box4_boxes">
                    <img src="" alt="Parceria" />
                    <div>
                        <p>Parceria</p>
                        <h3>Trabalhamos lado a lado para garantir seu sucesso.</h3>
                    </div>
                </div>
            </div>
        </div>
    )
}