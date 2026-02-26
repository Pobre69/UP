import { Navigate } from "react-router-dom";
import type React from "react";
import { useEffect, useState } from "react";

export default function ProtectedRoute({
  children,
}: {
  children: React.ReactNode;
}) {
  const [isAuthenticated, setIsAuthenticated] = useState<boolean | null>(null);

  useEffect(() => {
    const userEmail = localStorage.getItem("userEmail");
    setIsAuthenticated(!!userEmail);
  }, []);

  if (isAuthenticated === null) {
    return null;
  }

  return isAuthenticated ? <>{children}</> : <Navigate to="/Login" replace />;
}
