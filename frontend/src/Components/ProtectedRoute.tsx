import { Navigate } from "react-router-dom";
import { useEffect, useState } from "react";

export default function ProtectedRoute({ children }: { children: JSX.Element }) {
  const [isAuthenticated, setIsAuthenticated] = useState<boolean | null>(null);

  useEffect(() => {
    const userEmail = localStorage.getItem("userEmail");
    setIsAuthenticated(!!userEmail);
  }, []);

  if (isAuthenticated === null) {
    return null;
  }

  return isAuthenticated ? children : <Navigate to="/login" replace />;
}
