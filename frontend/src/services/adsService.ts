import { API_BASE_URL } from '../config/api';

type CampaignPayload = Record<string, unknown>;

export const adsService = {
  async getAdsData() {
    const response = await fetch(`${API_BASE_URL}/api/ads`, {
      method: 'GET',
      credentials: 'include',
      headers: { 'Content-Type': 'application/json' }
    });

    if (!response.ok) throw new Error('Erro ao buscar dados de anúncios');
    return response.json();
  },

  async createCampaign(data: CampaignPayload) {
    const response = await fetch(`${API_BASE_URL}/api/ads/create`, {
      method: 'POST',
      credentials: 'include',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(data)
    });

    if (!response.ok) throw new Error('Erro ao criar campanha');
    return response.json();
  },

  async updateCampaign(data: CampaignPayload) {
    const response = await fetch(`${API_BASE_URL}/api/ads/update`, {
      method: 'POST',
      credentials: 'include',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(data)
    });

    if (!response.ok) throw new Error('Erro ao atualizar campanha');
    return response.json();
  }
};
