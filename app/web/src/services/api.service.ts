import { apiClient } from './AuthService';
import type {
  Category,
  Transaction,
  Summary,
  ExpenseByCategory,
  ApiResponse,
  TransactionFormData,
  CategoryFormData,
  AnalyticsData,
  EvaluationResponse,
  CsvImportItem,
  PaginatedResponse,
  FeedbackHistory,
  UserProgress,
} from '../types';

// Category API
export const categoryApi = {
  getAll: async (type?: 'income' | 'expense'): Promise<Category[]> => {
    const params = type ? { type } : {};
    const response = await apiClient.get<ApiResponse<Category[]>>('/categories', { params });
    return response.data.data || [];
  },

  getById: async (id: number): Promise<Category> => {
    const response = await apiClient.get<ApiResponse<Category>>(`/categories/${id}`);
    if (!response.data.data) throw new Error('Category not found');
    return response.data.data;
  },

  create: async (data: CategoryFormData): Promise<Category> => {
    const response = await apiClient.post<ApiResponse<Category>>('/categories', data);
    if (!response.data.data) throw new Error('Failed to create category');
    return response.data.data;
  },

  update: async (id: number, data: Partial<CategoryFormData>): Promise<Category> => {
    const response = await apiClient.put<ApiResponse<Category>>(`/categories/${id}`, data);
    if (!response.data.data) throw new Error('Failed to update category');
    return response.data.data;
  },

  delete: async (id: number): Promise<void> => {
    await apiClient.delete(`/categories/${id}`);
  },
};

// Transaction API
export const transactionApi = {
  getAll: async (params?: {
    type?: 'income' | 'expense';
    category_id?: number | string | null;
    start_date?: string;
    end_date?: string;
    min_amount?: number;
    max_amount?: number;
    page?: number;
    per_page?: number;
  }): Promise<PaginatedResponse<Transaction>> => {
    const response = await apiClient.get<PaginatedResponse<Transaction>>('/transactions', { params });
    return response.data;
  },

  getById: async (id: number): Promise<Transaction> => {
    const response = await apiClient.get<ApiResponse<Transaction>>(`/transactions/${id}`);
    if (!response.data.data) throw new Error('Transaction not found');
    return response.data.data;
  },

  create: async (data: TransactionFormData): Promise<Transaction> => {
    const response = await apiClient.post<ApiResponse<Transaction>>('/transactions', data);
    if (!response.data.data) throw new Error('Failed to create transaction');
    return response.data.data;
  },

  update: async (id: number, data: Partial<TransactionFormData>): Promise<Transaction> => {
    const response = await apiClient.put<ApiResponse<Transaction>>(`/transactions/${id}`, data);
    if (!response.data.data) throw new Error('Failed to update transaction');
    return response.data.data;
  },

  delete: async (id: number): Promise<void> => {
    await apiClient.delete(`/transactions/${id}`);
  },

  getSummary: async (): Promise<{ summary: Summary; expenses_by_category: ExpenseByCategory[] }> => {
    const response = await apiClient.get<ApiResponse<{ summary: Summary; expenses_by_category: ExpenseByCategory[] }>>('/transactions/summary');
    if (!response.data.data) throw new Error('Failed to fetch summary');
    return response.data.data;
  },

  // Analytics endpoints
  getDailyAnalytics: async (days: number = 30): Promise<AnalyticsData> => {
    const response = await apiClient.get<ApiResponse<AnalyticsData>>('/transactions/analytics/daily', {
      params: { days },
    });
    if (!response.data.data) throw new Error('Failed to fetch daily analytics');
    return response.data.data;
  },

  getMonthlyAnalytics: async (months: number = 12): Promise<AnalyticsData> => {
    const response = await apiClient.get<ApiResponse<AnalyticsData>>('/transactions/analytics/monthly', {
      params: { months },
    });
    if (!response.data.data) throw new Error('Failed to fetch monthly analytics');
    return response.data.data;
  },

  getYearlyAnalytics: async (): Promise<AnalyticsData> => {
    const response = await apiClient.get<ApiResponse<AnalyticsData>>('/transactions/analytics/yearly');
    if (!response.data.data) throw new Error('Failed to fetch yearly analytics');
    return response.data.data;
  },

  importPreview: async (file: File): Promise<CsvImportItem[]> => {
    const formData = new FormData();
    formData.append('file', file);
    const response = await apiClient.post<ApiResponse<CsvImportItem[]>>('/transactions/import-preview', formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    });
    return response.data.data || [];
  },

  importConfirm: async (items: { data: any; skip?: boolean }[]): Promise<{ imported: number; skipped: number }> => {
    const response = await apiClient.post<ApiResponse<{ imported: number; skipped: number }>>('/transactions/import-confirm', { items });
    if (!response.data.data) throw new Error('Failed to confirm import');
    return response.data.data;
  },
};

// Legacy exports for backward compatibility
export interface MessageResponse {
  message: string;
  description: string;
}

export const apiService = {
  getMessage: async (): Promise<MessageResponse> => {
    const response = await apiClient.get<MessageResponse>('/message');
    return response.data;
  },
};

// Rule Engine API
export const ruleEngineApi = {
  evaluate: async (date?: string): Promise<EvaluationResponse> => {
    const params = date ? { date } : {};
    const response = await apiClient.get<ApiResponse<EvaluationResponse>>('/rules/evaluate', { params });
    if (!response.data.data) throw new Error('Failed to evaluate rules');
    return response.data.data;
  },
};

// Feedback API
export const feedbackApi = {
  getAll: async (params?: { page?: number; per_page?: number }): Promise<PaginatedResponse<FeedbackHistory>> => {
    const response = await apiClient.get<PaginatedResponse<FeedbackHistory>>('/feedback', { params });
    return response.data;
  },

  getProgress: async (): Promise<UserProgress[]> => {
    const response = await apiClient.get<ApiResponse<UserProgress[]>>('/feedback/progress');
    return response.data.data || [];
  },

  acknowledge: async (id: number): Promise<boolean> => {
    const response = await apiClient.post<ApiResponse<any>>(`/feedback/${id}/acknowledge`);
    return !!response.data.success;
  },
};

