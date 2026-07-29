#!/bin/bash

# Определяем IP-адрес хоста
if [[ "$OSTYPE" == "darwin"* ]]; then
    # macOS
    HOST_IP=$(ipconfig getifaddr en0 2>/dev/null || ipconfig getifaddr en1 2>/dev/null || echo "localhost")
elif [[ "$OSTYPE" == "linux-gnu"* ]]; then
    # Linux
    HOST_IP=$(hostname -I | awk '{print $1}')
else
    # Windows и другие
    HOST_IP=$(ipconfig | grep -A 1 "Ethernet" | grep "IPv4" | head -1 | awk '{print $NF}')
fi

# Если IP не определен, используем localhost
HOST_IP=${HOST_IP:-localhost}

echo "========================================="
echo "Scooter Rent - Запуск приложения"
echo "========================================="
echo "Хост IP: $HOST_IP"
echo "========================================="

export HOST_IP

docker-compose down
docker-compose up -d --build
docker exec -it scooter-server cp .env.example .env 
docker exec -it scooter-server php artisan key:generate --force 

echo ""
echo "========================================="
echo "Приложение запущено!"
echo "Веб-интерфейс: http://$HOST_IP"
echo "API: http://$HOST_IP:8000/api"
echo "========================================="
