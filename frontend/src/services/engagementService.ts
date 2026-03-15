import { API_BASE_URL } from '../config/api';

export const engagementService = {
  async getEngagementData() {
    const response = await fetch(`${API_BASE_URL}/api/engagement`, {
      method: 'GET',
      credentials: 'include',
      headers: { 'Content-Type': 'application/json' }
    });

    if (!response.ok) throw new Error('Erro ao buscar dados de engajamento');
    return response.json();
  }
};
