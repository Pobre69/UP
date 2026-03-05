import { useState } from "react";
import { Instagram, X, Trash2 } from "lucide-react";
import "./InstagramConnectModal.css";

interface InstagramConnectModalProps {
  isOpen: boolean;
  onClose: () => void;
  onConnect: (accessToken: string) => void;
  onDisconnect?: () => void;
  currentUsername?: string | null;
}

export function InstagramConnectModal({ isOpen, onClose, onConnect, onDisconnect, currentUsername }: InstagramConnectModalProps) {
  const [accessToken, setAccessToken] = useState("");
  const [loading, setLoading] = useState(false);

  if (!isOpen) return null;

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setLoading(true);
    try {
      await onConnect(accessToken);
      onClose();
    } catch (error) {
      console.error(error);
    } finally {
      setLoading(false);
    }
  };

  const handleDisconnect = async () => {
    if (!onDisconnect) return;
    if (!confirm('Tem certeza que deseja desconectar sua conta do Instagram?')) return;
    
    setLoading(true);
    try {
      await onDisconnect();
      onClose();
    } catch (error) {
      console.error(error);
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="modalOverlay" onClick={onClose}>
      <div className="modalContent" onClick={(e) => e.stopPropagation()}>
        <button className="modalClose" onClick={onClose}>
          <X size={20} />
        </button>
        
        <div className="modalHeader">
          <Instagram size={32} />
          <h2>{currentUsername ? 'Alterar Conta do Instagram' : 'Conectar Instagram'}</h2>
          {currentUsername && (
            <p className="currentAccount">Conta atual: <strong>@{currentUsername}</strong></p>
          )}
          <p>Cole seu token de acesso do Instagram para {currentUsername ? 'alterar' : 'conectar'} sua conta</p>
        </div>

        <form onSubmit={handleSubmit} className="modalForm">
          <div className="formGroup">
            <label htmlFor="accessToken">Access Token</label>
            <input
              id="accessToken"
              type="text"
              value={accessToken}
              onChange={(e) => setAccessToken(e.target.value)}
              placeholder="Cole seu access token aqui"
              required
            />
            <small>
              <a href="https://developers.facebook.com/docs/instagram-basic-display-api/getting-started" target="_blank" rel="noopener noreferrer">
                Como obter meu token?
              </a>
            </small>
          </div>

          <div className="modalActions">
            {currentUsername && onDisconnect && (
              <button 
                type="button" 
                className="disconnectButton" 
                onClick={handleDisconnect}
                disabled={loading}
              >
                <Trash2 size={16} />
                Desconectar Conta
              </button>
            )}
            <button type="submit" className="submitButton" disabled={loading}>
              {loading ? "Processando..." : currentUsername ? "Alterar Conta" : "Conectar Conta"}
            </button>
          </div>
        </form>
      </div>
    </div>
  );
}
