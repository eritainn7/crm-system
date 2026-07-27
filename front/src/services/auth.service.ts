import api from './api';
import { AuthResponse, User } from '../types';

export const authService = {
  async login(phone: string, password: string): Promise<AuthResponse> {
    const { data } = await api.post('/auth/log', { phone, password });
    localStorage.setItem('token', data.token);
    localStorage.setItem('user', JSON.stringify(data.user));
    return data;
  },

  async register(fullName: string, phone: string, password: string): Promise<AuthResponse> {
    const { data } = await api.post('/auth/reg', {
      full_name: fullName,
      phone,
      password,
      password_confirmation: password,
    });
    localStorage.setItem('token', data.token);
    localStorage.setItem('user', JSON.stringify(data.user));
    return data;
  },

  async logout(): Promise<void> {
    await api.post('/auth/out');
    localStorage.removeItem('token');
    localStorage.removeItem('user');
  },

  getCurrentUser(): User | null {
    const userStr = localStorage.getItem('user');
    return userStr ? JSON.parse(userStr) : null;
  },

  isAuthenticated(): boolean {
    return !!localStorage.getItem('token');
  },
};