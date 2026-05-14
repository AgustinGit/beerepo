# PROGRESS — Cervecería Agus

Tracking de avance por fase según `spec_software_cerveceria_agus.md`.

## Fase 0 — Setup

- [x] Estructura monorepo `apps/` creada
- [x] Laravel 11 instalado en `apps/admin`
- [x] Filament 3 instalado
- [x] Pest 3 reemplaza PHPUnit
- [x] Larastan 3 configurado (nivel 6)
- [x] Laravel Pint configurado
- [x] `docker-compose.yml` con Postgres 16 + Redis 7 + Meilisearch
- [x] `.env.example` con Postgres, Redis, Meilisearch, Mercado Pago, R2
- [x] Estructura action-based (`app/Actions`, `app/Services`, `app/Enums`, `app/ViewModels`)
- [x] GitHub Actions CI (Pint + Larastan + Pest)
- [x] `PROGRESS.md` y `DECISIONS.md` creados

### Pendiente para cerrar Fase 0 (manual)

- [ ] Levantar containers (`docker compose up -d`) y correr `php artisan migrate`
- [ ] Configurar S3-compatible (Cloudflare R2): completar credenciales en `.env`
- [ ] Crear cuentas Mercado Pago sandbox + prod, completar tokens en `.env`
- [ ] Configurar dominios y SSL (`admin.agus.club`, `agus.club`, `reparto.agus.club`)
- [ ] Configurar Sentry (errores) + UptimeRobot
- [ ] Primer deploy a staging

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
