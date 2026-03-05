import Header from "../Layout/Header";
import Footer from "../Layout/Footer";
import Form from "../Components/SignInPage/SignInForm";
import { ArrowLeft } from "lucide-react";
import "../Design/LoginPage/LoginForm.css";

export default function SignInPage() {
    return (
        <>
            <a href="/" className="back-link reveal" style={{ "--reveal-delay": "20ms" } as any}>
                <ArrowLeft size={18} />
                <span>Voltar ao início</span>
            </a>
            <Header />
            <Form />
            <Footer />
        </>
        
    )
}