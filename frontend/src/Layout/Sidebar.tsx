import { NavLink, useNavigate } from "react-router-dom";
import { useState } from "react";
import {
  LayoutDashboard,
  Heart,
  FileText,
  ClipboardList,
  CalendarDays,
  Megaphone,
  Inbox,
  Crown,
  LogOut,
} from "lucide-react";
import "../LayoutDesign/Sidebar.css";

const nav = [
  { to: "/app/dashboard", label: "Dashboard", icon: LayoutDashboard },
  { to: "/app/engajamento", label: "Engajamento", icon: Heart },
  { to: "/app/relatorios", label: "Relatórios", icon: FileText },
  { to: "/app/status", label: "Status do Serviço", icon: ClipboardList },
  { to: "/app/calendario", label: "Calendário", icon: CalendarDays },
  { to: "/app/anuncios", label: "Anúncios", icon: Megaphone },
  { to: "/app/solicitacoes", label: "Solicitações", icon: Inbox },
  { to: "/app/plano", label: "Meu Plano", icon: Crown },
];

export default function Sidebar() {
  const navigate = useNavigate();
  const [showLogoutModal, setShowLogoutModal] = useState(false);

  const handleLogout = async () => {
    try {
      await fetch('http://localhost/Sites/UP/backend/public/index.php/auth/logout', {
        method: 'POST',
        credentials: 'include',
      });
    } catch (error) {
      console.error('Erro ao fazer logout:', error);
    } finally {
      localStorage.removeItem('userEmail');
      localStorage.removeItem('user');
      sessionStorage.clear();
      navigate('/Login');
    }
  };

  return (
    <>
      <aside className="sidebar">
        <div className="sidebarTop">
          <div className="brand">
            <div className="brandMark">UP</div>
          </div>
        </div>

        <nav className="sidebarNav">
          {nav.map((item) => {
            const Icon = item.icon;
            return (
              <NavLink
                key={item.to}
                to={item.to}
                className={({ isActive }) =>
                  `navItem ${isActive ? "navItemActive" : ""}`
                }
              >
                <Icon size={18} />
                <span>{item.label}</span>
              </NavLink>
            );
          })}
        </nav>

        <div className="sidebarBottom">
          <button 
            onClick={() => setShowLogoutModal(true)} 
            className="navItem navItemLogout"
          >
            <LogOut size={18} />
            <span>Sair</span>
          </button>
        </div>
      </aside>

      {showLogoutModal && (
        <div className="modalOverlay" onClick={() => setShowLogoutModal(false)}>
          <div className="modalContent" onClick={(e) => e.stopPropagation()}>
            <h3>Confirmar saída</h3>
            <p>Tem certeza que deseja sair?</p>
            <div className="modalActions">
              <button onClick={() => setShowLogoutModal(false)} className="btnCancel">
                Cancelar
              </button>
              <button onClick={handleLogout} className="btnConfirm">
                Sair
              </button>
            </div>
          </div>
        </div>
      )}
    </>
  );
}
