# MCP Content Tools

Local Moodle plugin that adds Web Service functions for creating basic course content through the MCP webservice plugin.

## Functions

- `local_mcpcontent_create_label`
- `local_mcpcontent_create_page`
- `local_mcpcontent_create_url`
- `local_mcpcontent_create_quiz`
- `local_mcpcontent_update_quiz_settings`
- `local_mcpcontent_create_question_category`
- `local_mcpcontent_create_multichoice_question`
- `local_mcpcontent_create_truefalse_question`
- `local_mcpcontent_create_shortanswer_question`
- `local_mcpcontent_add_question_to_quiz`
- `local_mcpcontent_update_sections`

Each function requires:

- `local/mcpcontent:createcontent`
- `moodle/course:manageactivities`

The quiz functions also require:

- `mod/quiz:addinstance`

Question-bank functions also require:

- `moodle/question:add`
- `moodle/question:useall` or `moodle/question:usemine`
- `mod/quiz:manage` when adding questions to a quiz

## Install

Copy this folder to the Moodle codebase:

```bash
sudo cp -R local/mcpcontent /var/www/html/sites/moodle/local/mcpcontent
sudo chown -R root:root /var/www/html/sites/moodle/local/mcpcontent
sudo find /var/www/html/sites/moodle/local/mcpcontent -type d -exec chmod 755 {} \;
sudo find /var/www/html/sites/moodle/local/mcpcontent -type f -exec chmod 644 {} \;
sudo -u www-data /usr/bin/php /var/www/html/sites/moodle/admin/cli/upgrade.php --non-interactive
```

If the Moodle code is temporarily writable by `www-data` for plugin installation, adjust ownership according to your local policy.

## MCP Service Setup

After installation:

1. Go to `Site administration -> Server -> Web services -> External services`.
2. Open the MCP external service.
3. Add these functions:
   - `local_mcpcontent_create_label`
   - `local_mcpcontent_create_page`
   - `local_mcpcontent_create_url`
   - `local_mcpcontent_create_quiz`
   - `local_mcpcontent_update_quiz_settings`
   - `local_mcpcontent_create_question_category`
   - `local_mcpcontent_create_multichoice_question`
   - `local_mcpcontent_create_truefalse_question`
   - `local_mcpcontent_create_shortanswer_question`
   - `local_mcpcontent_add_question_to_quiz`
   - `local_mcpcontent_update_sections`
4. Ensure the MCP user has `local/mcpcontent:createcontent` in the target course context or a parent context.
5. Keep `moodle/course:manageactivities` enabled for that same user.
6. For quizzes, also enable `mod/quiz:addinstance`.
7. For question creation, enable `moodle/question:add` and either `moodle/question:useall` or `moodle/question:usemine`.
8. For adding questions to a quiz, enable `mod/quiz:manage`.

Then refresh/restart the MCP client and run `tools/list`. The `local_mcpcontent_*` tools should appear.

## Example Payloads

Create a page:

```json
{
  "courseid": 2,
  "sectionid": 2,
  "name": "Introduccion",
  "content": "<h2>Bienvenido</h2><p>Contenido creado por MCP.</p>",
  "intro": "<p>Resumen breve.</p>",
  "visible": true
}
```

Create a label:

```json
{
  "courseid": 2,
  "sectionid": 2,
  "content": "<h3>Semana 1</h3><p>Lee el material inicial.</p>",
  "visible": true
}
```

Create a URL:

```json
{
  "courseid": 2,
  "sectionid": 2,
  "name": "Documentacion Moodle",
  "externalurl": "https://docs.moodle.org/",
  "intro": "<p>Referencia externa.</p>",
  "visible": true
}
```

Create a quiz with a 15-minute timer, two attempts and 80% grade to pass:

```json
{
  "courseid": 2,
  "sectionid": 2,
  "name": "Cuestionario - Arquitectura del entorno",
  "intro": "<p>Responde las preguntas de comprensión de la unidad.</p>",
  "timelimit": 900,
  "attempts": 2,
  "grade": 100,
  "gradepass": 80,
  "questionsperpage": 1,
  "shuffleanswers": true,
  "visible": true
}
```

Update quiz settings by course module ID:

```json
{
  "cmid": 25,
  "intro": "<p>Cuestionario actualizado desde MCP.</p>",
  "timelimit": 900,
  "attempts": 2,
  "gradepass": 80,
  "visible": true
}
```

Create a question category:

```json
{
  "courseid": 2,
  "name": "Unidad 1 - Arquitectura",
  "info": "<p>Preguntas de comprensión de arquitectura.</p>"
}
```

Create a multiple-choice question and add it to a quiz:

```json
{
  "courseid": 2,
  "categoryid": 10,
  "name": "Función de Docker",
  "questiontext": "<p>¿Qué función cumple Docker en este entorno?</p>",
  "answers": [
    {"text": "Ejecutar servicios en contenedores", "fraction": 1, "feedback": "Correcto."},
    {"text": "Editar archivos de texto", "fraction": 0, "feedback": "Incorrecto."},
    {"text": "Administrar certificados", "fraction": 0, "feedback": "Incorrecto."}
  ],
  "addtoquizcmid": 25,
  "maxmark": 1
}
```

Create a true/false question and add it to a quiz:

```json
{
  "courseid": 2,
  "categoryid": 10,
  "name": "Producción o prueba",
  "questiontext": "<p>El curso deja una instalación lista para producción.</p>",
  "correctanswer": false,
  "feedbackfalse": "Correcto. Es una base de prueba.",
  "addtoquizcmid": 25
}
```

Create a short-answer question and add it to a quiz:

```json
{
  "courseid": 2,
  "categoryid": 10,
  "name": "Comando contenedores activos",
  "questiontext": "<p>¿Qué comando muestra los contenedores activos?</p>",
  "answers": [
    {"text": "docker ps", "fraction": 1, "feedback": "Correcto."}
  ],
  "addtoquizcmid": 25
}
```

Update section names:

```json
{
  "courseid": 2,
  "sections": [
    {
      "sectionid": 1,
      "name": "Informacion general"
    },
    {
      "sectionid": 2,
      "name": "Unidad 1"
    }
  ]
}
```
