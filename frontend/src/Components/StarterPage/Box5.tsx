import "../../Design/StarterPage/Box5.css";
import { Check } from "lucide-react";

const Planos = [
    {
        especial: false,
        titulo: "Gestão de Instagram",
        dinheiro: "R$ 250",
        subtitulo: "Ideal para empresas que precisam marcar presença, se organizar e começar a crescer no digital.",
        detalhes: [
            "Gestão completa do Instagram",
            "Planejamento de conteúdo mensal",
            "Criação de 8 a 12 posts por mês (feed)",
            "Criação de stories recorrentes",
            "Padronização do perfil (bio, foto, destaques)",
            "Estratégia básica de engajamento",
            "Relatório mensal com métricas principais",
            "Suporte direto para alinhamentos"
        ]
    },
    {
        especial: true,
        titulo: "Instagram + Tráfego Pago",
        dinheiro: "R$ 499",
        subtitulo: "Perfeito para negócios que querem alcance, mensagens e vendas.",
        detalhes: [
            "Tudo do Plano 1",
            "Criação e gestão de anúncios no Meta Ads",
            "Configuração de campanhas estratégicas",
            "Otimização contínua dos anúncios",
            "Possibilidade de Google Ads",
            "Análise de desempenho dos anúncios",
            "Relatórios mais detalhados",
            "Consultoria mensal estratégica"
        ]
    },
    {
        especial: false,
        titulo: "Site + Instagram + Tráfego",
        dinheiro: "R$ 999",
        subtitulo: "A solução completa para empresas que querem uma estrutura digital sólida.",
        detalhes: [
            "Tudo do Plano 2",
            "Criação de site institucional ou landing page",
            "Site otimizado para conversão",
            "Integração do site com anúncios",
            "Melhorias mensais no site",
            "Análise completa do funil digital",
            "Relatórios avançados completos",
            "Suporte prioritário e acompanhamento"
        ]
    }
]

export default function Box5() {
    return (
        <div id="box5">
            <div id="Title-TrabalheConosco">
                <h3><span className="title_Recolor">Venha trabalhar conosco</span>
                    <br />
                    <div className="TitlePhrase-TrabalheConosco">Não vendemos apenas posts ou anúncios. Construímos presença digital, atraímos clientes e ajudamos sua empresa a crescer de forma estratégica.</div>
                </h3>
            </div>
            <div id="Plans-holder">
                {Planos.map((plano) => (
                    <div className="Plan" id={`${plano.especial ? "Especial" : ""}`}>
                        <div className="Plan_Effect"><div></div></div>
                        {plano.especial && <div id="MaisPopular"><div><h3>Mais Popular</h3></div></div>}
                        <p>{plano.titulo}</p>
                        <div className="Plano_Valor"><span className={plano.especial ? "" : "text_purple_linear"}>{plano.dinheiro}</span><h3>/mês</h3></div>
                        <h3 style={{ fontSize: "0.9em" }}>{plano.subtitulo}</h3>
                        <div className="Plans_Detalhe">
                            {plano.detalhes.map((detalhe) => (
                                <div>
                                    <Check className="Plan_Check" id={plano.especial ? "Plan_ChekcEspecial" : ""} size={20}/>
                                    <h3><div className={`${plano.especial ? "text_White" : "text_Black"}`}>{detalhe}</div></h3>
                                </div>
                            ))}
                            {plano.especial && <div id="Plans_DetalheExtra"><h3>📌 A verba dos anúncios é paga separadamente pelo cliente</h3></div>}
                        </div>
                        <div className="Plans_extra" id={plano.especial ? "Planos_ExtraEspecial" : ""}>👉 Foco total em presença online, constância e imagem profissional.</div>
                        <button className="planoButton" id={`${plano.especial ? "planoButtonEspecial" : ""}`}>Quero esse plano</button>
                    </div>
                ))}
            </div>
        </div>
    )
}