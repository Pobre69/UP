import { Outlet, useLocation } from "react-router-dom";
import Sidebar from "./Sidebar";
import ScrollRevealProvider from "../Components/UI/ScrollRevealProvider";
import "../LayoutDesign/AppShell.css";

export default function AppShell() {
  const location = useLocation();
  
  return (
    <div className="appShell">
      <Sidebar />
      <main className="appMain">
        <ScrollRevealProvider key={location.pathname}>
          <Outlet />
        </ScrollRevealProvider>
      </main>
    </div>
  );
}
