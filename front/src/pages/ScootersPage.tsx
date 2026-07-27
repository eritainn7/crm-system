import React, { useEffect, useState } from 'react';
import { scooterService } from '../services/scooter.service';
import { Scooter } from '../types';
import { MapContainer, TileLayer, Marker, Popup } from 'react-leaflet';
import { Icon } from 'leaflet';
import { Plus, Edit2, Trash2, Search } from 'lucide-react';
import toast from 'react-hot-toast';
import ScooterModal from '../components/ScooterModal';

const statusColors: Record<string, string> = {
  available: 'bg-green-100 text-green-800',
  in_use: 'bg-blue-100 text-blue-800',
  maintenance: 'bg-yellow-100 text-yellow-800',
  offline: 'bg-gray-100 text-gray-800',
};

const statusLabels: Record<string, string> = {
  available: 'Доступен',
  in_use: 'В аренде',
  maintenance: 'Обслуживание',
  offline: 'Оффлайн',
};

const scooterIcon = new Icon({
  iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-green.png',
  shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
  iconSize: [25, 41],
  iconAnchor: [12, 41],
  popupAnchor: [1, -34],
  shadowSize: [41, 41],
});

const ScootersPage: React.FC = () => {
  const [scooters, setScooters] = useState<Scooter[]>([]);
  const [loading, setLoading] = useState(true);
  const [showModal, setShowModal] = useState(false);
  const [editingScooter, setEditingScooter] = useState<Scooter | null>(null);
  const [search, setSearch] = useState('');
  const [statusFilter, setStatusFilter] = useState('');
  const [viewMode, setViewMode] = useState<'table' | 'map'>('table');

  useEffect(() => {
    loadScooters();
  }, [statusFilter]);

  const loadScooters = async () => {
    try {
      const params: any = { per_page: 100 };
      if (statusFilter) params.status = statusFilter;
      if (search) params.model = search;
      
      const response = await scooterService.getAll(params);
      setScooters(response.data);
    } catch (error) {
      console.error('Error loading scooters:', error);
    } finally {
      setLoading(false);
    }
  };

  const handleCreate = async (scooter: Partial<Scooter>) => {
    try {
      await scooterService.create(scooter);
      toast.success('Самокат создан');
      loadScooters();
      setShowModal(false);
    } catch (error: any) {
      toast.error(error.response?.data?.message || 'Ошибка создания');
    }
  };

  const handleUpdate = async (scooter: Partial<Scooter>) => {
    if (!editingScooter) return;
    try {
      await scooterService.update(editingScooter.id, scooter);
      toast.success('Самокат обновлён');
      loadScooters();
      setShowModal(false);
      setEditingScooter(null);
    } catch (error: any) {
      toast.error(error.response?.data?.message || 'Ошибка обновления');
    }
  };

  const handleDelete = async (id: number) => {
    if (!window.confirm('Удалить самокат?')) return;
    try {
      await scooterService.delete(id);
      toast.success('Самокат удалён');
      loadScooters();
    } catch (error) {
      toast.error('Ошибка удаления');
    }
  };

  const batteryColor = (level: number) => {
    if (level > 70) return 'text-green-600';
    if (level > 30) return 'text-yellow-600';
    return 'text-red-600';
  };

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <h2 className="text-2xl font-bold text-gray-900">Самокаты</h2>
        <button
          onClick={() => {
            setEditingScooter(null);
            setShowModal(true);
          }}
          className="flex items-center px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors"
        >
          <Plus size={20} className="mr-2" />
          Добавить самокат
        </button>
      </div>

      {/* Фильтры */}
      <div className="flex flex-wrap gap-4">
        <div className="flex-1 min-w-[200px]">
          <div className="relative">
            <Search size={20} className="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" />
            <input
              type="text"
              placeholder="Поиск по модели..."
              value={search}
              onChange={(e) => setSearch(e.target.value)}
              onKeyUp={(e) => e.key === 'Enter' && loadScooters()}
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
          <option value="available">Доступен</option>
          <option value="in_use">В аренде</option>
          <option value="maintenance">Обслуживание</option>
          <option value="offline">Оффлайн</option>
        </select>
        <div className="flex rounded-lg overflow-hidden border border-gray-300">
          <button
            onClick={() => setViewMode('table')}
            className={`px-4 py-2 ${viewMode === 'table' ? 'bg-primary-600 text-white' : 'bg-white text-gray-700'}`}
          >
            Таблица
          </button>
          <button
            onClick={() => setViewMode('map')}
            className={`px-4 py-2 ${viewMode === 'map' ? 'bg-primary-600 text-white' : 'bg-white text-gray-700'}`}
          >
            Карта
          </button>
        </div>
      </div>

      {/* Таблица */}
      {viewMode === 'table' && (
        <div className="bg-white rounded-xl shadow-sm overflow-hidden">
          <div className="overflow-x-auto">
            <table className="min-w-full divide-y divide-gray-200">
              <thead className="bg-gray-50">
                <tr>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Модель</th>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Статус</th>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Заряд</th>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Координаты</th>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Обновлён</th>
                  <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Действия</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-gray-200">
                {scooters.map((scooter) => (
                  <tr key={scooter.id} className="hover:bg-gray-50">
                    <td className="px-6 py-4 text-sm text-gray-900">#{scooter.id}</td>
                    <td className="px-6 py-4 text-sm font-medium text-gray-900">{scooter.model}</td>
                    <td className="px-6 py-4">
                      <span className={`inline-flex px-2 py-1 text-xs font-semibold rounded-full ${statusColors[scooter.status]}`}>
                        {statusLabels[scooter.status]}
                      </span>
                    </td>
                    <td className="px-6 py-4">
                      <div className="flex items-center">
                        <div className="w-16 bg-gray-200 rounded-full h-2 mr-2">
                          <div
                            className={`h-2 rounded-full ${
                              scooter.battery_level > 70 ? 'bg-green-500' :
                              scooter.battery_level > 30 ? 'bg-yellow-500' : 'bg-red-500'
                            }`}
                            style={{ width: `${scooter.battery_level}%` }}
                          />
                        </div>
                        <span className={`text-sm font-medium ${batteryColor(scooter.battery_level)}`}>
                          {scooter.battery_level}%
                        </span>
                      </div>
                    </td>
                    <td className="px-6 py-4 text-sm text-gray-500">
                      {scooter.latitude.toFixed(4)}, {scooter.longitude.toFixed(4)}
                    </td>
                    <td className="px-6 py-4 text-sm text-gray-500">
                      {new Date(scooter.last_updated).toLocaleString('ru-RU')}
                    </td>
                    <td className="px-6 py-4 text-right">
                      <button
                        onClick={() => {
                          setEditingScooter(scooter);
                          setShowModal(true);
                        }}
                        className="text-primary-600 hover:text-primary-800 mr-3"
                      >
                        <Edit2 size={18} />
                      </button>
                      <button
                        onClick={() => handleDelete(scooter.id)}
                        className="text-red-600 hover:text-red-800"
                      >
                        <Trash2 size={18} />
                      </button>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </div>
      )}

      {/* Карта */}
      {viewMode === 'map' && (
        <div className="bg-white rounded-xl shadow-sm overflow-hidden" style={{ height: '600px' }}>
          <MapContainer
            center={[55.7558, 37.6173]}
            zoom={12}
            style={{ height: '100%', width: '100%' }}
          >
            <TileLayer
              url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"
              attribution='&copy; OpenStreetMap contributors'
            />
            {scooters.map((scooter) => (
              <Marker
                key={scooter.id}
                position={[scooter.latitude, scooter.longitude]}
                icon={scooterIcon}
              >
                <Popup>
                  <div className="p-2">
                    <h3 className="font-bold">{scooter.model}</h3>
                    <p className="text-sm">
                      Статус: {statusLabels[scooter.status]}
                    </p>
                    <p className="text-sm">
                      Заряд: {scooter.battery_level}%
                    </p>
                    <p className="text-sm text-gray-500">
                      ID: #{scooter.id}
                    </p>
                  </div>
                </Popup>
              </Marker>
            ))}
          </MapContainer>
        </div>
      )}

      {/* Модальное окно */}
      {showModal && (
        <ScooterModal
          scooter={editingScooter}
          onClose={() => {
            setShowModal(false);
            setEditingScooter(null);
          }}
          onSave={editingScooter ? handleUpdate : handleCreate}
        />
      )}
    </div>
  );
};

export default ScootersPage;