import { useEffect, useState } from 'react';
import { stripeService } from '../../services/stripeService';

export default function Sucesso() {
  const [session, setSession] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const sessionId = new URLSearchParams(window.location.search).get('session_id');
    
    if (sessionId) {
      stripeService.getCheckoutSession(sessionId)
        .then(setSession)
        .catch(console.error)
        .finally(() => setLoading(false));
    } else {
      setLoading(false);
    }
  }, []);

  if (loading) {
    return (
      <div style={{ 
        display: 'flex', 
        justifyContent: 'center', 
        alignItems: 'center', 
        minHeight: '100vh',
        flexDirection: 'column',
        gap: '20px'
      }}>
        <div>Carregando...</div>
      </div>
    );
  }

  return (
    <div style={{ 
      display: 'flex', 
      justifyContent: 'center', 
      alignItems: 'center', 
      minHeight: '100vh',
      flexDirection: 'column',
      gap: '20px',
      padding: '20px',
      textAlign: 'center'
    }}>
      <h1 style={{ color: '#7A1FFF', fontSize: '2.5rem' }}>🎉 Pagamento Realizado!</h1>
      <p style={{ fontSize: '1.2rem', color: '#333' }}>
        Obrigado por escolher a UP! Seu plano foi ativado com sucesso.
      </p>
      <p style={{ color: '#666' }}>
        Em breve entraremos em contato via WhatsApp para iniciar o trabalho.
      </p>
      <button 
        onClick={() => window.location.href = '/'}
        style={{
          background: 'linear-gradient(140deg, #8f67ff, #a718c4)',
          color: 'white',
          border: 'none',
          padding: '12px 24px',
          borderRadius: '8px',
          fontSize: '1rem',
          cursor: 'pointer'
        }}
      >
        Voltar ao Início
      </button>
    </div>
  );
}