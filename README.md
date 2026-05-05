# Moodle Provisioning Script

Script de provisión para instalar **Moodle** de forma rápida en Ubuntu usando un único comando.

Este instalador está orientado a instalaciones de **Moodle 5.2+** usando:

- Apache
- PHP 8.3
- PostgreSQL 16
- Moodle servido desde el directorio `/public`
- Instalación interactiva con `whiptail`

> Nota: Moodle 5.2 requiere versiones recientes de base de datos. Por compatibilidad y estabilidad, este script utiliza **PostgreSQL** como motor de base de datos.

---

## 🚀 Instalación rápida

Ejecuta el instalador directamente desde GitHub:

```bash
sudo bash -c 'bash <(curl -fsSL https://raw.githubusercontent.com/josianascanio/moodle/main/provision.sh)'
```

---

## 🧭 Flujo recomendado

1. Entra a la rama que necesitas.
2. Copia el comando de instalación.
3. Ejecútalo en tu servidor Ubuntu.
4. Completa los parámetros solicitados por el instalador.
5. Al finalizar, entra al sitio desde el navegador usando la IP o dominio configurado.

---

## ⚠️ Requisitos

- Ubuntu Server
- Acceso `sudo` o usuario `root`
- Conexión a internet
- Espacio disponible suficiente en disco
- Puerto 80 disponible si vas a usar Apache directamente

---

## 📦 Qué instala/configura

Dependiendo de las opciones seleccionadas, el script puede instalar y configurar:

- Paquetes base: `git`, `curl`, `wget`, `unzip`, `nano`, etc.
- Apache
- PHP 8.3 y extensiones requeridas por Moodle
- PostgreSQL 16
- Composer
- Graphviz
- Aspell
- Ghostscript
- Base de datos PostgreSQL para Moodle
- Configuración Apache apuntando a `moodle/public`
- Instalación CLI de Moodle
- Cron de Moodle para `www-data`

---

## 🌐 Acceso al sitio

Si durante la instalación colocaste como `WEBSITE_ADDRESS` una IP, por ejemplo:

```text
192.168.18.189
```
