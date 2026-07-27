import api from './api';
import { ApiResponse, Rent } from '../types';

export const rentService = {
  async getAll(params?: {
    status?: string;
    user_phone?: string;
    scooter_id?: number;
    per_page?: number;
  }): Promise<ApiResponse<Rent[]>> {
    const { data } = await api.get('/management/rents', { params });
    return data;
  },

  async getById(id: number): Promise<{ data: Rent }> {
    const { data } = await api.get(`/management/rents/${id}`);
    return data;
  },

  async create(scooterId: number): Promise<{ data: Rent }> {
    const { data } = await api.post('/management/rents', {
      scooter_id: scooterId,
    });
    return data;
  },

  async complete(id: number): Promise<{ data: Rent }> {
    const { data } = await api.put(`/management/rents/${id}/complete`);
    return data;
  },

  async getActive(): Promise<{ has_active_rent: boolean; data?: Rent }> {
    const { data } = await api.get('/rents/active');
    return data;
  },

  async getHistory(params?: { per_page?: number }): Promise<ApiResponse<Rent[]>> {
    const { data } = await api.get('/rents/history', { params });
    return data;
  },

  async getStats(): Promise<any> {
    const { data } = await api.get('/management/rents/stats');
    return data;
  },
};