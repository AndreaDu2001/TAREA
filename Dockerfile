# Frontend por defecto (Render lo usará automáticamente)
# Para backend, especifica en Render UI: Root Directory = backend

FROM node:16-alpine

WORKDIR /app

# Instalar libc6-compat para Next.js
RUN apk add --no-cache libc6-compat

# Copiar desde frontend
COPY frontend/package.json frontend/package-lock.json ./

# Instalar dependencias
RUN npm install --production

# Copiar build del frontend
COPY frontend/.next/standalone ./
COPY frontend/.next/static ./.next/static
COPY frontend/public ./public

EXPOSE 3000
ENV PORT 3000
ENV NODE_ENV production

CMD ["node", "server.js"]
