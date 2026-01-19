import "../../Design/StarterPage/Header.css";
import React from "../../Images/react.svg";

export default function Header() {
    return (
        <header>
            <div><img src={ React } id="logo" alt="UP Logo" /></div>
            <div id="header_links">
                <div>Inicio</div>
                <div>Serviçoes</div>
                <div>Sobre</div>
            </div>
            <div style={{ display: "flex", justifyContent: "right", marginRight: "50px" }}><button id="Consultoria_Button">Consultoria Gratuita</button></div>
        </header>
    )
}