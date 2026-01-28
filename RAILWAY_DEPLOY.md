# Railway Deployment Guide

## 🚀 3 Pasos para Desplegar en Railway (Sin Tarjeta)

### Paso 1: Ir a Railway
```
https://railway.app
```

### Paso 2: Conectar GitHub
1. Click **"Create New Project"**
2. Selecciona **"Deploy from GitHub"**
3. Autoriza Railway a acceder a GitHub
4. Selecciona el repo: **AndreaDu2001/TAREA**

### Paso 3: Railway detecta automáticamente
- ✅ Lee `docker-compose.yml`
- ✅ Crea Frontend service
- ✅ Crea Backend service
- ✅ Asigna dominios HTTPS gratis

## 📊 Servicios que obtendrás

```
Frontend:  https://tarea-frontend-xxx.railway.app
Backend:   https://tarea-backend-xxx.railway.app
```

## ⚙️ Configurar Variables de Entorno

Después de que Railway cree los servicios:

### Backend
1. Click en **tarea-backend**
2. **Variables** → Add
3. Agrega las variables de tu BD en Raiwai:
   - `MYSQL_HOST`: tu-host-raiwai.com
   - `MYSQL_PORT`: 3306
   - `MYSQL_USERNAME`: tu-usuario
   - `MYSQL_PASSWORD`: tu-contraseña
   - `MYSQL_DATABASE`: service_db

### Frontend
1. Click en **tarea-frontend**
2. **Variables** → Add
3. Agrega:
   - `NEXT_PUBLIC_API_URL`: https://tarea-backend-xxx.railway.app

## ✅ Ventajas Railway

- ✅ **Sin tarjeta de crédito**
- ✅ $5 USD crédito/mes gratis
- ✅ HTTPS automático
- ✅ Auto-deploy en cada push
- ✅ Logs en vivo
- ✅ Auto-restart si falla
- ✅ PostgreSQL + Redis gratis (si los necesitas)

## 🚨 Importante

Railway detecta automáticamente los servicios desde `docker-compose.yml`. No necesitas hacer nada más que conectar GitHub.

**¡Listo para desplegar!**
