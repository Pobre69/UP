import legacyConfig from "../config.json";

const trimTrailingSlash = (value: string): string => value.replace(/\/+$/, "");

const isUsableUrl = (value?: string | null): value is string => Boolean(value && value.trim());

const LOCAL_HOSTS = new Set(["localhost", "127.0.0.1"]);

const isLocalHost = (hostname: string): boolean => LOCAL_HOSTS.has(hostname);

const normalizeCandidate = (value: string): string => {
  const trimmed = value.trim();
  if (!trimmed) {
    return "";
  }

  if (/^https?:\/\//i.test(trimmed)) {
    return trimTrailingSlash(trimmed);
  }

  if (trimmed.startsWith("/")) {
    return trimTrailingSlash(`${window.location.origin}${trimmed}`);
  }

  return trimTrailingSlash(`${window.location.origin}/${trimmed.replace(/^\/+/, "")}`);
};

const alignLocalHost = (candidate: string): string => {
  try {
    const parsed = new URL(candidate);
    const current = window.location;

    if (!isLocalHost(parsed.hostname) || !isLocalHost(current.hostname)) {
      return trimTrailingSlash(parsed.toString());
    }

    parsed.protocol = current.protocol;
    parsed.hostname = current.hostname;

    if (current.port && parsed.port === current.port) {
      parsed.port = "";
    }

    return trimTrailingSlash(parsed.toString());
  } catch {
    return trimTrailingSlash(candidate);
  }
};

const fromCandidate = (value?: string | null): string | null => {
  if (!isUsableUrl(value)) {
    return null;
  }

  const normalized = normalizeCandidate(value);
  if (!normalized) {
    return null;
  }

  return alignLocalHost(normalized);
};

const getApiBaseUrl = (): string => {
  const envApiUrl = fromCandidate(import.meta.env.VITE_API_URL as string | undefined);
  if (envApiUrl) {
    return envApiUrl;
  }

  const runtimeApiUrl = fromCandidate((window as Window & { __UP_API_URL__?: string }).__UP_API_URL__);
  if (runtimeApiUrl) {
    return runtimeApiUrl;
  }

  const legacyApiUrl = fromCandidate(legacyConfig.backRoute as string | undefined);
  if (legacyApiUrl) {
    return legacyApiUrl;
  }

  return trimTrailingSlash(`${window.location.origin}/backend/public/index.php`);
};

export const API_BASE_URL = getApiBaseUrl();

export const API_CONFIG = {
  timeout: 30000,
  retryAttempts: 3,
  retryDelay: 1000,
} as const;

const isRetryableError = (error: unknown): boolean => {
  return error instanceof Error && (
    error.name === "AbortError" ||
    error.message.includes("Failed to fetch") ||
    error.message.includes("NetworkError") ||
    error.message.includes("timeout")
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
    return await fetch(url, {
      ...options,
      credentials: options.credentials ?? "include",
      signal: controller.signal,
    });
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
