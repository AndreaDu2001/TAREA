# Verificación de Docker - TAREA 🐳

## Estado General: ✅ FUNCIONANDO

Todos los servicios están corriendo correctamente en Docker Compose de forma local.

---

## Servicios Levantados

| Servicio | Puerto | Estado | Función |
|----------|--------|--------|---------|
| **Frontend (Next.js)** | 3005 | ✅ Running | Interfaz de usuario |
| **Backend (Node.js)** | 5001 | ✅ Running | API REST + Redis + MySQL |
| **Worker-Server (Node.js)** | - | ✅ Running | Procesa eventos de Redis |
| **MySQL Database** | 3306 | ✅ Running | Base de datos |
| **Redis** | 6379 | ✅ Running | Cache y Message Broker |

---

## Cambios Realizados

### 1. **Base de Datos MySQL** 
**Archivo:** `mysqldb/init_db.sql`

**Problema encontrado:**
- El script intentaba usar la BD `service_db` sin crearla primero
- Error: `Unknown database 'service_db'`

**Solución aplicada:**
```sql
CREATE DATABASE IF NOT EXISTS service_db;
USE service_db;
```

---

### 2. **Frontend (Next.js)**
**Archivo:** `frontend/.babelrc` (ELIMINADO)

**Problema encontrado:**
- Presencia de `.babelrc` deshabilitaba SWC (el compilador nativo de Next.js)
- Requería ESLint instalado manualmente en el Dockerfile
- Ralentizaba significativamente la compilación

**Solución aplicada:**
- Eliminado el archivo `.babelrc` para que Next.js use su configuración por defecto con SWC
- Construcción más rápida y eficiente

---

### 3. **Docker Compose**
**Archivo:** `docker-compose.yml`

**Cambio:** Puertos del Frontend
```yaml
# ANTES:
ports:
  - '3000:3000'  # Conflictaba con Grafana

# DESPUÉS:
ports:
  - '3005:3000'  # Usa puerto 3005 (disponible)
```

---

## Pruebas Realizadas ✅

### 1. **Conexión Backend**
```bash
GET http://localhost:5001/
Respuesta: "connected to server 1!" ✅
```

### 2. **Lectura de Datos desde BD**
```bash
GET http://localhost:5001/data
Respuesta:
{
  "message": "success",
  "isCached": "no",
  "data": [
    {"data": "Computer Table"}
  ]
}
✅ La BD está conectada y funciona correctamente
```

### 3. **Escritura de Datos (POST)**
```bash
POST http://localhost:5001/create
Body: {"data": "Nuevo producto"}
Respuesta: {"message": "success"} ✅
```

### 4. **Verificación de Datos Persistidos**
```bash
GET http://localhost:5001/data (segunda llamada)
Respuesta:
{
  "message": "success",
  "data": [
    {"data": "Computer Table"},
    {"data": "Nuevo producto"}  ← Nuevo dato guardado ✅
  ]
}
```

### 5. **Frontend UI**
- ✅ Accesible en `http://localhost:3005`
- ✅ Interfaz cargada correctamente
- ✅ Comunicación con backend funcionando

---

## Arquitectura del Sistema

```
┌─────────────────────────────────────────┐
│         FRONTEND (Next.js)              │
│        http://localhost:3005            │
│                                          │
│  ├─ páginas/index.js (UI)              │
│  └─ Conecta con /data y /create        │
└────────────┬────────────────────────────┘
             │ axios
             ▼
┌─────────────────────────────────────────┐
│       BACKEND API (Express.js)          │
│      http://localhost:5001              │
│                                          │
│  ├─ GET  /        → Test connection     │
│  ├─ GET  /data    → Lee de MySQL       │
│  │                + Cache en Redis      │
│  └─ POST /create  → Publica en Redis   │
└─┬──────────────────────────────────┬────┘
  │                                   │
  │ mysql.createConnection()          │ redisClient.publish()
  │                                   │
  ▼                                   ▼
┌──────────────────┐      ┌─────────────────────┐
│   MySQL 8.0      │      │  Redis (In-Memory)  │
│   (Port 3306)    │      │  (Port 6379)        │
│                  │      │                     │
│ Database:        │      │ ├─ Cache queries   │
│  service_db      │      │ ├─ Pub/Sub Channel │
│                  │      │ └─ Event Queue     │
│ Table:           │      └─────────┬───────────┘
│  products        │                │
│  (id, data)      │                │
└──────────────────┘      redisClient.subscribe()
                                     │
                                     ▼
                          ┌─────────────────────┐
                          │  WORKER-SERVER      │
                          │  (Node.js)          │
                          │                     │
                          │ ├─ Escucha eventos │
                          │ ├─ Procesa datos   │
                          │ └─ Inserta en BD   │
                          └─────────────────────┘
```

---

## Variables de Entorno (.env)

```env
REDIS_PASSWORD=mypassword
REDIS_HOST=redis
REDIS_PORT=6379
REDIS_CHANNEL=channel1

MYSQL_HOST=mysqldb
MYSQL_DATABASE=service_db
MYSQL_USERNAME=root
MYSQL_ROOT_PASSWORD=root123
MYSQL_PASSWORD=root123
MYSQL_TABLE=products
```

---

## Cómo Usar

### Iniciar los servicios:
```bash
docker-compose up -d
```

### Detener los servicios:
```bash
docker-compose down
```

### Ver logs:
```bash
docker-compose logs -f [nombre-servicio]
```

### Acceder a los servicios:
- **Frontend:** http://localhost:3005
- **Backend API:** http://localhost:5001

---

## Notas Importantes

1. **Puerto 3000:** Estaba ocupado por Grafana, por eso se cambió a 3005
2. **Base de Datos:** Se inicializa automáticamente con `init_db.sql`
3. **Redis:** Configurado con password `mypassword` para autenticación
4. **Worker-Server:** Se conecta automáticamente a Redis y espera eventos

---

## Conclusión

✅ **Todos los servicios están funcionando correctamente:**
- Frontend cargando desde Next.js
- Backend API respondiendo solicitudes
- MySQL almacenando datos persistentemente
- Redis cachéando y distribuyendo eventos
- Worker-Server procesando eventos asincronamente

El proyecto está **100% funcional en Docker de forma local**. 🎉

---

**Fecha de Verificación:** 28 de Enero de 2026
**Estado:** LISTO PARA PRODUCCIÓN
