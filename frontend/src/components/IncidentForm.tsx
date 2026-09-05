"use client";

import { useRouter } from "next/navigation";
import { useState, type FormEvent } from "react";
import { ApiError, createIncident } from "@/lib/api";
import { INCIDENT_SEVERITIES, type IncidentSeverity } from "@/types/incident";

export function IncidentForm() {
  const router = useRouter();

  const [title, setTitle] = useState("");
  const [description, setDescription] = useState("");
  const [severity, setSeverity] = useState<IncidentSeverity>("Low");
  const [affectedSystems, setAffectedSystems] = useState("");
  const [errors, setErrors] = useState<Record<string, string[]>>({});
  const [submitting, setSubmitting] = useState(false);
  const [generalError, setGeneralError] = useState<string | null>(null);

  async function handleSubmit(event: FormEvent) {
    event.preventDefault();
    setSubmitting(true);
    setErrors({});
    setGeneralError(null);

    try {
      const incident = await createIncident({
        title,
        description,
        severity,
        affected_systems: affectedSystems
          .split(",")
          .map((system) => system.trim())
          .filter((system) => system.length > 0),
      });

      router.push(`/incidents/${incident.id}`);
    } catch (err) {
      if (err instanceof ApiError) {
        setErrors(err.errors);
        setGeneralError(err.errors && Object.keys(err.errors).length > 0 ? null : err.message);
      } else {
        setGeneralError("Nao foi possivel criar o incidente.");
      }
    } finally {
      setSubmitting(false);
    }
  }

  return (
    <form className="incident-form" onSubmit={handleSubmit}>
      {generalError && <p className="error-message">{generalError}</p>}

      <label>
        Titulo
        <input value={title} onChange={(event) => setTitle(event.target.value)} />
        {errors.title?.map((message) => (
          <span key={message} className="field-error">
            {message}
          </span>
        ))}
      </label>

      <label>
        Descricao
        <textarea value={description} onChange={(event) => setDescription(event.target.value)} rows={4} />
        {errors.description?.map((message) => (
          <span key={message} className="field-error">
            {message}
          </span>
        ))}
      </label>

      <label>
        Severidade
        <select value={severity} onChange={(event) => setSeverity(event.target.value as IncidentSeverity)}>
          {INCIDENT_SEVERITIES.map((option) => (
            <option key={option} value={option}>
              {option}
            </option>
          ))}
        </select>
        {errors.severity?.map((message) => (
          <span key={message} className="field-error">
            {message}
          </span>
        ))}
      </label>

      <label>
        Sistemas afetados (separados por virgula)
        <input
          value={affectedSystems}
          onChange={(event) => setAffectedSystems(event.target.value)}
          placeholder="payment-gateway, checkout-api"
        />
        {errors.affected_systems?.map((message) => (
          <span key={message} className="field-error">
            {message}
          </span>
        ))}
      </label>

      <button type="submit" disabled={submitting}>
        {submitting ? "Criando..." : "Criar incidente"}
      </button>
    </form>
  );
}
