import { fetchApi } from './api';

export interface Article {
  id: string;
  title: string;
  content: string;
  auteur_id: string;
  publication_date: string;
  author?: {
    id: string;
    name: string;
  };
}

export interface PaginatedResponse<T> {
  data: T[];
  total: number;
  page: number;
  limit: number;
  has_previous: boolean;
  has_next: boolean;
  total_pages: number;
}

export interface ArticleDTO {
  title: string;
  content: string;
}

export const articlesService = {
  async getArticles(page = 1, limit = 10): Promise<PaginatedResponse<Article>> {
    const response = await fetchApi<PaginatedResponse<Article>>(
      `/articles?page=${page}&limit=${limit}`
    );
    return response as PaginatedResponse<Article>;
  },

  async getArticle(id: string): Promise<Article> {
    const response = await fetchApi<Article>(`/articles/${id}`);
    return response as Article;
  },

  async createArticle(data: ArticleDTO): Promise<Article> {
    const response = await fetchApi<Article>('/articles', {
      method: 'POST',
      body: JSON.stringify(data),
    });
    return response as Article;
  },

  async updateArticle(id: string, data: Partial<ArticleDTO>): Promise<Article> {
    const response = await fetchApi<Article>(`/articles/${id}`, {
      method: 'PUT',
      body: JSON.stringify(data),
    });
    return response as Article;
  },

  async deleteArticle(id: string): Promise<void> {
    await fetchApi(`/articles/${id}`, {
      method: 'DELETE',
    });
  },
};
