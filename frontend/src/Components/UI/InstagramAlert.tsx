import { Instagram, AlertCircle, RefreshCw } from "lucide-react";
import "./InstagramAlert.css";

interface InstagramAlertProps {
  onConnect: () => void;
  onChangeAccount?: () => void;
  hasAccount?: boolean;
}

export function InstagramAlert({ onConnect, onChangeAccount, hasAccount = false }: InstagramAlertProps) {
  return (
    <div className="instagramAlert">
      <div className="alertIcon">
        <AlertCircle size={24} />
      </div>
      <div className="alertContent">
        <h3 className="alertTitle">
          <Instagram size={20} />
          {hasAccount ? 'Erro ao buscar dados do Instagram' : 'Instagram não conectado'}
        </h3>
        <p className="alertText">
          {hasAccount 
            ? 'Não conseguimos buscar seus dados. O token pode ter expirado ou a conta foi desconectada.'
            : 'Conecte sua conta do Instagram para visualizar métricas reais.'}
        </p>
      </div>
      <div className="alertButtons">
        {hasAccount && onChangeAccount && (
          <button className="alertButtonSecondary" onClick={onChangeAccount}>
            <RefreshCw size={16} />
            Alterar Conta
          </button>
        )}
        <button className="alertButton" onClick={onConnect}>
          {hasAccount ? 'Reconectar' : 'Conectar Instagram'}
        </button>
      </div>
    </div>
  );
}
