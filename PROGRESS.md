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

### Modelo de datos (PROMPT 2) — hecho

- [x] Enums: SubscriptionStatus, OrderStatus, OrderSource, CouponType, CouponApplicableTo, CustomerSource
- [x] Migrations + modelos: Customer, Address, Plan, Subscription, SubscriptionBox, Product, Order, OrderItem, Coupon
- [x] Customer como modelo autenticable propio + guard `customer` separado del admin (ver DECISIONS.md)
- [x] Factories para los 9 modelos (con states: premium/basic, guest/paid, paused/cancelled, etc.)
- [x] Feature tests de creación + relaciones (19 tests, verde en sqlite)
- [x] DatabaseSeeder: admin `agus@agus.club` + planes Premium y Básico
- [x] FKs a Batch/Recipe/Delivery dejadas como columnas nullable sin constraint (son de Fase 2)

### Pendiente Fase 1

- [ ] PROMPT 1 ya cubierto en Fase 0
- [ ] PROMPT 3: integración Mercado Pago — pago único
- [ ] PROMPT 4: integración Mercado Pago — suscripciones recurrentes
- [ ] PROMPT 5: resources Filament (Order, Subscription, Customer, Product, Coupon) + widgets KPI
- [ ] PROMPT 6: tienda pública (Livewire + Tailwind)
- [ ] PROMPT 7: pre-venta / lista de espera
- [ ] PROMPT 8: emails transaccionales
- [ ] Auth de clientes (registro/login con guard `customer`) y login admin
- [ ] Legal: términos, privacidad (Ley 18.331), envíos

## Fase 2 — Operaciones

Pendiente.

## Fase 3 — Inteligencia de Producción

Pendiente.

## Fase 4 — Fidelización

Pendiente.

## Fase 5 — Eventos

Pendiente.
