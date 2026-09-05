export type IncidentStatus = "Open" | "In Progress" | "Resolved" | "Closed";

export type IncidentSeverity = "Low" | "Medium" | "High" | "Critical";

export const INCIDENT_STATUSES: IncidentStatus[] = [
  "Open",
  "In Progress",
  "Resolved",
  "Closed",
];

export const INCIDENT_SEVERITIES: IncidentSeverity[] = [
  "Low",
  "Medium",
  "High",
  "Critical",
];

export interface IncidentStatusHistory {
  id: number;
  incident_id: number;
  previous_status: IncidentStatus | null;
  new_status: IncidentStatus | null;
  previous_severity: IncidentSeverity | null;
  new_severity: IncidentSeverity | null;
  comment: string | null;
  created_at: string;
}

export interface Incident {
  id: number;
  title: string;
  description: string;
  severity: IncidentSeverity;
  status: IncidentStatus;
  affected_systems: string[];
  created_at: string;
  updated_at: string;
  history?: IncidentStatusHistory[];
}

export interface IncidentFilters {
  severity?: IncidentSeverity | "";
  status?: IncidentStatus | "";
  search?: string;
}

export interface CreateIncidentPayload {
  title: string;
  description: string;
  severity: IncidentSeverity;
  affected_systems: string[];
}

export interface UpdateIncidentStatusPayload {
  status: IncidentStatus;
  comment?: string;
}

export interface UpdateIncidentSeverityPayload {
  severity: IncidentSeverity;
  comment?: string;
}
