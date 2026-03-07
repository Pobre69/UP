import { API_BASE_URL } from '../config/api';

export interface DashboardStats {
  seguidores: { value: number; delta: number };
  cliquesPerfil: { value: number; delta: number };
  alcanceTotal: { value: number; delta: number };
  impressoes: { value: number; delta: number };
  engajamento: { value: number; delta: number };
}

export interface DashboardData {
  user: { handle: string; email: string };
  profileGrowthPct: number;
  stats: DashboardStats;
  seguidoresSerie: number[];
  alcanceSerie: number[];
  chartDates: string[];
  engajamentoResumo: {
    curtidasMedia: number;
    comentariosMedios: number;
    compartilhamentos: number;
    alcanceMedio: number;
    melhorStory: number;
  };
}

export const dashboardService = {
  async getDashboardData(): Promise<DashboardData> {
    try {
      const response = await fetch(`${API_BASE_URL}/api/dashboard`, {
        method: 'GET',
        credentials: 'include',
        headers: { 'Content-Type': 'application/json' }
      });

      if (!response.ok) {
        const errorText = await response.text();
        console.error('API Error:', response.status, errorText);
        throw new Error(`Erro ${response.status}: ${errorText}`);
      }
      
      const result = await response.json();
      return result.data;
    } catch (error) {
      console.error('Fetch error:', error);
      throw error;
    }
  }
};
