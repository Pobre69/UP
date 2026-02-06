import { useEffect, useState } from "react";
import "../LayoutDesign/Header.css";
import Up_Logo from "../Images/UP_logo.png";

export default function Header() {
    const [visible, setVisible] = useState(true);
    const [lastScrollY, setLastScrollY] = useState(0);
    const [hidePoint, setHidePoint] = useState(0);

    useEffect(() => {
        const handleScroll = () => {
            const currentScrollY = window.scrollY;

            if (currentScrollY === 0) {
                setVisible(true);
            } else if (currentScrollY > lastScrollY) {
                setVisible(false);
                setHidePoint(currentScrollY);
            } else if (currentScrollY < hidePoint - 15) {
                setVisible(true);
            }

            setLastScrollY(currentScrollY);
        };

        window.addEventListener('scroll', handleScroll);
        return () => window.removeEventListener('scroll', handleScroll);
    }, [lastScrollY, hidePoint]);

    const scrollToSection = (sectionId: string) => {
        const element = document.getElementById(sectionId);
        if (element) {
            element.scrollIntoView({ behavior: 'smooth' });
        }
    };

    return (
        <header className={visible ? 'header-visible' : 'header-hidden'}>
            <div><img src={ Up_Logo } id="logo" alt="UP Logo" /></div>
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