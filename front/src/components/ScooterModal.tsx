import React, { useState, useEffect } from 'react';
import { Scooter } from '../types';
import { X } from 'lucide-react';

interface ScooterModalProps {
  scooter: Scooter | null;
  onClose: () => void;
  onSave: (data: Partial<Scooter>) => Promise<void>;
}

type ScooterStatus = 'available' | 'in_use' | 'maintenance' | 'offline';

interface ScooterFormData {
  model: string;
  status: ScooterStatus;
  battery_level: number;
  latitude: number;
  longitude: number;
}

const ScooterModal: React.FC<ScooterModalProps> = ({ scooter, onClose, onSave }) => {
  const [form, setForm] = useState<ScooterFormData>({
    model: '',
    status: 'available',
    battery_level: 100,
    latitude: 55.7558,
    longitude: 37.6173,
  });
  const [loading, setLoading] = useState(false);

  useEffect(() => {
    if (scooter) {
      setForm({
        model: scooter.model,
        status: scooter.status as ScooterStatus,
        battery_level: scooter.battery_level,
        latitude: scooter.latitude,
        longitude: scooter.longitude,
      });
    }
  }, [scooter]);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setLoading(true);
    await onSave(form);
    setLoading(false);
  };

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
      <div className="bg-white rounded-xl shadow-xl w-full max-w-md mx-4">
        <div className="flex items-center justify-between p-6 border-b">
          <h3 className="text-lg font-semibold">
            {scooter ? 'Редактировать самокат' : 'Добавить самокат'}
          </h3>
          <button onClick={onClose} className="text-gray-400 hover:text-gray-600">
            <X size={24} />
          </button>
        </div>

        <form onSubmit={handleSubmit} className="p-6 space-y-4">
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">Модель</label>
            <input
              type="text"
              value={form.model}
              onChange={(e) => setForm({ ...form, model: e.target.value })}
              className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500"
              required
            />
          </div>

          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">Статус</label>
            <select
              value={form.status}
              onChange={(e) => setForm({ ...form, status: e.target.value as ScooterStatus })}
              className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500"
            >
              <option value="available">Доступен</option>
              <option value="in_use">В аренде</option>
              <option value="maintenance">Обслуживание</option>
              <option value="offline">Оффлайн</option>
            </select>
          </div>

          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">
              Заряд ({form.battery_level}%)
            </label>
            <input
              type="range"
              min="0"
              max="100"
              value={form.battery_level}
              onChange={(e) => setForm({ ...form, battery_level: parseInt(e.target.value) })}
              className="w-full"
            />
          </div>

          <div className="grid grid-cols-2 gap-4">
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">Широта</label>
              <input
                type="number"
                step="any"
                value={form.latitude}
                onChange={(e) => setForm({ ...form, latitude: parseFloat(e.target.value) })}
                className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500"
                required
              />
            </div>
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">Долгота</label>
              <input
                type="number"
                step="any"
                value={form.longitude}
                onChange={(e) => setForm({ ...form, longitude: parseFloat(e.target.value) })}
                className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500"
                required
              />
            </div>
          </div>

          <div className="flex justify-end space-x-3 pt-4">
            <button
              type="button"
              onClick={onClose}
              className="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50"
            >
              Отмена
            </button>
            <button
              type="submit"
              disabled={loading}
              className="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 disabled:opacity-50"
            >
              {loading ? 'Сохранение...' : 'Сохранить'}
            </button>
          </div>
        </form>
      </div>
    </div>
  );
};

export default ScooterModal;