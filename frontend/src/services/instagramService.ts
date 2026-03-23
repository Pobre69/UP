import { API_BASE_URL, fetchWithRetry } from '../config/api';

export const instagramService = {
  async connectAccount(accessToken: string) {
    try {
      if (!accessToken || accessToken.trim() === '') {
        throw new Error('Access token é obrigatório');
      }

      const response = await fetchWithRetry(
        `${API_BASE_URL}/api/instagram/connect`,
        {
          method: 'POST',
          credentials: 'include',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ access_token: accessToken.trim() })
        }
      );

      const data = await response.json();

      if (!response.ok) {
        throw new Error(data.mensagem || 'Erro ao conectar conta do Instagram');
      }

      if (!data.success) {
        throw new Error(data.mensagem || 'Erro ao conectar conta do Instagram');
      }

      return data;
    } catch (error: any) {
      if (error.message.includes('JSON')) {
        throw new Error(
          'Erro de comunicação com o servidor.\n\nVerifique se o backend está rodando corretamente.'
        );
      }
      if (error.message.includes('timeout')) {
        throw new Error('Tempo limite de conexão excedido. Tente novamente.');
      }
      throw error;
    }
  },

  async disconnectAccount() {
    try {
      const response = await fetchWithRetry(
        `${API_BASE_URL}/api/instagram/disconnect`,
        {
          method: 'POST',
          credentials: 'include',
          headers: { 'Content-Type': 'application/json' }
        }
      );

      const data = await response.json();

      if (!response.ok) {
        throw new Error(data.mensagem || 'Erro ao desconectar conta do Instagram');
      }

      if (!data.success) {
        throw new Error(data.mensagem || 'Erro ao desconectar conta do Instagram');
      }

      return data;
    } catch (error: any) {
      if (error.message.includes('JSON')) {
        throw new Error(
          'Erro de comunicação com o servidor.\n\nVerifique se o backend está rodando corretamente.'
        );
      }
      if (error.message.includes('timeout')) {
        throw new Error('Tempo limite de conexão excedido. Tente novamente.');
      }
      throw error;
    }
  },

  async getConnectionStatus() {
    try {
      const response = await fetchWithRetry(
        `${API_BASE_URL}/api/instagram/status`,
        {
          method: 'GET',
          credentials: 'include',
          headers: { 'Content-Type': 'application/json' }
        }
      );

      const data = await response.json();

      if (!response.ok) {
        if (response.status === 401) {
          throw new Error('Sessão expirada');
        }
        throw new Error(data.mensagem || 'Erro ao verificar status da conexão');
      }

      if (!data.success) {
        throw new Error(data.mensagem || 'Erro ao verificar status da conexão');
      }

      return data;
    } catch (error: any) {
      if (error.message.includes('timeout')) {
        throw new Error('Tempo limite de conexão excedido. Tente novamente.');
      }
      throw error;
    }
  },

  async getMetrics() {
    try {
      const response = await fetchWithRetry(
        `${API_BASE_URL}/api/instagram/metrics`,
        {
          method: 'GET',
          credentials: 'include',
          headers: { 'Content-Type': 'application/json' }
        }
      );

      const data = await response.json();

      if (!response.ok) {
        if (response.status === 401) {
          throw new Error('Sessão expirada');
        }
        throw new Error(data.mensagem || 'Erro ao buscar métricas do Instagram');
      }

      if (!data.success) {
        throw new Error(data.mensagem || 'Erro ao buscar métricas do Instagram');
      }

      return data;
    } catch (error: any) {
      if (error.message.includes('timeout')) {
        throw new Error('Tempo limite de conexão excedido. Tente novamente.');
      }
      throw error;
    }
  },

  async getPosts() {
    try {
      const response = await fetchWithRetry(
        `${API_BASE_URL}/api/instagram/posts`,
        {
          method: 'GET',
          credentials: 'include',
          headers: { 'Content-Type': 'application/json' }
        }
      );

      const data = await response.json();

      if (!response.ok) {
        if (response.status === 401) {
          throw new Error('Sessão expirada');
        }
        throw new Error(data.mensagem || 'Erro ao buscar posts do Instagram');
      }

      if (!data.success) {
        throw new Error(data.mensagem || 'Erro ao buscar posts do Instagram');
      }

      return data;
    } catch (error: any) {
      if (error.message.includes('timeout')) {
        throw new Error('Tempo limite de conexão excedido. Tente novamente.');
      }
      throw error;
    }
  },

  async getMetricsHistory() {
    try {
      const response = await fetchWithRetry(
        `${API_BASE_URL}/api/instagram/metrics/history`,
        {
          method: 'GET',
          credentials: 'include',
          headers: { 'Content-Type': 'application/json' }
        }
      );

      const data = await response.json();

      if (!response.ok) {
        if (response.status === 401) {
          throw new Error('Sessão expirada');
        }
        throw new Error(data.mensagem || 'Erro ao buscar histórico de métricas');
      }

      if (!data.success) {
        throw new Error(data.mensagem || 'Erro ao buscar histórico de métricas');
      }

      return data;
    } catch (error: any) {
      if (error.message.includes('timeout')) {
        throw new Error('Tempo limite de conexão excedido. Tente novamente.');
      }
      throw error;
    }
  },

  async refreshToken() {
    try {
      const response = await fetchWithRetry(
        `${API_BASE_URL}/api/instagram/refresh`,
        {
          method: 'POST',
          credentials: 'include',
          headers: { 'Content-Type': 'application/json' }
        }
      );

      const data = await response.json();

      if (!response.ok) {
        if (response.status === 401) {
          throw new Error('Sessão expirada');
        }
        throw new Error(data.mensagem || 'Erro ao renovar token');
      }

      if (!data.success) {
        throw new Error(data.mensagem || 'Erro ao renovar token');
      }

      return data;
    } catch (error: any) {
      if (error.message.includes('timeout')) {
        throw new Error('Tempo limite de conexão excedido. Tente novamente.');
      }
      throw error;
    }
  }
};
