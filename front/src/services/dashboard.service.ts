import api from './api';
import { DashboardData } from '../types';

export const dashboardService = {
  async getData(): Promise<{ data: DashboardData }> {
    const { data } = await api.get('/dashboard');
    return data;
  },
};