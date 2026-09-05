"use client";

import Link from "next/link";
import { useState } from "react";
import { DashboardSummary } from "@/components/DashboardSummary";
import { IncidentFilters } from "@/components/IncidentFilters";
import { IncidentList } from "@/components/IncidentList";
import { useIncidents } from "@/hooks/useIncidents";
import type { IncidentFilters as Filters } from "@/types/incident";

export default function Home() {
  const [filters, setFilters] = useState<Filters>({});
  const { incidents, loading, error } = useIncidents(filters);

  return (
    <main className="page">
      <header className="page-header">
        <h1>Incident Hub</h1>
        <Link href="/incidents/new" className="button-link">
          Novo incidente
        </Link>
      </header>

      <DashboardSummary />
      <IncidentFilters filters={filters} onChange={setFilters} />
      <IncidentList incidents={incidents} loading={loading} error={error} />
    </main>
  );
}
