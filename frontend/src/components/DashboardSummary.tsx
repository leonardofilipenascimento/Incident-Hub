"use client";

import { useDashboard } from "@/hooks/useDashboard";

export function DashboardSummary() {
  const { summary, loading, error } = useDashboard();

  if (loading) return <p className="hint">Carregando dashboard...</p>;
  if (error) return <p className="error-message">{error}</p>;
  if (!summary) return null;

  return (
    <div className="dashboard-cards">
      <div className="dashboard-card">
        <span className="dashboard-card-value">{summary.open_incidents}</span>
        <span className="dashboard-card-label">Incidentes Open</span>
      </div>
      <div className="dashboard-card dashboard-card-critical">
        <span className="dashboard-card-value">{summary.unresolved_critical_incidents}</span>
        <span className="dashboard-card-label">Critical nao resolvidos</span>
      </div>
      <div className="dashboard-card dashboard-card-resolved">
        <span className="dashboard-card-value">{summary.resolved_incidents}</span>
        <span className="dashboard-card-label">Resolvidos</span>
      </div>
    </div>
  );
}
