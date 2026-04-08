import { API_BASE_URL } from '../config/api';

export interface ContactPayload {
  name: string;
  email: string;
  subject: string;
  message: string;
}

export const contactService = {
  async send(data: ContactPayload): Promise<void> {
    const response = await fetch(`${API_BASE_URL}/api/requests/create`, {
      method: 'POST',
      credentials: 'include',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        titulo: `[${data.subject}] — ${data.name} <${data.email}>`,
        tipo: 'Feedback',
        texto: data.message,
      }),
    });

    if (!response.ok) throw new Error('Erro ao enviar mensagem');
  },
};
