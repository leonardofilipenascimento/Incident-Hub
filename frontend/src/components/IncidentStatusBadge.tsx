import type { IncidentStatus } from "@/types/incident";

const STATUS_CLASS: Record<IncidentStatus, string> = {
  Open: "badge badge-status-open",
  "In Progress": "badge badge-status-in-progress",
  Resolved: "badge badge-status-resolved",
  Closed: "badge badge-status-closed",
};

export function IncidentStatusBadge({ status }: { status: IncidentStatus }) {
  return <span className={STATUS_CLASS[status]}>{status}</span>;
}
