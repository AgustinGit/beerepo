# Deploy a producción (shared hosting)

Guía para instalar el backoffice (`apps/admin`) en un shared PHP hosting con
MySQL, PHP 8.2+, SSH y cron. Asume que podés elegir a qué carpeta apunta el
dominio (document root).

> **Qué se ve hoy:** solo el backoffice Filament en `/admin` (login + dashboard).
> Todavía no hay tienda pública. Sirve para validar el deploy y que las
> migraciones corran en el MySQL real.

## 0. Crear la base de datos (panel del hosting)

En cPanel → **MySQL Databases**:

1. Crear base, ej. `agusclub` → queda `cpaneluser_agusclub`.
2. Crear usuario, ej. `agus` → queda `cpaneluser_agus`, con contraseña fuerte.
3. Asociar el usuario a la base con **ALL PRIVILEGES**.

Anotar los 3 valores (con prefijo).

## 1. Subir el código (fuera del document root)

Clonar el repo en una carpeta que **no** sea servida por la web, ej. `~/agusclub`:

```bash
cd ~
git clone <url-del-repo> agusclub
cd agusclub
git checkout <rama>
```

(Si no hay git en el server, subir por SFTP a `~/agusclub`.)

## 2. Apuntar el dominio a `apps/admin/public`

En el panel, configurar el document root del dominio/subdominio a:

```
/home/cpaneluser/agusclub/apps/admin/public
```

Así el `.env`, `vendor/` y el resto del código quedan fuera de lo público.

## 3. Dependencias PHP

Verificar si hay Composer:

```bash
composer --version        # si responde, hay composer
# alternativas comunes en shared hosting:
php -v                     # confirmar que el CLI es 8.2+
which composer composer.phar
```

- **Si hay Composer:**
  ```bash
  cd ~/agusclub/apps/admin
  composer install --no-dev --optimize-autoloader
  ```
- **Si NO hay Composer:** correr `composer install --no-dev --optimize-autoloader`
  en tu máquina y subir la carpeta `vendor/` por SFTP a `apps/admin/vendor/`.

## 4. Configurar el `.env`

```bash
cd ~/agusclub/apps/admin
cp .env.production.example .env
```

Editar `.env` y completar:
- `APP_URL` = la URL del dominio (con https)
- `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` = los del paso 0
- `DB_HOST` = `localhost` (si falla la conexión, probar `127.0.0.1`)

Generar la clave de la app:

```bash
php artisan key:generate
```

## 5. Migrar y cargar datos iniciales

```bash
php artisan migrate --force
php artisan db:seed --force   # crea admin agus@agus.club / password + planes
```

Errores típicos de conexión:
- `[1045] Access denied` → usuario/contraseña mal.
- `[2002] Connection refused` → alternar `DB_HOST` entre `localhost` y `127.0.0.1`.

## 6. Assets y cachés

```bash
php artisan filament:assets   # publica los assets de Filament (NO necesita Node)
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

> Node solo hará falta cuando exista la tienda pública (Tailwind/Vite). En ese
> momento se compila con `npm run build` en tu máquina y se sube `public/build/`.

## 7. Permisos

```bash
chmod -R 775 storage bootstrap/cache
```

## 8. Probar

Ir a `https://TU-DOMINIO/admin` y entrar con `agus@agus.club` / `password`.
**Cambiar esa contraseña enseguida.**

## 9. Cron (opcional por ahora)

Todavía no hay tareas críticas, pero dejar listo el cron del hosting:

```cron
* * * * * cd ~/agusclub/apps/admin && php artisan schedule:run >> /dev/null 2>&1
* * * * * cd ~/agusclub/apps/admin && php artisan queue:work --stop-when-empty --max-time=55 >> /dev/null 2>&1
```

## Re-deploys posteriores

```bash
cd ~/agusclub && git pull
cd apps/admin
composer install --no-dev --optimize-autoloader   # si hay composer
php artisan migrate --force
php artisan config:cache && php artisan route:cache && php artisan view:cache
php artisan filament:assets
```
