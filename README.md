# Cervecería Agus

ERP vertical de microcervecería + tienda online. Stack: Laravel 11 + Filament 3 + Livewire + PostgreSQL + Redis.

Spec completo: [`spec_software_cerveceria_agus.md`](./spec_software_cerveceria_agus.md).
Progreso: [`PROGRESS.md`](./PROGRESS.md) · Decisiones: [`DECISIONS.md`](./DECISIONS.md).

## Estructura

```
beerepo/
├── apps/
│   └── admin/          # Backoffice Laravel 11 + Filament 3 (Fase 0)
│       (apps/tienda y apps/reparto se crearán en Fases 1 y 2)
├── docker-compose.yml  # Postgres 16 + Redis 7 + Meilisearch
├── PROGRESS.md
├── DECISIONS.md
└── spec_software_cerveceria_agus.md
```

## Setup local

Requisitos: PHP 8.3+, Composer 2, Docker, Node 20+.

```bash
# 1. Levantar servicios
docker compose up -d

# 2. Setup app admin
cd apps/admin
cp .env.example .env
composer install
php artisan key:generate
php artisan migrate
php artisan serve
```

El backoffice queda en `http://localhost:8000/admin`.

### Crear primer usuario admin

```bash
php artisan tinker
> User::create(['name' => 'Agus', 'email' => 'agus@agus.club', 'password' => bcrypt('cambialo')])
```

(El acceso al panel está restringido a emails `@agus.club` por defecto — ver `app/Models/User.php`.)

## Calidad de código

```bash
cd apps/admin
./vendor/bin/pint              # formato (Pint, PSR-12 + reglas custom)
./vendor/bin/pint --test       # verificar sin escribir
./vendor/bin/phpstan analyse   # análisis estático (Larastan nivel 6)
./vendor/bin/pest              # tests (Pest 3)
```

CI corre los 3 en cada PR (`.github/workflows/ci.yml`).
