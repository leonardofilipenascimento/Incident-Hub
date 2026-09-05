import type { IncidentStatusHistory } from "@/types/incident";

function describeChange(entry: IncidentStatusHistory): string {
  if (entry.new_status) {
    return `Status: ${entry.previous_status ?? "-"} -> ${entry.new_status}`;
  }

  if (entry.new_severity) {
    return `Severidade: ${entry.previous_severity ?? "-"} -> ${entry.new_severity}`;
  }

  return "Alteracao registrada";
}

export function IncidentTimeline({ history }: { history: IncidentStatusHistory[] }) {
  if (history.length === 0) {
    return <p className="hint">Nenhuma alteracao registrada ainda.</p>;
  }

  return (
    <ul className="timeline">
      {history.map((entry) => (
        <li key={entry.id}>
          <div className="timeline-header">
            <strong>{describeChange(entry)}</strong>
            <time>{new Date(entry.created_at).toLocaleString("pt-BR")}</time>
          </div>
          {entry.comment && <p className="timeline-comment">{entry.comment}</p>}
        </li>
      ))}
    </ul>
  );
}
