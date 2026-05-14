# PROGRESS — Cervecería Agus

Tracking de avance por fase según `spec_software_cerveceria_agus.md`.

## Fase 0 — Setup

- [x] Estructura monorepo `apps/` creada
- [x] Laravel 11 instalado en `apps/admin`
- [x] Filament 3 instalado
- [x] Pest 3 reemplaza PHPUnit
- [x] Larastan 3 configurado (nivel 6)
- [x] Laravel Pint configurado
- [x] Stack revisado por restricciones del shared hosting (ver DECISIONS.md 2026-05-14)
- [x] `docker-compose.yml` con MySQL 8 (sin Redis ni Meilisearch)
- [x] `.env.example` con MySQL, cache/queue/session en `database`, Mercado Pago, R2
- [x] Estructura action-based (`app/Actions`, `app/Services`, `app/Enums`, `app/ViewModels`)
- [x] GitHub Actions CI (Pint + Larastan + Pest) contra MySQL, PHP 8.2
- [x] `PROGRESS.md` y `DECISIONS.md` creados

### Pendiente para cerrar Fase 0 (manual)

- [ ] Levantar contenedor (`docker compose up -d`) y correr `php artisan migrate`
- [ ] Verificar versión exacta de PHP del hosting de producción
- [ ] Configurar S3-compatible (Cloudflare R2) o decidir `FILESYSTEM_DISK=local`
- [ ] Crear cuentas Mercado Pago sandbox + prod, completar tokens en `.env`
- [ ] Configurar dominios y SSL (`admin.agus.club`, `agus.club`, `reparto.agus.club`)
- [ ] Configurar cron en el hosting: `schedule:run` + `queue:work --stop-when-empty`
- [ ] Configurar Sentry (errores) + UptimeRobot
- [ ] Primer deploy a staging vía SSH (`git pull` + `composer install` + `migrate`)

## Fase 1 — MVP Comercial (LANZAMIENTO)

Pendiente. Ver sección 6.1 del spec.

## Fase 2 — Operaciones

Pendiente.

## Fase 3 — Inteligencia de Producción

Pendiente.

## Fase 4 — Fidelización

Pendiente.

## Fase 5 — Eventos

Pendiente.
