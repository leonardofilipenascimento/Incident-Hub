"use client";

import { useState, type FormEvent } from "react";
import { IncidentSeverityBadge } from "@/components/IncidentSeverityBadge";
import { IncidentStatusBadge } from "@/components/IncidentStatusBadge";
import { IncidentTimeline } from "@/components/IncidentTimeline";
import { ApiError, updateIncidentSeverity, updateIncidentStatus } from "@/lib/api";
import { INCIDENT_SEVERITIES, INCIDENT_STATUSES, type Incident, type IncidentSeverity, type IncidentStatus } from "@/types/incident";

interface IncidentDetailsProps {
  incident: Incident;
  onUpdated: () => void;
}

export function IncidentDetails({ incident, onUpdated }: IncidentDetailsProps) {
  const isClosed = incident.status === "Closed";

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
        <strong>Sistemas afetados:</strong> {incident.affected_systems.join(", ")}
      </p>

      {isClosed && <p className="hint">Incidente fechado — status e severidade nao podem mais ser alterados.</p>}

      <div className="actions">
        <StatusForm incident={incident} disabled={isClosed} onUpdated={onUpdated} />
        <SeverityForm incident={incident} disabled={isClosed} onUpdated={onUpdated} />
      </div>

      <section>
        <h2>Historico</h2>
        <IncidentTimeline history={incident.history ?? []} />
      </section>
    </div>
  );
}

function StatusForm({
  incident,
  disabled,
  onUpdated,
}: {
  incident: Incident;
  disabled: boolean;
  onUpdated: () => void;
}) {
  const [status, setStatus] = useState<IncidentStatus>(incident.status);
  const [comment, setComment] = useState("");
  const [error, setError] = useState<string | null>(null);
  const [submitting, setSubmitting] = useState(false);

  async function handleSubmit(event: FormEvent) {
    event.preventDefault();
    setSubmitting(true);
    setError(null);

    try {
      await updateIncidentStatus(incident.id, { status, comment: comment || undefined });
      setComment("");
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
        <select value={status} onChange={(event) => setStatus(event.target.value as IncidentStatus)} disabled={disabled}>
          {INCIDENT_STATUSES.map((option) => (
            <option key={option} value={option}>
              {option}
            </option>
          ))}
        </select>
      </label>

      <label>
        Comentario (obrigatorio para Resolved/Closed)
        <textarea value={comment} onChange={(event) => setComment(event.target.value)} rows={2} disabled={disabled} />
      </label>

      <button type="submit" disabled={disabled || submitting}>
        {submitting ? "Salvando..." : "Atualizar status"}
      </button>
    </form>
  );
}

function SeverityForm({
  incident,
  disabled,
  onUpdated,
}: {
  incident: Incident;
  disabled: boolean;
  onUpdated: () => void;
}) {
  const [severity, setSeverity] = useState<IncidentSeverity>(incident.severity);
  const [comment, setComment] = useState("");
  const [error, setError] = useState<string | null>(null);
  const [submitting, setSubmitting] = useState(false);

  async function handleSubmit(event: FormEvent) {
    event.preventDefault();
    setSubmitting(true);
    setError(null);

    try {
      await updateIncidentSeverity(incident.id, { severity, comment: comment || undefined });
      setComment("");
      onUpdated();
    } catch (err) {
      setError(err instanceof ApiError ? err.message : "Nao foi possivel atualizar a severidade.");
    } finally {
      setSubmitting(false);
    }
  }

  return (
    <form className="action-form" onSubmit={handleSubmit}>
      <h3>Alterar severidade</h3>
      {error && <p className="error-message">{error}</p>}

      <label>
        Nova severidade
        <select value={severity} onChange={(event) => setSeverity(event.target.value as IncidentSeverity)} disabled={disabled}>
          {INCIDENT_SEVERITIES.map((option) => (
            <option key={option} value={option}>
              {option}
            </option>
          ))}
        </select>
      </label>

      <label>
        Comentario (opcional)
        <textarea value={comment} onChange={(event) => setComment(event.target.value)} rows={2} disabled={disabled} />
      </label>

      <button type="submit" disabled={disabled || submitting}>
        {submitting ? "Salvando..." : "Atualizar severidade"}
      </button>
    </form>
  );
}
