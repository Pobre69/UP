import "../../Design/StarterPage/Box2.css";

export default function Box2() {
    return (
        
        <div id="box2">
            <div id="Title-ComoTrabalhamos">
                <h3> 
                    Como <span className="text_purple_linear">trabalhamos</span>
                    <br />
                    <span className="TitlePhrase">Um processo simples e eficiente para transformar sua presença digital</span>
                </h3>
            </div>
            <div id="BoxRow">
                <div className="BoxContainer">
                    <div className="BoxContent">
                        <div className="iconBox">
                            1
                        </div>
                        <p className="ptitle">Consultoria & Diagnóstico</p>
                        <p className="ptext">Entendemos o seu negócio, analisamos seu Instagram, público e objetivos. Nada é genérico, tudo começa com estratégia.</p>
                    </div>
                </div>
                <div className="BoxContainer">
                    <div className="BoxContent">
                        <div className="iconBox">
                            2
                        </div>
                        <p className="ptitle">Planejamento Estratégico</p>
                        <p className="ptext">Definimos identidade visual, tipo de conteúdo, frequência de posts e tom de comunicação. Aqui nasce o plano de crescimento.</p>
                    </div>
                </div>                   
                <div className="BoxContainer">
                    <div className="BoxContent">
                        <div className="iconBox">
                            3
                        </div>
                        <p className="ptitle">Criação & Publicação</p>
                        <p className="ptext">Criamos posts e stories profissionais e publicamos nos melhores horários, mantendo o perfil organizado e ativo.</p>
                    </div>
                </div>
                <div className="BoxContainer">
                    <div className="BoxContent">
                        <div className="iconBox">
                            4
                        </div>
                        <p className="ptitle">Acompanhamento Contínuo</p>
                        <p className="ptext">Acompanhamos resultados, ajustamos estratégias e mantemos contato constante durante todo o plano.</p>
                    </div>
                </div>
            </div>
        </div>
    )
}
