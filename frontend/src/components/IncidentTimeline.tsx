import type { IncidentStatusHistory } from "@/types/incident";

export function IncidentTimeline({ history }: { history: IncidentStatusHistory[] }) {
  if (history.length === 0) {
    return <p className="hint">Nenhuma alteracao registrada ainda.</p>;
  }

  return (
    <ul className="timeline">
      {history.map((entry) => (
        <li key={entry.id}>
          <div className="timeline-header">
            <strong>
              {entry.previous_status} -&gt; {entry.new_status}
            </strong>
            <time>{new Date(entry.created_at).toLocaleString("pt-BR")}</time>
          </div>
        </li>
      ))}
    </ul>
  );
}
