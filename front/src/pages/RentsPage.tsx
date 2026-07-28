import React, { useEffect, useState } from 'react';
import { rentService } from '../services/rent.service';
import { scooterService } from '../services/scooter.service';
import { Rent, Scooter } from '../types';
import { Bike, Clock, CheckCircle, XCircle, Search, Filter, RefreshCw } from 'lucide-react';
import toast from 'react-hot-toast';

const statusColors: Record<string, string> = {
  active: 'bg-blue-100 text-blue-800',
  completed: 'bg-green-100 text-green-800',
};

const statusLabels: Record<string, string> = {
  active: 'Активна',
  completed: 'Завершена',
};

const RentsPage: React.FC = () => {
  const [rents, setRents] = useState<Rent[]>([]);
  const [availableScooters, setAvailableScooters] = useState<Scooter[]>([]);
  const [loading, setLoading] = useState(true);
  const [statusFilter, setStatusFilter] = useState('');
  const [phoneFilter, setPhoneFilter] = useState('');
  const [showCreateModal, setShowCreateModal] = useState(false);
  const [selectedScooter, setSelectedScooter] = useState<number | null>(null);
  const [creating, setCreating] = useState(false);

  useEffect(() => {
    loadRents();
    loadAvailableScooters();
  }, [statusFilter]);

  const loadRents = async () => {
    try {
      setLoading(true);
      const params: any = { per_page: 100 };
      if (statusFilter) params.status = statusFilter;
      
      const response = await rentService.getMyRents(params);
      setRents(response.data);
    } catch (error) {
      console.error('Error loading rents:', error);
      toast.error('Ошибка загрузки аренд');
    } finally {
      setLoading(false);
    }
  };

  const loadAvailableScooters = async () => {
    try {
      const response = await scooterService.getAvailable();
      setAvailableScooters(response.data || []);
    } catch (error) {
      console.error('Error loading scooters:', error);
    }
  };

  const handleCreateRent = async () => {
    if (!selectedScooter) {
      toast.error('Выберите самокат');
      return;
    }

    setCreating(true);
    try {
      await rentService.create(selectedScooter);
      toast.success('Аренда создана!');
      setShowCreateModal(false);
      setSelectedScooter(null);
      loadRents();
      loadAvailableScooters();
    } catch (error: any) {
      const message = error.response?.data?.message || 'Ошибка создания аренды';
      toast.error(message);
    } finally {
      setCreating(false);
    }
  };

  const handleCompleteRent = async (id: number) => {
    if (!window.confirm('Завершить аренду?')) return;
    
    try {
      await rentService.complete(id);
      toast.success('Аренда завершена!');
      loadRents();
      loadAvailableScooters();
    } catch (error: any) {
      toast.error(error.response?.data?.message || 'Ошибка завершения аренды');
    }
  };

  const formatDateTime = (dateStr: string) => {
    return new Date(dateStr).toLocaleString('ru-RU', {
      day: '2-digit',
      month: '2-digit',
      year: 'numeric',
      hour: '2-digit',
      minute: '2-digit',
    });
  };

  const formatDuration = (minutes: number | null) => {
    if (minutes === null) return '-';
    const hours = Math.floor(minutes / 60);
    const mins = minutes % 60;
    if (hours > 0) {
      return `${hours} ч ${mins} мин`;
    }
    return `${mins} мин`;
  };

  if (loading) {
    return (
      <div className="flex items-center justify-center h-64">
        <div className="w-8 h-8 border-4 border-primary-600 border-t-transparent rounded-full animate-spin" />
      </div>
    );
  }

  return (
    <div className="space-y-6">
      {/* Заголовок и кнопки */}
      <div className="flex items-center justify-between">
        <h2 className="text-2xl font-bold text-gray-900">Аренды</h2>
        <div className="flex space-x-3">
          <button
            onClick={() => {
              loadRents();
              loadAvailableScooters();
              toast.success('Данные обновлены');
            }}
            className="flex items-center px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors"
          >
            <RefreshCw size={18} className="mr-2" />
            Обновить
          </button>
          <button
            onClick={() => setShowCreateModal(true)}
            className="flex items-center px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors"
          >
            <Bike size={20} className="mr-2" />
            Новая аренда
          </button>
        </div>
      </div>

      {/* Фильтры */}
      <div className="flex flex-wrap gap-4">
        <div className="flex-1 min-w-[200px]">
          <div className="relative">
            <Search size={20} className="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" />
            <input
              type="text"
              placeholder="Поиск по телефону..."
              value={phoneFilter}
              onChange={(e) => setPhoneFilter(e.target.value)}
              onKeyUp={(e) => e.key === 'Enter' && loadRents()}
              className="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
            />
          </div>
        </div>
        <select
          value={statusFilter}
          onChange={(e) => setStatusFilter(e.target.value)}
          className="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500"
        >
          <option value="">Все статусы</option>
          <option value="active">Активные</option>
          <option value="completed">Завершённые</option>
        </select>
      </div>

      {/* Статистика */}
      <div className="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div className="bg-white rounded-xl shadow-sm p-6">
          <div className="flex items-center justify-between">
            <div>
              <p className="text-sm text-gray-600">Всего аренд</p>
              <p className="text-2xl font-bold text-gray-900">{rents.length}</p>
            </div>
            <div className="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center">
              <Filter size={24} className="text-purple-600" />
            </div>
          </div>
        </div>
        <div className="bg-white rounded-xl shadow-sm p-6">
          <div className="flex items-center justify-between">
            <div>
              <p className="text-sm text-gray-600">Активные</p>
              <p className="text-2xl font-bold text-blue-600">
                {rents.filter(r => r.status === 'active').length}
              </p>
            </div>
            <div className="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
              <Clock size={24} className="text-blue-600" />
            </div>
          </div>
        </div>
        <div className="bg-white rounded-xl shadow-sm p-6">
          <div className="flex items-center justify-between">
            <div>
              <p className="text-sm text-gray-600">Завершённые</p>
              <p className="text-2xl font-bold text-green-600">
                {rents.filter(r => r.status === 'completed').length}
              </p>
            </div>
            <div className="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
              <CheckCircle size={24} className="text-green-600" />
            </div>
          </div>
        </div>
      </div>

      {/* Таблица аренд */}
      <div className="bg-white rounded-xl shadow-sm overflow-hidden">
        <div className="overflow-x-auto">
          <table className="min-w-full divide-y divide-gray-200">
            <thead className="bg-gray-50">
              <tr>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Самокат</th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Телефон</th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Начало</th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Окончание</th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Длительность</th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Стоимость</th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Статус</th>
                <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Действия</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-gray-200">
              {rents.length === 0 ? (
                <tr>
                  <td colSpan={9} className="px-6 py-12 text-center text-gray-500">
                    <Bike size={48} className="mx-auto mb-4 text-gray-300" />
                    <p className="text-lg">Нет аренд</p>
                    <p className="text-sm">Создайте новую аренду, нажав кнопку выше</p>
                  </td>
                </tr>
              ) : (
                rents.map((rent) => (
                  <tr key={rent.id} className="hover:bg-gray-50">
                    <td className="px-6 py-4 text-sm text-gray-900">#{rent.id}</td>
                    <td className="px-6 py-4 text-sm font-medium text-gray-900">
                      {rent.scooter?.model || 'Самокат'}
                    </td>
                    <td className="px-6 py-4 text-sm text-gray-500">{rent.user_phone}</td>
                    <td className="px-6 py-4 text-sm text-gray-500">
                      {formatDateTime(rent.start_time)}
                    </td>
                    <td className="px-6 py-4 text-sm text-gray-500">
                      {rent.end_time ? formatDateTime(rent.end_time) : '-'}
                    </td>
                    <td className="px-6 py-4 text-sm text-gray-500">
                      {formatDuration(rent.duration_minutes)}
                    </td>
                    <td className="px-6 py-4 text-sm font-medium">
                      {rent.cost_rub ? `${rent.cost_rub} ₽` : '-'}
                    </td>
                    <td className="px-6 py-4">
                      <span className={`inline-flex px-2 py-1 text-xs font-semibold rounded-full ${statusColors[rent.status]}`}>
                        {statusLabels[rent.status]}
                      </span>
                    </td>
                    <td className="px-6 py-4 text-right">
                      {rent.status === 'active' && (
                        <button
                          onClick={() => handleCompleteRent(rent.id)}
                          className="inline-flex items-center px-3 py-1 bg-green-50 text-green-700 rounded-lg hover:bg-green-100 transition-colors text-sm"
                        >
                          <CheckCircle size={16} className="mr-1" />
                          Завершить
                        </button>
                      )}
                    </td>
                  </tr>
                ))
              )}
            </tbody>
          </table>
        </div>
      </div>

      {/* Модальное окно создания аренды */}
      {showCreateModal && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
          <div className="bg-white rounded-xl shadow-xl w-full max-w-lg mx-4">
            <div className="flex items-center justify-between p-6 border-b">
              <h3 className="text-lg font-semibold">Новая аренда</h3>
              <button
                onClick={() => setShowCreateModal(false)}
                className="text-gray-400 hover:text-gray-600"
              >
                <XCircle size={24} />
              </button>
            </div>

            <div className="p-6">
              <p className="text-sm text-gray-600 mb-4">
                Выберите доступный самокат для аренды. Аренда будет создана на ваш аккаунт.
              </p>

              {availableScooters.length === 0 ? (
                <div className="text-center py-8">
                  <Bike size={48} className="mx-auto mb-4 text-gray-300" />
                  <p className="text-gray-500">Нет доступных самокатов</p>
                  <p className="text-sm text-gray-400">Все самокаты заняты или на обслуживании</p>
                </div>
              ) : (
                <div className="space-y-3 max-h-96 overflow-y-auto">
                  {availableScooters.map((scooter) => (
                    <div
                      key={scooter.id}
                      onClick={() => setSelectedScooter(scooter.id)}
                      className={`p-4 rounded-lg border-2 cursor-pointer transition-all ${
                        selectedScooter === scooter.id
                          ? 'border-primary-500 bg-primary-50'
                          : 'border-gray-200 hover:border-gray-300'
                      }`}
                    >
                      <div className="flex items-center justify-between">
                        <div>
                          <p className="font-medium text-gray-900">{scooter.model}</p>
                          <p className="text-sm text-gray-500">
                            ID: #{scooter.id} | 🔋 {scooter.battery_level}%
                          </p>
                          <p className="text-xs text-gray-400">
                            📍 {scooter.latitude.toFixed(4)}, {scooter.longitude.toFixed(4)}
                          </p>
                        </div>
                        {selectedScooter === scooter.id && (
                          <CheckCircle size={24} className="text-primary-600" />
                        )}
                      </div>
                    </div>
                  ))}
                </div>
              )}
            </div>

            <div className="flex justify-end space-x-3 p-6 border-t">
              <button
                onClick={() => setShowCreateModal(false)}
                className="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50"
              >
                Отмена
              </button>
              <button
                onClick={handleCreateRent}
                disabled={!selectedScooter || creating || availableScooters.length === 0}
                className="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 disabled:opacity-50 disabled:cursor-not-allowed flex items-center"
              >
                {creating ? (
                  <>
                    <div className="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin mr-2" />
                    Создание...
                  </>
                ) : (
                  <>
                    <Bike size={18} className="mr-2" />
                    Создать аренду
                  </>
                )}
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
};

export default RentsPage;
