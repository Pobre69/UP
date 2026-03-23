import { Navigate, useLocation } from 'react-router-dom';
import { useEffect, useState } from 'react';
import { API_BASE_URL, fetchWithRetry } from '../config/api';

interface ProtectedRouteProps {
  children: React.ReactNode;
}

export function ProtectedRoute({ children }: ProtectedRouteProps) {
  const location = useLocation();
  const [isAuthenticated, setIsAuthenticated] = useState<boolean | null>(null);
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    const validateSession = async () => {
      try {
        setIsLoading(true);
        setError(null);

        // Tentar validar a sessão com o backend
        const response = await fetchWithRetry(
          `${API_BASE_URL}/auth/validate`,
          {
            method: 'GET',
            credentials: 'include',
            headers: {
              'Content-Type': 'application/json'
            }
          }
        );

        if (response.ok) {
          const data = await response.json();
          if (data.success) {
            setIsAuthenticated(true);
          } else {
            setIsAuthenticated(false);
          }
        } else if (response.status === 401) {
          // Sessão expirada ou não autenticado
          setIsAuthenticated(false);
        } else {
          // Erro de servidor, mas não redirecionar ainda
          setError('Erro ao validar sessão');
          setIsAuthenticated(null);
        }
      } catch (err) {
        console.error('Erro ao validar sessão:', err);
        // Se não conseguir validar (erro de rede), verificar localStorage como fallback
        const userEmail = localStorage.getItem('userEmail');
        if (userEmail) {
          setIsAuthenticated(true);
        } else {
          setIsAuthenticated(false);
        }
      } finally {
        setIsLoading(false);
      }
    };

    validateSession();
  }, []);

  if (isLoading) {
    return (
      <div style={{
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'center',
        height: '100vh',
        background: 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)'
      }}>
        <div style={{
          textAlign: 'center',
          background: 'white',
          padding: '40px',
          borderRadius: '10px',
          boxShadow: '0 10px 25px rgba(0,0,0,0.15)'
        }}>
          <div style={{
            width: '40px',
            height: '40px',
            border: '4px solid #f3f3f3',
            borderTop: '4px solid #667eea',
            borderRadius: '50%',
            animation: 'spin 1s linear infinite',
            margin: '0 auto 20px'
          }} />
          <p style={{ color: '#666', margin: 0 }}>Validando sessão...</p>
          <style>{`
            @keyframes spin {
              0% { transform: rotate(0deg); }
              100% { transform: rotate(360deg); }
            }
          `}</style>
        </div>
      </div>
    );
  }

  if (error && isAuthenticated === null) {
    // Erro de servidor, mas deixar passar (pode ser erro temporário)
    return <>{children}</>;
  }

  if (!isAuthenticated) {
    // Não autenticado, redirecionar para login
    return <Navigate to="/Login" state={{ from: location }} replace />;
  }

  return <>{children}</>;
}
