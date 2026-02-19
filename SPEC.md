# PAS Robotics Manage — Especificacion Completa del Sistema

## 1. Vision General

Construye una aplicacion web de gestion de mantenimiento preventivo y correctivo para robots industriales. El nucleo del sistema es un **editor visual de bloques WYSIWYG** donde el usuario disena plantillas de informes de mantenimiento y ve en tiempo real como se imprimiran como PDF. Cuando un tecnico va a una intervencion, el sistema genera informes a partir de esas plantillas, el tecnico rellena los datos en campo, y el sistema produce un PDF profesional listo para entregar al cliente.

### Stack tecnologico
- **Backend**: Laravel 12 (PHP 8.2+)
- **Panel de administracion**: Filament v3.3 (basado en Livewire 3 + Alpine.js)
- **CSS**: Tailwind CSS v4
- **Build**: Vite 7 con `laravel-vite-plugin`
- **Base de datos**: SQLite para desarrollo, MySQL/PostgreSQL para produccion
- **PDF**: `barryvdh/laravel-dompdf` v3+ con fuente DejaVu Sans
- **Idioma de la interfaz**: Espanol

---

## 2. Modelo de Dominio

### Entidades de negocio

```
Cliente
  +-- Plantas (ubicaciones fisicas del cliente)
  |     +-- Maquinas (lineas de produccion)
  |           +-- Sistemas roboticos
  +-- Intervenciones (visitas de mantenimiento)
        +-- Informes (un informe por sistema intervenido)
              +-- Componentes del informe (datos rellenados)
```

**Cliente** (`clients`): empresa a la que se presta servicio. Almacena nombre, sede, y parametros de facturacion (tarifa hora trabajo, tarifa hora viaje, dietas, peajes, km).

**Planta** (`plants`): ubicacion fisica del cliente. Tiene direccion y pertenece a un cliente. Unique por `(client_id, name)`.

**Maquina** (`machines`): linea de produccion o celula dentro de una planta. Pertenece a cliente+planta.

**Fabricante** (`manufacturers`): catalogo de fabricantes de robots (ABB, KUKA, FANUC...). Lookup table con nombre, activo/inactivo, orden.

**Modelo de componente** (`component_models`): catalogo de modelos de controladora, unidad mecanica o drive unit. Pertenece a un fabricante. Tiene `type` (controller | mechanical_unit | drive_unit), `name`, `notes`, y `axis_oils_config` (JSON). Unique por `(manufacturer_id, type, name)`.

**Version de template** (`component_model_template_versions`): cada modelo de componente tiene multiples versiones de plantilla. Cada version almacena un `schema` JSON con la definicion de bloques, un `version` numerico auto-incremental, un `status` (draft | active | deprecated), y notas. Unique por `(component_model_id, version)`. **Regla de negocio**: solo puede haber UNA version `active` por `component_model_id` a la vez.

**Sistema** (`systems`): un sistema robotico instalado en un cliente. Pertenece a cliente + planta + maquina + fabricante. Unique por `(client_id, manufacturer_id, name)`.

**Componente del sistema** (`system_components`): instancia fisica de un componente dentro de un sistema. Tiene tipo (controller | mechanical_unit | drive_unit), referencia al modelo (`component_model_id`), label, serial_number, axes_count, y un JSON `meta`. Un sistema tiene exactamente 1 controladora, N unidades mecanicas, N drive units.

**Intervencion** (`interventions`): visita de mantenimiento. Pertenece a un cliente. Tiene tipo (preventive | corrective), status (draft | in_progress | completed | invoiced), referencia, titulo, fechas inicio/fin, notas. Many-to-many con sistemas via `intervention_systems`.

**Informe** (`reports`): un informe por cada sistema intervenido. Unique por `(intervention_id, system_id)`. Tiene status (draft | finalized | delivered), fechas de realizacion, notas, y `created_by_user_id` para auditoria.

**Componente del informe** (`report_components`): datos rellenados por el tecnico para un componente especifico. Tiene `system_component_id` (FK unica al componente fisico), `component_type`, `label`, `sort_order`, `template_version_id`, `schema_json` (snapshot congelado), `data_json` (datos rellenados).

**Aceites** (`oils`) y **Consumibles** (`consumables`): catalogos con nombre, fabricante, costes y precios.

---

## 3. El Editor de Bloques WYSIWYG

### Arquitectura

Componente **Livewire** embebido en una pagina Filament full-screen. Usa **Alpine.js** para interacciones rapidas (drag and drop, seleccion) y **Livewire** para gestion de estado y persistencia.

### Layout: 3 paneles

```
+----------------+-----------------------------------------+------------------+
|                |                                         |                  |
|   PALETA       |           CANVAS A4                     |  CONFIGURACION   |
|   DE BLOQUES   |        (794 x 1123 px)                  |  DEL BLOQUE      |
|                |                                         |  SELECCIONADO    |
|  Categorias:   |  +-----------------------------------+  |                  |
|  - Estructura  |  | [Header]                          |  |  Titulo: ____    |
|  - Campos      |  | [SectionTitle]                    |  |  Color: #___     |
|  - Inspeccion  |  | [Tristate] <-- seleccionado       |  |  Nivel: ___      |
|  - Media       |  | [Tristate]                        |  |  Required: [ ]   |
|                |  | [Table]                           |  |                  |
|                |  | [Signature]                       |  |  [Duplicar]      |
|                |  +-----------------------------------+  |  [Eliminar]      |
|                |                                         |                  |
|                |  Indicador A4: 210x297mm                |                  |
+----------------+-----------------------------------------+------------------+
```

**Panel izquierdo (Paleta)**: bloques agrupados por categoria (Estructura, Campos de datos, Inspeccion, Media y firma). Clic para anadir al final, o drag para insertar en posicion.

**Panel central (Canvas A4)**: contenedor 794x1123px (proporcional A4 a 96 DPI) con margenes configurables. Cada bloque se renderiza con los **mismos estilos que el PDF**. Clic selecciona, hover revela controles (mover, duplicar, eliminar). Boton "+" entre bloques para insertar. Indicador de salto de pagina (linea punteada cada 1123px). Drag and drop para reordenar (SortableJS o HTML5 Drag API).

**Panel derecho (Configuracion)**: aparece al seleccionar un bloque. Campos especificos del tipo de bloque. Tipos de campo de config soportados: text, number, toggle, select, color, key_value_list (con botones anadir/eliminar), table_columns (editor completo), table_rows (editor de filas fijas). ESC cierra el panel.

**Toolbar superior**: toggle paleta, nombre del template + badge "Sin guardar", selector de estado (Borrador/Activo/Obsoleto), boton guardar, boton "Configuracion de pagina" (modal con margenes, orientacion, font size), boton "Vista previa PDF" (iframe o nueva pestana).

### Estado del componente Livewire

```php
public ?int $templateVersionId;
public string $templateName;
public string $templateStatus;       // draft | active | deprecated
public array $blocks = [];           // [{id, type, config}, ...]
public ?string $selectedBlockId;     // UUID del bloque seleccionado
public array $pageConfig = [
    'orientation' => 'portrait',
    'margins' => ['top' => 20, 'right' => 15, 'bottom' => 20, 'left' => 15],
    'fontSize' => 10,
];
public bool $unsavedChanges = false;
```

### Acciones

- `addBlock(type, ?afterIndex)` -- crea bloque con UUID, config por defecto, clave auto-generada
- `removeBlock(id)` -- elimina bloque
- `duplicateBlock(id)` -- copia con nuevo UUID y sufijo `_copy_XXXX` en la clave
- `moveBlockUp(id)` / `moveBlockDown(id)` -- reordena
- `reorderBlocks(orderedIds)` -- reordena por drag and drop
- `selectBlock(?id)` -- selecciona/deselecciona
- `updateBlockConfig(id, key, value)` -- actualiza campo de config
- `updateNestedConfig(id, dotPath, value)` -- actualiza campo anidado (ej: `columns.0.label`)
- `save()` -- persiste schema. **Valida claves unicas antes de guardar.**

### Migracion automatica de schemas legacy

Al abrir un template con schema antiguo (secciones con campos), el editor lo convierte automaticamente al formato de bloques: `text->text_field`, `number->number_field`, `tristate->tristate`, etc. Cada seccion se convierte en `section_title` seguido de sus campos.

---

## 4. Catalogo de Tipos de Bloque

Cada bloque es una clase PHP que implementa `BlockInterface`. El contrato exige que cada bloque sepa:

1. **Identificarse**: `type()`, `label()`, `icon()`, `category()`
2. **Configurarse**: `defaultConfig()`, `configSchema()`
3. **Renderizarse en 3 contextos**:
   - `renderPreview(config)` -- HTML para el canvas del editor
   - `renderPdf(config, data)` -- HTML para DomPDF
   - `renderFilamentFields(config, prefix)` -- array de componentes Filament para el formulario de llenado
4. **Inicializar datos**: `initializeData(config)` -- estructura de datos vacia
5. **Validar datos**: `validateData(config, data)` -- array de errores

**CRITICO**: el metodo `renderFilamentFields()` elimina la necesidad de mantener un match statement separado en FillReport. Cada bloque sabe generar sus propios campos Filament. Anadir un nuevo bloque = crear una sola clase PHP y registrarla.

### 4.1 Estructura

**`header`** -- Cabecera del informe
- Config: `title`, `subtitle`, `showLogo` (bool), `showDate` (bool), `showReference` (bool), `logoPosition` (left|right|center), `logoUrl` (string)
- Preview: titulo, subtitle, placeholder de logo, fecha y referencia
- PDF: tabla con logo real (si hay URL), titulo, fecha del informe y referencia. Datos contextuales (fecha, referencia, cliente, sistema) se inyectan desde Report/Intervention, no desde data_json.
- Data: no produce datos (bloque de presentacion)

**`section_title`** -- Titulo de seccion
- Config: `title`, `description`, `level` (1|2|3), `color` (hex)
- Preview/PDF: barra lateral de color, titulo con tamano segun nivel, descripcion
- Data: no produce datos

**`divider`** -- Separador
- Config: `style` (solid|dashed|dotted|space), `spacing` (small|medium|large), `color` (hex)
- Data: no produce datos

### 4.2 Campos de datos

Todos tienen: `key` (snake_case, unico, obligatorio), `label`, `required` (bool), `width` (full|half|third|two_thirds), `help`.

**`text_field`** -- Campo de texto corto
- Config: + `placeholder`
- Filament: `TextInput`
- Data: `null` -> `string`

**`number_field`** -- Campo numerico
- Config: + `unit` (str), `min`, `max`
- Filament: `TextInput::numeric()->suffix(unit)`
- Data: `null` -> `number`

**`date_field`** -- Campo de fecha
- Filament: `DatePicker`
- Data: `null` -> `string (Y-m-d)`

**`text_area`** -- Texto largo
- Config: + `rows` (int, defecto 3), `placeholder`
- Filament: `Textarea`
- Data: `''` -> `string`

**`select_field`** -- Lista desplegable
- Config: + `options` (array de `{value, label}`)
- Filament: `Select`
- Data: `null` -> `string`
- UI config: editor de opciones con botones anadir/eliminar

### 4.3 Inspeccion

**`tristate`** -- Punto de inspeccion OK/NOK/N/A
- Config: `key`, `label`, `withObservation` (bool), `required` (bool), `maintenanceLevel` (general|level1|level2|level3)
- Preview: indicador de nivel con color (verde=L1, amber=L2, rojo=L3), etiqueta, 3 botones OK/NOK/NA, campo de observaciones
- PDF: tabla compacta con etiqueta izda, badges OK/NOK/NA dcha con el seleccionado resaltado, observacion en cursiva. El indicador de maintenanceLevel TAMBIEN aparece en el PDF como badge de color.
- Filament: `Fieldset` con `Radio (ok|nok|na)` inline + `Textarea` para observacion
- Data: `{value: null, observation: ''}` -> `{value: 'ok'|'nok'|'na', observation: string}`

**`checklist`** -- Lista de verificacion
- Config: `key`, `label`, `items` (array de `{key, label}`)
- Filament: `CheckboxList`
- Data: `[]` -> `array de keys marcados`

### 4.4 Tabla

**`table`** -- Tabla de datos
- Config: `key`, `label`, `columns` (array de `{key, label, type, width}`), `fixedRows` (array), `allowAddRows` (bool), `minRows`, `maxRows`
- Tipos de columna: text, number, date, tristate, select
- Preview: tabla con cabeceras oscuras, filas predefinidas o placeholder. "y N mas" si >3 filas fijas.
- PDF: tabla compacta con fondos alternados, tristate como badges coloreados
- Filament: `Repeater` con campos segun tipo de columna, respetando minRows/maxRows
- UI config: editor completo de columnas (anadir, eliminar, reordenar, editar nombre/tipo/ancho) + editor de filas fijas

### 4.5 Media y firma

**`image`** -- Imagen
- Config: `key`, `label`, `multiple` (bool), `maxFiles`, `maxSizeMb`, `width`
- Filament: `FileUpload::image()`
- PDF: `<img>` embebido via path absoluto
- Data: `null` -> `string (path)` o `[]` -> `array de paths`

**`signature`** -- Firma digital
- Config: `key`, `label`, `role` (str), `required` (bool), `width`
- Preview: rectangulo con icono de firma y texto del rol
- PDF: imagen base64 de la firma, o linea de firma con espacio si no firmado
- Filament: **widget real de captura de firma** (canvas JS con toDataURL), NO un Textarea
- Data: `null` -> `string (data:image/png;base64,...)`

---

## 5. Formato del Schema JSON

```json
{
  "blocks": [
    {
      "id": "uuid-v4",
      "type": "header",
      "config": {
        "title": "Informe de Mantenimiento Preventivo",
        "subtitle": "Controladora IRC5",
        "showLogo": true,
        "showDate": true,
        "showReference": true,
        "logoPosition": "left",
        "logoUrl": ""
      }
    },
    {
      "id": "uuid-v4",
      "type": "tristate",
      "config": {
        "key": "estado_general",
        "label": "Estado general del equipo",
        "withObservation": true,
        "required": true,
        "maintenanceLevel": "level1"
      }
    }
  ],
  "pageConfig": {
    "orientation": "portrait",
    "margins": { "top": 20, "right": 15, "bottom": 20, "left": 15 },
    "fontSize": 10
  }
}
```

### Reglas de integridad
- Cada bloque tiene `id` UUID v4 unico dentro del schema
- Bloques de datos tienen `key` obligatorio, unico, snake_case, max 64 chars
- `pageConfig.orientation`: portrait | landscape
- `pageConfig.margins`: numeros en mm (min 5, max 50)
- `pageConfig.fontSize`: entre 8 y 14

---

## 6. Generacion de PDF

### PdfGenerator Service

**`generateReportPdf(Report)`**: genera el PDF completo.
1. Carga relaciones eager
2. Extrae pageConfig del schema
3. Aplica a DomPDF: `setPaper('a4', $orientation)`
4. Para cada ReportComponent, itera bloques y llama `Block::renderPdf(config, data)`
5. Para HeaderBlock, inyecta datos contextuales (fecha, referencia, cliente, sistema) desde Report/Intervention
6. Envuelve en vista `pdf.report` con metadatos, separadores, footer con paginacion

**`renderTemplatePreviewPdf(array $schema)`**: PDF de vista previa del template sin datos reales.

**CRITICO**: `pageConfig` (orientacion, margenes, fontSize) DEBE aplicarse en PdfGenerator y en las vistas PDF. No puede ser ignorado.

### Vista PDF
- `@page { margin: 0 }` -- DomPDF sin margenes propios
- `.page { padding: {margins}mm }` -- margenes desde pageConfig
- Footer fijo: `position: fixed; bottom: 10mm` con empresa, fecha, numero pagina
- Logo de empresa configurable en el header
- Fuente: DejaVu Sans

### Rutas PDF (autenticadas)
- `GET /template/{id}/preview-pdf` -- stream vista previa template
- `GET /report/{id}/pdf` -- stream informe
- `GET /report/{id}/download` -- descarga informe

---

## 7. Flujo de Datos Completo

### 1. Diseno del template
Admin abre modelo de componente -> RelationManager muestra versiones -> "Abrir editor" -> pagina TemplateEditor carga Livewire BlockEditor -> admin arrastra bloques, configura, ve preview A4, guarda.

### 2. Creacion del informe (ReportComposer)
Al crear un Report para intervencion+sistema:
1. **Dentro de DB::transaction()**
2. Para cada SystemComponent del sistema:
   - Resuelve version de template activa (fallback a mas reciente con Log::warning)
   - Crea ReportComponent con `schema_json` = snapshot congelado, `data_json` = datos inicializados via `initializeData()`
3. Cambios futuros al template NO afectan informes existentes

### 3. Llenado del informe (FillReport)
Tecnico abre informe -> FillReport lee schema_json de cada ReportComponent -> para cada bloque llama `Block::renderFilamentFields(config, prefix)` -> genera campos Filament dinamicamente -> tecnico rellena -> data_json se actualiza.

### 4. Generacion del PDF
Clic "Descargar PDF" -> PdfGenerator itera componentes -> cada bloque renderiza con renderPdf(config, data) -> HTML -> DomPDF -> PDF.

---

## 8. Arquitectura de Bloques (Detalle Tecnico)

### BlockInterface

```php
interface BlockInterface
{
    // Identidad
    public static function type(): string;
    public static function label(): string;
    public static function icon(): string;
    public static function category(): string;

    // Configuracion
    public static function defaultConfig(): array;
    public static function configSchema(): array;

    // Renderizado (3 contextos)
    public static function renderPreview(array $config): string;
    public static function renderPdf(array $config, array $data = []): string;
    public static function renderFilamentFields(array $config, string $prefix): array;

    // Datos
    public static function initializeData(array $config): mixed;
    public static function validateData(array $config, array $data): array;
}
```

### BaseBlock (clase abstracta)
Proporciona defaults: `category()->'general'`, `initializeData()->null`, `validateData()->[]`, `renderFilamentFields()->[]`. Helpers compartidos: `e()` (escape HTML), `configField()` (DSL para config), `widthStyle()` (CSS inline), `labelHtml()` (etiqueta con asterisco).

### BlockRegistry (registro estatico)
Metodos: `all()`, `grouped()`, `resolve(type)`, `renderPreview()`, `renderPdf()`, `renderFilamentFields()`, `defaultConfig()`, `initializeData()`, `categoryLabels()`.

**Principio clave**: anadir un nuevo bloque = 1 clase PHP nueva + 1 linea en BlockRegistry. Nada mas.

---

## 9. Integracion Filament

**TemplateEditor** (pagina custom): ruta `/admin/template-editor/{id}`. No en navegacion. Embebe Livewire BlockEditor full-height.

**TemplateVersionsRelationManager** (compartido por los 3 resources de modelo): tabla con versiones, acciones: abrir editor, vista previa PDF, duplicar version, editar meta, eliminar. Al crear nueva version pre-popula con schema starter (header + section_title + tristate).

**FillReport**: tabs por componente. Cada tab llama `BlockRegistry::renderFilamentFields(type, config, prefix)` para generar campos dinamicamente. Acciones: Descargar PDF, Ver PDF.

**Reglas de negocio en UI**:
- Al activar una version, las demas activas del mismo component_model se marcan deprecated automaticamente
- No se puede eliminar la unica version activa sin confirmacion extra
- Guardar valida: claves unicas, no vacias, snake_case

---

## 10. Validaciones y Seguridad

- Claves de bloque unicas dentro del schema, snake_case, no vacias, max 64 chars
- Opciones de select/checklist con al menos 1 item
- Columnas de table con al menos 1 columna
- Rutas PDF requieren autenticacion
- Escape HTML consistente via `static::e()`
- ReportComposer envuelto en DB::transaction()
- Si usa version no-active como fallback, emite Log::warning()

---

## 11. Seeds de Ejemplo

### Template Controladora (IRC5)
- Header "Control de Controladora"
- Seccion "Inspeccion General": 5 tristates (cables, ventilacion, leds, pantalla, conexiones)
- Seccion "Parametros": campos numericos (temperatura, voltaje)
- Seccion "Software": campos de texto (version firmware, version software)
- Seccion "Observaciones": textarea

### Template Unidad Mecanica (IRB 6700)
- Header
- Seccion "Inspeccion Visual": 8 tristates (carroceria, cables, mangueras, conectores, topes, engrase, ruidos, juego mecanico)
- Seccion "Mediciones por Eje": tabla [Eje, Holgura(mm), Corriente(A), Estado(tristate)]
- Seccion "Aceites": tabla [Eje, Aceite actual, Volumen(ml), Estado(tristate)]
- Seccion "Firma": firma del tecnico + firma del cliente

### Template Drive Unit
- Header
- Seccion "Inspeccion": 4 tristates
- Seccion "Parametros electricos": tabla [Parametro, Valor, Unidad, Estado]
- Seccion "Observaciones": textarea + firma

---

## 12. Notas Clave para la Implementacion

1. **Cada bloque es autocontenido**: anadir un nuevo tipo = 1 clase PHP + 1 linea en Registry. No tocar FillReport, ni el Blade del editor, ni ningun otro archivo.

2. **Estilos preview = estilos PDF**: usar inline styles exclusivamente (DomPDF no soporta Tailwind). El canvas del editor simula un A4 real.

3. **pageConfig debe llegar hasta DomPDF**: orientacion, margenes y fontSize configurados en el editor se aplican en PdfGenerator y en las vistas PDF.

4. **Transacciones en ReportComposer**: siempre envolver en DB::transaction().

5. **Logo de empresa**: setting global de la aplicacion (subida de imagen) que se inyecta en los HeaderBlock con showLogo:true.

6. **Snapshot pattern**: schema_json en ReportComponent es una copia congelada. Cambios al template no afectan informes existentes.

7. **Firma digital real**: el bloque signature necesita un widget JavaScript de captura de trazos (no un textarea), que produce base64 PNG.
