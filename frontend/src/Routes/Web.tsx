import { Routes, Route } from "react-router-dom";
import StarterPage from "../Pages/StarterPage";
import SignInPage from "../Pages/SignInPage";

export default function WebRoutes() {
  return (
    <Routes>
      <Route path="/" element={<StarterPage />} />
      <Route path="/SignIn" element={<SignInPage />} />
    </Routes>
  );
}
