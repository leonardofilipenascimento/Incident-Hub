export type IncidentStatus = "Open" | "In Progress" | "Resolved";

export type IncidentSeverity = "Low" | "Medium" | "High" | "Critical";

export const INCIDENT_STATUSES: IncidentStatus[] = ["Open", "In Progress", "Resolved"];

export const INCIDENT_SEVERITIES: IncidentSeverity[] = [
  "Low",
  "Medium",
  "High",
  "Critical",
];

export interface IncidentStatusHistory {
  id: number;
  incident_id: number;
  previous_status: IncidentStatus;
  new_status: IncidentStatus;
  created_at: string;
}

export interface Incident {
  id: number;
  title: string;
  description: string;
  severity: IncidentSeverity;
  owner: string;
  status: IncidentStatus;
  created_at: string;
  updated_at: string;
  history?: IncidentStatusHistory[];
}

export interface IncidentFilters {
  severity?: IncidentSeverity | "";
  status?: IncidentStatus | "";
}

export interface CreateIncidentPayload {
  title: string;
  description: string;
  severity: IncidentSeverity;
  owner: string;
}

export interface UpdateIncidentStatusPayload {
  status: IncidentStatus;
}

export interface DashboardSummary {
  open_incidents: number;
  unresolved_critical_incidents: number;
  resolved_incidents: number;
}
