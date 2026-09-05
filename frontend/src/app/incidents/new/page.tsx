import Link from "next/link";
import { IncidentForm } from "@/components/IncidentForm";

export default function NewIncidentPage() {
  return (
    <main className="page">
      <header className="page-header">
        <h1>Novo incidente</h1>
        <Link href="/">Voltar</Link>
      </header>

      <IncidentForm />
    </main>
  );
}
