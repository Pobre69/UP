import { Routes, Route, Navigate } from "react-router-dom";
import StarterPage from "../Pages/StarterPage";
import SignInPage from "../Pages/SignInPage";
import LoginPage from "../Pages/LoginPage";
import ProtectedRoute from "../Components/ProtectedRoute";

import AppShell from "../Layout/AppShell";
import DashboardPage from "../Pages/App/DashboardPage";
import EngagementPage from "../Pages/App/EngagementPage";
import ReportsPage from "../Pages/App/ReportsPage";
import ServiceStatusPage from "../Pages/App/ServiceStatusPage";
import CalendarPage from "../Pages/App/CalendarPage";
import AdsPage from "../Pages/App/AdsPage";
import RequestsPage from "../Pages/App/RequestsPage";
import PlanPage from "../Pages/App/PlanPage";

export default function WebRoutes() {
  return (
    <Routes>
      {/* suas rotas atuais (mantidas) */}
      <Route path="/" element={<StarterPage />} />
      <Route path="/SignIn" element={<SignInPage />} />
      <Route path="/Login" element={<LoginPage />} />
      <Route path="/login" element={<Navigate to="/Login" replace />} />

      {/* NOVA área logada */}
      <Route path="/app" element={<ProtectedRoute><AppShell /></ProtectedRoute>}>
        <Route index element={<Navigate to="/app/dashboard" replace />} />
        <Route path="dashboard" element={<DashboardPage />} />
        <Route path="engajamento" element={<EngagementPage />} />
        <Route path="relatorios" element={<ReportsPage />} />
        <Route path="status" element={<ServiceStatusPage />} />
        <Route path="calendario" element={<CalendarPage />} />
        <Route path="anuncios" element={<AdsPage />} />
        <Route path="solicitacoes" element={<RequestsPage />} />
        <Route path="plano" element={<PlanPage />} />
      </Route>

      <Route path="*" element={<Navigate to="/" replace />} />
    </Routes>
  );
}
