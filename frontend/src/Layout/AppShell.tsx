import { Outlet, useLocation } from "react-router-dom";
import { useEffect, useState } from "react";
import Sidebar from "./Sidebar";
import ScrollRevealProvider from "../Components/UI/ScrollRevealProvider";
import { appService } from "../services";
import "../LayoutDesign/AppShell.css";

export default function AppShell() {
  const location = useLocation();
  const [syncing, setSyncing] = useState(true);
  const [syncMessage, setSyncMessage] = useState("Atualizando seus dados...");

  useEffect(() => {
    let isMounted = true;

    const syncData = async () => {
      try {
        setSyncing(true);
        const result = await appService.syncLatestData();
        if (!isMounted) return;

        if (result.data?.connected === false) {
          setSyncMessage("Conta carregada. Conecte o Instagram para sincronizar métricas.");
        } else {
          setSyncMessage(result.data?.message || "Dados sincronizados com sucesso.");
        }
      } catch (error) {
        if (!isMounted) return;
        const message = error instanceof Error ? error.message : "Não foi possível atualizar os dados agora.";
        setSyncMessage(message);
      } finally {
        if (isMounted) {
          window.setTimeout(() => setSyncing(false), 250);
        }
      }
    };

    void syncData();

    return () => {
      isMounted = false;
    };
  }, []);
  
  return (
    <div className="appShell">
      <Sidebar />
      <main className="appMain">
        {syncing ? (
          <div className="page" style={{ display: "flex", alignItems: "center", justifyContent: "center", minHeight: "60vh" }}>
            <div style={{ textAlign: "center" }}>
              <div
                style={{
                  width: "42px",
                  height: "42px",
                  border: "4px solid #f3f3f3",
                  borderTop: "4px solid #667eea",
                  borderRadius: "50%",
                  animation: "spin 1s linear infinite",
                  margin: "0 auto 18px",
                }}
              />
              <p style={{ margin: 0, color: "#666" }}>{syncMessage}</p>
              <style>{`
                @keyframes spin {
                  0% { transform: rotate(0deg); }
                  100% { transform: rotate(360deg); }
                }
              `}</style>
            </div>
          </div>
        ) : (
          <ScrollRevealProvider key={location.pathname}>
            <Outlet />
          </ScrollRevealProvider>
        )}
      </main>
    </div>
  );
}
