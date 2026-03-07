{/*import { useEffect } from "react";*/}
import "../../Design/StarterPage/Box1.css";
import backgroundImage from "../../Images/bkg_up.jpeg";

export default function Box1() {
    const scrollToBox5 = () => {
        const element = document.getElementById('box5');
        if (element) {
            element.scrollIntoView({ behavior: 'smooth' });
        }
    };

    return (
        <div id="box1" className="reveal" style={{ backgroundImage: `url(${backgroundImage})`, backgroundSize: 'cover', backgroundPosition: 'center' }}>
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
                </div>
            </div>

        </div>
    )
}