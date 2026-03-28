import { AlertCircle } from "lucide-react";
import "./ErrorModal.css";

interface ErrorModalProps {
  isOpen: boolean;
  title: string;
  message: string;
  onClose: () => void;
  onRetry?: () => void;
}

export function ErrorModal({ isOpen, title, message, onClose, onRetry }: ErrorModalProps) {
  if (!isOpen) return null;

  return (
    <div className="errorModalOverlay" onClick={onClose}>
      <div className="errorModalContent" onClick={(e) => e.stopPropagation()}>
        <div className="errorModalHeader">
          <div className="errorIcon">
            <AlertCircle size={32} />
          </div>
          <h2>{title}</h2>
          <p>{message}</p>
        </div>

        <div className="errorModalActions">
          {onRetry && (
            <button className="errorModalButton primary" onClick={onRetry}>
              Tentar Novamente
            </button>
          )}
          <button 
            className={`errorModalButton ${onRetry ? 'secondary' : 'primary'}`} 
            onClick={onClose}
          >
            {onRetry ? 'Cancelar' : 'Entendi'}
          </button>
        </div>
      </div>
    </div>
  );
}
