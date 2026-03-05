import { API_BASE_URL } from '../config/api';

export const reportsService = {
  async getReportsData() {
    const response = await fetch(`${API_BASE_URL}/api/reports`, {
      method: 'GET',
      credentials: 'include',
      headers: { 'Content-Type': 'application/json' }
    });

    if (!response.ok) throw new Error('Erro ao buscar relatórios');
    return response.json();
  },

  async exportReport(format: string) {
    const response = await fetch(`${API_BASE_URL}/api/reports/export?format=${format}`, {
      method: 'GET',
      credentials: 'include'
    });

    if (!response.ok) throw new Error('Erro ao exportar relatório');
    return response.blob();
  }
};
