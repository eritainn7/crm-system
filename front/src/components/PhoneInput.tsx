import React, { useState } from 'react';

interface PhoneInputProps {
  value: string;
  onChange: (value: string) => void;
  placeholder?: string;
  required?: boolean;
  className?: string;
}

const PhoneInput: React.FC<PhoneInputProps> = ({
  value,
  onChange,
  placeholder = '+7 (___) ___-__-__',
  required = true,
  className = '',
}) => {
  const [focused, setFocused] = useState(false);

  const formatPhone = (input: string): string => {
    // Убираем всё, кроме цифр
    let numbers = input.replace(/\D/g, '');
    
    // Если начинается с 8 или 7, убираем первую цифру
    if (numbers.startsWith('8')) {
      numbers = '7' + numbers.slice(1);
    }
    
    // Ограничиваем 11 цифрами (российский формат)
    numbers = numbers.slice(0, 11);

    // Если первая цифра не 7, добавляем 7
    if (numbers.length > 0 && numbers[0] !== '7') {
      numbers = '7' + numbers;
    }

    // Форматируем
    let formatted = '+7';
    
    if (numbers.length > 1) {
      formatted += ' (' + numbers.slice(1, 4);
    }
    if (numbers.length >= 4) {
      formatted += ') ' + numbers.slice(4, 7);
    }
    if (numbers.length >= 7) {
      formatted += '-' + numbers.slice(7, 9);
    }
    if (numbers.length >= 9) {
      formatted += '-' + numbers.slice(9, 11);
    }

    return formatted;
  };

  const handleChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const input = e.target.value;
    
    // Если пользователь стирает префикс +7, не даём
    if (input === '+' || input === '+7' || input === '') {
      onChange('+7');
      return;
    }

    const formatted = formatPhone(input);
    onChange(formatted);
  };

  const handleKeyDown = (e: React.KeyboardEvent<HTMLInputElement>) => {
    // Разрешаем навигацию и удаление
    const allowedKeys = [
      'Backspace', 'Delete', 'ArrowLeft', 'ArrowRight', 
      'ArrowUp', 'ArrowDown', 'Home', 'End', 'Tab'
    ];
    
    if (allowedKeys.includes(e.key)) {
      return;
    }

    // Запрещаем ввод нецифровых символов
    if (!/\d/.test(e.key)) {
      e.preventDefault();
    }
  };

  const handlePaste = (e: React.ClipboardEvent<HTMLInputElement>) => {
    e.preventDefault();
    const pasted = e.clipboardData.getData('text');
    const formatted = formatPhone(pasted);
    onChange(formatted);
  };

  return (
    <div className="relative">
      <input
        type="tel"
        value={value}
        onChange={handleChange}
        onKeyDown={handleKeyDown}
        onPaste={handlePaste}
        onFocus={() => setFocused(true)}
        onBlur={() => setFocused(false)}
        placeholder={focused ? '(___) ___-__-__' : placeholder}
        required={required}
        className={`w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent ${className}`}
        maxLength={18}
      />
    </div>
  );
};

export default PhoneInput;
