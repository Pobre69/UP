import { API_BASE_URL } from '../config/api';

export const planService = {
  async getPlanData() {
    const response = await fetch(`${API_BASE_URL}/api/plan`, {
      method: 'GET',
      credentials: 'include',
      headers: { 'Content-Type': 'application/json' }
    });

    if (!response.ok) throw new Error('Erro ao buscar dados do plano');
    return response.json();
  }
};
