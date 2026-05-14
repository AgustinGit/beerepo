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
