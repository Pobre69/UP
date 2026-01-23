import { useState } from "react";
import "../../Design/StarterPage/Box6.css";

const perguntas = [
    {
        pergunta: "Em quanto tempo começo a ver resultados?",
        resposta:
        "Os primeiros resultados aparecem nas primeiras semanas, principalmente em organização do perfil e engajamento. Resultados mais consistentes vêm com o tempo e otimização contínua."
    },
    {
        pergunta: "Posso mudar de plano a qualquer momento?",
        resposta:
        "Sim! Você pode fazer upgrade ou downgrade do seu plano a qualquer momento. As alterações são aplicadas imediatamente e os valores são calculados proporcionalmente."
    },
    {
        pergunta: "O valor dos anúncios está incluso no plano?",
        resposta:
        "Não. O investimento em anúncios é pago diretamente às plataformas. A UP é responsável pela gestão, estratégia e otimização das campanhas."
    },
    {
        pergunta: "Como funciona o suporte?",
        resposta:
        "O contato é direto via WhatsApp. Você acompanha tudo de perto, tira dúvidas e recebe feedback durante todo o período do plano."
    },
    {
        pergunta: "Meus dados estão seguros?",
        resposta:
        "Absolutamente! Utilizamos criptografia de ponta a ponta, servidores seguros e seguimos todas as normas da LGPD. Seus dados são backupeados diariamente."
    },
    {
        pergunta: "Existe contrato de fidelidade?",
        resposta:
        "Não existe fidelidade mínima. Você pode cancelar sua assinatura a qualquer momento sem multas ou taxas adicionais."
    },
    {
        pergunta: "Como funciona o acompanhamento?",
        resposta:
        "O acompanhamento é feito de forma contínua e próxima. Analisamos os resultados, ajustamos as estratégias e mantemos contato direto com você durante todo o plano."
    }
];

export default function Box6() {
    const [ativo, setAtivo] = useState<number | null>(null);

    return (
        <div id="box6">
            <div id="box6_comentario">
                <p>
                    Questões <span className="text_Purple">Frequentes</span>
                </p>
                <h3>Tire suas dúvidas sobre nossos serviços e planos.</h3>
            </div>

            <div id="box6_perguntas">
                {perguntas.map((item, index) => (
                    <div
                        key={index}
                        className={`pergunta ${ativo === index ? "ativo" : ""}`}
                        onClick={() => setAtivo(index)}
                    >
                        <p>{item.pergunta}</p>
                        <h3>{item.resposta}</h3>
                    </div>
                ))}
            </div>
        </div>
    );
}
