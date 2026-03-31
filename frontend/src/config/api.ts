const trimTrailingSlash = (value: string): string => value.replace(/\/+$/, "");

const getApiBaseUrl = (): string => {
  const envApiUrl = (import.meta.env.VITE_API_URL as string | undefined)?.trim();
  if (envApiUrl) {
    return trimTrailingSlash(envApiUrl);
  }

  const runtimeApiUrl = (window as Window & { __UP_API_URL__?: string }).__UP_API_URL__?.trim();
  if (runtimeApiUrl) {
    return trimTrailingSlash(runtimeApiUrl);
  }

  const { origin, pathname } = window.location;

  if (import.meta.env.DEV) {
    return "http://localhost/Sites/UP/backend/public/index.php";
  }

  if (pathname.includes('/backend/public/')) {
    return `${origin}/backend/public/index.php`;
  }

  return `${origin}/backend/public/index.php`;
};

export const API_BASE_URL = getApiBaseUrl();

export const API_CONFIG = {
  timeout: 30000,
  retryAttempts: 3,
  retryDelay: 1000,
} as const;

const isRetryableError = (error: unknown): boolean => {
  return error instanceof Error && (
    error.name === 'AbortError' ||
    error.message.includes('Failed to fetch') ||
    error.message.includes('NetworkError') ||
    error.message.includes('timeout')
  );
};

export async function fetchWithRetry(
  url: string,
  options: RequestInit = {},
  retries: number = API_CONFIG.retryAttempts
): Promise<Response> {
  const controller = new AbortController();
  const timeoutId = window.setTimeout(() => controller.abort(), API_CONFIG.timeout);

  try {
    const response = await fetch(url, {
      ...options,
      credentials: options.credentials ?? 'include',
      signal: controller.signal,
    });

    return response;
  } catch (error) {
    if (retries > 0 && isRetryableError(error)) {
      await new Promise((resolve) => setTimeout(resolve, API_CONFIG.retryDelay));
      return fetchWithRetry(url, options, retries - 1);
    }
    throw error;
  } finally {
    window.clearTimeout(timeoutId);
  }
}
