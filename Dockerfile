# Stage 1: Build frontend
FROM node:16-alpine AS frontend-builder

WORKDIR /frontend
RUN apk add --no-cache libc6-compat

COPY frontend/package.json frontend/package-lock.json ./
RUN npm install

COPY frontend . 
RUN npm run build

# Stage 2: Build backend dependencies
FROM node:14-alpine AS backend-builder

WORKDIR /backend

COPY backend/package.json backend/package-lock.json ./
RUN npm install --production

# Stage 3: Production image (Frontend by default)
FROM node:16-alpine

WORKDIR /app
ENV NODE_ENV production
ENV PORT 3000

# Install libc6-compat for Next.js
RUN apk add --no-cache libc6-compat

# Copy frontend build
COPY --from=frontend-builder /frontend/public ./public
COPY --from=frontend-builder /frontend/package.json ./
COPY --from=frontend-builder /frontend/.next/standalone ./
COPY --from=frontend-builder /frontend/.next/static ./.next/static

# Copy backend files (for reference if needed)
COPY --from=backend-builder /backend/node_modules ./backend_modules

EXPOSE 3000

CMD ["node", "server.js"]
