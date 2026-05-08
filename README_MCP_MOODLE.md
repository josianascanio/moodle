# Moodle MCP Setup

Guia para replicar la conexion MCP con Moodle y permitir creacion de cursos, secciones y contenido basico.

Esta configuracion usa dos plugins:

- Plugin oficial MCP: `webservice_mcp`
- Plugin local de contenido: `local_mcpcontent`

## 1. Requisitos

- Moodle instalado y funcionando.
- Acceso administrador a Moodle.
- Web services habilitados.
- Plugin oficial MCP instalado.
- Plugin local `local_mcpcontent` instalado.
- Un usuario dedicado para MCP, por ejemplo `mcpuser`.

Ejemplo usado en pruebas:

```text
Moodle URL: http://192.168.2.39
MCP endpoint: http://192.168.2.39/webservice/mcp/server.php?wstoken=TOKEN
```

## Orden Recomendado

Ejecutar la configuracion en este orden:

1. Instalar Moodle y confirmar que el sitio responde.
2. Instalar el plugin oficial `webservice_mcp`.
3. Instalar el plugin local `local_mcpcontent`.
4. Habilitar Web Services en Moodle.
5. Habilitar el protocolo `Model Context Protocol (MCP)`.
6. Crear el usuario dedicado MCP.
7. Importar el rol MCP exportado o crear el rol manualmente.
8. Asignar el rol MCP al usuario.
9. Crear el servicio externo MCP.
10. Agregar las funciones webservice necesarias al servicio.
11. Autorizar el usuario MCP en el servicio.
12. Crear el token para ese usuario y servicio.
13. Configurar OpenCode con el endpoint MCP y token.
14. Probar `initialize` y `tools/list`.
15. Probar creacion de categoria, curso, contenido y secciones.

## 2. Instalar Plugin Oficial MCP

Instalar desde el directorio oficial de plugins Moodle:

```text
webservice_mcp
```

Luego ir a:

```text
Administracion del sitio -> Plugins -> Servicios web -> Gestionar protocolos
```

Activar:

```text
Model Context Protocol (MCP)
```

## 3. Instalar Plugin Local de Contenido

El ZIP instalable esta en este repositorio:

```text
local_mcpcontent.zip
```

Instalar desde Moodle:

```text
Administracion del sitio -> Plugins -> Instalar plugins
```

Seleccionar:

```text
Tipo de plugin: Local plugin
Archivo: local_mcpcontent.zip
```

El plugin agrega funciones webservice propias para crear contenido que el plugin MCP oficial no podia crear directamente.

## 4. Habilitar Web Services

Ir a:

```text
Administracion del sitio -> Caracteristicas avanzadas
```

Activar:

```text
Habilitar servicios web
```

## 5. Crear Usuario MCP

Crear un usuario dedicado, por ejemplo:

```text
Usuario: mcpuser
Correo: mcp@example.com
Autenticacion: Manual
```

Recomendaciones:

- No usar el usuario administrador principal.
- No compartir el token.
- Usar una cuenta dedicada para poder revocar acceso facilmente.

## 6. Rol MCP

Puedes usar una de estas dos opciones:

- Importar el rol exportado desde otra instancia Moodle.
- Crear el rol manualmente y configurar los permisos uno por uno.

La opcion recomendada es importar el rol exportado, porque reduce errores de permisos.

## 7. Importar Rol MCP Exportado

Si ya tienes el rol exportado desde otra instancia Moodle, normalmente el archivo es XML.

Este repositorio incluye un rol exportado de referencia:

```text
cursomcp.xml
```

Ruta:

```text
Administracion del sitio -> Usuarios -> Permisos -> Definir roles
```

Pasos:

1. Clic en `Anadir un nuevo rol`.
2. En la pantalla de creacion, buscar la opcion `Usar rol o arquetipo`.
3. Seleccionar `Subir preajuste` o `Importar rol desde archivo`, segun la traduccion de Moodle.
4. Subir el archivo XML del rol exportado.
5. Continuar.
6. Revisar el nombre corto, nombre completo y descripcion.
7. Confirmar que los contextos permitidos incluyan `Sistema`, `Categoria` o `Curso` segun el alcance deseado.
8. Guardar cambios.

Despues de importar, abrir el rol y verificar que existan al menos estos permisos:

```text
webservice/mcp:use
local/mcpcontent:createcontent
moodle/course:create
moodle/course:update
moodle/course:manageactivities
moodle/category:manage
```

Notas:

- Si Moodle no muestra una opcion clara de importar en `Anadir un nuevo rol`, revisa dentro de `Definir roles` si aparece un boton o enlace llamado `Importar rol`.
- El archivo de rol exportado no instala plugins ni funciones webservice; solo replica permisos/capabilities.
- Si el rol importado incluye una capability de un plugin que todavia no esta instalado, instala primero el plugin y luego revisa el rol.

## 8. Crear Rol MCP Manualmente

Crear un rol dedicado para el usuario MCP.

Ruta:

```text
Administracion del sitio -> Usuarios -> Permisos -> Definir roles
```

Crear un rol basado en `Profesor editor` o `Manager`, segun el alcance que quieras.

Nombre recomendado:

```text
MCP Course Manager
```

Contextos donde puede asignarse:

```text
Sistema
Categoria
Curso
```

Para pruebas se puede asignar a nivel sistema. Para produccion, preferir categoria o curso especifico.

## 9. Permisos del Rol

Estos son los permisos usados para que MCP pueda crear categorias, cursos, secciones y contenido basico.

### Permisos MCP

```text
webservice/mcp:use = Allow
local/mcpcontent:createcontent = Allow
```

### Permisos de cursos y categorias

```text
moodle/category:manage = Allow
moodle/category:viewhiddencategories = Allow
moodle/course:create = Allow
moodle/course:update = Allow
moodle/course:manageactivities = Allow
moodle/course:visibility = Allow
moodle/course:view = Allow
moodle/course:viewhiddencourses = Allow
moodle/course:changefullname = Allow
moodle/course:changeshortname = Allow
moodle/course:changeidnumber = Allow
moodle/course:changesummary = Allow
moodle/course:changecategory = Allow
moodle/course:sectionvisibility = Allow
moodle/course:activityvisibility = Allow
```

### Permisos opcionales usados por algunas funciones expuestas

```text
moodle/course:delete = Allow
moodle/course:movesections = Allow
moodle/backup:backupcourse = Allow
moodle/restore:restorecourse = Allow
moodle/backup:backuptargetimport = Allow
moodle/restore:restoretargetimport = Allow
```

Notas:

- `moodle/course:delete` solo es necesario si quieres borrar cursos desde MCP.
- Los permisos de backup/restore solo son necesarios si expones funciones de duplicar/importar curso.
- Para limitar riesgo, no agregues funciones destructivas al servicio si no las necesitas.

## 10. Asignar Rol al Usuario MCP

Asignar el rol al usuario `mcpuser`.

Para pruebas:

```text
Administracion del sitio -> Usuarios -> Permisos -> Asignar roles del sistema
```

Para produccion, mejor asignarlo en una categoria especifica:

```text
Administracion del sitio -> Cursos -> Gestionar cursos y categorias -> Categoria -> Permisos
```

## 11. Crear Servicio Externo MCP

Ir a:

```text
Administracion del sitio -> Servidor -> Servicios web -> Servicios externos
```

Crear un servicio:

```text
Nombre: MCP Service
Nombre corto: mcp_service
Habilitado: Si
Solo usuarios autorizados: Si
```

Agregar el usuario `mcpuser` como usuario autorizado del servicio.

## 12. Funciones del Servicio

Agregar estas funciones al servicio externo.

### Funciones base para cursos y categorias

```text
core_course_create_categories
core_course_create_courses
core_course_get_contents
core_course_get_courses
core_course_get_courses_by_field
core_course_update_categories
core_course_update_courses
core_course_view_course
```

### Funciones para estructura del curso

```text
core_courseformat_get_state
core_courseformat_get_section_content_items
core_courseformat_update_course
core_courseformat_file_handlers
core_courseformat_get_overview_information
```

### Funciones de lectura de recursos Moodle

```text
mod_label_get_labels_by_courses
mod_page_get_pages_by_courses
mod_page_view_page
mod_resource_get_resources_by_courses
mod_resource_view_resource
mod_url_get_urls_by_courses
mod_url_view_url
```

### Funciones del plugin local `local_mcpcontent`

Estas son las funciones clave para crear contenido real y renombrar secciones:

```text
local_mcpcontent_create_label
local_mcpcontent_create_page
local_mcpcontent_create_url
local_mcpcontent_update_sections
```

### Funciones destructivas opcionales

Agregar solo si realmente se necesitan:

```text
core_course_delete_categories
core_course_delete_courses
core_course_delete_modules
```

## 13. Crear Token

Ir a:

```text
Administracion del sitio -> Servidor -> Servicios web -> Gestionar tokens
```

Crear token con:

```text
Usuario: mcpuser
Servicio: MCP Service
```

Copiar el token generado.

## 14. Configurar OpenCode

Editar:

```text
~/.config/opencode/opencode.json
```

Agregar o actualizar el bloque MCP:

```json
"moodle": {
  "type": "remote",
  "url": "http://192.168.2.39/webservice/mcp/server.php?wstoken=TOKEN",
  "enabled": true
}
```

Reiniciar OpenCode despues de cambiar la configuracion.

## 15. Pruebas Rapidas

### Inicializar MCP

```bash
curl -sS -X POST "http://192.168.2.39/webservice/mcp/server.php?wstoken=TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"jsonrpc":"2.0","method":"initialize","params":{},"id":1}'
```

Debe devolver algo similar a:

```json
{
  "jsonrpc": "2.0",
  "result": {
    "serverInfo": {
      "name": "Moodle MCP Server"
    }
  },
  "id": 1
}
```

### Listar herramientas

```bash
curl -sS -X POST "http://192.168.2.39/webservice/mcp/server.php?wstoken=TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"jsonrpc":"2.0","method":"tools/list","params":{},"id":2}'
```

Confirmar que aparecen:

```text
local_mcpcontent_create_label
local_mcpcontent_create_page
local_mcpcontent_create_url
local_mcpcontent_update_sections
```

## 16. Ejemplos MCP

### Crear etiqueta

```json
{
  "name": "local_mcpcontent_create_label",
  "arguments": {
    "courseid": 2,
    "sectionid": 2,
    "content": "<h3>Bienvenida</h3><p>Contenido creado desde MCP.</p>",
    "name": "Bienvenida",
    "visible": true
  }
}
```

### Crear pagina

```json
{
  "name": "local_mcpcontent_create_page",
  "arguments": {
    "courseid": 2,
    "sectionid": 2,
    "name": "Introduccion",
    "intro": "<p>Resumen breve.</p>",
    "content": "<h2>Introduccion</h2><p>Pagina creada desde MCP.</p>",
    "visible": true
  }
}
```

### Crear URL

```json
{
  "name": "local_mcpcontent_create_url",
  "arguments": {
    "courseid": 2,
    "sectionid": 2,
    "name": "Documentacion Moodle",
    "externalurl": "https://docs.moodle.org/",
    "intro": "<p>Referencia externa.</p>",
    "visible": true
  }
}
```

### Renombrar secciones

```json
{
  "name": "local_mcpcontent_update_sections",
  "arguments": {
    "courseid": 2,
    "sections": [
      {
        "sectionid": 1,
        "name": "Informacion general"
      },
      {
        "sectionid": 2,
        "name": "Unidad 1: Introduccion"
      },
      {
        "sectionid": 3,
        "name": "Unidad 2: Desarrollo"
      },
      {
        "sectionid": 4,
        "name": "Unidad 3: Cierre"
      }
    ]
  }
}
```

## 17. Problemas Comunes

### `403 Forbidden`

Revisar:

- Web services habilitados.
- Protocolo MCP habilitado.
- Token correcto.
- Servicio externo habilitado.
- Usuario autorizado en el servicio.
- Capability `webservice/mcp:use`.

### Herramientas no aparecen en `tools/list`

Revisar:

- La funcion fue agregada al servicio externo.
- El plugin fue instalado y la actualizacion finalizo.
- El usuario/token pertenece al servicio correcto.
- Reiniciar el cliente MCP/OpenCode.

### No crea contenido

El plugin MCP oficial puede listar y leer modulos, pero algunos recursos (`page`, `url`, `label`, `resource`) no soportan `quick creation` con `core_courseformat_new_module`.

Para eso se usan las funciones del plugin local:

```text
local_mcpcontent_create_label
local_mcpcontent_create_page
local_mcpcontent_create_url
```

### No renombra secciones

Revisar:

- Funcion `local_mcpcontent_update_sections` agregada al servicio.
- Permiso `moodle/course:update`.
- Permiso `local/mcpcontent:createcontent`.
- Usar `sectionid`, no el numero visual de la seccion.

## 18. Seguridad

- No subir tokens a Git.
- Rotar el token si se comparte por accidente.
- Evitar funciones destructivas si no se necesitan.
- Preferir asignar el rol MCP a una categoria especifica, no a todo el sistema.
- Usar HTTPS en produccion.
