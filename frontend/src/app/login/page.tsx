'use client';

import { useState } from 'react';
import { useRouter } from 'next/navigation';
import { authService } from '@/services/auth';
import { ApiError } from '@/services/api';
import Footer from '@/components/Layout/Footer';

export default function LoginPage() {
  const router = useRouter();
  const [isLogin, setIsLogin] = useState(true);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [fieldErrors, setFieldErrors] = useState<Record<string, string>>({});
  const [formData, setFormData] = useState({
    name: '',
    email: '',
    password: '',
  });

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setLoading(true);
    setError(null);
    setFieldErrors({});

    try {
      if (isLogin) {
        await authService.login({
          email: formData.email,
          password: formData.password,
        });
      } else {
        await authService.register({
          name: formData.name,
          email: formData.email,
          password: formData.password,
        });
      }
      router.push('/account');
    } catch (err) {
      if (err instanceof ApiError && err.errors) {
        // Erreurs de validation spécifiques
        setFieldErrors(err.errors);
        setError(err.message || 'Erreurs de validation');
      } else if (err instanceof ApiError && err.status === 409) {
        // Utilisateur déjà existant
        setError(err.message || 'Cet email est déjà utilisé');
        setFieldErrors({ email: 'Cet email est déjà utilisé' });
      } else {
        setError(err instanceof Error ? err.message : 'Une erreur est survenue');
      }
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="min-h-screen flex flex-col bg-gray-100">
      <div className="flex-1 flex items-center justify-center">
        <div className="bg-white p-8 rounded-lg shadow-md w-full max-w-md">
        <h1 className="text-2xl font-bold mb-6 text-center">
          {isLogin ? 'Connexion' : 'Inscription'}
        </h1>

        {error && (
          <div className="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            {error}
          </div>
        )}

        <form onSubmit={handleSubmit}>
          {!isLogin && (
            <div className="mb-4">
              <label className="block text-gray-700 text-sm font-bold mb-2">
                Nom
              </label>
              <input
                type="text"
                value={formData.name}
                onChange={(e) => {
                  setFormData({ ...formData, name: e.target.value });
                  if (fieldErrors.name) {
                    const newErrors = { ...fieldErrors };
                    delete newErrors.name;
                    setFieldErrors(newErrors);
                  }
                }}
                className={`shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline ${
                  fieldErrors.name ? 'border-red-500' : ''
                }`}
                required
              />
              {fieldErrors.name && (
                <p className="text-red-500 text-xs mt-1">{fieldErrors.name}</p>
              )}
            </div>
          )}

          <div className="mb-4">
            <label className="block text-gray-700 text-sm font-bold mb-2">
              Email
            </label>
            <input
              type="email"
              value={formData.email}
              onChange={(e) => {
                setFormData({ ...formData, email: e.target.value });
                if (fieldErrors.email) {
                  const newErrors = { ...fieldErrors };
                  delete newErrors.email;
                  setFieldErrors(newErrors);
                }
              }}
              className={`shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline ${
                fieldErrors.email ? 'border-red-500' : ''
              }`}
              required
            />
            {fieldErrors.email && (
              <p className="text-red-500 text-xs mt-1">{fieldErrors.email}</p>
            )}
          </div>

          <div className="mb-6">
            <label className="block text-gray-700 text-sm font-bold mb-2">
              Mot de passe
            </label>
            <input
              type="password"
              value={formData.password}
              onChange={(e) => {
                setFormData({ ...formData, password: e.target.value });
                if (fieldErrors.password) {
                  const newErrors = { ...fieldErrors };
                  delete newErrors.password;
                  setFieldErrors(newErrors);
                }
              }}
              className={`shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline ${
                fieldErrors.password ? 'border-red-500' : ''
              }`}
              required
            />
            {fieldErrors.password && (
              <p className="text-red-500 text-xs mt-1">{fieldErrors.password}</p>
            )}
          </div>

          <button
            type="submit"
            disabled={loading}
            className="w-full bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline disabled:opacity-50"
          >
            {loading ? 'Chargement...' : isLogin ? 'Se connecter' : 'S\'inscrire'}
          </button>
        </form>

        <p className="mt-4 text-center text-sm">
          {isLogin ? (
            <>
              Pas de compte ?{' '}
              <button
                onClick={() => {
                  setIsLogin(false);
                  setError(null);
                  setFieldErrors({});
                  setFormData({ name: '', email: '', password: '' });
                }}
                className="text-blue-500 hover:text-blue-700"
              >
                S'inscrire
              </button>
            </>
          ) : (
            <>
              Déjà un compte ?{' '}
              <button
                onClick={() => {
                  setIsLogin(true);
                  setError(null);
                  setFieldErrors({});
                  setFormData({ name: '', email: '', password: '' });
                }}
                className="text-blue-500 hover:text-blue-700"
              >
                Se connecter
              </button>
            </>
          )}
        </p>
        </div>
      </div>
      <Footer />
    </div>
  );
}

