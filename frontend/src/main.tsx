import { StrictMode } from "react";
import { createRoot } from "react-dom/client";
import { BrowserRouter } from "react-router-dom";
import WebRoutes from "./Routes/Web";
import "./styles/global.css";
import ScrollRevealProvider from "./Components/UI/ScrollRevealProvider";

createRoot(document.getElementById("root")!).render(
  <StrictMode>
    <ScrollRevealProvider>
      <BrowserRouter>
        <WebRoutes />
      </BrowserRouter>
    </ScrollRevealProvider>
  </StrictMode>
);
