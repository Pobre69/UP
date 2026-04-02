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
import { API_BASE_URL } from "../config/api";
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
  const [isLoggingOut, setIsLoggingOut] = useState(false);
  
  const handleLogout = async () => {
    setIsLoggingOut(true);
    try {
      const response = await fetch(`${API_BASE_URL}/auth/logout`, {
        method: 'POST',
        credentials: 'include',
        headers: {
          'Content-Type': 'application/json'
        }
      });
      
      if (!response.ok) {
        console.error('Erro ao fazer logout:', response.statusText);
      }
    } catch (error) {
      console.error('Erro ao fazer logout:', error);
    } finally {
      // Limpar dados locais independentemente de sucesso ou erro
      localStorage.removeItem('user');
      sessionStorage.clear();
      
      // Limpar headers de cache
      // (isso é feito no backend, mas podemos adicionar aqui também)
      
      setIsLoggingOut(false);
      setShowLogoutModal(false);
      
      // Redirecionar para login
      navigate('/Login', { replace: true });
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
            disabled={isLoggingOut}
          >
            <LogOut size={18} />
            <span>{isLoggingOut ? 'Saindo...' : 'Sair'}</span>
          </button>
        </div>
      </aside>
      {showLogoutModal && (
        <div className="modalOverlay" onClick={() => !isLoggingOut && setShowLogoutModal(false)}>
          <div className="modalContent" onClick={(e) => e.stopPropagation()}>
            <h3>Deseja realmente sair?</h3>
            <p>Ao sair, você será desconectado e precisará fazer login novamente para acessar sua conta.</p>
            <div className="modalActions">
              <button 
                onClick={() => setShowLogoutModal(false)} 
                className="btnCancel"
                disabled={isLoggingOut}
              >
                Cancelar
              </button>
              <button 
                onClick={handleLogout} 
                className="btnConfirm"
                disabled={isLoggingOut}
              >
                {isLoggingOut ? 'Saindo...' : 'Confirmar Saída'}
              </button>
            </div>
          </div>
        </div>
      )}
    </>
  );
}
