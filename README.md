# Requisiciones

Sistema interno de DIAPSA para gestionar las **requisiciones de viáticos** y su **comprobación de gastos**, que hoy se manejan en Excel. Basado en **Laravel 13** y **React** con InertiaJS y componentes de ShadcnUI.

## Índice

1. [Características principales](#características-principales)
2. [Requisitos](#requisitos)
3. [Instalación rápida](#instalación-rápida)
4. [Entorno con Laragon](#entorno-con-laragon)
5. [Comprobación de código](#comprobación-de-código)
6. [Integración continua](#integración-continua)
7. [Buenas prácticas al crear una rama](#buenas-prácticas-al-crear-una-rama)
8. [Commits](#commits)
9. [Documentación técnica](#documentación-técnica)
10. [Tecnologías utilizadas](#tecnologías-utilizadas)
11. [Estructura básica](#estructura-básica)
12. [Estado del proyecto](#estado-del-proyecto)
13. [Licencia](#licencia)

## Características principales

- **Requisición de viáticos**: conceptos con cálculo automático (gasolina por distancia, comidas por persona y día, hospedaje por noche).
- **Autorizaciones**: coordinador, administración y confirmación del depósito por parte del solicitante.
- **Depósitos**: uno o varios anticipos por requisición.
- **Comprobación de gastos**: facturas (CFDI) y tickets por tipo de gasto, sin facturas duplicadas.
- **Presupuesto contra gastado**: por tipo de gasto, con el saldo final (reembolso, devolución o descuento de nómina).

## Requisitos

- PHP 8.3 o superior (Laragon trae 8.4)
- Composer 2
- Node.js 22 o superior y npm
- MySQL 8.4
- Git

## Instalación rápida

1. Clona el repositorio y entra en la carpeta

```bash
git clone https://github.com/diapsa/requisiciones.git
cd requisiciones
```

2. Instala todo con el script de Composer

```bash
composer setup
```

Este script hace, en orden: `composer install`, copia `.env.example` a `.env` si no existe, `php artisan key:generate`, `php artisan migrate --force`, `npm install` y `npm run build`.

El `.env.example` ya trae `DB_DATABASE=diapsa_requisitions`, usuario `root` sin contraseña (Laragon), idioma `es` y zona horaria `America/Mexico_City`. Si la base `diapsa_requisitions` no existe, la migración la crea sola; si corres `php artisan migrate` a mano, te pregunta si quieres crearla.

> Si ya tienes un `.env`, no copies encima el `.env.example`: perderías tu `APP_KEY` y tus cambios.

3. Inicia el entorno de desarrollo

Con Laragon, Apache ya sirve Laravel; solo hace falta Vite:

```bash
npm run dev
```

y abrir `http://requisiciones.test` (la primera vez, ver [Entorno con Laragon](#entorno-con-laragon)).

Sin Laragon, este comando levanta el servidor de Laravel, la cola y Vite juntos, en `http://127.0.0.1:8000`:

```bash
composer run dev
```

## Entorno con Laragon

Laragon crea el dominio `http://requisiciones.test` a partir del nombre de la carpeta, pero **solo al recargarse**. La primera vez:

1. En Laragon: **Detener** y luego **Iniciar todo** (o Apache → Recargar).
2. Acepta el permiso de administrador para que agregue el dominio al archivo hosts.
3. Deja corriendo `npm run dev` para ver los cambios de React al momento.

Si el navegador muestra `DNS_PROBE_FINISHED_NXDOMAIN`, Laragon todavía no creó el dominio.

Con `npm run dev` no corre la cola de trabajos. Cuando el sistema use trabajos en cola (por ejemplo, envío de correos), córrela aparte:

```bash
php artisan queue:listen
```

## Comprobación de código

- Todo lo que revisa GitHub Actions (formato, lint, tipos, PHPStan y pruebas)

```bash
composer ci:check
```

- Formato y lint de JavaScript/TypeScript

```bash
npm run check
npm run check:fix
npm run types:check
```

- Formato de PHP

```bash
composer lint
```

- Pruebas de PHP (usan SQLite en memoria, no tocan tu base de datos)

```bash
php artisan test
```

## Integración continua

- `.github/workflows/tests.yml` corre en cada push a `develop` y `master`, y en todos los pull requests: PHP 8.4, Node 22, un servicio MySQL 8.4 con la base `diapsa_requisitions`, `composer setup` y `composer ci:check`.
- `.github/dependabot.yml` propone cada semana actualizaciones de las acciones de GitHub.

## Buenas prácticas al crear una rama

- El flujo es: rama de trabajo → `develop` → `master`.
- Crea ramas con nombres descriptivos en inglés, en kebab-case, a partir de `develop`:
  - `feat/` para nuevas funcionalidades (`feat/requisitions-schema`).
  - `fix/` para correcciones.
  - `docs/` para documentación.
  - `chore/` para configuración y mantenimiento.
  - `refactor/` para reorganizar código sin cambiar su comportamiento.
  - `test/` para pruebas.
- Cada rama debe abordar un único cambio o funcionalidad.
- Al terminar, abre un pull request hacia `develop`. Cuando se libera una versión, otro de `develop` hacia `master`.
- No subas al repositorio:
  - el `.env`;
  - los formatos de Excel reales (tienen nombres, cuentas bancarias y datos de clientes);
  - los archivos de Laravel Boost y de los agentes de IA (`AGENTS.md`, `boost.json`, `.claude/`…), que cada quien genera con `php artisan boost:install`.

## Commits

Formato `type(scope): description`, en inglés y en imperativo:

| Tipo | Uso |
|---|---|
| `feat` | Funcionalidad nueva |
| `fix` | Corrección |
| `chore` | Configuración o mantenimiento |
| `docs` | Documentación |
| `style` | Cambios visuales sin lógica |
| `test` | Pruebas |

Ejemplos: `feat(requisitions): calculate fuel cost by distance`, `fix(expenses): reject duplicated CFDI`.

## Documentación técnica

La documentación del proyecto está en la carpeta [`docs/`](docs/):

- **[Índice y resumen](docs/README.md)**: proceso que cubre el sistema y decisiones tomadas.
- **[Análisis de los formatos de Excel](docs/analisis-formatos-excel.md)**: qué contiene cada formato actual y sus problemas.
- **[Base de datos](docs/base-de-datos.md)**: convenciones, diagrama relacional y diccionario de datos.
- **[Reglas de negocio](docs/reglas-de-negocio.md)**: estados, autorizaciones, cálculos, políticas y puntos por confirmar.

## Tecnologías utilizadas

- [Laravel 13](https://laravel.com) con [Fortify](https://laravel.com/docs/fortify) y [Wayfinder](https://github.com/laravel/wayfinder)
- [React 19](https://react.dev) con TypeScript
- [InertiaJS 3](https://inertiajs.com)
- [Tailwind CSS 4](https://tailwindcss.com) y [ShadcnUI](https://ui.shadcn.com/)
- MySQL 8.4
- PHPUnit 12

## Estructura básica

- `app/` modelos, controladores y servicios principales.
- `bootstrap/` archivos de arranque de Laravel.
- `config/` configuración de la aplicación.
- `database/` migraciones, factories y seeders.
- `public/` archivos servidos directamente por el servidor web.
- `resources/js/` componentes y páginas React.
- `routes/` definición de rutas.
- `storage/` archivos generados como logs o cachés.
- `tests/` pruebas automatizadas.
- `docs/` documentación del proyecto.

## Estado del proyecto

**Última actualización**: noviembre de 2026

### ✅ Completado
- Estructura base con Laravel 13 y el starter kit de React
- Base de datos `diapsa_requisitions` y CI con MySQL
- Análisis de los formatos de Excel y propuesta de base de datos

### 🔄 En desarrollo
- Revisión del diseño de la base de datos con el encargado senior
- Confirmación de reglas pendientes con administración

### 📋 Próximos pasos
- Migraciones, modelos y catálogos
- Captura de requisiciones y autorizaciones
- Depósitos y comprobación de gastos

## Licencia

Proyecto privado y propietario.
