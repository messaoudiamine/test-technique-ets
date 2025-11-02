'use client';

import { useState, useEffect } from 'react';
import { authService, User } from '@/services/auth';
import { isAuthenticated, removeToken } from '@/lib/auth';
import { useRouter } from 'next/navigation';

export const useAuth = () => {
  const [user, setUser] = useState<User | null>(null);
  const [loading, setLoading] = useState(true);
  const router = useRouter();

  useEffect(() => {
    if (isAuthenticated()) {
      authService
        .getProfile()
        .then(setUser)
        .catch(() => {
          removeToken();
          router.push('/login');
        })
        .finally(() => setLoading(false));
    } else {
      setLoading(false);
    }
  }, [router]);

  const logout = () => {
    authService.logout();
    setUser(null);
    router.push('/login');
  };

  const isAdmin = user?.roles?.includes('ROLE_ADMIN') || false;

  return { user, loading, logout, isAuthenticated: isAuthenticated(), isAdmin };
};

