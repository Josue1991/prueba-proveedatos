# Prueba Proveedatos — CRUD Contactos

Aplicación web full-stack para la gestión de contactos, con exportación de reportes, flujo de aprobación y documentación de API. Totalmente dockerizada.

---

## Tecnologías

### Backend
| Tecnología | Versión | Rol |
|---|---|---|
| PHP | 8.2 | Lenguaje del servidor |
| Apache | 2.4 | Servidor web (mod_rewrite) |
| PDO | — | Acceso a base de datos |
| Patrón MVC | — | Arquitectura del backend |

### Frontend
| Tecnología | Versión | Rol |
|---|---|---|
| AngularJS | 1.8.3 | Framework SPA |
| ngRoute | 1.8.3 | Enrutamiento del cliente |
| Bootstrap | 4.6.2 | UI responsiva |
| Bootstrap Icons | 1.11 | Iconografía |

### Base de datos
| Tecnología | Versión | Rol |
|---|---|---|
| MySQL | 8.0 | Motor relacional |

### Infraestructura
| Tecnología | Versión | Rol |
|---|---|---|
| Docker | — | Contenedores |
| Docker Compose | — | Orquestación de servicios |
| Nginx | alpine | Servidor del frontend |

### Documentación
| Tecnología | Versión | Rol |
|---|---|---|
| Swagger UI | 5.x | Explorador de API |
| OpenAPI | 3.0 | Especificación de la API |

---

## Estructura del proyecto

```
.
├── Api/                        # Backend PHP MVC
│   ├── config/                 # Configuración de base de datos
│   ├── core/                   # Database (PDO), Router, Controller base
│   ├── controllers/            # ContactoController, ReporteController
│   ├── models/                 # ContactoModel, ReporteModel
│   ├── helpers/                # Response helper
│   ├── routes/                 # Definición de rutas
│   ├── public/                 # Entry point (index.php, .htaccess)
│   │   └── swagger/            # Swagger UI + openapi.json
│   ├── Dockerfile
│   └── apache.conf
├── FrontApp/                   # Frontend AngularJS
│   ├── controllers/            # Controladores AngularJS
│   ├── services/               # Servicio HTTP ($http factory)
│   ├── views/                  # Vistas parciales HTML
│   ├── app.js                  # Módulo y rutas AngularJS
│   ├── index.html              # Shell HTML
│   ├── Dockerfile
│   └── nginx.conf
├── db/
│   ├── init.sql                # Schema: contacto, Region, Provincia, reporte
│   └── seeds/
│       ├── 02_seed_region.sql  # 4 regiones del Ecuador
│       └── 03_seed_provincia.sql # 24 provincias del Ecuador
├── docker-compose.yml
└── .gitignore
```

---

## Requisitos previos

- [Docker Desktop](https://www.docker.com/products/docker-desktop/) instalado y en ejecución
- Puertos **4200**, **8080** y **3307** disponibles en el host

---

## Levantar el proyecto

```bash
# 1. Clonar el repositorio
git clone https://github.com/Josue1991/prueba-proveedatos.git
cd prueba-proveedatos

# 2. Construir y levantar todos los servicios
docker-compose up --build -d

# 3. Verificar que los contenedores estén en ejecución
docker-compose ps
```

> La primera vez MySQL ejecuta los scripts de inicialización (`db/init.sql` + seeds), lo que puede tomar ~30 segundos. La API espera a que MySQL esté `healthy` antes de iniciar.

### Detener los servicios

```bash
docker-compose down
```

### Detener y eliminar datos (volumen de MySQL)

```bash
docker-compose down -v
```

---

## URLs de acceso

| Servicio | URL | Descripción |
|---|---|---|
| **Frontend** | http://localhost:4200 | Aplicación AngularJS |
| **API REST** | http://localhost:8080/api/contactos | Endpoint principal |
| **Swagger UI** | http://localhost:8080/swagger/ | Documentación interactiva de la API |
| **MySQL** | `localhost:3307` | Acceso directo a la base de datos |

### Credenciales de base de datos

| Parámetro | Valor |
|---|---|
| Host | localhost |
| Puerto | 3307 |
| Base de datos | proovedatos |
| Usuario | root |
| Contraseña | secret |

---

## Endpoints de la API

| Método | Ruta | Descripción |
|---|---|---|
| `GET` | `/api/contactos` | Listar todos los contactos |
| `GET` | `/api/contactos/{id}` | Obtener un contacto por ID |
| `POST` | `/api/contactos` | Crear nuevo contacto |
| `PUT` | `/api/contactos/{id}` | Actualizar contacto |
| `DELETE` | `/api/contactos/{id}` | Eliminar contacto |
| `GET` | `/api/contactos/export?format=excel\|pdf&orderBy=campo&dir=asc\|desc` | Exportar contactos |
| `GET` | `/api/reportes` | Listar historial de exportaciones |
| `PUT` | `/api/reportes/{id}` | Aprobar o rechazar un reporte |

---

## Funcionalidades

- **CRUD completo** de contactos (nombre, email, teléfono, ciudad)
- **Exportación a Excel** (SpreadsheetML `.xls`) con columnas de ancho fijo y encabezados con estilo
- **Exportación a PDF** mediante diálogo de impresión del navegador
- **Ordenamiento** de la tabla por cualquier columna (ascendente/descendente)
- **Historial de reportes** con flujo de aprobación (`pendiente` → `aprobado` / `rechazado`)
- **Validación** de email y teléfono en backend y frontend
- **Seeds** precargados con las 4 regiones y 24 provincias del Ecuador
- **Swagger UI** para explorar y probar todos los endpoints

---

## Vistas del Frontend

| Ruta | Vista | Descripción |
|---|---|---|
| `/#/` | Lista | Tabla de contactos con ordenamiento y exportación |
| `/#/nuevo` | Formulario | Crear nuevo contacto |
| `/#/editar/:id` | Formulario | Editar contacto existente |
| `/#/ver/:id` | Detalle | Vista de solo lectura de un contacto |
| `/#/reportes` | Reportes | Historial de exportaciones con aprobación |
