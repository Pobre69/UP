// Detectar automaticamente a URL base da API
const getApiBaseUrl = (): string => {
  const envApiUrl = import.meta.env.VITE_API_URL as string | undefined;

  if (envApiUrl) {
    return envApiUrl;
  }

  if (import.meta.env.DEV) {
    return "http://localhost/Sites/UP/backend/public/index.php";
  }

  return `${window.location.protocol}//${window.location.host}/api`;
};

export const API_BASE_URL = getApiBaseUrl();

// Configurações de timeout e retry
export const API_CONFIG = {
  timeout: 30000, // 30 segundos
  retryAttempts: 3,
  retryDelay: 1000, // 1 segundo
};

const isRetryableError = (error: unknown): boolean => {
  return error instanceof Error && (
    error.name === "AbortError" ||
    error.message.includes("Failed to fetch") ||
    error.message.includes("NetworkError")
  );
};

// Função auxiliar para fazer requisições com retry
export async function fetchWithRetry(
  url: string,
  options: RequestInit = {},
  retries: number = API_CONFIG.retryAttempts
): Promise<Response> {
  try {
    const controller = new AbortController();
    const timeoutId = setTimeout(() => controller.abort(), API_CONFIG.timeout);

    const response = await fetch(url, {
      ...options,
      signal: controller.signal,
    });

    clearTimeout(timeoutId);
    return response;
  } catch (error) {
    if (retries > 0 && isRetryableError(error)) {
      await new Promise((resolve) => setTimeout(resolve, API_CONFIG.retryDelay));
      return fetchWithRetry(url, options, retries - 1);
    }
    throw error;
  }
}
