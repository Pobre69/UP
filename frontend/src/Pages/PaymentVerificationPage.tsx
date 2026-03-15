import { useEffect, useState } from "react";
import { useNavigate } from "react-router-dom";
import { Loader2, CheckCircle, XCircle } from "lucide-react";
import config from "../config.json";

export default function PaymentVerificationPage() {
  const navigate = useNavigate();
  const [status, setStatus] = useState<'checking' | 'approved' | 'pending'>('checking');
  const [message, setMessage] = useState('Verificando pagamento...');
  const [planoSelecionado, setPlanoSelecionado] = useState<string | null>(null);

  const paymentLinks: Record<string, string> = {
    'basico': 'https://pay.cakto.com.br/nvtso3j_754042',  //'https://pay.cakto.com.br/qq5rz6e_752674',
    'premium': 'https://pay.cakto.com.br/3mz49rp_754011',
    'completo': 'https://pay.cakto.com.br/4sgtxw3_754018'
  };

  useEffect(() => {
    const checkPayment = async () => {
      try {
        const response = await fetch(`${config.backRoute}/payment/verificar`, {
          method: 'GET',
          credentials: 'include',
        });

        const data = await response.json();

        if (data.success && data.ativo) {
          setStatus('approved');
          setMessage('Pagamento confirmado! Redirecionando...');
          setTimeout(() => {
            navigate('/app');
          }, 2000);
        } else {
          setStatus('pending');
          setPlanoSelecionado(data.planoSelecionado);
          setMessage('Aguardando confirmação do pagamento...');
        }
      } catch (error) {
        console.error('Erro ao verificar pagamento:', error);
        setStatus('pending');
        setMessage('Erro ao verificar pagamento. Tente novamente.');
      }
    };

    checkPayment();
    const interval = setInterval(checkPayment, 5000);

    return () => clearInterval(interval);
  }, [navigate]);

  return (
    <div style={{
      display: 'flex',
      flexDirection: 'column',
      alignItems: 'center',
      justifyContent: 'center',
      minHeight: '100vh',
      padding: '2rem',
      textAlign: 'center'
    }}>
      <div style={{
        background: 'white',
        borderRadius: '12px',
        padding: '3rem',
        boxShadow: '0 4px 6px rgba(0,0,0,0.1)',
        maxWidth: '500px'
      }}>
        {status === 'checking' && (
          <>
            <Loader2 size={64} className="spin" style={{ margin: '0 auto 1.5rem', color: '#8b5cf6' }} />
            <h2 style={{ marginBottom: '1rem' }}>Verificando Pagamento</h2>
            <p style={{ color: '#666' }}>{message}</p>
          </>
        )}

        {status === 'approved' && (
          <>
            <CheckCircle size={64} style={{ margin: '0 auto 1.5rem', color: '#10b981' }} />
            <h2 style={{ marginBottom: '1rem', color: '#10b981' }}>Pagamento Confirmado!</h2>
            <p style={{ color: '#666' }}>{message}</p>
          </>
        )}

        {status === 'pending' && (
          <>
            <XCircle size={64} style={{ margin: '0 auto 1.5rem', color: '#f59e0b' }} />
            <h2 style={{ marginBottom: '1rem', color: '#f59e0b' }}>Aguardando Pagamento</h2>
            <p style={{ color: '#666', marginBottom: '1.5rem' }}>
              Sua conta será ativada automaticamente após a confirmação do pagamento.
            </p>
            {planoSelecionado && paymentLinks[planoSelecionado] && (
              <a
                href={paymentLinks[planoSelecionado]}
                target="_blank"
                rel="noopener noreferrer"
                style={{
                  display: 'inline-block',
                  padding: '0.75rem 1.5rem',
                  background: '#8b5cf6',
                  color: 'white',
                  border: 'none',
                  borderRadius: '8px',
                  cursor: 'pointer',
                  fontSize: '1rem',
                  textDecoration: 'none',
                  marginBottom: '1rem'
                }}
              >
                Realizar Pagamento
              </a>
            )}
            <button
              onClick={() => window.location.reload()}
              style={{
                display: 'block',
                width: '100%',
                padding: '0.75rem 1.5rem',
                background: 'transparent',
                color: '#8b5cf6',
                border: '2px solid #8b5cf6',
                borderRadius: '8px',
                cursor: 'pointer',
                fontSize: '1rem',
                marginTop: '0.5rem'
              }}
            >
              Verificar Novamente
            </button>
          </>
        )}
      </div>
    </div>
  );
}
