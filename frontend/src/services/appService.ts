import { API_BASE_URL } from '../config/api';

export interface AppSyncResponse {
  success: boolean;
  data?: {
    synced: boolean;
    connected: boolean;
    metricsUpdated: boolean;
    postsUpdated: boolean;
    metricsCount?: number;
    postsCount?: number;
    message?: string;
  };
  mensagem?: string;
}

export const appService = {
  async syncLatestData(): Promise<AppSyncResponse> {
    const response = await fetch(`${API_BASE_URL}/api/app/sync`, {
      method: 'POST',
      credentials: 'include',
      headers: { 'Content-Type': 'application/json' },
    });

    const result = await response.json().catch(() => ({ success: false, mensagem: 'Resposta inválida do servidor' }));
    if (!response.ok) {
      throw new Error(result.mensagem || 'Erro ao sincronizar dados da conta');
    }

    return result as AppSyncResponse;
  }
};
