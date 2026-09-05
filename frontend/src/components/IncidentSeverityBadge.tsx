import type { IncidentSeverity } from "@/types/incident";

const SEVERITY_CLASS: Record<IncidentSeverity, string> = {
  Low: "badge badge-severity-low",
  Medium: "badge badge-severity-medium",
  High: "badge badge-severity-high",
  Critical: "badge badge-severity-critical",
};

export function IncidentSeverityBadge({ severity }: { severity: IncidentSeverity }) {
  return <span className={SEVERITY_CLASS[severity]}>{severity}</span>;
}
