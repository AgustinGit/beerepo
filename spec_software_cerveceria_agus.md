# Cervecería Agus — Spec de Software para Claude Code

> Documento maestro de especificación del sistema interno de operaciones y tienda online de la cervecería. Pensado como contexto cargable en Claude Code: cada sección es auto-contenida y puede pegarse al inicio de una sesión para guiar el trabajo.

---

## 0. Cómo usar este documento con Claude Code

**Workflow recomendado:**

1. Al arrancar una sesión nueva, pegá la sección 0, 1, 2 y 3 como contexto. Eso le da a Claude Code el panorama y el stack.
2. Para cada tarea específica, pegá adicionalmente la sección de la fase en la que estés trabajando.
3. Usá los prompts sugeridos al final de cada fase como punto de partida.
4. Pedile a Claude Code que mantenga un archivo `PROGRESS.md` en el repo con check-list de avances.
5. Antes de cada commit importante, pedile que actualice `DECISIONS.md` con decisiones técnicas tomadas y por qué.

**Reglas de oro para todo el proyecto:**

- Si Claude Code propone agregar una librería pesada para algo trivial, cuestionarlo.
- Si una funcionalidad no está explícitamente en este documento, validar antes de implementarla. Scope creep mata el plazo.
- Test mínimo viable: feature tests de los flujos críticos (checkout, suscripción, webhook MP). No buscar 100% coverage.
- Deploy temprano: subir a staging al final de cada fase, no esperar al final.

---

## 1. Contexto del negocio

**Negocio:** Microcervecería artesanal con foco en venta directa al consumidor mediante club de suscripción mensual.

**Operador:** Agus, fundador único. Juez BJCP certificado, 8 años de experiencia cocinando. Desarrollador (PHP/Laravel ecosystem). Opera desde chacra en Canelones, Uruguay.

**Producto:**

- Líneas de cerveza, todas en lata de 500ml:
  - **Lupulada premium** (IPAs, APAs, hazy) — fermentada en isobárico 40L, sin oxidación
  - **Artesanal** (estilos varios refermentados en lata) — fermentada en baldes 20-30L
- Sin línea de botella retornable (decisión estratégica para simplificar logística).

**Canales de venta:**

1. **Club Premium** ($1.080/mes): caja mensual con 6 latas curadas + vaso cervecero trimestral + envío AMM bonificado + acceso anticipado a limitadas
2. **Club Básico** ($350/mes): cuota baja, da acceso a precios mayoristas (descuento sobre PVP) y acceso a limitadas
3. **Venta puntual a consumidor externo:** PVP completo + costo de envío
4. **Venta a locales aliados:** precios mayoristas
5. **Catas y maridajes en clubes privados** (fase posterior, ~mes 4-6)

**Precios:**

| Producto | PVP | Mayorista (socio Básico / local) |
|---|---|---|
| Lata artesanal 500ml | $180 | $128 |
| Lata lupulada 500ml | $220 | $156 |

**Restricciones críticas:**

- Único operador → el software debe optimizar el tiempo de Agus, no agregar trabajo
- Lanzamiento target: ~6 semanas desde inicio de desarrollo (coincide con primera tanda lista para Premium)
- Hosting ya provisto, no se requiere arquitectura cloud compleja
- Posicionamiento premium → diseño, UX y branding son parte del producto, no decorativo

---

## 2. Visión del sistema

**Lo que NO es:** una tienda online genérica con extras.

**Lo que SÍ es:** un ERP vertical de microcervecería con tienda integrada. Cuatro dominios conviven:

1. **Producción** — recetas, cocciones, fermentación, envasado, trazabilidad por batch
2. **Inventario** — stock de insumos, stock de producto terminado, alertas
3. **Comercial** — tienda, suscripciones, socios, pagos, cupones, fidelización
4. **Logística** — armado de pedidos, planificación de reparto, navegación turn-by-turn

**Métrica de éxito del software:**

- Agus invierte <15 min en gestionar un día normal de operación (excluyendo cocción/envasado)
- Cero pedidos perdidos o mal armados
- Cero pagos manuales (todo MP)
- Socios tienen experiencia premium sin que Agus intervenga manualmente

---

## 3. Stack técnico definitivo

### Backoffice + Tienda (apps separadas, misma DB)

- **Framework:** Laravel 11 (LTS)
- **Admin panel:** Filament v3
- **Frontend tienda pública:** Laravel + Livewire + Alpine.js + Tailwind CSS
  - Alternativa: Inertia + Vue 3 si se requiere SPA más rica (decidir en Fase 1)
- **DB:** PostgreSQL 16
- **Cache + Queue:** Redis
- **Búsqueda:** Laravel Scout + Meilisearch (productos, miembros)
- **Storage de archivos:** S3-compatible (Cloudflare R2 recomendado por costo)
- **Email transaccional:** Postmark o Resend
- **WhatsApp:** API oficial vía proveedor (360Dialog o similar)
- **Pagos:** Mercado Pago (SDK oficial PHP)
- **Suscripciones recurrentes:** Mercado Pago Suscripciones + Laravel Cashier-MP (o implementación custom)

### App de reparto

- **PWA con Capacitor** (build Android para tienda interna)
- **Framework:** mismo Laravel + Livewire para mantener stack único, o Next.js/React si se prefiere DX SPA
- **Mapas:** Google Maps API (deep links para navegación turn-by-turn que se proyecta en Android Auto)
- **Geolocalización:** browser Geolocation API
- **Offline-first:** trabajar offline y sincronizar al volver online (importante en zona rural)

### Infraestructura

- **Hosting:** ya provisto (asumir VPS Linux)
- **Reverse proxy:** Nginx o Caddy
- **Process manager:** Supervisor para queue workers
- **Deployment:** Deployer o Laravel Forge si está disponible, o GitHub Actions + rsync
- **Backups:** Spatie Laravel-Backup, daily a S3
- **Monitoring:** Sentry (errores) + UptimeRobot (uptime)

### Convenciones de código

- **PSR-12** + Larastan nivel 6 mínimo
- **Tests:** Pest (más legibles que PHPUnit)
- **Linting:** Laravel Pint en pre-commit
- **Estructura de carpetas:** acción-based (no MVC plano) — uso de Actions, Services, ViewModels

---

## 4. Arquitectura de aplicaciones

Tres apps separadas, base de datos compartida.

### 4.1 Tienda pública (`tienda.agus.club` o `agus.club`)

**Audiencia:** público general, prospectos, socios accediendo a su cuenta.

**Funcionalidades:**

- Landing page con storytelling de marca (Agus, BJCP, chacra)
- Catálogo de productos disponibles
- Página de detalle de cerveza (con datos BJCP, scorecard del juez, historia del batch)
- Página de cada batch (URL pública con QR escaneable)
- Carrito y checkout con MP (pago único)
- Suscripción al club (Premium o Básico) con MP recurrente
- Sistema de cupones
- Área del socio:
  - Estado de suscripción, próximo envío, próximo cobro
  - Beer passport (cervezas recibidas, mapa de estilos)
  - Wallet (saldo a favor)
  - Puntos de fidelización
  - Historial de pedidos
  - Votación por próximo batch limitado
  - Cancelar/pausar suscripción
- Página de calendario de catas (cuando aplique)
- Modo regalo (suscripción de 3/6/12 meses como regalo)

### 4.2 Backoffice (`admin.agus.club`)

**Audiencia:** solo Agus.

**Tecnología base:** Filament v3 con custom resources.

**Módulos:**

- Dashboard con KPIs (MRR, socios activos, ventas mes, stock crítico, churn)
- Gestión de productos y batches
- Gestión de recetas (BJCP categories, ingredientes, costos calculados)
- Calendario de planificación de cocciones
- Gestión de inventario (insumos + producto terminado)
- Pedidos y armado de envíos
- Planificación de reparto (asignar pedidos a una ruta)
- Gestión de socios y suscripciones
- Gestión de catas/eventos (fase posterior)
- Cupones y campañas
- Reportes financieros y operativos
- Configuración de plan, precios, etc.

### 4.3 App de reparto (`reparto.agus.club`)

**Audiencia:** Agus en la camioneta.

**PWA con instalación local en Android para integración con Android Auto.**

**Funcionalidades:**

- Login simple (PIN o token)
- Lista de paradas del día ordenadas por ruta optimizada
- Tocar parada → deep link a Google Maps con navegación turn-by-turn (Maps se muestra en pantalla Android Auto)
- Marcar entrega completada (foto + opcionalmente firma)
- Marcar entrega no entregada con motivo
- Indicador de paradas restantes / completadas
- Modo offline con sincronización automática al recuperar señal

**Importante sobre Android Auto:** la PWA corre en el celular (Agus la usa antes de salir y entre paradas). Google Maps, abierto desde la PWA, sí proyecta navegación en Android Auto. La UI custom no aparece en la pantalla del auto, pero esto resuelve el flujo real al 95%.

---

## 5. Modelo de dominio

### 5.1 Producción

```php
// Recipe — receta de un estilo
Recipe {
  id, name, bjcp_category, bjcp_subcategory,
  target_og, target_fg, target_abv, target_ibu, target_srm,
  description, food_pairing, brewer_notes,
  ingredients: relation HasMany RecipeIngredient,
  estimated_cost_per_liter: computed,
  active: bool,
  created_at, updated_at
}

RecipeIngredient {
  id, recipe_id, ingredient_id, quantity, unit, addition_stage
  // stage: mash | boil_60 | boil_30 | boil_15 | boil_5 | flameout | dry_hop | yeast
}

// Brew — cocción concreta (una instancia de una receta)
Brew {
  id, recipe_id, brew_date, brew_number, // brew_number autoincremental tipo "#47"
  initial_volume_liters, post_boil_volume_liters,
  measured_og, mash_temp, boil_time_minutes,
  water_profile_notes, brewer_notes,
  status, // planned | brewing | fermenting | conditioning | packaged | discarded
  created_at, updated_at
}

// Fermentation — proceso de fermentación de un brew
Fermentation {
  id, brew_id, tank_id,
  start_date, end_date,
  avg_temperature, measured_fg, calculated_abv,
  dry_hop_dates: json,
  notes,
  status // primary | secondary | conditioning | done
}

// Tank — fermentador o balde
Tank {
  id, name, capacity_liters, type, // type: isobaric | plastic_bucket | other
  current_brew_id: nullable, // si está ocupado
  status // empty | in_use | cleaning | maintenance
}

// Batch — producto terminado (lo que se envasa de un brew)
Batch {
  id, brew_id, product_id,
  package_date, expiry_date,
  format, // can_500ml | other
  units_produced, units_remaining,
  qr_code, qr_url, // URL pública del batch
  bjcp_scorecard: json, // notas oficiales como juez
  status, // in_stock | running_low | sold_out
  public_notes // visible en página pública del batch
}
```

### 5.2 Inventario

```php
Ingredient {
  id, name, type, // grain | hop | yeast | other
  unit, // kg | g | l | pack
  current_stock, min_stock_alert,
  avg_cost_per_unit, supplier,
  notes
}

StockMovement {
  id, ingredient_id, type, // purchase | consumption | adjustment | waste
  quantity, unit_cost, reference_type, reference_id,
  // reference puede ser Brew, Order, etc
  notes, occurred_at
}

Package { // material de packaging: latas vacías, tapas, etiquetas, cajas envío
  id, name, type, current_stock, min_stock_alert, unit_cost
}

Product { // referencia comercial agrupa batches del mismo estilo/receta
  id, name, recipe_id, slug, description,
  retail_price, wholesale_price,
  image_url, gallery: json,
  active, featured
}
```

### 5.3 Comercial — clientes y suscripciones

```php
User { // tabla base Laravel
  id, name, email, password, phone,
  email_verified_at, remember_token
}

Customer extends User {
  id, document_id, addresses: HasMany Address,
  default_address_id, notes_admin, marketing_opt_in,
  total_orders, total_spent, // denormalizado para perf
  loyalty_points,
  wallet_balance,
  source // referido | cata | redes | organico
}

Address {
  id, customer_id, label, recipient_name,
  street, number, apt, neighborhood, city, department,
  postal_code, country, lat, lng, notes_delivery
}

Plan {
  id, name, // Premium | Básico
  monthly_price, annual_discount_pct,
  cans_per_box, // 6 para Premium, 0 para Básico
  includes_glass_every_n_months, // 3 para Premium
  shipping_included_zones: json, // ["montevideo", "canelones", "costa"]
  discount_pct_on_purchases, // descuento si compra latas adicionales (Básico tiene precio mayorista)
  benefits: json,
  active
}

Subscription {
  id, customer_id, plan_id,
  status, // active | paused | cancelled | past_due
  mp_subscription_id, // ID de Mercado Pago
  start_date, next_billing_date, cancelled_at,
  total_paid_cycles,
  shipping_address_id,
  preferences: json // estilos preferidos, alergias, etc
}

SubscriptionBox { // caja efectivamente enviada cada mes
  id, subscription_id, billing_cycle,
  scheduled_send_date, sent_date,
  contents: json, // lista de batches/productos
  includes_glass: bool,
  shipping_address_snapshot: json,
  delivery_id: nullable
}
```

### 5.4 Comercial — pedidos y pagos

```php
Order {
  id, customer_id, // nullable si es checkout invitado
  status, // pending | paid | preparing | ready_for_delivery | shipped | delivered | cancelled
  subtotal, shipping_cost, discount_total, total,
  shipping_address_snapshot: json,
  notes,
  coupon_code, // nullable
  source, // web | admin | club | cata
  mp_payment_id, mp_payment_status,
  paid_at, delivered_at,
  delivery_id: nullable
}

OrderItem {
  id, order_id, product_id, batch_id, // batch específico asignado al enviar
  quantity, unit_price, line_total,
  is_gift_item, // por si es regalo
}

Coupon {
  id, code, type, // percent | fixed
  value, max_uses, used_count,
  min_order_total, applicable_to, // all | premium_only | basic_only | first_purchase
  starts_at, expires_at, active
}

LoyaltyPoint {
  id, customer_id, type, // earned | redeemed
  points, reason, reference_type, reference_id, occurred_at
}

WalletTransaction {
  id, customer_id, type, // credit | debit
  amount, reason, reference_type, reference_id, occurred_at
}
```

### 5.5 Logística

```php
Route {
  id, route_date, status, // planned | in_progress | completed
  total_stops, completed_stops,
  estimated_distance_km, estimated_duration_minutes,
  started_at, completed_at
}

Delivery {
  id, order_id, route_id, // route nullable hasta asignación
  stop_order, // orden dentro de la ruta
  status, // pending | en_route | delivered | failed | rescheduled
  address_snapshot: json, lat, lng,
  delivered_at, delivery_proof_url, signature_url,
  customer_notes, delivery_notes,
  attempt_count
}
```

### 5.6 Planificación de producción

```php
BrewPlan { // calendario anual
  id, planned_date, recipe_id,
  expected_volume_liters, expected_units,
  status, // planned | confirmed | brewed | cancelled
  notes
}
```

### 5.7 Marketing y comunidad

```php
BatchVote { // votación para próximo batch limitado
  id, batch_proposal_id, customer_id, voted_at
}

BatchProposal { // opciones para que socios voten
  id, name, style, description, image_url,
  voting_starts_at, voting_ends_at,
  winner: bool, votes_count, active
}

GiftSubscription { // suscripción regalada
  id, purchaser_id, recipient_email, recipient_name,
  plan_id, months, total_paid, mp_payment_id,
  redemption_code, redeemed_at, subscription_id // si ya activó
}

Notification {
  id, customer_id, channel, // email | whatsapp | sms
  template, sent_at, status, payload: json
}

Event { // catas y maridajes (fase 5)
  id, type, // tasting | pairing
  name, description, scheduled_at, duration_minutes,
  location, capacity, price,
  member_premium_price, member_basic_price,
  active
}

EventReservation {
  id, event_id, customer_id, // nullable si no es socio
  attendees, total_paid, mp_payment_id, status, attended
}
```

---

## 6. Roadmap por fases

### Fase 0 — Setup (1 semana)

**Objetivo:** repos creados, entorno funcionando, stack instalado.

**Tareas:**

- Crear repo monorepo o múltiples repos (definir)
- Configurar Laravel 11, Filament, Postgres, Redis localmente
- Configurar S3-compatible storage
- Crear estructura base de carpetas y namespaces
- Configurar Pest + Pint + Larastan
- CI/CD básico (GitHub Actions: lint + tests)
- Deployment a staging
- Crear cuentas Mercado Pago (sandbox + prod)
- Configurar dominios y SSL

**Definition of Done:**

- `composer install && php artisan migrate && php artisan serve` levanta el proyecto
- Tests vacíos pasan en CI
- Staging accesible vía HTTPS
- Sentry capturando errores

---

### Fase 1 — MVP Comercial (4-6 semanas) ← LANZAMIENTO

**Objetivo:** Agus puede vender al público y cobrar suscripciones del club en producción.

**Scope:**

- Auth: registro y login de clientes, login admin separado
- Modelo de datos: Customer, Address, Plan, Subscription, Order, OrderItem, Product, Coupon, MP entities
- Catálogo de productos público (alta manual desde Filament)
- Página de producto con datos BJCP simulados (no requiere batch aún)
- Carrito y checkout con MP (pago único)
- Suscripción al club Premium y Básico con MP recurrente
- Webhook MP para confirmar pagos
- Cupones de descuento (código en checkout)
- Área del socio básica:
  - Ver suscripción activa, próximo cobro, próximo envío
  - Historial de pedidos
  - Cancelar suscripción (con reactivación posible)
  - Pausar suscripción 1-3 meses
  - Editar dirección de envío
- Backoffice Filament:
  - CRUD de productos y planes
  - Listado de pedidos con filtros
  - Listado de suscripciones activas/pausadas/canceladas
  - Marcar pedido como pagado/preparado/enviado manualmente
  - Crear cupones
- Página de pre-venta / lista de espera (clave para captar antes del lanzamiento)
- Email transaccional: bienvenida, confirmación de pago, suscripción activada
- Legal: términos y condiciones, política de privacidad, política de envíos

**Out of scope en Fase 1:**

- Tracking de stock automático
- Trazabilidad por batch
- App de reparto
- WhatsApp
- Beer passport / loyalty
- Catas

**Prompts iniciales para Claude Code:**

```
PROMPT 1: Setup inicial
"Vamos a arrancar un proyecto Laravel 11 con Filament 3 que será el backoffice de una microcervecería. Stack: PostgreSQL, Redis, Pest, Pint, Larastan. El proyecto se llama 'agus-club'. Creá el setup inicial con docker-compose para desarrollo local, configurá Pint con reglas PSR-12, Larastan nivel 6, y Pest con un test dummy. Estructura de carpetas: app/Actions, app/Services, app/Filament, app/Models, app/Http. No agregues nada más fuera de este scope."

PROMPT 2: Modelo de datos Fase 1
"Tenés el spec del proyecto en SPEC.md. Vamos a crear las migrations y modelos Eloquent solo de las entidades necesarias para Fase 1: Customer (extiende User), Address, Plan, Subscription, SubscriptionBox, Product, Order, OrderItem, Coupon. NO crees aún Batch, Brew, Recipe, etc. Cada modelo con factories y un feature test mínimo de creación. Seguí las convenciones de Laravel 11. Usá enum casts donde aplique (status fields)."

PROMPT 3: Integración Mercado Pago — pago único
"Implementá la integración con Mercado Pago para checkout de pago único. Usar el SDK oficial mercadopago/dx-php. Crear: Service MercadoPagoService con métodos createPreference(Order) y handleWebhook(payload). Action ProcessOrderPayment. Endpoint webhook protegido con verificación de firma. Test de integración con sandbox de MP. Documentar en docs/payments.md el flujo completo."

PROMPT 4: Integración Mercado Pago — suscripciones
"Implementá suscripciones recurrentes con MP. Crear modelos: Subscription, SubscriptionBox. Service MercadoPagoSubscriptionsService. Soporte para: alta de suscripción, pausar, reactivar, cancelar, cambio de tarjeta. Webhook que actualice estado local cuando MP cobra/falla. Considerar past_due con reintentos. Documentar flujo completo."

PROMPT 5: Filament backoffice
"Generá resources Filament para Order, Subscription, Customer, Product, Coupon. Cada uno con: filtros relevantes, actions custom (ej. 'Marcar como pagado', 'Cancelar suscripción'), widgets de stats en el dashboard principal (MRR, socios activos, pedidos pendientes). UI en español. Iconos heroicons."

PROMPT 6: Tienda pública
"Implementá la tienda pública con Livewire + Tailwind. Páginas: home (landing con storytelling), catálogo, detalle de producto, /club (página de planes con CTA suscripción), checkout (carrito + dirección + pago MP), success/failure post-pago, /mi-cuenta (área socio). Diseño: paleta oscura premium, casi negro con acentos verdes esmeralda y dorados. Tipografía display sans-serif para títulos, serif para body. Mobile-first."

PROMPT 7: Pre-venta y lista de espera
"Antes del lanzamiento necesitamos captar pre-suscripciones. Implementá: página /preventa con formulario (email + plan elegido). Al enviar, redirige a checkout con flag pre_sale=true. La suscripción se crea pero no cobra hasta una fecha configurable (ej. 1 oct 2024). Notificación por email cuando se active. Filament admin para ver lista de espera."

PROMPT 8: Emails transaccionales
"Configurar Postmark o Resend. Crear mailables: WelcomeMember, OrderConfirmation, SubscriptionActivated, NextBoxScheduled, PaymentFailed, SubscriptionCancelled. Diseño consistente con la marca. Todos con preheader, alt text, dark mode friendly."
```

**Criterios de aceptación Fase 1:**

- [ ] Un visitante puede ver el catálogo y comprar una caja única con MP sandbox
- [ ] Un visitante puede suscribirse al Plan Premium con MP sandbox y la suscripción aparece en su área
- [ ] El webhook de MP actualiza correctamente el estado de pagos y suscripciones
- [ ] Un socio puede pausar y reactivar su suscripción
- [ ] Un socio puede cancelar su suscripción
- [ ] Cupones funcionan en checkout
- [ ] Pre-venta puede tomarse antes del lanzamiento sin cobrar
- [ ] Backoffice permite gestionar todo desde Filament
- [ ] Sitio en producción en `agus.club` con SSL
- [ ] Emails llegan correctamente
- [ ] Cero pedidos perdidos en pruebas de stress básicas

---

### Fase 2 — Operaciones (4-6 semanas, post-lanzamiento)

**Objetivo:** automatizar inventario, trazabilidad básica y reparto.

**Scope:**

- Modelo de Producción: Recipe, Brew, Fermentation, Tank, Batch
- Modelo de Inventario: Ingredient, StockMovement, Package
- Filament resources para todos los nuevos modelos
- Asignación de batches a OrderItems al preparar pedido
- Generación de QR code por Batch
- Página pública por batch (URL: `agus.club/batch/{qr_code}`)
- Modulo de armado de pedidos: workflow "preparar pedido" que descuenta stock automáticamente
- Modelo de Logística: Route, Delivery
- Planificación de ruta: en backoffice, asignar pedidos del día a una ruta, ordenar paradas
- App PWA de reparto:
  - Login con token
  - Lista de paradas
  - Deep link a Google Maps por parada
  - Marcar entregado con foto
  - Sincronización offline
- Alertas de stock bajo de insumos
- Reportes básicos: ventas mes, socios activos, churn rate

**Prompts iniciales:**

```
PROMPT FASE 2.1: Producción e inventario
"Continuamos el proyecto agus-club. Vamos a agregar el dominio de Producción. Crear migrations/modelos: Recipe, RecipeIngredient, Brew, Fermentation, Tank, Batch, Ingredient, StockMovement, Package. Relaciones según SPEC.md sección 5.1 y 5.2. Filament resources para cada uno con UX optimizada para data entry rápido por Agus. Workflow: Recipe → Brew (instanciada) → Fermentation → Batch (envasado). El packaging de un Brew genera unidades de Batch que se restan del stock de Package (latas, tapas, etc) automáticamente."

PROMPT FASE 2.2: Trazabilidad por batch + QR
"Cada Batch tiene un qr_code único generado al crearse. Implementá: generación de QR (librería simplesoftwareio/simple-qrcode o equivalente), página pública /batch/{qr_code} con info del batch (estilo, fechas, OG/FG/ABV/IBU, notas del cervecero como juez BJCP), opción de imprimir hoja de etiquetas con QRs en formato A4 (10 por hoja). Diseño de la página pública: storytelling, foto, datos técnicos en cards."

PROMPT FASE 2.3: Armado de pedidos
"Implementá workflow de preparación de pedidos. En Filament Order resource agregá action 'Preparar pedido' que: muestre los items, permita asignar un Batch concreto a cada OrderItem (solo batches con stock disponible), descuente del stock al confirmar, cambie status a 'ready_for_delivery'. Si no hay stock suficiente de ningún batch para un producto, mostrar warning. Vista de cola de armado en dashboard."

PROMPT FASE 2.4: Reparto
"Implementá módulo de reparto. En Filament: vista 'Ruta del día' que liste todos los pedidos con status 'ready_for_delivery' del día seleccionado. Permitir ordenar paradas (drag & drop). Crear Route. Generar link/QR para abrir la PWA de reparto.

PWA de reparto: subdomain reparto.agus.club. Login con token de ruta. Lista de paradas con estado. Tocar parada → muestra detalle + botón 'Abrir en Maps' (deep link). Botón 'Marcar entregado' que capture foto + GPS. Funciona offline (queue local + sync). Considerar Workbox para service worker."

PROMPT FASE 2.5: Alertas y reportes
"Implementá: comando schedule:run que detecte stock bajo de Ingredient/Package y envíe notificación a Agus por email + WhatsApp. Reportes en Filament: ventas por mes (chart), socios activos histórico, churn rate, top productos. Widgets en dashboard principal."
```

**Definition of Done Fase 2:**

- [ ] Agus puede registrar una cocción completa en <5 min
- [ ] Los batches generados aparecen con QR en pocos segundos
- [ ] La página pública del batch carga rápido y se ve premium
- [ ] El armado de pedido descuenta stock correctamente
- [ ] La PWA de reparto funciona en celular real
- [ ] La integración con Google Maps abre navegación turn-by-turn
- [ ] Las paradas se marcan offline y syncan al volver
- [ ] Las alertas de stock llegan oportunamente

---

### Fase 3 — Inteligencia de Producción (3-4 semanas)

**Objetivo:** planificar el año y entender costos y márgenes reales.

**Scope:**

- Recipes con cálculo automático de costo/L basado en ingredient costs históricos (rolling avg de últimos 3 StockMovements de tipo purchase)
- Margen calculado por receta: precio venta - costo
- BrewPlan: calendario anual de cocciones, drag & drop
- Forecasting básico de demanda: dado X socios activos + Y venta externa promedio, calcular litros necesarios por mes
- Comparación demanda proyectada vs producción planificada → alertas si shortage
- Dashboard de producción: tanks ocupados, próximas cocciones, próximos envasados
- Reportes financieros: P&L mensual, margen por receta, COGS

**Prompts:**

```
PROMPT FASE 3.1: Costeo y margen
"Implementá cálculo automático de costo por receta. Recipe::estimated_cost_per_liter() = sum(ingredient_qty * avg_cost) / batch_size. Mostrar en Filament Recipe resource con stats. Comparar con precio de venta y mostrar margen. Si el costo sube por encima de threshold, alerta."

PROMPT FASE 3.2: Calendario de planificación
"Implementá BrewPlan. Vista de calendario tipo FullCalendar en Filament (livewire-calendar o similar). Drag & drop para reorganizar. Filtros por receta. Indicador visual de capacidad de tanks. Cuando un BrewPlan se convierte en Brew real, se vincula y muta a status 'brewed'."

PROMPT FASE 3.3: Forecasting
"Implementá demanda proyectada: dado activos Premium + Básico + ventas externas (rolling avg 3 meses), calcular litros de cerveza por estilo necesarios próximos 3 meses. Comparar con BrewPlan. Endpoint /admin/forecast con UI clara: necesario vs planificado vs producido."
```

---

### Fase 4 — Fidelización (2-3 semanas)

**Objetivo:** retener socios y profundizar relación.

**Scope:**

- Sistema de puntos: ganar puntos por compras, referidos, antigüedad. Redimir por descuentos.
- Wallet de socio: saldo a favor (devoluciones, créditos, regalos).
- Beer passport: cada caja recibida se acumula. Mapa de estilos cubiertos. PDF anual descargable.
- Sistema de referidos: código único por socio. Si un referido se suscribe, ambos ganan puntos.
- Votación por próximo batch limitado: BatchProposals con opciones, socios Premium votan.
- WhatsApp notifications: integrar API. Templates aprobados: confirmación de envío, llegada de caja, cumpleaños, alertas de batch limitado.
- Niveles de socio: bronze (0-6 meses), silver (6-12), gold (12-24), platinum (24+). Badge en perfil + beneficio mínimo extra.
- Modo regalo: comprar suscripción de 3/6/12 meses como regalo. Email al destinatario con código de canje.

**Prompts:**

```
PROMPT FASE 4.1: Puntos y wallet
"Implementá LoyaltyPoint y WalletTransaction. Reglas configurables: 1 punto por cada $100 gastados, 100 puntos por referido suscripto. Redención: 100 puntos = $50 de descuento. UI en área del socio. Eventos: PointsEarned, PointsRedeemed."

PROMPT FASE 4.2: Beer passport
"Cada SubscriptionBox enviado genera entries en el passport del socio. Vista en área socio: timeline de cajas + grilla de estilos cubiertos (basado en BJCP categories de los batches recibidos). Generación de PDF anual con todas las cervezas, datos, fotos. PDF como gancho de renovación."

PROMPT FASE 4.3: Referidos
"Cada Customer tiene un referral_code único. Página /invita-amigos en área socio con link de invitación. Al checkout con ?ref=CODIGO, vincular orden al referidor. Cuando el referido completa primera compra/suscripción, otorgar puntos a ambos. Métricas para Agus: top referidores."

PROMPT FASE 4.4: Votación
"Crear BatchProposal model. Admin crea propuestas (3-4 opciones de próximo limitado). Solo socios Premium votan. Una votación por proposal. Cierre automático en fecha. Ganador se marca y notifica. UI atractiva con descripciones y fotos de los estilos."

PROMPT FASE 4.5: WhatsApp
"Integrar WhatsApp Business API vía proveedor (360Dialog). Mailables se duplican como WhatsApp messages para socios opted-in. Templates: order_shipped, box_arriving_today, batch_voting_open, birthday, payment_failed. Webhook de respuestas."

PROMPT FASE 4.6: Niveles de socio
"Calcular tier según antigüedad de Subscription (months_paid). Badge visible en perfil + checkout. Beneficio extra por tier: gold gana cata gratis 1/año, platinum gana edición limitada extra 1/año."

PROMPT FASE 4.7: Modo regalo
"Implementá GiftSubscription. Checkout flag is_gift. Si is_gift: campos de destinatario + fecha de envío del email de canje. Generar redemption_code único. Email al destinatario con instrucciones. Destinatario canjea creando cuenta. Suscripción se activa con n meses ya pagados."
```

---

### Fase 5 — Eventos (cuando se lancen catas, ~mes 4-6)

**Objetivo:** vender entradas a catas/maridajes con descuentos automáticos para socios.

**Scope:**

- Modelo Event y EventReservation
- Calendario público de eventos
- Reserva online con MP (precio según tier de socio)
- Capacidad limitada con bloqueo concurrente
- Recordatorios automáticos
- Check-in: lista de asistentes con QR
- Post-evento: encuesta de satisfacción + oferta de upgrade a Premium

---

## 7. Features adicionales detalladas

### 7.1 Carta interactiva de catas BJCP

En la página de cada Batch, mostrar scorecard tipo BJCP con:

- Aroma, Apariencia, Sabor, Sensación en boca, Impresión general
- Puntaje total /50
- Notas del juez (vos como BJCP certificado)
- Comparación visual contra el rango ideal del estilo
- "Esta cerveza está dentro del estilo X según BJCP 2021"

Posicionamiento brutal: ninguna industrial muestra esto.

### 7.2 Beer Passport

UI tipo timeline + mapa de estilos. Componente Livewire con grilla de BJCP categories que se "iluminan" cuando el socio recibe cervezas de ese estilo. PDF anual generado con Spatie/laravel-pdf.

### 7.3 Sistema de votos para próximo batch limitado

Card-based UI con las 3-4 propuestas. Voto único por socio Premium. Countdown a cierre. Notificación al ganador para Agus de "ya saber qué cocinar". Comunicación al socio: "tu voto produjo esta cerveza".

### 7.4 Wallet del socio

Tabla `wallet_transactions`. Saldo computado. UI en área socio. Usable en checkout (toggle "usar wallet"). Devoluciones automáticas a wallet en lugar de reembolsos en MP (mejor para cashflow).

### 7.5 WhatsApp Business API

Integración con 360Dialog (proveedor confiable en LATAM). Templates pre-aprobados por Meta. Opt-in obligatorio. Mensajes transaccionales más conversación 1:1 si el socio responde.

### 7.6 Modo regalo

Ya descrito en Fase 4.7.

### 7.7 ~~Integración con DAC API~~

Descartado por decisión del fundador.

### 7.8 Dashboard de métricas para Agus

Métricas clave en home de backoffice:

- **MRR** (suma de cuotas activas Premium + Básico)
- **Active members** por plan
- **Churn rate mensual** (rolling 3 meses)
- **LTV promedio** estimado
- **CAC** si hay tracking de campañas (utm)
- **Stock crítico** (insumos bajo threshold)
- **Pedidos del día** (ready_for_delivery)
- **Próximas cocciones planificadas**
- **Suscripciones por vencer próxima semana** (renovación auto)

Widgets Filament o componente Livewire custom.

---

## 8. Integraciones externas

### Mercado Pago

- SDK: `mercadopago/dx-php`
- Pago único: Preference API
- Suscripciones: Preapproval API
- Webhooks: validar firma con secret
- Reintentos: configurar política de 3 reintentos en MP dashboard
- Eventos a manejar:
  - `payment.created` / `payment.updated` → actualizar Order
  - `preapproval.updated` → actualizar Subscription status
  - `authorized_payment.updated` → cobros recurrentes exitosos/fallidos

### WhatsApp Business

- Proveedor: 360Dialog (alternativas: Twilio, Wati)
- Templates: order_shipped, box_arriving_today, batch_voting_open, birthday, payment_failed, abandoned_cart
- Aprobación de templates lleva 24-72hs, planificar
- Costo por mensaje template ~USD 0.04-0.08

### Google Maps

- API key con restricciones por dominio
- Geocoding API: convertir direcciones a lat/lng al guardar Address
- Directions API: optimizar orden de paradas de una Route
- Deep links: `https://www.google.com/maps/dir/?api=1&destination={lat},{lng}` para abrir Maps con navegación

### Email

- Postmark (mejor deliverability) o Resend (más barato y moderno)
- Domain verification (SPF, DKIM, DMARC)
- Templates en HTML responsive

---

## 9. Convenciones de código

### Estructura de carpetas

```
app/
├── Actions/           # acciones de un solo propósito (CreateOrder, ActivateSubscription)
├── Console/
├── Enums/             # status enums
├── Events/            # eventos de dominio
├── Filament/          # resources del admin
│   ├── Resources/
│   ├── Widgets/
│   └── Pages/
├── Http/
│   ├── Controllers/
│   ├── Livewire/
│   ├── Middleware/
│   └── Requests/
├── Listeners/
├── Mail/
├── Models/
├── Notifications/
├── Observers/
├── Policies/
├── Providers/
├── Services/          # integraciones (MercadoPagoService, WhatsAppService)
└── ViewModels/        # transformación de datos para vistas
```

### Naming

- Models singular: `Customer`, `Order`, `Batch`
- Tables plural: `customers`, `orders`, `batches`
- Routes kebab-case en español: `/mi-cuenta`, `/club/premium`, `/preventa`
- Filament resource paths en español
- Comentarios y commits en español
- Variables y método names en inglés

### Tests

- Pest sobre PHPUnit
- Feature tests para flujos críticos: checkout, suscripción, webhook MP
- Unit tests para Services y Actions con lógica compleja
- No tests de Filament resources (no agregan valor)
- Mínimo de coverage no enforced

### Git

- Trunk-based development (rama `main` + feature branches cortas)
- Conventional commits: `feat:`, `fix:`, `refactor:`, `docs:`, `test:`
- Tags semver para releases

---

## 10. Pre-lanzamiento checklist

Antes de abrir el sitio al público al final de Fase 1:

- [ ] DNS apuntando a producción con SSL válido
- [ ] Mercado Pago en modo producción (no sandbox) con webhooks verificados
- [ ] Emails configurados con SPF/DKIM/DMARC
- [ ] Términos y condiciones publicados
- [ ] Política de privacidad publicada (cumplir Ley 18.331 Uruguay)
- [ ] Política de envíos y devoluciones publicada
- [ ] Backup automático diario funcionando
- [ ] Sentry capturando errores en producción
- [ ] Uptime monitoring activado
- [ ] Pruebas E2E manuales de los flujos críticos en producción real (con tarjetas de prueba o pequeños montos)
- [ ] Pre-venta abierta con primeros 15-25 socios pagados antes del lanzamiento público
- [ ] Plan de comunicación: redes sociales, email a contactos, WhatsApp broadcast a red personal
- [ ] Stock inicial preparado: latas armadas, etiquetas pegadas, batches creados en sistema
- [ ] Plan de respuesta a incidentes: cómo Agus contacta soporte de MP, soporte de hosting, etc.

---

## 11. Decisiones explícitas tomadas

Para evitar revisitas y bikeshedding:

| Decisión | Resolución |
|---|---|
| Stack | Laravel 11 + Filament 3 + Livewire + Tailwind |
| DB | PostgreSQL 16 |
| Suscripciones | Mercado Pago Preapproval |
| Apps | Tienda + Backoffice + PWA reparto, separadas |
| Frontend SPA | No por defecto, Livewire suficiente. Evaluar Inertia/Vue solo si UX lo exige |
| Trazabilidad | Por batch, no por lata individual |
| Android Auto | No UI custom, solo deep link a Google Maps |
| Integración DAC | Descartada |
| Lenguaje UI | Español (todo) |
| Lenguaje código | Inglés |
| Hosting | Provisto |
| WhatsApp | Vía 360Dialog (a confirmar al integrar) |
| Email | Postmark o Resend (decidir al integrar) |

---

## 12. Riesgos identificados

| Riesgo | Mitigación |
|---|---|
| Scope creep extiende lanzamiento más allá de 6 semanas | Fase 1 estrictamente la mínima. Todo lo demás post-lanzamiento. Si Claude Code propone algo fuera de scope, rechazar. |
| Webhook de MP no llega o se pierde | Reintentos + log de webhook events + endpoint de reconciliación manual |
| Stock se queda sin sincronizar después de fase 2 | Tests de feature del flujo armado-de-pedido. Reconciliación nocturna programada. |
| App de reparto falla en zona sin señal | Offline-first con Workbox + IndexedDB. Sync diferido. |
| Agus se queda sin tiempo para producir mientras desarrolla | Foco brutal en Fase 1. Diferir todo lo que no afecte el lanzamiento. |
| Diseño "AI genérico" mata posicionamiento premium | Brief de marca específico antes de Fase 1. Referencias visuales. Considerar contratar diseñador puntual para hero/landing si presupuesto permite. |

---

## 13. Prompts maestros para Claude Code

**Prompt de bootstrapping de sesión:**

```
Hola Claude. Estoy trabajando en agus-club, un sistema para una microcervecería artesanal en Uruguay. El spec completo está en SPEC.md (adjunto).

Stack: Laravel 11 + Filament 3 + Livewire + PostgreSQL.

Estamos en [FASE X]. Las decisiones técnicas ya tomadas están en DECISIONS.md. El progreso actual está en PROGRESS.md.

Reglas de trabajo:
1. Si propones algo fuera del scope de la fase actual, primero pregúntame.
2. Seguí estrictamente PSR-12, Pint, Larastan nivel 6.
3. Cada feature nueva debe incluir al menos un feature test del flujo principal.
4. UI en español, código en inglés.
5. Cuando termines una tarea, actualizá PROGRESS.md con un check.
6. Si necesitás una decisión que no esté documentada, preguntá antes de asumir.

Tarea de hoy: [DESCRIBIR]
```

**Prompt para revisión de PR:**

```
Revisá el siguiente PR con el siguiente criterio:
- ¿Está en scope de la fase actual?
- ¿Cumple PSR-12 y Larastan nivel 6?
- ¿Tiene al menos un feature test del flujo principal?
- ¿Sigue las convenciones del proyecto?
- ¿Hay refactor evidente que mejoraría legibilidad?
- ¿Hay riesgo de seguridad?

Devuelvelo con: APROBADO / CAMBIOS PEDIDOS y lista concreta.
```

---

## 14. Glosario

- **Batch:** unidad de producto terminado, salida de un Brew específico envasado. Tiene QR único.
- **Brew:** una cocción concreta (instancia de una Recipe).
- **BJCP:** Beer Judge Certification Program. Agus es juez certificado.
- **MRR:** Monthly Recurring Revenue, suma de cuotas mensuales activas.
- **Churn:** % de socios que cancela por mes.
- **LTV:** Lifetime Value, valor total estimado de un socio durante su permanencia.
- **CAC:** Customer Acquisition Cost.
- **AMM:** Área Metropolitana de Montevideo (Montevideo + Canelones + Costa).
- **MP:** Mercado Pago.

---

*Documento vivo. Última actualización: inicio del proyecto. Revisar al cierre de cada fase.*
