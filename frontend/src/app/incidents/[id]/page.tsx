"use client";

import Link from "next/link";
import { useParams } from "next/navigation";
import { IncidentDetails } from "@/components/IncidentDetails";
import { useIncident } from "@/hooks/useIncident";

export default function IncidentDetailPage() {
  const params = useParams<{ id: string }>();
  const id = Number(params.id);

  const { incident, loading, error, notFound, refetch } = useIncident(id);

  return (
    <main className="page">
      <header className="page-header">
        <Link href="/">Voltar</Link>
      </header>

      {loading && <p className="hint">Carregando incidente...</p>}
      {error && <p className="error-message">{error}</p>}
      {notFound && <p className="error-message">Incidente nao encontrado.</p>}
      {incident && <IncidentDetails incident={incident} onUpdated={refetch} />}
    </main>
  );
}
