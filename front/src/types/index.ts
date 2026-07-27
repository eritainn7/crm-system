export interface User {
  id: number;
  full_name: string;
  phone: string;
}

export interface Scooter {
  id: number;
  model: string;
  status: 'available' | 'in_use' | 'maintenance' | 'offline';
  battery_level: number;
  latitude: number;
  longitude: number;
  last_updated: string;
  created_at?: string;
  updated_at?: string;
}

export interface Rent {
  id: number;
  user_phone: string;
  scooter: {
    id: number;
    model: string;
  };
  start_time: string;
  end_time: string | null;
  duration_minutes: number | null;
  cost_rub: number | null;
  status: 'active' | 'completed';
}

export interface DashboardData {
  scooters: {
    total: number;
    by_status: {
      available: number;
      in_use: number;
      maintenance: number;
      offline: number;
    };
    battery: {
      average: number;
      by_status: {
        available: number;
        in_use: number;
        maintenance: number;
        offline: number;
      };
    };
  };
  user: {
    phone: string;
    full_name: string;
    active_rents_count: number;
    has_active_rent: boolean;
    active_rent: {
      id: number;
      scooter_model: string;
      start_time: string;
      duration_minutes: number;
    } | null;
  };
  rents: {
    total: number;
    active: number;
    completed: number;
  };
}

export interface AuthResponse {
  message: string;
  user: User;
  token: string;
}

export interface ApiResponse<T> {
  message: string;
  data: T;
  pagination?: {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
  };
}