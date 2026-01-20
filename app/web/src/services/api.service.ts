import { apiClient } from '../config/api';

export interface HealthResponse {
  status: string;
  timestamp: string;
  service: string;
}

export interface MessageResponse {
  message: string;
  description: string;
  timestamp: string;
}

export const apiService = {
  async getHealth(): Promise<HealthResponse> {
    const response = await apiClient.get<HealthResponse>('/health');
    return response.data;
  },

  async getMessage(): Promise<MessageResponse> {
    const response = await apiClient.get<MessageResponse>('/message');
    return response.data;
  },
};
