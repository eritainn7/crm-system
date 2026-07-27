import api from './api';
import { ApiResponse, Scooter } from '../types';

export const scooterService = {
  async getAll(params?: {
    status?: string;
    min_battery?: number;
    model?: string;
    sort_by?: string;
    per_page?: number;
  }): Promise<ApiResponse<Scooter[]>> {
    const { data } = await api.get('/management/scooters', { params });
    return data;
  },

  async getById(id: number): Promise<{ data: Scooter }> {
    const { data } = await api.get(`/management/scooters/${id}`);
    return data;
  },

  async create(scooter: Partial<Scooter>): Promise<{ data: Scooter }> {
    const { data } = await api.post('/management/scooters', scooter);
    return data;
  },

  async update(id: number, scooter: Partial<Scooter>): Promise<{ data: Scooter }> {
    const { data } = await api.put(`/management/scooters/${id}`, scooter);
    return data;
  },

  async delete(id: number): Promise<void> {
    await api.delete(`/management/scooters/${id}`);
  },

  async getAvailable(lat?: number, lng?: number, radius?: number): Promise<ApiResponse<Scooter[]>> {
    const { data } = await api.get('/scooters/available', {
      params: { latitude: lat, longitude: lng, radius },
    });
    return data;
  },

  async batchUpdateStatus(ids: number[], status: string): Promise<void> {
    await api.post('/management/scooters/batch-status', { ids, status });
  },
};