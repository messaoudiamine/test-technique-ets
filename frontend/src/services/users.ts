import { fetchApi } from './api';

export interface User {
  id: string;
  name: string;
  email: string;
  roles: string[];
}

export interface PaginatedUsersResponse {
  data: User[];
  total: number;
  page: number;
  limit: number;
  has_previous: boolean;
  has_next: boolean;
  total_pages: number;
}

export const usersService = {
  async getUsers(page: number = 1, limit: number = 10): Promise<PaginatedUsersResponse> {
    const response = await fetchApi<PaginatedUsersResponse>(`/users?page=${page}&limit=${limit}`);
    return response as PaginatedUsersResponse;
  },

  async updateUser(id: string, data: Partial<{ name: string; email: string; password: string }>): Promise<User> {
    const response = await fetchApi<User>(`/users/${id}`, {
      method: 'PUT',
      body: JSON.stringify(data),
    });
    return response as User;
  },

  async deleteUser(id: string): Promise<void> {
    await fetchApi(`/users/${id}`, {
      method: 'DELETE',
    });
  },
};

