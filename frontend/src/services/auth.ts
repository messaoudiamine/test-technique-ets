import { fetchApi } from './api';
import { setToken, removeToken } from '@/lib/auth';

export interface LoginData {
  email: string;
  password: string;
}

export interface RegisterData {
  name: string;
  email: string;
  password: string;
}

export interface User {
  id: string;
  name: string;
  email: string;
  roles: string[];
}

export const authService = {
  async register(data: RegisterData) {
    const response = await fetchApi<{ token: string; user: User }>('/auth/register', {
      method: 'POST',
      body: JSON.stringify(data),
    });

    if (response.token) {
      setToken(response.token);
    }

    return response;
  },

  async login(data: LoginData) {
    const response = await fetchApi<{ token: string }>('/auth/login', {
      method: 'POST',
      body: JSON.stringify(data),
    });

    if (response.token) {
      setToken(response.token);
    }

    return response;
  },

  async getProfile(): Promise<User> {
    const response = await fetchApi<User>('/users/profile');
    return response as User;
  },

  async updateProfile(data: Partial<{ name: string; email: string; password: string }>) {
    const response = await fetchApi<User>('/users/profile', {
      method: 'PUT',
      body: JSON.stringify(data),
    });
    return response;
  },

  logout() {
    removeToken();
  },
};
