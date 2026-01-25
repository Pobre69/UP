export default function Cancelado() {
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
      <h1 style={{ color: '#ff6b6b', fontSize: '2.5rem' }}>❌ Pagamento Cancelado</h1>
      <p style={{ fontSize: '1.2rem', color: '#333' }}>
        Não se preocupe! Você pode tentar novamente quando quiser.
      </p>
      <p style={{ color: '#666' }}>
        Estamos aqui para ajudar caso tenha alguma dúvida.
      </p>
      <div style={{ display: 'flex', gap: '16px', flexWrap: 'wrap', justifyContent: 'center' }}>
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
        <a 
          href="https://wa.me/5511999999999?text=Olá,%20tive%20problemas%20com%20o%20pagamento"
          target="_blank"
          rel="noopener noreferrer"
          style={{
            background: '#25D366',
            color: 'white',
            border: 'none',
            padding: '12px 24px',
            borderRadius: '8px',
            fontSize: '1rem',
            textDecoration: 'none',
            display: 'inline-block'
          }}
        >
          Falar no WhatsApp
        </a>
      </div>
    </div>
  );
}