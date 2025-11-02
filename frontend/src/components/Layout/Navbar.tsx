'use client';

import Link from 'next/link';
import { useAuth } from '@/hooks/useAuth';

export default function Navbar() {
  const { user, logout, isAdmin } = useAuth();

  return (
    <nav className="bg-blue-600 text-white p-4">
      <div className="container mx-auto px-20 flex justify-between items-center">
        <div className="flex space-x-4">
            <Link href="/account" className="hover:text-blue-200">
                Mon Compte
            </Link>
            <Link href="/articles" className="hover:text-blue-200">
              Articles
          </Link>
          {isAdmin && (
            <Link href="/admin" className="hover:text-blue-200">
              Utilisateurs
            </Link>
          )}
        </div>
        <div className="flex items-center space-x-4">
          {user && (
            <span className="text-sm">Bonjour, {user.name}</span>
          )}
          <button
            onClick={logout}
            className="bg-red-500 hover:bg-red-700 px-4 py-2 rounded"
          >
            Déconnexion
          </button>
        </div>
      </div>
    </nav>
  );
}

