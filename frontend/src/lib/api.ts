import type {
  CreateIncidentPayload,
  DashboardSummary,
  Incident,
  IncidentFilters,
  UpdateIncidentStatusPayload,
} from "@/types/incident";

const API_URL = process.env.NEXT_PUBLIC_API_URL ?? "http://localhost:8000/api";

export class ApiError extends Error {
  status: number;
  errors: Record<string, string[]>;

  constructor(message: string, status: number, errors: Record<string, string[]> = {}) {
    super(message);
    this.name = "ApiError";
    this.status = status;
    this.errors = errors;
  }
}

async function request<T>(path: string, options: RequestInit = {}): Promise<T> {
  const response = await fetch(`${API_URL}${path}`, {
    ...options,
    headers: {
      "Content-Type": "application/json",
      Accept: "application/json",
      // Evita a pagina de aviso do ngrok free tier quando a API estiver exposta via tunel.
      "ngrok-skip-browser-warning": "true",
      ...options.headers,
    },
  });

  const body = await response.json().catch(() => ({}));

  if (!response.ok) {
    throw new ApiError(
      body.message ?? "Erro inesperado ao comunicar com a API.",
      response.status,
      body.errors ?? {}
    );
  }

  return body as T;
}

function buildQueryString(filters: IncidentFilters): string {
  const params = new URLSearchParams();

  if (filters.severity) params.set("severity", filters.severity);
  if (filters.status) params.set("status", filters.status);

  const query = params.toString();
  return query ? `?${query}` : "";
}

export async function fetchIncidents(filters: IncidentFilters = {}): Promise<Incident[]> {
  const { data } = await request<{ data: Incident[] }>(
    `/incidents${buildQueryString(filters)}`
  );
  return data;
}

export async function fetchIncident(id: number): Promise<Incident> {
  return request<Incident>(`/incidents/${id}`);
}

export async function createIncident(payload: CreateIncidentPayload): Promise<Incident> {
  return request<Incident>("/incidents", {
    method: "POST",
    body: JSON.stringify(payload),
  });
}

export async function updateIncidentStatus(
  id: number,
  payload: UpdateIncidentStatusPayload
): Promise<Incident> {
  return request<Incident>(`/incidents/${id}/status`, {
    method: "PATCH",
    body: JSON.stringify(payload),
  });
}

export async function fetchDashboardSummary(): Promise<DashboardSummary> {
  return request<DashboardSummary>("/dashboard");
}
