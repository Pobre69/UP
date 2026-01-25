import { useEffect, useState } from "react";
import "../../Design/StarterPage/Box1.css";

export default function Box1() {
    const [acimaDe650, setIsAbove650] = useState(
        window.innerWidth >= 650
    );

    const scrollToBox5 = () => {
        const element = document.getElementById('box5');
        if (element) {
            element.scrollIntoView({ behavior: 'smooth' });
        }
    };

    useEffect(() => {
        const handleResize = () => {
        setIsAbove650(window.innerWidth >= 650);
        };

        window.addEventListener("resize", handleResize);
        return () => window.removeEventListener("resize", handleResize);
    }, []);

    return (
        <div id="box1">
            <div id="box1_part1">
                <div id="Card"><span id="Ponto"></span>Transformação Digital</div>
                <p className="text_White">Elevando o Seu <br /><span className="text_purple_linear">Negócio</span></p>
                <h4 className="text_Gray">Potencialize seus resultados com soluções digitais inovadoras. Juntos, construímos o futuro do seu negócio.</h4>
                <div id="SaibaMaisButtonEffects">
                    <div className="glow left"></div>
                    <div className="glow right"></div>
                    <button id="SaibaMaisButton" onClick={scrollToBox5}>Saiba Mais</button>
                </div>
                <div id="box1_textDisplay">
                    <div><span className="box1_textEdit2">500+</span><br /><span className="box1_textEdit">Clientes</span></div>
                    <div><span className="box1_textEdit2">98%</span><br /><span className="box1_textEdit">Satisfação</span></div>
                    <div><span className="box1_textEdit2">5 anos</span><br /><span className="box1_textEdit">Experiência</span></div>
                </div>
            </div>
            {acimaDe650 && (
                <div id="box1_part2">
                    <img id="box1_img" src="../../Images/" alt="Imagem de Gestão" />
                    <div id="box1_img_Effect"></div>
                </div>
            )}
        </div>
    )
}