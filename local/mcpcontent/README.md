# MCP Content Tools

Local Moodle plugin that adds Web Service functions for creating basic course content through the MCP webservice plugin.

## Functions

- `local_mcpcontent_create_label`
- `local_mcpcontent_create_page`
- `local_mcpcontent_create_url`
- `local_mcpcontent_update_sections`

Each function requires:

- `local/mcpcontent:createcontent`
- `moodle/course:manageactivities`

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
   - `local_mcpcontent_update_sections`
4. Ensure the MCP user has `local/mcpcontent:createcontent` in the target course context or a parent context.
5. Keep `moodle/course:manageactivities` enabled for that same user.

Then refresh/restart the MCP client and run `tools/list`. The three `local_mcpcontent_*` tools should appear.

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
