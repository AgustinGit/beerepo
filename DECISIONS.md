# DECISIONS — Cervecería Agus

Decisiones técnicas tomadas durante el desarrollo. Append-only — no editar entradas pasadas, agregar nuevas si una decisión se revierte.

## 2026-05-14 — Fase 0: setup inicial

### Estructura del repo: monorepo `apps/`

Decisión: monorepo con `apps/admin`, `apps/tienda`, `apps/reparto` en lugar de repos separados.

Por qué: el spec menciona "apps separadas, misma DB" pero no requiere repos separados. Monorepo simplifica versionado y desarrollo cuando hay un único operador. Si en el futuro se quiere splitear, es reversible.

Costo: composer install se corre por app. CI con jobs separados por app.

### Solo `apps/admin` instalado en Fase 0

Decisión: en Fase 0 solo se crea `apps/admin` (backoffice Filament). `apps/tienda` y `apps/reparto` se crean al iniciar las fases correspondientes (Fase 1 y Fase 2 respectivamente).

Por qué: el spec Fase 0 PROMPT 1 habla de "un proyecto Laravel 11 con Filament 3 que será el backoffice". El frontend de tienda viene en Fase 1.

### PHP 8.3 mínimo (CI) / 8.4 en local

Decisión: CI corre PHP 8.3 (mínimo de Laravel 11). Local usa 8.4 si está disponible.

Por qué: 8.3 es el mínimo LTS de Laravel 11. No hay razón para forzar 8.4 todavía.

### `declare(strict_types=1)` obligatorio

Decisión: Pint configurado con `declare_strict_types: true` y `strict_param: true`. Todos los archivos PHP nuevos deben tener `declare(strict_types=1);`.

Por qué: cazar errores de tipo temprano. Larastan nivel 6 lo aprovecha.

### Larastan nivel 6

Decisión: nivel 6 fijo. Subir a 7-8 solo cuando el código base esté estable.

Por qué: el spec lo pide explícitamente.

### Pest 3 en lugar de PHPUnit

Decisión: Pest 3 reemplaza PHPUnit. Sintaxis `it(...)` y `test(...)`.

Por qué: spec lo pide.

### Filament `panel('admin')` en path `/admin`

Decisión: panel único llamado `admin` en `/admin`. Color primario emerald (alineado a "paleta oscura premium con verdes esmeralda y dorados" del spec sección 1 PROMPT 6).

### `User::canAccessPanel` restringido a `@agus.club`

Decisión: por defecto solo emails terminados en `@agus.club` pueden acceder al panel.

Por qué: el backoffice es solo para Agus. Cualquier customer registrado en Fase 1 no debe poder entrar al admin aunque haya un único modelo User.

Pendiente: en Fase 1 separar Customer de User admin, o agregar columna `is_admin`.

### Servicios locales: Postgres 16, Redis 7, Meilisearch 1.10

Decisión: `docker-compose.yml` en la raíz del monorepo levanta los 3 servicios. Meilisearch incluido desde Fase 0 aunque se use en Fase 1+ (no cuesta nada tenerlo listo).

### `.env.example` con stack completo

Decisión: el `.env.example` incluye variables vacías para Mercado Pago, Cloudflare R2 y Meilisearch desde Fase 0, aunque no se usen aún.

Por qué: documentar el stack completo evita sorpresas en Fase 1 y deja explícito qué credenciales hay que conseguir.

### Locale `es` por defecto, timezone `America/Montevideo`

Decisión: app locale `es`, fallback `es`, faker `es_AR`, timezone `America/Montevideo`.

Por qué: el producto es 100% para Uruguay y el spec exige UI en español.

## 2026-05-14 — Revisión de stack por restricciones del hosting

El hosting de producción es **shared PHP hosting**: tiene MySQL, PHP 8.2+, SSH y cron, pero NO Docker, NO Redis, NO Meilisearch, NO Supervisor. Esto invalida varias decisiones fijas del spec (sección 11). Se revisan acá.

### MySQL en lugar de PostgreSQL 16 — REEMPLAZA decisión del spec

Decisión: MySQL 8 en dev (contenedor docker-compose) y prod. Se descarta PostgreSQL.

Por qué: el shared hosting solo ofrece MySQL. Migrar el stack ahora es barato (no hay migraciones escritas todavía); hacerlo después sería caro.

Implicancia: evitar features PostgreSQL-specific (arrays nativos, operadores JSONB, tipos `inet`, etc.). Las columnas `json` del modelo de dominio funcionan en MySQL 8 sin problema. Eloquent abstrae el resto.

### Cache + Queue + Session en driver `database` — REEMPLAZA Redis del spec

Decisión: los tres servicios usan el driver `database`. Se descarta Redis.

Por qué: shared hosting no ofrece Redis. Las migraciones por defecto de Laravel 11 ya crean las tablas `cache`, `jobs` y `sessions`, así que funciona sin configuración extra. Para un operador único el volumen no justifica Redis.

### Queue procesada por cron — REEMPLAZA Supervisor del spec

Decisión: `QUEUE_CONNECTION=database`. En producción la queue se procesa con un cron job que corre `php artisan queue:work --stop-when-empty --max-time=55` cada minuto, junto al `php artisan schedule:run` del scheduler.

Por qué: shared hosting no tiene Supervisor para mantener workers vivos. El patrón cron + `--stop-when-empty` es el estándar para shared hosting.

En Fase 1 puede arrancarse incluso con `QUEUE_CONNECTION=sync` si no hay jobs críticos en background.

### Búsqueda: Laravel Scout con driver `database` — REEMPLAZA Meilisearch del spec

Decisión: cuando se necesite búsqueda (Fase 1+), usar Laravel Scout con driver `database`. Se descarta Meilisearch.

Por qué: Meilisearch no corre en shared hosting y un servicio hosteado agrega costo. El catálogo de productos y la base de socios son chicos; el driver `database` de Scout (o incluso `LIKE` queries) alcanza de sobra.

### Deploy vía SSH + git

Decisión: deploy a producción por SSH: `git pull` + `composer install --no-dev --optimize-autoloader` + `php artisan migrate --force` + `php artisan config:cache route:cache view:cache`.

Por qué: hay acceso SSH, así que no hace falta subir `vendor/` por FTP ni correr migraciones desde phpMyAdmin.

### CI corre PHP 8.2

Decisión: el workflow de CI usa PHP 8.2 (antes 8.3).

Por qué: 8.2 es el mínimo confirmado del hosting y el mínimo de Laravel 11. Correr CI en 8.2 garantiza compatibilidad con cualquier 8.2+.

### Dev = prod (paridad de entorno)

Decisión: el `docker-compose.yml` de dev levanta exactamente lo que hay en prod (MySQL 8) y nada más. Los tests en CI corren contra MySQL real, no sqlite.

Por qué: el objetivo declarado es que algo que anda en dev ande en prod. Sin Redis ni Meilisearch en dev, no hay forma de depender accidentalmente de ellos.
