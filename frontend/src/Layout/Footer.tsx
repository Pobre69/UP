import "../LayoutDesign/Footer.css";
import {Twitter, Instagram} from "lucide-react";
import Up_Logo from "../Images/UP_logo.png";

export default function Footer() {
    return (
        <footer>
            <div id="Footer_Conteudo">
                <div id="footer_social">
                    <img id="footer_logo" src={Up_Logo} alt="Logo" />
                    <h4>Elevando negócios através de soluções digitais inovadoras e resultados mensuráveis.</h4>
                    <div id="footer_icons">
                        {/* <a href=""><Linkedin /></a> */}
                        <a href=""><Instagram /></a>
                        <a href=""><Twitter /></a>
                    </div>
                </div>
                <div>
                    <p>Links Úteis</p>
                    <h3><a href="#box1">Início</a></h3>
                    <h3><a href="#box2">Serviços</a></h3>
                    <h3><a href="#box3">Sobre</a></h3>
                    <h3><a href="#box5">Planos</a></h3>
                    <h3><a href="#box6">FAQ</a></h3>
                </div>
                <div>
                    <p>Legal</p>
                    <h3>Termos de Uso</h3>
                    <h3>Política de Privacidade</h3>
                    <h3>LGPD</h3>
                </div>
                <div>
                    <p>Contato</p>
                    <h3>upagenciamarketing.ads@gmail.com</h3>
                    <h3>(31) 98461-6744</h3>
                    <h3>Minas Gerais, Belo Horizonte - Brasil</h3>
                </div>
            </div>
            <div id="DireitosReservados">
                <h3>© 2026 UP. Todos os direitos reservados.</h3>
            </div>
        </footer>
    )
}