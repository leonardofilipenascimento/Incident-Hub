"use client";

import { useState, type FormEvent } from "react";
import { IncidentSeverityBadge } from "@/components/IncidentSeverityBadge";
import { IncidentStatusBadge } from "@/components/IncidentStatusBadge";
import { IncidentTimeline } from "@/components/IncidentTimeline";
import { ApiError, updateIncidentStatus } from "@/lib/api";
import { INCIDENT_STATUSES, type Incident, type IncidentStatus } from "@/types/incident";

interface IncidentDetailsProps {
  incident: Incident;
  onUpdated: () => void;
}

export function IncidentDetails({ incident, onUpdated }: IncidentDetailsProps) {
  return (
    <div className="incident-details">
      <header>
        <h1>{incident.title}</h1>
        <div className="badges">
          <IncidentSeverityBadge severity={incident.severity} />
          <IncidentStatusBadge status={incident.status} />
        </div>
      </header>

      <p>{incident.description}</p>

      <p>
        <strong>Responsavel:</strong> {incident.owner}
      </p>
      <p>
        <strong>Criado em:</strong> {new Date(incident.created_at).toLocaleString("pt-BR")}
      </p>
      <p>
        <strong>Ultima atualizacao:</strong> {new Date(incident.updated_at).toLocaleString("pt-BR")}
      </p>

      <div className="actions">
        <StatusForm incident={incident} onUpdated={onUpdated} />
      </div>

      <section>
        <h2>Historico</h2>
        <IncidentTimeline history={incident.history ?? []} />
      </section>
    </div>
  );
}

function StatusForm({ incident, onUpdated }: { incident: Incident; onUpdated: () => void }) {
  const [status, setStatus] = useState<IncidentStatus>(incident.status);
  const [error, setError] = useState<string | null>(null);
  const [submitting, setSubmitting] = useState(false);

  async function handleSubmit(event: FormEvent) {
    event.preventDefault();
    setSubmitting(true);
    setError(null);

    try {
      await updateIncidentStatus(incident.id, { status });
      onUpdated();
    } catch (err) {
      setError(err instanceof ApiError ? err.message : "Nao foi possivel atualizar o status.");
    } finally {
      setSubmitting(false);
    }
  }

  return (
    <form className="action-form" onSubmit={handleSubmit}>
      <h3>Transicionar status</h3>
      {error && <p className="error-message">{error}</p>}

      <label>
        Novo status
        <select value={status} onChange={(event) => setStatus(event.target.value as IncidentStatus)}>
          {INCIDENT_STATUSES.map((option) => (
            <option key={option} value={option}>
              {option}
            </option>
          ))}
        </select>
      </label>

      <button type="submit" disabled={submitting}>
        {submitting ? "Salvando..." : "Atualizar status"}
      </button>
    </form>
  );
}
