import { API_BASE_URL, fetchWithRetry } from '../config/api';

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
      const response = await fetchWithRetry(
        `${API_BASE_URL}/api/dashboard`,
        {
          method: 'GET',
          credentials: 'include',
          headers: { 'Content-Type': 'application/json' }
        }
      );

      if (!response.ok) {
        const errorText = await response.text();
        console.error('API Error:', response.status, errorText);
        
        if (response.status === 401) {
          throw new Error('Sessão expirada. Por favor, faça login novamente.');
        }
        
        throw new Error(`Erro ${response.status}: ${errorText}`);
      }

      const result = await response.json();
      
      // Validar estrutura da resposta
      if (!result.success || !result.data) {
        throw new Error('Resposta inválida do servidor');
      }

      // Validar dados obrigatórios
      const data = result.data;
      if (!data.user || !data.stats) {
        throw new Error('Dados incompletos recebidos do servidor');
      }

      // Garantir que os valores numéricos são números
      return {
        user: data.user,
        profileGrowthPct: Number(data.profileGrowthPct) || 0,
        stats: {
          seguidores: {
            value: Number(data.stats.seguidores?.value) || 0,
            delta: Number(data.stats.seguidores?.delta) || 0
          },
          cliquesPerfil: {
            value: Number(data.stats.cliquesPerfil?.value) || 0,
            delta: Number(data.stats.cliquesPerfil?.delta) || 0
          },
          alcanceTotal: {
            value: Number(data.stats.alcanceTotal?.value) || 0,
            delta: Number(data.stats.alcanceTotal?.delta) || 0
          },
          impressoes: {
            value: Number(data.stats.impressoes?.value) || 0,
            delta: Number(data.stats.impressoes?.delta) || 0
          },
          engajamento: {
            value: Number(data.stats.engajamento?.value) || 0,
            delta: Number(data.stats.engajamento?.delta) || 0
          }
        },
        seguidoresSerie: Array.isArray(data.seguidoresSerie) ? data.seguidoresSerie.map(Number) : [],
        alcanceSerie: Array.isArray(data.alcanceSerie) ? data.alcanceSerie.map(Number) : [],
        chartDates: Array.isArray(data.chartDates) ? data.chartDates : [],
        engajamentoResumo: {
          curtidasMedia: Number(data.engajamentoResumo?.curtidasMedia) || 0,
          comentariosMedios: Number(data.engajamentoResumo?.comentariosMedios) || 0,
          compartilhamentos: Number(data.engajamentoResumo?.compartilhamentos) || 0,
          alcanceMedio: Number(data.engajamentoResumo?.alcanceMedio) || 0,
          melhorStory: Number(data.engajamentoResumo?.melhorStory) || 0
        }
      };
    } catch (error) {
      console.error('Fetch error:', error);
      throw error;
    }
  }
};
