import { API_BASE_URL } from '../config/api';

export const serviceStatusService = {
  async getServiceStatus() {
    const response = await fetch(`${API_BASE_URL}/api/service-status`, {
      method: 'GET',
      credentials: 'include',
      headers: { 'Content-Type': 'application/json' }
    });

    if (!response.ok) throw new Error('Erro ao buscar status do serviço');
    return response.json();
  },

  async createContent(data: any) {
    const response = await fetch(`${API_BASE_URL}/api/service-status/create`, {
      method: 'POST',
      credentials: 'include',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(data)
    });

    if (!response.ok) throw new Error('Erro ao criar conteúdo');
    return response.json();
  },

  async updateContentStatus(data: any) {
    const response = await fetch(`${API_BASE_URL}/api/service-status/update`, {
      method: 'POST',
      credentials: 'include',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(data)
    });

    if (!response.ok) throw new Error('Erro ao atualizar status');
    return response.json();
  }
};
