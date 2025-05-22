<?php

namespace App\Services;

use App\Pipes\RoutesMatrix\TestMatrix;

class PromptBuilder
{
    /**
     * Genera un prompt exhaustivo para que un LLM analice el código consolidado
     * de una ruta Laravel y devuelva una matriz de pruebas.
     *
     * @param string $code El código consolidado (.code.txt)
     * @param string|null $suffix Opcional: texto adicional específico para un experto
     * @return string Prompt listo para enviar al LLM
     */
    public static function forCodeAnalysis(string $code, ?string $suffix = '', string $focus = 'full'): string
    {
        $extraInstructions = '';

        if ($focus === TestMatrix::VALIDATION_FLOW) {
            $extraInstructions = <<<TXT
                Concéntrate exclusivamente en las validaciones de entrada. Genera escenarios de:
                - Datos faltantes, mal formateados o inválidos.
                - Casos límite de campos.
                - Entradas que violan las reglas definidas en FormRequest o llamadas a validate().
                - Casos exitosos con inputs válidos.

                Ignora lógica interna o dependencias del sistema.
            TXT;
        } else if ($focus === TestMatrix::LOGIC_FLOW) {
            $extraInstructions = <<<TXT
                Debes incluir escenarios que reflejen:
                    - Caminos condicionales (if, else, aborts, retornos tempranos, excepciones)
                    - Llamadas a servicios, pipes, repositorios o cualquier clase auxiliar
                    - Resultados dependientes de valores internos (ej. estados, flags, configuraciones)
                    - Efectos secundarios como dispatch de eventos, registros en log, actualización de estado
                    - Mocks necesarios para simular condiciones del sistema o resultados de servicios
                    - Y en general pruebas que evaluen la lógica del código
                No generes casos de validación ni de permisos de usuario. Enfócate en los caminos posibles dentro del flujo de ejecución del código y sus efectos.

                TXT;
        } else if ($focus === TestMatrix::AUTH_FLOW) {
        $extraInstructions = <<<TXT

             Concéntrate únicamente en el control de acceso. Incluye escenarios como:
            - Accesos permitidos y denegados según el rol del usuario
            - Verificaciones en políticas (Policy::class), gates, middleware de autorización
            - Casos donde el usuario no tiene permisos suficientes o está bloqueado
            - Rutas que dependen de reglas como `can:`, `Gate::allows`, `abort_if(!auth()->user()->hasPermission(...))`
            - Efectos de acceso denegado (ej. código 403, redirecciones, abortos)

            No generes escenarios de validación de datos ni de lógica interna..
            TXT;
    }
        return  self::baseMatrixPrompt($code, $suffix, $extraInstructions);

    }

    public static function baseMatrixPrompt(string $code, ?string $suffix = '', $extraInstructions = ''): string
    {
        return <<<PROMPT
            El siguiente bloque contiene todo el código relacionado a una ruta de una aplicación Laravel: controlador, Form Requests, middleware y clases de servicio.

            Tu tarea es analizar completamente este código y generar una matriz de pruebas **exhaustiva** que cubra todos los escenarios relevantes.

            Concéntrate exclusivamente en las validaciones de entrada. Genera escenarios de:
            - Datos faltantes, mal formateados o inválidos.
            - Casos límite de campos.
            - Entradas que violan las reglas definidas en FormRequest o llamadas a validate().
            - Casos exitosos con inputs válidos.

            Ignora lógica interna o dependencias del sistema.

            {$extraInstructions}

            ### Formato de salida:
            Una lista en JSON con objetos que contengan:
            - "title": nombre del escenario de prueba.
            - "input": array con los datos enviados.
            - "mocks": array con servicios o condiciones externas simuladas.

            El archivo debe ser exhaustivo y cubrir **todos los caminos posibles** que puedan surgir del análisis de este código.

            Código a analizar:
            {$code}

            Responde solo con el JSON. No incluyas explicaciones.
            PROMPT;
    }

    public static function normalizeTestMatrixPompt($json)
    {
        return <<<PROMPT
            A continuación recibirás una matriz de pruebas generada por múltiples expertos LLM. Tu tarea es:

            - Deduplicar los escenarios similares
            - Unificar estructura y formato
            - Elegir el nombre más claro para cada "title"
            - Simplificar los "mocks" donde sea redundante
            - Eliminar entradas repetidas o contradictorias

            Conserva todos los casos válidos, pero elimina lo innecesario.
            Responde solo con un JSON con el mismo formato: una lista de objetos con "title", "input" y "mocks".

            Matriz original:
            {$json}
        PROMPT;
    }

    public static function forSelfReview(string $code, array $previousMatrix): string
    {
        $previous = json_encode($previousMatrix, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        return <<<PROMPT
            A continuación se muestra el código completo de una ruta Laravel y una matriz de pruebas que tú mismo generaste previamente.

            Tu tarea es revisar si esa matriz cubre **todos los escenarios posibles** que pueden derivarse de dicho código.

            - Si consideras que la matriz **ya es completa**, responde con un array JSON vacío: `[]`
            - Si identificas **escenarios que omitiste**, responde con una nueva lista en el mismo formato, solo con los casos faltantes.

            El formato de cada elemento debe incluir:
            - "title": nombre del escenario
            - "input": array con los datos enviados
            - "mocks": array con condiciones externas o dependencias simuladas

            Código original:
                {$code}
            Tu respuesta previa:
                {$previous}
            Responde solo con JSON. No incluyas explicaciones ni comentarios.
            PROMPT;
    }

    public static function forTestGeneration(string $routeUri, array $scenario, string $testFormat = 'pest'): string
    {
        $scenarioJson = json_encode($scenario, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        return <<<PROMPT
        A continuación recibirás un escenario de prueba basado en una ruta Laravel. Tu tarea es generar el código de prueba automatizada correspondiente en formato "{$testFormat}".

        Incluye:
        - Preparación de mocks según lo descrito en "mocks"
        - Envío de input (generalmente usando postJson o similar)
        - Asserts necesarios para verificar que el escenario funcione correctamente

        No expliques nada. Responde solo con el archivo PHP completo. Agrega comentarios al código si lo ve necesario

        Ruta: {$routeUri}

        Escenario:
            {$scenarioJson}
        PROMPT;
    }

    public static function forSwaggerSpecGeneration(string $code, string $uri): string
    {
        return <<<PROMPT
            A continuación se presenta el código consolidado de la ruta **{$uri}** de una aplicación Laravel. Este incluye el controlador, validaciones (FormRequests), middleware, servicios utilizados, eventos lanzados y lógica condicional.

            Tu tarea es generar una especificación Swagger (OpenAPI 3.0.0) para **esa ruta exacta**, incorporando la mayor cantidad de detalles técnicos posibles.

            ### Instrucciones:

            1. **Usa exactamente la URI proporcionada**: `{$uri}`.
            2. Incluye el **método HTTP** si es deducible. Por defecto, usa `post`.
            3. Genera un `operationId` representativo como `createJob`, `deleteUser`, etc.
            4. **Tag**: infiere desde el recurso implicado en la URI (ej. `/jobs/{id}` → `Job`).
            5. **description**: redacta una descripción clara que incluya:
               - Qué hace esta acción
               - Qué payload espera
               - Por cada campo del payload: tipo, validaciones(aplicables solo al HTTP method de la ruta) y restricciones. En el caso de las validaciones, redactalas en lenguaje común, no código y de ser posible resúmelas
               - Qué eventos, notificaciones o broadcasts se lanzan
            6. **requestBody**:
               - Incluye el `schema` completo con `type`, `properties`, `required`, `enum`, `format`, etc.
               - Para **cada propiedad**, agrega un ejemplo dentro de `examples`.
                 - Si es un número: usar valores entre 1 y 9
                 - Si es un string: usar valores representativos (ej. `"name": "John"`, `"email": "john@example.com"`)
            7. **responses**:
               - Documenta todas las posibles (`200`, `422`, `403`, `404`, etc.)
               - Describe cada una y, si es posible, proporciona ejemplos de respuesta.
               - En caso de que el controlador retorne un Illuminate\Http\Resources\Json o, a los efectos, utice un DTO para serializar el response, trata de inferir la estructura del response a través de estos recursos

            ### Reglas:

            - Devuelve un objeto JSON Swagger **válido** (sin explicaciones adicionales).
            - Usa `"openapi": "3.0.0"` como versión raíz.
            - Si no puedes inferir algún campo, simplemente omítelo.

            Código a analizar:
            {$code}
            PROMPT;
}

    public static function forDeptracYaml(string $rulesText): string
    {
        $baseDir = app_path();
        return <<<PROMPT
        Eres un asistente experto en arquitectura de software y herramientas estáticas. Tu tarea es generar un archivo de configuración válido para Deptrac  (v2.0.5) en formato YAML, a partir de las siguientes reglas arquitectónicas expresadas en lenguaje natural.

        Asume que el proyecto sigue una estructura estándar de Laravel, con namespaces como:
        - Controllers: App\Http\Controllers
        - Services: App\Services
        - Repositories: App\Repositories
        - Models: App\Models
        - Infraestructura técnica: Illuminate\*, DB, Cache, Storage

        Además, si se mencionan módulos como App\Admin o App\User, agrégalos como capas separadas.

        Debes seguir estrictamente la estructura de configuración compatible con Deptrac v2.0.5:
            * La raíz debe ser deptrac:
            * Los patrones classNameRegex deben usar delimitadores de regex #...#
            * Usa classNameRegex en lugar de class o className
            * Cada \ debe escaparse como \\
            * No uses parameters: ni escapes innecesarios como \\\\\\\\
            * Usa comillas simples  para value si su valor corresponde a una expresion regular
            * No uses ninguna etiqueta que no esté explicícitamente definida en https://github.com/deptrac/deptrac/blob/main/deptrac.yaml

        La estructura del archivo debe incluir al menos:
            * paths: apuntando a {$baseDir}
            * exclude_files: para omitir tests
            * layers: con sus respectivos collectors (usando classNameRegex) y value ('#...#')
            * ruleset: que defina las dependencias permitidas entre capas

        Estas son las reglas que debe respetar el archivo. Exprésalas exclusivamente mediante ruleset conforme al esquema oficial de Deptrac. No uses ninguna propiedad adicional:

        {$rulesText}

        Por favor genera solo el contenido YAML sin comentarios adicionales.
        PROMPT;
    }


    public static function forCodeRefactor(string $code): string
    {
        $baseDir = app_path();
        return <<<PROMPT
        Eres un arquitecto de software y desarrollador senior especializado en Laravel (versión 9/10) y PHP (8.1+). Te entregaré un fragmento de código que necesita ser refactorizado.

        Objetivos de la refactorización:
        1. Sustituir cualquier instanciación directa con `new` por **inyección de dependencias vía constructor** o a través del contenedor de servicios de Laravel (`app()->make()` cuando corresponda).
        2. Asegurar que **servicios, repositorios y utilidades** sean completamente **mockeables** y desacoplados, cumpliendo con la inversión de dependencias (D de SOLID).
        3. Aplicar los principios de **SOLID**, **KISS** y **DRY**, asegurando legibilidad, mantenibilidad y bajo acoplamiento.
        4. Usar buenas prácticas de arquitectura en Laravel (Service Layer, Repositories, Contracts, etc.).
        5. Identificar y señalar posibles mejoras adicionales (rendimiento, seguridad, organización).
        6. Optimización estructural o de rendimiento si es aplicable.
        7. Sugiere refactorizaciones estructurales como extraer/implementar interfaces, separar responsabilidades o consolidar reglas duplicadas

        Entrega tu resultado en este formato:
        -  Código refactorizado completo
        -  Comentarios clave si es necesario (inline o al pie)
        -  Breve justificación de los cambios aplicados

        Contexto del código:
        - Laravel 10+ y PHP 8.2+
        - Arquitectura con capas de servicio, repositorio y validación separadas
        - Preferencia por inyección de dependencias y constructor en lugar de `Facades` o `new`
        - Se debe facilitar el uso de mocks en tests unitarios

        Aquí está el código a refactorizar:
        {$code}

        Formatea la respuesta en **HTML válido**, con una estructura clara y legible, como si fuera un resumen visual de un Pull Request. Quiero  un documento HTML completo, renderizable en navegador (con etiquetas <html>, <head>, <body>, estilos inline y todo lo necesario para visualizarlo directamente).No generes fragmentos ni HTML parcial.

        Para cada archivo modificado, muestra:
        - El código original (resaltado con un fondo gris claro)
        - El código refactorizado (resaltado con fondo verde claro)
        - Una observación breve explicando el motivo del cambio

        Para archivos nuevos sugeridos:
        - Muestra únicamente el código nuevo y su observación, no es necesario mostrar un "antes"

        Estructura recomendada del HTML(No ajustes los colores por legibilidad, aplica exactamente los estilos que te doy):
        - Usa etiquetas `<section>` o `<div>` para separar cada archivo o clase analizada
        - Usa `<pre><code>` para formatear los bloques de código
        - Usa clases o estilos siguientes:
          - `.original-code`:  con fondo #a7a7a7, padding de 1em,border-radius de 1px, border-style  dashed, border-color: gray
          - `.refactored-code` :  con fondo #a7a7a7, padding de 1em,border-radius de 1px, border-style  dashed, border-color: gray
          - `.comment` con fondo `#fff5b1` o similar para observaciones

        El HTML debe ser válido, semántico y directamente renderizable por un navegador.
        PROMPT;
    }


}
