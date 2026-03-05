import { API_BASE_URL } from '../config/api';

export const instagramService = {
  async connectAccount(accessToken: string) {
    const response = await fetch(`${API_BASE_URL}/api/instagram/connect`, {
      method: 'POST',
      credentials: 'include',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ access_token: accessToken })
    });

    if (!response.ok) throw new Error('Erro ao conectar conta do Instagram');
    return response.json();
  },

  async disconnectAccount() {
    const response = await fetch(`${API_BASE_URL}/api/instagram/disconnect`, {
      method: 'POST',
      credentials: 'include',
      headers: { 'Content-Type': 'application/json' }
    });

    if (!response.ok) throw new Error('Erro ao desconectar conta do Instagram');
    return response.json();
  },

  async getConnectionStatus() {
    const response = await fetch(`${API_BASE_URL}/api/instagram/status`, {
      method: 'GET',
      credentials: 'include',
      headers: { 'Content-Type': 'application/json' }
    });

    if (!response.ok) throw new Error('Erro ao verificar status da conexão');
    return response.json();
  },

  async getMetrics() {
    const response = await fetch(`${API_BASE_URL}/api/instagram/metrics`, {
      method: 'GET',
      credentials: 'include',
      headers: { 'Content-Type': 'application/json' }
    });

    if (!response.ok) throw new Error('Erro ao buscar métricas do Instagram');
    return response.json();
  },

  async getPosts() {
    const response = await fetch(`${API_BASE_URL}/api/instagram/posts`, {
      method: 'GET',
      credentials: 'include',
      headers: { 'Content-Type': 'application/json' }
    });

    if (!response.ok) throw new Error('Erro ao buscar posts do Instagram');
    return response.json();
  },

  async getMetricsHistory() {
    const response = await fetch(`${API_BASE_URL}/api/instagram/metrics/history`, {
      method: 'GET',
      credentials: 'include',
      headers: { 'Content-Type': 'application/json' }
    });

    if (!response.ok) throw new Error('Erro ao buscar histórico de métricas');
    return response.json();
  },

  async refreshToken() {
    const response = await fetch(`${API_BASE_URL}/api/instagram/refresh`, {
      method: 'POST',
      credentials: 'include',
      headers: { 'Content-Type': 'application/json' }
    });

    if (!response.ok) throw new Error('Erro ao renovar token');
    return response.json();
  }
};
