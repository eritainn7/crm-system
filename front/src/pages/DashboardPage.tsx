import React, { useEffect, useState } from 'react';
import { dashboardService } from '../services/dashboard.service';
import { DashboardData } from '../types';
import { Bike, Battery, Clock, DollarSign, Users } from 'lucide-react';

const DashboardPage: React.FC = () => {
  const [data, setData] = useState<DashboardData | null>(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    loadDashboard();
  }, []);

  const loadDashboard = async () => {
    try {
      const response = await dashboardService.getData();
      setData(response.data);
    } catch (error) {
      console.error('Error loading dashboard:', error);
    } finally {
      setLoading(false);
    }
  };

  if (loading) {
    return (
      <div className="flex items-center justify-center h-64">
        <div className="w-8 h-8 border-4 border-primary-600 border-t-transparent rounded-full animate-spin" />
      </div>
    );
  }

  if (!data) return null;

  const statusCards = [
    {
      label: 'Доступны',
      value: data.scooters.by_status.available,
      color: 'text-green-600',
      bg: 'bg-green-50',
      icon: Bike,
    },
    {
      label: 'В аренде',
      value: data.scooters.by_status.in_use,
      color: 'text-blue-600',
      bg: 'bg-blue-50',
      icon: Clock,
    },
    {
      label: 'Обслуживание',
      value: data.scooters.by_status.maintenance,
      color: 'text-yellow-600',
      bg: 'bg-yellow-50',
      icon: Battery,
    },
    {
      label: 'Оффлайн',
      value: data.scooters.by_status.offline,
      color: 'text-gray-600',
      bg: 'bg-gray-50',
      icon: Bike,
    },
  ];

  return (
    <div className="space-y-6">
      <h2 className="text-2xl font-bold text-gray-900">Дашборд</h2>

      {/* Приветствие */}
      <div className="bg-gradient-to-r from-primary-500 to-primary-700 rounded-xl p-6 text-white">
        <h3 className="text-xl font-semibold">
          Добро пожаловать, {data.user.full_name}!
        </h3>
        <p className="mt-1 opacity-90">
          {data.user.has_active_rent
            ? `У вас активная аренда: ${data.user.active_rent?.scooter_model} (${data.user.active_rent?.duration_minutes} мин)`
            : 'У вас нет активных аренд'}
        </p>
      </div>

      {/* Статусы самокатов */}
      <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        {statusCards.map((card) => {
          const Icon = card.icon;
          return (
            <div key={card.label} className={`${card.bg} rounded-xl p-6`}>
              <div className="flex items-center justify-between">
                <div>
                  <p className="text-sm text-gray-600">{card.label}</p>
                  <p className={`text-3xl font-bold mt-1 ${card.color}`}>
                    {card.value}
                  </p>
                </div>
                <Icon size={32} className={card.color} />
              </div>
            </div>
          );
        })}
      </div>

      {/* Детальная статистика */}
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {/* Заряд батарей */}
        <div className="bg-white rounded-xl shadow-sm p-6">
          <h3 className="text-lg font-semibold text-gray-900 mb-4">
            Уровень заряда
          </h3>
          <div className="space-y-4">
            {Object.entries(data.scooters.battery.by_status).map(([status, avg]) => (
              <div key={status}>
                <div className="flex justify-between text-sm mb-1">
                  <span className="text-gray-600 capitalize">
                    {status === 'available' ? 'Доступны' :
                     status === 'in_use' ? 'В аренде' :
                     status === 'maintenance' ? 'Обслуживание' : 'Оффлайн'}
                  </span>
                  <span className="font-medium">{avg}%</span>
                </div>
                <div className="w-full bg-gray-200 rounded-full h-2">
                  <div
                    className={`h-2 rounded-full ${
                      avg > 70 ? 'bg-green-500' :
                      avg > 30 ? 'bg-yellow-500' : 'bg-red-500'
                    }`}
                    style={{ width: `${avg}%` }}
                  />
                </div>
              </div>
            ))}
          </div>
          <div className="mt-4 pt-4 border-t">
            <div className="flex justify-between items-center">
              <span className="text-gray-600">Средний заряд</span>
              <span className="text-2xl font-bold text-primary-600">
                {data.scooters.battery.average}%
              </span>
            </div>
          </div>
        </div>

        {/* Статистика аренд */}
        <div className="bg-white rounded-xl shadow-sm p-6">
          <h3 className="text-lg font-semibold text-gray-900 mb-4">
            Аренды
          </h3>
          <div className="space-y-4">
            <div className="flex items-center justify-between p-4 bg-blue-50 rounded-lg">
              <div className="flex items-center">
                <Clock className="text-blue-600 mr-3" />
                <span className="text-gray-700">Активные аренды</span>
              </div>
              <span className="text-2xl font-bold text-blue-600">
                {data.rents.active}
              </span>
            </div>
            <div className="flex items-center justify-between p-4 bg-green-50 rounded-lg">
              <div className="flex items-center">
                <DollarSign className="text-green-600 mr-3" />
                <span className="text-gray-700">Завершённые аренды</span>
              </div>
              <span className="text-2xl font-bold text-green-600">
                {data.rents.completed}
              </span>
            </div>
            <div className="flex items-center justify-between p-4 bg-purple-50 rounded-lg">
              <div className="flex items-center">
                <Users className="text-purple-600 mr-3" />
                <span className="text-gray-700">Всего аренд</span>
              </div>
              <span className="text-2xl font-bold text-purple-600">
                {data.rents.total}
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};

export default DashboardPage;