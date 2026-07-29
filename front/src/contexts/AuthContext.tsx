import React, { createContext, useContext, useState, useEffect } from 'react';
import { User } from '../types';
import { authService } from '../services/auth.service';

interface AuthContextType {
  user: User | null;
  isAuthenticated: boolean;
  login: (phone: string, password: string) => Promise<void>;
  register: (fullName: string, phone: string, password: string) => Promise<void>;
  logout: () => Promise<void>;
}

const AuthContext = createContext<AuthContextType>({} as AuthContextType);

export const useAuth = () => useContext(AuthContext);

export const AuthProvider: React.FC<{ children: React.ReactNode }> = ({ children }) => {
  // Начинаем с null, а не с getCurrentUser()
  const [user, setUser] = useState<User | null>(null);
  const [isLoading, setIsLoading] = useState(true);

  // Загружаем пользователя после монтирования
  useEffect(() => {
    try {
      const savedUser = authService.getCurrentUser();
      if (savedUser) {
        setUser(savedUser);
      }
    } catch (error) {
      console.warn('Failed to load user, clearing storage');
      localStorage.removeItem('user');
      localStorage.removeItem('token');
    } finally {
      setIsLoading(false);
    }
  }, []);

  const isAuthenticated = !!user;

  const login = async (phone: string, password: string) => {
    const response = await authService.login(phone, password);
    setUser(response.user);
  };

  const register = async (fullName: string, phone: string, password: string) => {
    const response = await authService.register(fullName, phone, password);
    setUser(response.user);
  };

  const logout = async () => {
    await authService.logout();
    setUser(null);
  };

  // Показываем загрузку только при первом рендере
  if (isLoading) {
    return null; 
  }

  return (
    <AuthContext.Provider value={{ user, isAuthenticated, login, register, logout }}>
      {children}
    </AuthContext.Provider>
  );
};