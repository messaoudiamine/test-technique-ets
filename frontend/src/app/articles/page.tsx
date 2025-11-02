'use client';

import { useState, useEffect } from 'react';
import { useRouter } from 'next/navigation';
import { useAuth } from '@/hooks/useAuth';
import { articlesService, Article } from '@/services/articles';
import { ApiError } from '@/services/api';
import Navbar from '@/components/Layout/Navbar';
import Footer from '@/components/Layout/Footer';

export default function ArticlesPage() {
  const router = useRouter();
  const { isAuthenticated, loading: authLoading } = useAuth();
  const [articles, setArticles] = useState<Article[]>([]);
  const [loading, setLoading] = useState(true);
  const [page, setPage] = useState(1);
  const [totalPages, setTotalPages] = useState(1);
  const [showForm, setShowForm] = useState(false);
  const [editingArticle, setEditingArticle] = useState<Article | null>(null);
  const [formData, setFormData] = useState({ title: '', content: '' });
  const [error, setError] = useState<string | null>(null);
  const [fieldErrors, setFieldErrors] = useState<Record<string, string>>({});

  useEffect(() => {
    if (!authLoading && !isAuthenticated) {
      router.push('/login');
    }
  }, [authLoading, isAuthenticated, router]);

  useEffect(() => {
    if (isAuthenticated) {
      loadArticles();
    }
  }, [page, isAuthenticated]);

  const loadArticles = async () => {
    try {
      setLoading(true);
      const response = await articlesService.getArticles(page, 6);
      setArticles(response.data);
      setTotalPages(response.total_pages);
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Erreur lors du chargement');
    } finally {
      setLoading(false);
    }
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setError(null);
    setFieldErrors({});

    try {
      if (editingArticle) {
        await articlesService.updateArticle(editingArticle.id, formData);
      } else {
        await articlesService.createArticle(formData);
      }
      setShowForm(false);
      setEditingArticle(null);
      setFormData({ title: '', content: '' });
      setFieldErrors({});
      loadArticles();
    } catch (err) {
      if (err instanceof ApiError && err.errors) {
        // Erreurs de validation spécifiques
        setFieldErrors(err.errors);
        setError(err.message || 'Erreurs de validation');
      } else {
        setError(err instanceof Error ? err.message : 'Une erreur est survenue');
      }
    }
  };

  const handleEdit = (article: Article) => {
    setEditingArticle(article);
    setFormData({ title: article.title, content: article.content });
    setShowForm(true);
  };

  const handleDelete = async (id: string) => {
    if (!confirm('Êtes-vous sûr de vouloir supprimer cet article ?')) {
      return;
    }

    try {
      await articlesService.deleteArticle(id);
      loadArticles();
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Erreur lors de la suppression');
    }
  };

  const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('fr-FR', {
      year: 'numeric',
      month: 'long',
      day: 'numeric',
    });
  };

  if (authLoading || !isAuthenticated) {
    return <div>Chargement...</div>;
  }

  return (
    <>
      <Navbar />
      <div className="min-h-screen flex flex-col">
        <div className="container mx-auto px-20 py-8 flex-1">
        <div className="flex justify-between items-center mb-6">
          <h1 className="text-3xl font-bold">Mes Articles</h1>
          <button
            onClick={() => {
              setShowForm(true);
              setEditingArticle(null);
              setFormData({ title: '', content: '' });
            }}
            className="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
          >
            + Nouvel Article
          </button>
        </div>

        {error && (
          <div className="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            {error}
          </div>
        )}

        {showForm && (
          <div className="bg-white p-6 rounded-lg shadow-md mb-6">
            <h2 className="text-2xl font-bold mb-4">
              {editingArticle ? 'Modifier l\'article' : 'Nouvel article'}
            </h2>
            <form onSubmit={handleSubmit}>
              <div className="mb-4">
                <label className="block text-gray-700 text-sm font-bold mb-2">
                  Titre
                </label>
                <input
                  type="text"
                  value={formData.title}
                  onChange={(e) => {
                    setFormData({ ...formData, title: e.target.value });
                    // Supprimer l'erreur du champ quand l'utilisateur tape
                    if (fieldErrors.title) {
                      const newErrors = { ...fieldErrors };
                      delete newErrors.title;
                      setFieldErrors(newErrors);
                    }
                  }}
                  className={`shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline ${
                    fieldErrors.title ? 'border-red-500' : ''
                  }`}
                  required
                />
                {fieldErrors.title && (
                  <p className="text-red-500 text-xs mt-1">{fieldErrors.title}</p>
                )}
              </div>
              <div className="mb-4">
                <label className="block text-gray-700 text-sm font-bold mb-2">
                  Contenu
                </label>
                <textarea
                  value={formData.content}
                  onChange={(e) => {
                    setFormData({ ...formData, content: e.target.value });
                    // Supprimer l'erreur du champ quand l'utilisateur tape
                    if (fieldErrors.content) {
                      const newErrors = { ...fieldErrors };
                      delete newErrors.content;
                      setFieldErrors(newErrors);
                    }
                  }}
                  className={`shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline ${
                    fieldErrors.content ? 'border-red-500' : ''
                  }`}
                  rows={6}
                  required
                />
                {fieldErrors.content && (
                  <p className="text-red-500 text-xs mt-1">{fieldErrors.content}</p>
                )}
              </div>
              <div className="flex space-x-2">
                <button
                  type="submit"
                  className="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded"
                >
                  {editingArticle ? 'Modifier' : 'Créer'}
                </button>
                <button
                  type="button"
                  onClick={() => {
                    setShowForm(false);
                    setEditingArticle(null);
                    setFormData({ title: '', content: '' });
                    setFieldErrors({});
                    setError(null);
                  }}
                  className="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded"
                >
                  Annuler
                </button>
              </div>
            </form>
          </div>
        )}

        {loading ? (
          <div>Chargement des articles...</div>
        ) : articles.length === 0 ? (
          <div className="bg-white p-6 rounded-lg shadow-md text-center">
            <p className="text-gray-500">Aucun article pour le moment.</p>
          </div>
        ) : (
          <>
            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
              {articles.map((article) => (
                <div key={article.id} className="bg-white p-6 rounded-lg shadow-md flex flex-col">
                  <div className="flex-1">
                    <h2 className="text-xl font-bold mb-2 line-clamp-2">{article.title}</h2>
                    <p className="text-gray-700 mb-4 line-clamp-4 text-sm">{article.content}</p>
                    <div className="text-sm text-gray-500 mb-4">
                      <p>Publié le {formatDate(article.publication_date)}</p>
                      {article.author && (
                        <p>Auteur: {article.author.name}</p>
                      )}
                    </div>
                  </div>
                  <div className="flex space-x-2 mt-auto pt-4 border-t justify-end">
                    <button
                      onClick={() => handleEdit(article)}
                      className="bg-blue-500 hover:bg-blue-700 text-white p-2 rounded transition-colors"
                      title="Modifier"
                    >
                      <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                      </svg>
                    </button>
                    <button
                      onClick={() => handleDelete(article.id)}
                      className="bg-red-500 hover:bg-red-700 text-white p-2 rounded transition-colors"
                      title="Supprimer"
                    >
                      <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                      </svg>
                    </button>
                  </div>
                </div>
              ))}
            </div>

            <div className="mt-8 flex justify-center items-center space-x-4">
              <button
                onClick={() => setPage(page - 1)}
                disabled={page === 1}
                className="bg-gray-500 hover:bg-gray-700 disabled:bg-gray-300 disabled:cursor-not-allowed text-white font-bold py-2 px-4 rounded transition-colors flex items-center justify-center"
                title="Page précédente"
              >
                <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 19l-7-7 7-7" />
                </svg>
              </button>
              <div className="flex items-center space-x-2">
                <span className="text-gray-700 font-medium">
                  Page <span className="font-bold">{page}</span> sur <span className="font-bold">{totalPages}</span>
                </span>
              </div>
              <button
                onClick={() => setPage(page + 1)}
                disabled={page === totalPages}
                className="bg-gray-500 hover:bg-gray-700 disabled:bg-gray-300 disabled:cursor-not-allowed text-white font-bold py-2 px-4 rounded transition-colors flex items-center justify-center"
                title="Page suivante"
              >
                <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5l7 7-7 7" />
                </svg>
              </button>
            </div>
          </>
        )}
        </div>
      </div>
      <Footer />
    </>
  );
}

