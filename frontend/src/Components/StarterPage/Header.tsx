import "../../Design/StarterPage/Header.css";
import React from "../../Images/react.svg";

export default function Header() {
    const scrollToSection = (sectionId: string) => {
        const element = document.getElementById(sectionId);
        if (element) {
            element.scrollIntoView({ behavior: 'smooth' });
        }
    };

    return (
        <header>
            <div><img src={ React } id="logo" alt="UP Logo" /></div>
            <div id="header_links">
                <div onClick={() => scrollToSection('box1')} style={{ cursor: 'pointer' }}>Inicio</div>
                <div onClick={() => scrollToSection('box3')} style={{ cursor: 'pointer' }}>Serviçoes</div>
                <div onClick={() => scrollToSection('box4')} style={{ cursor: 'pointer' }}>Sobre</div>
            </div>
            <div style={{ display: "flex", justifyContent: "right", marginRight: "50px" }}>
                <a id="Consultoria_Button" href="https://wa.me/5511999999999?text=Olá,%20vim%20pelo%20site!"
                target="_blank"
                rel="noopener noreferrer">
                    Consultoria Gratuita
                </a>
            </div>
        </header>
    )
}