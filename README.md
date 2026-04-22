# AlmhaBackendV2 🚀

Bienvenido al repositorio de **AlmhaBackendV2**. Esta es una API REST robusta construida bajo los principios de la **Arquitectura Hexagonal (Domain-Driven Design)** utilizando el framework Laravel 13.

---

## 🏗 Arquitectura

El proyecto **no** sigue la estructura tradicional plana (MVC) de Laravel. Todo el código de negocio principal y reglas del sistema viven dentro del directorio `src/`, divididos en Módulos de responsabilidad única.

Cada módulo respeta estrictamente estas **tres capas**:
1. **Domain (Dominio):** El corazón del módulo. Contiene las Entidades puras, *Value Objects*, Excepciones de negocio y Contratos (Interfaces). Cero acoplamiento con la base de datos o el protocolo HTTP.
2. **Application (Aplicación):** Contiene los *Use Cases* (Casos de Uso) que orquestan las acciones. Sirven de puente entre lo que pide la infraestructura y lo que resuelve el dominio.
3. **Infrastructure (Infraestructura):** Todo lo que se comunica con el exterior. Controladores de Laravel, Rutas (`api.php`), Repositorios basados en Eloquent y adaptadores de paquetes de terceros.

---

## 📦 Módulos Implementados

Actualmente la plataforma cuenta con los siguientes de módulos base:

- **Auth (`src/Admin/Auth`):** Manejo de la autenticación *stateless*. Orquesta el inicio de sesión para generar tokens **JWT** (`php-open-source-saver/jwt-auth`).
- **Role (`src/Admin/Role`):** Gestión y asignación de roles y permisos. Utiliza bajo el capó a `spatie/laravel-permission`, adaptado al guardia de la API.
- **User (`src/Admin/User`):** CRUD completo para la administración del personal o usuarios. Soporta peticiones `GET`, `POST`, `PUT`, y `DELETE` con validaciones de Value Objects.
- **Blog (`src/Admin/Blog`):** Sistema de gestión de contenido multi-idioma. 
    - Incluye categorías jerárquicas y artículos (blogs).
    - Soporta **traducción automática** mediante Google Translate API integrada en la capa de aplicación.
    - Manejo de imágenes optimizado con almacenamiento en **MinIO/S3** a través del trait compartido `StoresImages`.
    - Recuperación por ID con filtrado dinámico por idioma vía encabezado de petición `Accept-Language`.

---

## 🛠 Componentes Compartidos (Shared)

Para garantizar la consistencia y reducir la duplicación, el sistema utiliza componentes transversales en `src/Shared`:

- **Infrastructure/Traits/StoresImages:** Lógica reutilizable para el guardado de imágenes en S3/MinIO, construcción de URLs dinámicas y limpieza de directorios de almacenamiento.

---

## ⚙️ Instalación y Configuración (Local)

Para levantar el proyecto en tu entorno local, sigue esta guía rápida:

1. **Instalar dependencias de PHP:**
   ```bash
   composer install
   ```

2. **Configurar el entorno:**
   - Copia el archivo de ejemplo: `cp .env.example .env`
   - Configura las credenciales de tu base de datos (`DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`), preferiblemente MySQL/PostgreSQL.

3. **Generar las llaves de encriptación:**
   ```bash
   php artisan key:generate
   php artisan jwt:secret
   ```

4. **Preparar la Base de Datos:**
   Para crear las tablas nativas, las tablas pivots de Spatie y poblar los permisos y usuarios por defecto, corre:
   ```bash
   php artisan migrate:fresh --seed
   ```
   *(El seeder `RolesAndPermissionsSeeder` se encargará de crear el rol "admin", el rol "editor" y asignarlo al primer usuario).*

5. **Levantar el servidor de desarrollo:**
   ```bash
   php artisan serve
   ```

---

## 🛡 Autorización, Endpoints y Guards

El sistema está diseñado para ser consumido como una **API Pura**. 
Tanto las rutas nativas como la validación de Roles de Spatie están instruidas para utilizar exclusivamente el guard **`api`**. 

**Regla General:** Para consumir los endpoints protegidos, el frontend siempre debe enviar el JWT recibido en el *Login* usando los encabezados HTTP:
```
Authorization: Bearer <aquí-va-el-token>
Accept: application/json
```

---

## 🌐 Endpoints públicos para el frontend (`/api/client/*`)

El módulo `src/Landing/*` expone endpoints públicos de solo lectura consumidos por **AlmhaFrontendClient** (Astro). Todos devuelven el envelope `{ success, message, data }` y respetan el header `Accept-Language` (ES/EN).

| Endpoint | Descripción |
|----------|-------------|
| `GET /api/client/maintenance` | Flag de modo mantenimiento |
| `GET /api/client/navbar-data` | Carousel + procedimientos agrupados + redes + contacto |
| `GET /api/client/home` | Backgrounds + carousels + bloque video de la home |
| `GET /api/client/contact-data?lang=es` | Settings de contacto y títulos de procedimientos |
| `GET /api/client/blog` | Listado paginado (`page`, `filter[category_code]`, `filter[search]`, `sort`) |
| `GET /api/client/blog/{slug}` | Detalle + `random_blogs` |
| `GET /api/client/procedure` | Listado paginado igual que blog |
| `GET /api/client/procedure/{slug}` | Detalle con `section`, `preStep`, `phase`, `do`/`dont`, `faq`, `gallery`, WhatsApp |
| `GET /api/client/members` | Listado del equipo (Team con `status=active`) |
| `GET /api/client/members/{slug}` | Detalle del miembro + galería de resultados |
| `POST /api/client/subscribe` | Newsletter (alias del `Landing/Subscription`) |

### Proxies a n8n (también usados por el frontend)

| Endpoint | Descripción | Rate limit |
|----------|-------------|-----------|
| `POST /api/v1/contact` | Formulario de contacto → n8n | 5 req/min/IP |
| `POST /api/v1/chat` | Chat widget → n8n | 30 req/min/IP |

Configurar en `.env`:
```
N8N_CONTACT_WEBHOOK_URL=https://...
N8N_CHAT_WEBHOOK_URL=https://...
```

---

## 🌱 Datos de prueba

`php artisan migrate:fresh --seed` ejecuta `DatabaseSeeder` que corre:
- `RolesAndPermissionsSeeder` — roles y permisos de Spatie
- `BlogTestSeeder` — 2 blogs de ejemplo (`tech` category)
- `DesignModuleSeeder` — 6 keys de diseño (`main_banner`, `background_1..3`, `brands_carousel`, `alternate_main_banner`)
- `SettingsSeeder` — settings en grupos `general`, `social`, `system`
- `ClientDataSeeder` — 3 procedimientos con secciones/FAQ/galería, 2 miembros del equipo, media poblada en los designs

Con esto, todos los endpoints `/api/client/*` retornan contenido real y el frontend puede renderizar sin tocar el admin.

---

## 🔗 Corriendo junto con AlmhaFrontendClient

1. Backend: `php artisan serve` (por defecto `http://localhost:8000`)
2. Frontend: en `AlmhaFrontendClient/.env` establecer `PUBLIC_API_URL=http://localhost:8000`, luego `npm run dev`
3. El middleware del frontend (`src/middleware.ts`) hace una petición SSR a `/api/client/maintenance` en cada request — el backend debe estar corriendo o verás errores 503 de mantenimiento.

