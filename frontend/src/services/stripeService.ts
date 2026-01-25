const API_URL = 'http://localhost:3001';

export const stripeService = {
  async createCheckoutSession(planoId: string) {
    try {
      const response = await fetch(`${API_URL}/create-checkout-session`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({ planoId }),
      });

      if (!response.ok) {
        throw new Error('Erro ao criar sessão de pagamento');
      }

      const data = await response.json();
      return data;
    } catch (error) {
      console.error('Erro no serviço Stripe:', error);
      throw error;
    }
  },

  async getCheckoutSession(sessionId: string) {
    try {
      const response = await fetch(`${API_URL}/checkout-session/${sessionId}`);
      
      if (!response.ok) {
        throw new Error('Erro ao buscar sessão');
      }

      const data = await response.json();
      return data;
    } catch (error) {
      console.error('Erro ao buscar sessão:', error);
      throw error;
    }
  }
};