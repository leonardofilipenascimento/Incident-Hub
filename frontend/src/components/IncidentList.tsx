"use client";

import Link from "next/link";
import { IncidentSeverityBadge } from "@/components/IncidentSeverityBadge";
import { IncidentStatusBadge } from "@/components/IncidentStatusBadge";
import type { Incident } from "@/types/incident";

interface IncidentListProps {
  incidents: Incident[];
  loading: boolean;
  error: string | null;
}

export function IncidentList({ incidents, loading, error }: IncidentListProps) {
  if (loading) {
    return <p className="hint">Carregando incidentes...</p>;
  }

  if (error) {
    return <p className="error-message">{error}</p>;
  }

  if (incidents.length === 0) {
    return <p className="hint">Nenhum incidente encontrado.</p>;
  }

  return (
    <table className="incident-table">
      <thead>
        <tr>
          <th>Titulo</th>
          <th>Severidade</th>
          <th>Responsavel</th>
          <th>Status</th>
          <th>Criado em</th>
        </tr>
      </thead>
      <tbody>
        {incidents.map((incident) => (
          <tr key={incident.id}>
            <td>
              <Link href={`/incidents/${incident.id}`}>{incident.title}</Link>
            </td>
            <td>
              <IncidentSeverityBadge severity={incident.severity} />
            </td>
            <td>{incident.owner}</td>
            <td>
              <IncidentStatusBadge status={incident.status} />
            </td>
            <td>{new Date(incident.created_at).toLocaleString("pt-BR")}</td>
          </tr>
        ))}
      </tbody>
    </table>
  );
}
