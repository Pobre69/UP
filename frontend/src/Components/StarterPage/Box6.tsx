import "../../Design/StarterPage/Box6.css";

export default function Box6() {
    function perguntaClicked(e: React.MouseEvent<HTMLDivElement>) {
        e.currentTarget.style.border = "#9B2BFF solid 1px";
        e.currentTarget.style.transition = "border 0.3s ease-in-out";
        const box6_perguntas = document.getElementById("box6_perguntas");
        if (!box6_perguntas) return;
        box6_perguntas.childNodes.forEach((element: any) => {
            if (element !== e.currentTarget) {
                element.style.border = "transparent solid 1px";
            }
        });
    }

    return (
        <div id="box6">
            <div id="box6_comentario">
                <p>Questões <span className="text_Purple">Frequentes</span></p>
                <h3>Tire suas dúvidas sobre nossos serviços e planos.</h3>
            </div>
            <div id="box6_perguntas">
                <div onClick={(e) => perguntaClicked(e) }>
                    <p>Em quanto tempo começo a ver resultados?</p>
                    <h3>Os primeiros resultados aparecem nas primeiras semanas, principalmente em organização do perfil e engajamento. Resultados mais consistentes vêm com o tempo e otimização contínua.</h3>
                </div>
                <div onClick={(e) => perguntaClicked(e) }>
                    <p>Posso mudar de plano a qualquer momento?</p>
                    <h3>Sim! Você pode fazer upgrade ou downgrade do seu plano a qualquer momento. As alterações são aplicadas imediatamente e os valores são calculados proporcionalmente.</h3>
                </div>
                <div onClick={(e) => perguntaClicked(e) }>
                    <p>O valor dos anúncios está incluso no plano?</p>
                    <h3>Não. O investimento em anúncios é pago diretamente às plataformas. A UP é responsável pela gestão, estratégia e otimização das campanhas.</h3>
                </div>
                <div onClick={(e) => perguntaClicked(e) }>
                    <p>Como funciona o suporte?</p>
                    <h3>O contato é direto via WhatsApp. Você acompanha tudo de perto, tira dúvidas e recebe feedback durante todo o período do plano.</h3>
                </div>
                <div onClick={(e) => perguntaClicked(e) }>
                    <p>Meus dados estão seguros?</p>
                    <h3>Absolutamente! Utilizamos criptografia de ponta a ponta, servidores seguros e seguimos todas as normas da LGPD. Seus dados são backupeados diariamente.</h3>
                </div>
                <div onClick={(e) => perguntaClicked(e) }>
                    <p>Existe contrato de fidelidade?</p>
                    <h3>Não existe fidelidade mínima. Você pode cancelar sua assinatura a qualquer momento sem multas ou taxas adicionais.</h3>
                </div>
                <div onClick={(e) => perguntaClicked(e) }>
                    <p>Como funciona o acompanhamento?</p>
                    <h3>O acompanhamento é feito de forma contínua e próxima. Analisamos os resultados, ajustamos as estratégias e mantemos contato direto com você durante todo o plano. Você recebe feedbacks, pode acompanhar a evolução do perfil e tirar dúvidas sempre que precisar, garantindo que tudo esteja alinhado com os objetivos do seu negócio.</h3>
                </div>
            </div>
        </div>
    )
}