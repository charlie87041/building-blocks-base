# Building Blocks Base

Este repositorio se distribuye como el paquete **boost-brains/laravel-code-check**.
Automatiza tareas de análisis y documentación de rutas en proyectos Laravel.
Las pipelines definidas en `config/routeanalyzer.php` procesan las rutas,
generan archivos con el código unificado, matrices de pruebas, especificaciones Swagger
y verifican reglas arquitectónicas con Deptrac.

## Instalación
1. Clonar el repositorio y ejecutar `composer install` (PHP 8.2).
2. Instalar dependencias de Node con `npm install`.
3. Copiar `.env.example` a `.env` y configurar las credenciales necesarias (por ejemplo, claves para los proveedores LLM en `config/llm.php`).
4. Ejecutar las migraciones y arrancar el servidor con:
   ```bash
    php artisan migrate
    php artisan serve
    ```

### Instalar como dependencia

En otro proyecto Laravel puedes agregarlo ejecutando:

```bash
composer require boost-brains/laravel-code-check
```

## Comandos Artisan

| Comando | Descripción |
| ------- | ----------- |
| `bb:routes` | Analiza las rutas y genera el grafo de dependencias y la ontología OWL. |
| `bb:routes:code` | Construye el archivo de código unificado de cada ruta, a partir del grafo generado previamente. |
| `bb:routes:docs-swagger` | A partir del código unificado intenta crear una especificación Swagger para la ruta. |
| `bb:routes:test-matrix` | Analiza el código consolidado y sugiere matrices de pruebas para cada ruta. |
| `bb:routes:test-make` | Usa las matrices guardadas para generar archivos de prueba PHP/Pest. |
| `bb:app:is-cool` | Ejecuta verificaciones de arquitectura con Deptrac, usando las reglas descritas en `nl_rules.txt`. |

Los archivos generados se almacenan bajo `storage/app/route-analysis`. Por ejemplo,
`BuildUnifiedCodeFilePipe` escribe `unified-code/<ruta>.code.txt` y lo indexa para posteriores pasos;
`WriteSwaggerSpecToStoragePipe` guarda las especificaciones en `docs/swagger`; y
`GenerateTestFilePipe` escribe las pruebas en `tests/generated`.

## Limpieza y reglas de arquitectura

El comando `bb:app:is-cool` genera un `deptrac.yaml` a partir de las reglas en `nl_rules.txt` y luego ejecuta Deptrac,
guardando los resultados en `storage/app/architecture/violations`.

Ejemplo parcial de `nl_rules.txt`:

```
Las capas son: Controller, Service, Repository, Model, Infrastructure.

Los controllers solo pueden depender de servicios.
Los servicios pueden depender de repositorios y modelos.
Los repositorios pueden depender de modelos.
Los modelos no deben depender de ninguna otra capa.
```

## Configuración de LLM

Los expertos LLM (por defecto GPT-4 Turbo) se configuran en `config/llm.php`.
Se permite auto-revisión de respuestas (`self_review_enabled`) y cada experto puede
limitarse a cierto rol (tests, docs, etc.).

## Integración con Codex

Para que cada pull request se analice automáticamente y se ejecuten las
pruebas en GitHub Actions, es necesario definir la clave `CODEX_API_KEY` como
secreto del repositorio:

1. Abre la sección **Settings** del repositorio en GitHub.
2. Navega a **Secrets and variables** > **Actions**.
3. Pulsa **New repository secret** e introduce `CODEX_API_KEY` como nombre.
4. Copia la clave proporcionada por Codex y guarda los cambios.

La workflow situada en `.github/workflows/codex-qa.yml` usará este secreto para
enviar el código a Codex y ejecutar `composer test` en cada PR.

---

Este proyecto sirve como base demostrativa para explorar cómo automatizar
pruebas, documentación y validaciones de arquitectura en proyectos Laravel
mediante modelos de lenguaje y análisis del propio código.
