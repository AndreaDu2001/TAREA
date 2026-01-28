# Railway Services Configuration

## Para desplegar en Railway con múltiples servicios:

### Opción 1: Usar la interfaz de Railway
1. Crea un nuevo proyecto en Railway
2. Conecta GitHub a AndreaDu2001/TAREA
3. Railway detectará dos servicios:
   - Frontend (desde frontend/Dockerfile)
   - Backend (desde backend/Dockerfile)

### Opción 2: Usar Railway CLI
```bash
# Instalar Railway CLI
npm install -g @railway/cli

# Login
railway login

# Link proyecto
railway link

# Deploy
railway up
```

## Estructura para Railway

El proyecto está estructurado en servicios independientes:

```
TAREA/
├── frontend/          # Servicio 1: Next.js
│   └── Dockerfile
├── backend/           # Servicio 2: Express
│   └── Dockerfile
└── docker-compose.yml # Orquestación local
```

## Variables de Entorno Necesarias

### Backend
- `MYSQL_HOST`
- `MYSQL_PORT`
- `MYSQL_DATABASE`
- `MYSQL_USERNAME`
- `MYSQL_PASSWORD`

### Frontend
- `NEXT_PUBLIC_API_URL`

## Crear servicios manualmente en Railway

Si no detecta automáticamente:

1. **Frontend Service**
   - Nombre: `tarea-frontend`
   - Root Directory: `frontend`
   - Dockerfile Path: `frontend/Dockerfile`

2. **Backend Service**
   - Nombre: `tarea-backend`
   - Root Directory: `backend`
   - Dockerfile Path: `backend/Dockerfile`

---

**Recomendación:** Usa la CLI de Railway (`railway up`) que es más confiable que la interfaz web.
