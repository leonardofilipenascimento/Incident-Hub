"use client";

import { useCallback, useEffect, useState } from "react";
import { ApiError, fetchIncident } from "@/lib/api";
import type { Incident } from "@/types/incident";

interface UseIncidentResult {
  incident: Incident | null;
  loading: boolean;
  error: string | null;
  notFound: boolean;
  refetch: () => void;
}

export function useIncident(id: number): UseIncidentResult {
  const [incident, setIncident] = useState<Incident | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [notFound, setNotFound] = useState(false);
  const [reloadToken, setReloadToken] = useState(0);

  const refetch = useCallback(() => setReloadToken((token) => token + 1), []);

  useEffect(() => {
    let cancelled = false;

    // Reset de loading/error/notFound a cada novo fetch: padrao de data-fetching documentado
    // em react.dev (Synchronizing with Effects). Sem isso o spinner nao reaparece ao trocar de incidente.
    /* eslint-disable react-hooks/set-state-in-effect */
    setLoading(true);
    setError(null);
    setNotFound(false);
    /* eslint-enable react-hooks/set-state-in-effect */

    fetchIncident(id)
      .then((data) => {
        if (!cancelled) setIncident(data);
      })
      .catch((err: unknown) => {
        if (cancelled) return;

        if (err instanceof ApiError && err.status === 404) {
          setNotFound(true);
          return;
        }

        setError(err instanceof ApiError ? err.message : "Nao foi possivel carregar o incidente.");
      })
      .finally(() => {
        if (!cancelled) setLoading(false);
      });

    return () => {
      cancelled = true;
    };
  }, [id, reloadToken]);

  return { incident, loading, error, notFound, refetch };
}
