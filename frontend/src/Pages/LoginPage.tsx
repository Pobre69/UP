import LoginForm from "../Components/LoginPage/LoginForm";
import ScrollRevealProvider from "../Components/UI/ScrollRevealProvider";

export default function LoginPage() {
  return (
    <ScrollRevealProvider>
      <LoginForm />
    </ScrollRevealProvider>
  );
}
