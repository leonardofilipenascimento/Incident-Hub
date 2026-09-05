"use client";

import { useCallback, useEffect, useState } from "react";
import { ApiError, fetchIncidents } from "@/lib/api";
import type { Incident, IncidentFilters } from "@/types/incident";

interface UseIncidentsResult {
  incidents: Incident[];
  loading: boolean;
  error: string | null;
  refetch: () => void;
}

export function useIncidents(filters: IncidentFilters): UseIncidentsResult {
  const [incidents, setIncidents] = useState<Incident[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [reloadToken, setReloadToken] = useState(0);

  const refetch = useCallback(() => setReloadToken((token) => token + 1), []);

  useEffect(() => {
    let cancelled = false;

    // Reset de loading/error a cada novo fetch: padrao de data-fetching documentado em
    // react.dev (Synchronizing with Effects). Sem isso o spinner nao reaparece ao trocar os filtros.
    /* eslint-disable react-hooks/set-state-in-effect */
    setLoading(true);
    setError(null);
    /* eslint-enable react-hooks/set-state-in-effect */

    fetchIncidents(filters)
      .then((data) => {
        if (!cancelled) setIncidents(data);
      })
      .catch((err: unknown) => {
        if (!cancelled) {
          setError(err instanceof ApiError ? err.message : "Nao foi possivel carregar os incidentes.");
        }
      })
      .finally(() => {
        if (!cancelled) setLoading(false);
      });

    return () => {
      cancelled = true;
    };
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [filters.severity, filters.status, filters.search, reloadToken]);

  return { incidents, loading, error, refetch };
}
