import { API_BASE_URL } from '../config/api';

export const calendarService = {
  async getCalendarData() {
    const response = await fetch(`${API_BASE_URL}/api/calendar`, {
      method: 'GET',
      credentials: 'include',
      headers: { 'Content-Type': 'application/json' }
    });

    if (!response.ok) throw new Error('Erro ao buscar dados do calendário');
    return response.json();
  },

  async schedulePost(data: any) {
    const response = await fetch(`${API_BASE_URL}/api/calendar/schedule`, {
      method: 'POST',
      credentials: 'include',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(data)
    });

    if (!response.ok) throw new Error('Erro ao agendar post');
    return response.json();
  }
};
