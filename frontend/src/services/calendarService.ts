import { API_BASE_URL } from '../config/api';

type SchedulePostPayload = Record<string, unknown>;

export const calendarService = {
  async getCalendarData(year?: number, month?: number) {
    const params = new URLSearchParams();
    if (year) params.set("year", String(year));
    if (month) params.set("month", String(month));
    const query = params.toString();
    const response = await fetch(`${API_BASE_URL}/api/calendar${query ? `?${query}` : ""}`, {
      method: 'GET',
      credentials: 'include',
      headers: { 'Content-Type': 'application/json' }
    });

    if (!response.ok) throw new Error('Erro ao buscar dados do calendário');
    return response.json();
  },

  async schedulePost(data: SchedulePostPayload) {
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
