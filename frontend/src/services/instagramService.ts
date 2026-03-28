import { API_BASE_URL, fetchWithRetry } from '../config/api';

type InstagramApiResponse = {
  success?: boolean;
  mensagem?: string;
  data?: unknown;
  [key: string]: unknown;
};

const getErrorMessage = (error: unknown): string => {
  return error instanceof Error ? error.message : 'Erro desconhecido';
};

const normalizeInstagramError = (error: unknown): never => {
  const message = getErrorMessage(error);

  if (message.includes('JSON')) {
    throw new Error('Erro de comunicação com o servidor.\n\nVerifique se o backend está rodando corretamente.');
  }

  if (message.includes('timeout')) {
    throw new Error('Tempo limite de conexão excedido. Tente novamente.');
  }

  throw (error instanceof Error ? error : new Error(message));
};

const parseResponse = async (response: Response, defaultMessage: string): Promise<InstagramApiResponse> => {
  const data = await response.json() as InstagramApiResponse;

  if (!response.ok) {
    if (response.status === 401) {
      throw new Error('Sessão expirada');
    }

    throw new Error(data.mensagem || defaultMessage);
  }

  if (!data.success) {
    throw new Error(data.mensagem || defaultMessage);
  }

  return data;
};

export const instagramService = {
  async connectAccount(accessToken: string) {
    try {
      if (!accessToken || accessToken.trim() === '') {
        throw new Error('Access token é obrigatório');
      }

      const response = await fetchWithRetry(`${API_BASE_URL}/api/instagram/connect`, {
        method: 'POST',
        credentials: 'include',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ access_token: accessToken.trim() })
      });

      return parseResponse(response, 'Erro ao conectar conta do Instagram');
    } catch (error: unknown) {
      return normalizeInstagramError(error);
    }
  },

  async disconnectAccount() {
    try {
      const response = await fetchWithRetry(`${API_BASE_URL}/api/instagram/disconnect`, {
        method: 'POST',
        credentials: 'include',
        headers: { 'Content-Type': 'application/json' }
      });

      return parseResponse(response, 'Erro ao desconectar conta do Instagram');
    } catch (error: unknown) {
      return normalizeInstagramError(error);
    }
  },

  async getConnectionStatus() {
    try {
      const response = await fetchWithRetry(`${API_BASE_URL}/api/instagram/status`, {
        method: 'GET',
        credentials: 'include',
        headers: { 'Content-Type': 'application/json' }
      });

      return parseResponse(response, 'Erro ao verificar status da conexão');
    } catch (error: unknown) {
      return normalizeInstagramError(error);
    }
  },

  async getMetrics() {
    try {
      const response = await fetchWithRetry(`${API_BASE_URL}/api/instagram/metrics`, {
        method: 'GET',
        credentials: 'include',
        headers: { 'Content-Type': 'application/json' }
      });

      return parseResponse(response, 'Erro ao buscar métricas do Instagram');
    } catch (error: unknown) {
      return normalizeInstagramError(error);
    }
  },

  async getPosts() {
    try {
      const response = await fetchWithRetry(`${API_BASE_URL}/api/instagram/posts`, {
        method: 'GET',
        credentials: 'include',
        headers: { 'Content-Type': 'application/json' }
      });

      return parseResponse(response, 'Erro ao buscar posts do Instagram');
    } catch (error: unknown) {
      return normalizeInstagramError(error);
    }
  },

  async getMetricsHistory() {
    try {
      const response = await fetchWithRetry(`${API_BASE_URL}/api/instagram/metrics/history`, {
        method: 'GET',
        credentials: 'include',
        headers: { 'Content-Type': 'application/json' }
      });

      return parseResponse(response, 'Erro ao buscar histórico de métricas');
    } catch (error: unknown) {
      return normalizeInstagramError(error);
    }
  },

  async refreshToken() {
    try {
      const response = await fetchWithRetry(`${API_BASE_URL}/api/instagram/refresh`, {
        method: 'POST',
        credentials: 'include',
        headers: { 'Content-Type': 'application/json' }
      });

      return parseResponse(response, 'Erro ao renovar token');
    } catch (error: unknown) {
      return normalizeInstagramError(error);
    }
  }
};
