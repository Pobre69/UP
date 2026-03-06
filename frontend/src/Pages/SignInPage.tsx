import Form from "../Components/SignInPage/SignInForm";
import ScrollRevealProvider from "../Components/UI/ScrollRevealProvider";

export default function SignInPage() {
    return (
        <ScrollRevealProvider>
            <Form />
        </ScrollRevealProvider>
    );
}