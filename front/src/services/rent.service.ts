import api from './api';
import { ApiResponse, Rent } from '../types';

export const rentService = {
  // Мои аренды (все статусы)
  async getMyRents(params?: {
    status?: string;
    per_page?: number;
  }): Promise<ApiResponse<Rent[]>> {
    const { data } = await api.get('/rents', { params });
    return data;
  },

  // Создать аренду
  async create(scooterId: number): Promise<{ data: Rent }> {
    const { data } = await api.post('/rents', {
      scooter_id: scooterId,
    });
    return data;
  },

  // Завершить аренду
  async complete(id: number): Promise<{ data: Rent }> {
    const { data } = await api.put(`/rents/${id}/complete`);
    return data;
  },

  // Активная аренда
  async getActive(): Promise<{ has_active_rent: boolean; data?: Rent }> {
    const { data } = await api.get('/rents/active');
    return data;
  },

  // История аренд
  async getHistory(params?: { per_page?: number }): Promise<ApiResponse<Rent[]>> {
    const { data } = await api.get('/rents/history', { params });
    return data;
  },
};
