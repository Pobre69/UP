import { Outlet } from "react-router-dom";
import Sidebar from "./Sidebar";
import "../LayoutDesign/AppShell.css";

export default function AppShell() {
  return (
    <div className="appShell">
      <Sidebar />
      <main className="appMain">
        <Outlet />
      </main>
    </div>
  );
}
