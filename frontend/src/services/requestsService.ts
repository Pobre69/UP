import { API_BASE_URL } from '../config/api';

type RequestPayload = Record<string, unknown>;

export const requestsService = {
  async getRequests() {
    const response = await fetch(`${API_BASE_URL}/api/requests`, {
      method: 'GET',
      credentials: 'include',
      headers: { 'Content-Type': 'application/json' }
    });

    if (!response.ok) throw new Error('Erro ao buscar requisições');
    return response.json();
  },

  async createRequest(data: RequestPayload) {
    const response = await fetch(`${API_BASE_URL}/api/requests/create`, {
      method: 'POST',
      credentials: 'include',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(data)
    });

    if (!response.ok) throw new Error('Erro ao criar requisição');
    return response.json();
  }
};
