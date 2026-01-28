# 🚀 SETUP MANUAL EN RAILWAY (Rápido)

## Paso 1: Crear servicio Frontend

En Railway dashboard:
1. Click **"New Service"** → **"GitHub"**
2. Selecciona el repo **AndreaDu2001/TAREA**
3. Llena:
   - **Service Name:** `tarea-frontend`
   - **Root Directory:** `frontend`
   - **Dockerfile Path:** `frontend/Dockerfile`
   - **Start Command:** `yarn start`
4. Click **"Deploy"**

## Paso 2: Crear servicio Backend

1. Click **"New Service"** → **"GitHub"**
2. Selecciona el repo **AndreaDu2001/TAREA**
3. Llena:
   - **Service Name:** `tarea-backend`
   - **Root Directory:** `backend`
   - **Dockerfile Path:** `backend/Dockerfile`
   - **Start Command:** `node index.js`
4. Click **"Deploy"**

## Paso 3: Configurar variables de entorno

### Backend
1. Click en servicio **tarea-backend**
2. Ir a **"Variables"**
3. Agregar:
   ```
   NODE_ENV = production
   PORT = 5001
   MYSQL_HOST = <tu-host-raiwai>
   MYSQL_PORT = 3306
   MYSQL_DATABASE = service_db
   MYSQL_USERNAME = <usuario>
   MYSQL_PASSWORD = <contraseña>
   MYSQL_TABLE = products
   ```

### Frontend
1. Click en servicio **tarea-frontend**
2. Ir a **"Variables"**
3. Agregar:
   ```
   NEXT_PUBLIC_API_URL = https://tarea-backend-xxx.railway.app
   NODE_ENV = production
   PORT = 3000
   ```

## Paso 4: Verificar URLs

Después del deploy:
- Frontend: `https://tarea-frontend-xxx.railway.app`
- Backend: `https://tarea-backend-xxx.railway.app`

El frontend debería poder conectar al backend ahora.

## Solución de Problemas

### Si sigue dando 502
1. Revisa los logs del backend (Click **"View Logs"**)
2. Verifica que las variables de entorno estén correctas
3. Asegúrate que el MYSQL_HOST sea accesible desde Internet

### Si el frontend no carga
1. Verifica que `NEXT_PUBLIC_API_URL` sea correcto
2. Rebuild: Click **"Trigger Deploy"**
