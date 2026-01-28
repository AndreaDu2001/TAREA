# Despliegue en Render - TAREA 🚀

## Pasos para Desplegar en Render

### 1. **Preparar el Repositorio**
```bash
# Asegúrate que todo está en git
git add .
git commit -m "Preparado para Render Blueprint"
git push origin main
```

### 2. **Crear una Cuenta en Render**
- Ir a https://render.com
- Registrarse con GitHub
- Conectar tu cuenta de GitHub

### 3. **Usar Blueprint**

#### Opción A: Desde el Dashboard de Render (Más fácil)
1. Ir a https://dashboard.render.com
2. Click en "New +" → "Blueprint"
3. Conectar tu repositorio GitHub (AndreaDu2001/TAREA)
4. Seleccionar rama: `main`
5. Click en "Apply" para desplegar

#### Opción B: Desde la CLI
```bash
# Instalar Render CLI (opcional)
npm install -g @render-com/cli

# Desplegar
render deploy
```

---

## 📋 Variables de Entorno Requeridas

En el dashboard de Render, configurar las siguientes variables:

### Para Backend y Worker-Server:
```
REDIS_PASSWORD = [GENERADA POR RENDER]
MYSQL_ROOT_PASSWORD = [GENERADA POR RENDER]
MYSQL_PASSWORD = [GENERADA POR RENDER]
```

Render las vinculará automáticamente desde los servicios Redis y MySQL.

---

## 🏗️ Estructura del Blueprint

El archivo `render.yaml` configura 5 servicios:

### 1. **Frontend** (Web Service)
- Runtime: Node.js
- Puerto: 3000
- Comando: `yarn start` (Next.js)
- Auto-deploy: ✅

### 2. **Backend** (Web Service)
- Runtime: Node.js
- Puerto: 5001
- Comando: `node index.js`
- Conecta a: MySQL + Redis
- Auto-deploy: ✅

### 3. **Worker-Server** (Background Worker)
- Runtime: Node.js
- Tipo: Background Job
- Procesa eventos de Redis
- Conecta a: MySQL + Redis
- Auto-deploy: ✅

### 4. **MySQL Database**
- Versión: 8.0
- Base de datos: `service_db`
- Backups: 7 días
- Credenciales: Generadas por Render

### 5. **Redis**
- Plan: Starter (Gratuito)
- Política de memoria: allkeys-lru
- Contraseña: Generada por Render

---

## ⚙️ Configuración Automática

El blueprint vincula automáticamente:
- ✅ Host/Puerto de MySQL
- ✅ Host/Puerto de Redis
- ✅ Contraseñas generadas por Render
- ✅ Variables de entorno entre servicios

---

## 🔗 URLs Resultantes

Después del despliegue:
```
Frontend:  https://tarea-frontend.onrender.com
Backend:   https://tarea-backend.onrender.com
```

El frontend se conectará automáticamente al backend.

---

## 📝 Modificaciones Necesarias en el Frontend

Render generará URLs automáticas. Actualiza `frontend/pages/index.js`:

**Cambiar de:**
```javascript
const baseUrl = "http://localhost:5001";
```

**A:**
```javascript
const baseUrl = process.env.NEXT_PUBLIC_API_URL || "http://localhost:5001";
```

Agregar en `frontend/.env.production`:
```
NEXT_PUBLIC_API_URL=https://tarea-backend.onrender.com
```

---

## 🛠️ Troubleshooting en Render

### Build falla
```bash
# Ver logs en dashboard → Logs
# Verificar que package.json existe en cada carpeta
ls backend/package.json
ls frontend/package.json
ls worker-server/package.json
```

### Servicios no se conectan
```bash
# Render vincula automáticamente
# Si no funciona, verificar variables en dashboard:
# Render → Service → Environment
```

### Base de datos no se inicializa
```bash
# Render no ejecuta init_db.sql automáticamente
# Necesitas conectarte e inicializar manualmente:
# Opción: Agregar un script en backend para inicializar
```

---

## 🚨 Próximos Pasos IMPORTANTES

### 1. **Actualizar Frontend para Render**
Modificar `frontend/pages/index.js`:
```javascript
const baseUrl = process.env.NEXT_PUBLIC_API_URL || "http://localhost:5001";
```

### 2. **Agregar Init Script (Opcional)**
Si quieres que MySQL se inicialice automáticamente, crear `backend/init-db.js`:
```javascript
const mysql = require('mysql2/promise');

async function initDB() {
  const conn = await mysql.createConnection({
    host: process.env.MYSQL_HOST,
    user: process.env.MYSQL_USERNAME,
    password: process.env.MYSQL_PASSWORD,
  });
  
  await conn.execute('CREATE DATABASE IF NOT EXISTS service_db');
  await conn.execute('USE service_db');
  await conn.execute(`
    CREATE TABLE IF NOT EXISTS products (
      id INT NOT NULL AUTO_INCREMENT,
      data LONGTEXT NOT NULL,
      PRIMARY KEY (id)
    )
  `);
  
  console.log('Database initialized');
  conn.end();
}

if (require.main === module) {
  initDB().catch(console.error);
}

module.exports = initDB;
```

Luego ejecutar en el buildCommand del backend:
```bash
node init-db.js && yarn start
```

---

## 💰 Costos Estimados en Render (Plan Gratuito)

- ✅ Frontend: GRATIS (primer web service)
- ✅ Backend: GRATIS (si cabe en 0.5GB RAM)
- ✅ Worker-Server: GRATIS (background job)
- ✅ MySQL: GRATIS (primero gratuito)
- ✅ Redis: GRATIS (plan starter)

**Total: ~$0 USD** en el plan gratuito (con limitaciones)

### Planes Pagos:
- Web Service: $7/mes
- PostgreSQL/MySQL: $15/mes
- Redis: $5/mes

---

## ✅ Checklist de Despliegue

- [ ] Código en GitHub
- [ ] Credenciales actualizadas en `.env`
- [ ] `render.yaml` en raíz del repo
- [ ] Cuenta en Render creada
- [ ] Blueprint aplicado
- [ ] Variables de entorno configuradas
- [ ] Build completado sin errores
- [ ] Servicios iniciaron correctamente
- [ ] Frontend accesible en HTTPS
- [ ] Backend responde a requests
- [ ] Base de datos conectada
- [ ] Redis funcionando
- [ ] Worker procesa eventos

---

## 🔐 Seguridad

Render mantiene:
- ✅ Certificados SSL automáticos
- ✅ Variables de entorno encriptadas
- ✅ Contraseñas generadas aleatoriamente
- ✅ Backups automáticos (MySQL)
- ✅ Aislamiento de redes

---

## 📞 Soporte

- Docs: https://render.com/docs
- Status: https://status.render.com
- Email: support@render.com

---

**¡Listo para desplegar!** 🎉
