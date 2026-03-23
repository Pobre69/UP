import { CheckCircle2, Clock, FileEdit, Send } from "lucide-react";
import { useState, useEffect } from "react";
import { Card, PageHeader, StatCard } from "../../Components/UI/Cards";
import { API_BASE_URL, fetchWithRetry } from "../../config/api";
import "./pages.css";

interface ContentItem {
  id: number;
  title: string;
  content_type: string;
  description?: string;
  status: 'review' | 'planned' | 'scheduled' | 'published';
  scheduled_date?: string;
  published_date?: string;
  created_at: string;
}

interface ServiceStatusSummary {
  review: number;
  planned: number;
  scheduled: number;
  published: number;
}

export default function ServiceStatusPage() {
  const [summary, setSummary] = useState<ServiceStatusSummary>({
    review: 0,
    planned: 0,
    scheduled: 0,
    published: 0
  });
  const [content, setContent] = useState<ContentItem[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    const loadServiceStatus = async () => {
      try {
        setLoading(true);
        setError(null);

        const response = await fetchWithRetry(
          `${API_BASE_URL}/api/service-status`,
          {
            method: 'GET',
            credentials: 'include',
            headers: { 'Content-Type': 'application/json' }
          }
        );

        if (!response.ok) {
          if (response.status === 401) {
            throw new Error('Sessão expirada');
          }
          throw new Error(`Erro ao carregar status: ${response.status}`);
        }

        const data = await response.json();

        if (!data.success || !data.data) {
          throw new Error('Resposta inválida do servidor');
        }

        // Validar e atualizar dados
        if (data.data.summary) {
          setSummary({
            review: Number(data.data.summary.review) || 0,
            planned: Number(data.data.summary.planned) || 0,
            scheduled: Number(data.data.summary.scheduled) || 0,
            published: Number(data.data.summary.published) || 0
          });
        }

        if (Array.isArray(data.data.content)) {
          setContent(data.data.content);
        }
      } catch (err) {
        console.error('Erro ao carregar status do serviço:', err);
        setError(err instanceof Error ? err.message : 'Erro desconhecido');
      } finally {
        setLoading(false);
      }
    };

    loadServiceStatus();
  }, []);

  const getStatusIcon = (status: string) => {
    switch (status) {
      case 'review':
        return <FileEdit size={18} />;
      case 'planned':
        return <Clock size={18} />;
      case 'scheduled':
        return <Send size={18} />;
      case 'published':
        return <CheckCircle2 size={18} />;
      default:
        return <Clock size={18} />;
    }
  };

  const getStatusLabel = (status: string) => {
    switch (status) {
      case 'review':
        return 'Em Revisão';
      case 'planned':
        return 'Planejado';
      case 'scheduled':
        return 'Agendado';
      case 'published':
        return 'Publicado';
      default:
        return status;
    }
  };

  if (loading) {
    return (
      <div className="page">
        <PageHeader
          title="Status do Serviço"
          subtitle="Acompanhe o andamento de todas as suas peças e conteúdos."
        />
        <div style={{
          display: 'flex',
          alignItems: 'center',
          justifyContent: 'center',
          height: '200px'
        }}>
          <div style={{
            textAlign: 'center'
          }}>
            <div style={{
              width: '40px',
              height: '40px',
              border: '4px solid #f3f3f3',
              borderTop: '4px solid #667eea',
              borderRadius: '50%',
              animation: 'spin 1s linear infinite',
              margin: '0 auto 20px'
            }} />
            <p style={{ color: '#666' }}>Carregando...</p>
            <style>{`
              @keyframes spin {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
              }
            `}</style>
          </div>
        </div>
      </div>
    );
  }

  return (
    <div className="page">
      <PageHeader
        title="Status do Serviço"
        subtitle="Acompanhe o andamento de todas as suas peças e conteúdos."
      />

      {error && (
        <Card style={{
          background: '#fee',
          borderLeft: '4px solid #f33',
          marginBottom: '20px'
        }}>
          <p style={{ color: '#c33', margin: 0 }}>
            <strong>Erro:</strong> {error}
          </p>
        </Card>
      )}

      <div className="gridStats gridStats4">
        <StatCard
          icon={<FileEdit size={18} />}
          label="Em Revisão"
          value={summary.review.toString()}
        />
        <StatCard
          icon={<Clock size={18} />}
          label="Planejado"
          value={summary.planned.toString()}
        />
        <StatCard
          icon={<Send size={18} />}
          label="Agendado"
          value={summary.scheduled.toString()}
        />
        <StatCard
          icon={<CheckCircle2 size={18} />}
          label="Publicado"
          value={summary.published.toString()}
        />
      </div>

      <Card className="chartCard" style={{ minHeight: 180 }}>
        {content.length === 0 ? (
          <div className="emptyBox">Nenhum conteúdo registrado ainda.</div>
        ) : (
          <div style={{ padding: '20px' }}>
            <table style={{
              width: '100%',
              borderCollapse: 'collapse'
            }}>
              <thead>
                <tr style={{ borderBottom: '2px solid #eee' }}>
                  <th style={{ textAlign: 'left', padding: '10px', fontWeight: 'bold' }}>Título</th>
                  <th style={{ textAlign: 'left', padding: '10px', fontWeight: 'bold' }}>Tipo</th>
                  <th style={{ textAlign: 'left', padding: '10px', fontWeight: 'bold' }}>Status</th>
                  <th style={{ textAlign: 'left', padding: '10px', fontWeight: 'bold' }}>Data</th>
                </tr>
              </thead>
              <tbody>
                {content.map((item) => (
                  <tr key={item.id} style={{ borderBottom: '1px solid #eee' }}>
                    <td style={{ padding: '10px' }}>{item.title}</td>
                    <td style={{ padding: '10px' }}>{item.content_type}</td>
                    <td style={{ padding: '10px', display: 'flex', alignItems: 'center', gap: '8px' }}>
                      {getStatusIcon(item.status)}
                      {getStatusLabel(item.status)}
                    </td>
                    <td style={{ padding: '10px', fontSize: '12px', color: '#666' }}>
                      {new Date(item.created_at).toLocaleDateString('pt-BR')}
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        )}
      </Card>
    </div>
  );
}
