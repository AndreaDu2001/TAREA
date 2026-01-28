# 🚀 Despliegue Rápido en Render

## 3 Pasos para Desplegar

### 1️⃣ Push a GitHub
```bash
git add .
git commit -m "Listo para Render"
git push origin main
```

### 2️⃣ Ir a Render Dashboard
https://dashboard.render.com

### 3️⃣ Crear Blueprint
- Click "New +" → "Blueprint"
- Seleccionar repo: **AndreaDu2001/TAREA**
- Click "Apply"

---

## ⏱️ Tiempo de Despliegue
- **Build**: 2-3 minutos
- **Deploy**: 1-2 minutos
- **Total**: ~5 minutos

---

## 🔗 URLs Resultantes
```
Frontend:  https://tarea-frontend.onrender.com
Backend:   https://tarea-backend.onrender.com
```

---

## ✅ El Blueprint Automáticamente:
- ✅ Configura Frontend (Next.js)
- ✅ Configura Backend (Express)
- ✅ Configura Worker (Background)
- ✅ Crea Base de datos MySQL
- ✅ Crea Cache Redis
- ✅ Vincula variables de entorno
- ✅ Inicializa la BD automáticamente
- ✅ Activa SSL/HTTPS

---

## 🔐 Variables de Entorno
Render genera automáticamente:
- ✅ `MYSQL_ROOT_PASSWORD`
- ✅ `MYSQL_PASSWORD`
- ✅ `REDIS_PASSWORD`

No necesitas configurarlas manualmente.

---

## 📊 Logs en Vivo
```
Dashboard → tarea-backend → Logs
Dashboard → tarea-frontend → Logs
Dashboard → tarea-worker → Logs
```

---

## ❌ Si Algo Falla

**Build error:**
```bash
# Ver logs completos en Dashboard → Logs
# Verificar que las carpetas existen:
ls -la backend/
ls -la frontend/
ls -la worker-server/
```

**Backend no conecta a BD:**
```bash
# Los valores se vinculan automáticamente
# Si no funciona, ir a Dashboard → Environment
# Verificar: MYSQL_HOST, MYSQL_PORT, MYSQL_PASSWORD
```

**Frontend no ve Backend:**
```bash
# El frontend usa NEXT_PUBLIC_API_URL
# Verificar en: .env.production
# Debe ser: https://tarea-backend.onrender.com
```

---

## 💡 Tip: Monitoreo
Render incluye gratis:
- 📊 Métricas de CPU/RAM
- 📝 Logs en vivo
- 🔔 Notificaciones de errores
- 📈 Historial de despliegues

---

**¿Necesitas ayuda?**
- Dashboard: https://dashboard.render.com
- Docs: https://render.com/docs
- Status: https://status.render.com

---

¡Listo! Tu app estará en internet en 5 minutos. 🎉
