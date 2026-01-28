# Guía de Docker - TAREA

## 🚀 Quick Start

### Prerequisitos
- Docker instalado
- Docker Compose instalado
- Puerto 3005, 5001, 3306 y 6379 disponibles

### Iniciar todo
```bash
cd /ruta/a/TAREA
docker-compose up -d
```

### Verificar estado
```bash
docker-compose ps
```

---

## 📊 Endpoints Disponibles

### Frontend
```
http://localhost:3005
```
Interfaz de usuario en Next.js. Permite:
- Ver datos desde la BD
- Insertar nuevos datos
- Interactuar en tiempo real

### Backend API
```
GET  http://localhost:5001/
GET  http://localhost:5001/data
POST http://localhost:5001/create
```

**Ejemplo POST:**
```bash
curl -X POST http://localhost:5001/create \
  -H "Content-Type: application/json" \
  -d '{"data":"Mi nuevo producto"}'
```

---

## 🔧 Troubleshooting

### Los contenedores no inician
```bash
# Limpiar todo y reiniciar
docker-compose down -v
docker-compose up --build
```

### Error de puerto ya en uso
```bash
# Cambiar el puerto en docker-compose.yml
# De:   - '3005:3000'
# A:    - '3006:3000'
```

### MySQL no se conecta
```bash
# Verificar logs
docker logs mysqldb

# La BD se inicializa en init_db.sql
# Si hay cambios, hacer clean start:
docker-compose down -v
docker-compose up --build
```

### Redis no responde
```bash
# Verificar conexión
docker logs worker-server  # Debe mostrar "Redis ready for action!"

# Reiniciar redis
docker-compose restart redis-db
```

---

## 🐛 Debug

### Ver logs en tiempo real
```bash
# Todos los servicios
docker-compose logs -f

# Un servicio específico
docker-compose logs -f backend
docker-compose logs -f frontend
docker-compose logs -f mysqldb
docker-compose logs -f worker-server
docker-compose logs -f redis-db
```

### Acceder a un contenedor
```bash
# Shell del backend
docker exec -it backend sh

# Base de datos MySQL
docker exec -it mysqldb mysql -u root -proot123 service_db

# Redis CLI
docker exec -it redis-db redis-cli -a mypassword
```

---

## 📁 Estructura de Archivos

```
TAREA/
├── docker-compose.yml       # Orquestación de servicios
├── Dockerfile               # Frontend PHP (NO USADO AHORA)
│
├── frontend/
│   ├── Dockerfile          # Next.js build multi-stage
│   ├── package.json
│   ├── next.config.js
│   └── pages/
│       ├── _app.js
│       └── index.js        # UI principal
│
├── backend/
│   ├── Dockerfile          # Node.js + Express
│   ├── package.json
│   ├── index.js            # API REST
│   └── request.http        # Ejemplos de requests
│
├── worker-server/
│   ├── Dockerfile          # Node.js consumer
│   ├── package.json
│   └── index.js            # Escucha Redis
│
├── mysqldb/
│   ├── Dockerfile          # MySQL 8.0
│   └── init_db.sql         # Script inicializador
│
├── config/
│   └── config.php          # Config (no usado)
│
├── .env                    # Variables de entorno
└── DOCKER_VERIFICATION.md  # Esta documentación
```

---

## 🔐 Seguridad (Para Desarrollo)

⚠️ **NOTA:** Las credenciales actuales son para DESARROLLO. Para producción:

```env
# .env (ACTUAL - SOLO DESARROLLO)
MYSQL_PASSWORD=root123          ← CAMBIAR EN PROD
REDIS_PASSWORD=mypassword       ← CAMBIAR EN PROD
MYSQL_ROOT_PASSWORD=root123     ← CAMBIAR EN PROD
```

---

## 📈 Monitoreo

### Verificar salud de servicios
```bash
# Health check manual
Invoke-WebRequest http://localhost:5001/ -UseBasicParsing

# Ver estadísticas Docker
docker stats
```

---

## 🛠️ Desarrollo Local

### Cambios en código
```bash
# Frontend
cd frontend
npm run dev          # O yarn dev para desarrollo local
docker-compose up -d # Build en docker

# Backend
cd backend
npm run dev          # O yarn dev para desarrollo local

# Worker-server
cd worker-server
npm run dev          # O yarn dev
```

---

## 📝 Notas Importantes

1. **Redis**: Se usa como cache y message broker
2. **MySQL**: Datos persistidos en volúmenes de Docker
3. **Worker-Server**: Procesa mensajes de forma asincrónica
4. **Frontend**: Build estático, optimizado con Next.js

---

## 🎯 Flujo de Datos

```
Usuario → Frontend (UI) 
       → Backend API (HTTP)
       → MySQL (Lectura/Escritura)
       → Redis (Cache/Pub-Sub)
       → Worker-Server (Procesamiento async)
       → MySQL (Actualización)
```

---

Última actualización: 28 de Enero de 2026
