import { API_BASE_URL } from '../config/api';

export const instagramService = {
  async connectAccount(accessToken: string) {
    try {
      const response = await fetch(`${API_BASE_URL}/api/instagram/connect`, {
        method: 'POST',
        credentials: 'include',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ access_token: accessToken })
      });

      const data = await response.json();
      
      if (!response.ok) {
        throw new Error(data.mensagem || 'Erro ao conectar conta do Instagram');
      }
      
      return data;
    } catch (error: any) {
      if (error.message.includes('JSON')) {
        throw new Error('Erro de comunicação com o servidor.\n\nVerifique se o backend está rodando corretamente.');
      }
      throw error;
    }
  },

  async disconnectAccount() {
    try {
      const response = await fetch(`${API_BASE_URL}/api/instagram/disconnect`, {
        method: 'POST',
        credentials: 'include',
        headers: { 'Content-Type': 'application/json' }
      });

      const data = await response.json();
      
      if (!response.ok) {
        throw new Error(data.mensagem || 'Erro ao desconectar conta do Instagram');
      }
      
      return data;
    } catch (error: any) {
      if (error.message.includes('JSON')) {
        throw new Error('Erro de comunicação com o servidor.\n\nVerifique se o backend está rodando corretamente.');
      }
      throw error;
    }
  },

  async getConnectionStatus() {
    const response = await fetch(`${API_BASE_URL}/api/instagram/status`, {
      method: 'GET',
      credentials: 'include',
      headers: { 'Content-Type': 'application/json' }
    });

    const data = await response.json();
    
    if (!response.ok) {
      throw new Error(data.mensagem || 'Erro ao verificar status da conexão');
    }
    
    return data;
  },

  async getMetrics() {
    const response = await fetch(`${API_BASE_URL}/api/instagram/metrics`, {
      method: 'GET',
      credentials: 'include',
      headers: { 'Content-Type': 'application/json' }
    });

    const data = await response.json();
    
    if (!response.ok) {
      throw new Error(data.mensagem || 'Erro ao buscar métricas do Instagram');
    }
    
    return data;
  },

  async getPosts() {
    const response = await fetch(`${API_BASE_URL}/api/instagram/posts`, {
      method: 'GET',
      credentials: 'include',
      headers: { 'Content-Type': 'application/json' }
    });

    const data = await response.json();
    
    if (!response.ok) {
      throw new Error(data.mensagem || 'Erro ao buscar posts do Instagram');
    }
    
    return data;
  },

  async getMetricsHistory() {
    const response = await fetch(`${API_BASE_URL}/api/instagram/metrics/history`, {
      method: 'GET',
      credentials: 'include',
      headers: { 'Content-Type': 'application/json' }
    });

    const data = await response.json();
    
    if (!response.ok) {
      throw new Error(data.mensagem || 'Erro ao buscar histórico de métricas');
    }
    
    return data;
  },

  async refreshToken() {
    const response = await fetch(`${API_BASE_URL}/api/instagram/refresh`, {
      method: 'POST',
      credentials: 'include',
      headers: { 'Content-Type': 'application/json' }
    });

    const data = await response.json();
    
    if (!response.ok) {
      throw new Error(data.mensagem || 'Erro ao renovar token');
    }
    
    return data;
  }
};
