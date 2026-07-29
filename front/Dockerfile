# Этап сборки
FROM node:18-alpine as build

WORKDIR /app

# Копирование package.json и package-lock.json
COPY package*.json ./

# Установка зависимостей (этот слой кэшируется)
RUN npm ci --silent --only=production

# Копирование исходного кода
COPY . .

# Сборка приложения
RUN npm run build

# Этап production
FROM nginx:alpine

# Копирование собранного приложения
COPY --from=build /app/build /usr/share/nginx/html

# Копирование конфигурации nginx
COPY nginx/nginx.conf /etc/nginx/conf.d/default.conf

# Health check для nginx
HEALTHCHECK --interval=30s --timeout=3s --start-period=5s --retries=3 \
    CMD wget --quiet --tries=1 --spider http://localhost:80 || exit 1

EXPOSE 80

CMD ["nginx", "-g", "daemon off;"]