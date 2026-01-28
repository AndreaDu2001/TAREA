FROM node:16-alpine

# Placeholder - Railway lee docker-compose.yml automáticamente
# Los servicios reales están definidos en:
# - frontend/Dockerfile
# - backend/Dockerfile

WORKDIR /app
COPY . .

# Este Dockerfile es solo un placeholder para Railway
# Los servicios se ejecutan desde docker-compose.yml

CMD ["echo", "Por favor ejecuta con docker-compose up"]
