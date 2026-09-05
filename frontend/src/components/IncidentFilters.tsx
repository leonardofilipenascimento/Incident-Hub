"use client";

import { INCIDENT_SEVERITIES, INCIDENT_STATUSES, type IncidentFilters as Filters } from "@/types/incident";

interface IncidentFiltersProps {
  filters: Filters;
  onChange: (filters: Filters) => void;
}

export function IncidentFilters({ filters, onChange }: IncidentFiltersProps) {
  return (
    <div className="filters">
      <select
        value={filters.severity ?? ""}
        onChange={(event) => onChange({ ...filters, severity: event.target.value as Filters["severity"] })}
      >
        <option value="">Todas as severidades</option>
        {INCIDENT_SEVERITIES.map((severity) => (
          <option key={severity} value={severity}>
            {severity}
          </option>
        ))}
      </select>

      <select
        value={filters.status ?? ""}
        onChange={(event) => onChange({ ...filters, status: event.target.value as Filters["status"] })}
      >
        <option value="">Todos os status</option>
        {INCIDENT_STATUSES.map((status) => (
          <option key={status} value={status}>
            {status}
          </option>
        ))}
      </select>

      <input
        type="search"
        placeholder="Buscar por titulo ou descricao..."
        value={filters.search ?? ""}
        onChange={(event) => onChange({ ...filters, search: event.target.value })}
      />
    </div>
  );
}
