#!/usr/bin/env bash
set -euo pipefail

# ============================================================
# Moodle Provision Script - PostgreSQL only
# hecho por Josian
# ============================================================

# -----------------------------
# Root check
# -----------------------------
if [[ "${EUID}" -ne 0 ]]; then
  echo "Este script debe ejecutarse como root. Usa: sudo bash $0"
  exit 1
fi

# -----------------------------
# Ensure whiptail exists
# -----------------------------
if ! command -v whiptail >/dev/null 2>&1; then
  echo "Instalando dependencias mínimas..."
  apt update -qq
  apt install -y whiptail
fi

# -----------------------------
# Whiptail helpers
# -----------------------------
abort_if_cancel() {
  local code="$1"
  if [[ "$code" -ne 0 ]]; then
    echo "Cancelado por el usuario."
    exit 0
  fi
}

w_input() {
  local title="$1"
  local prompt="$2"
  local default="$3"
  local height="${4:-10}"
  local width="${5:-75}"

  local result
  result=$(whiptail --title "$title" --inputbox "$prompt" "$height" "$width" "$default" 3>&1 1>&2 2>&3)
  abort_if_cancel $?
  echo "$result"
}

w_password() {
  local title="$1"
  local prompt="$2"
  local default="$3"
  local height="${4:-10}"
  local width="${5:-75}"

  local result
  result=$(whiptail --title "$title" --passwordbox "$prompt" "$height" "$width" "$default" 3>&1 1>&2 2>&3)
  abort_if_cancel $?
  echo "$result"
}

w_msg() {
  local title="$1"
  local msg="$2"

  whiptail --title "$title" --msgbox "$msg" 22 90
  abort_if_cancel $?
}

w_menu() {
  local title="$1"
  local text="$2"
  shift 2

  local result
  result=$(whiptail --title "$title" --menu "$text" 16 75 6 "$@" 3>&1 1>&2 2>&3)
  abort_if_cancel $?
  echo "$result"
}

w_checklist() {
  local title="$1"
  local text="$2"
  shift 2

  local result
  result=$(whiptail --title "$title" --checklist "$text" 24 100 15 "$@" 3>&1 1>&2 2>&3)
  abort_if_cancel $?
  echo "$result"
}

step() {
  echo -e "\n===== $* =====\n"
}

option_enabled() {
  local options="$1"
  local option="$2"
  [[ "$options" == *"\"$option\""* ]]
}

check_disk_space() {
  local path="$1"
  local required_mb="$2"

  mkdir -p "$path"

  local available_mb
  available_mb=$(df -Pm "$path" | awk 'NR==2 {print $4}')

  if [[ "$available_mb" -lt "$required_mb" ]]; then
    echo "ERROR: No hay suficiente espacio libre en $path."
    echo "Espacio disponible: ${available_mb} MB"
    echo "Espacio recomendado mínimo: ${required_mb} MB"
    echo ""
    echo "Revisa con:"
    echo "  df -h"
    echo "  sudo du -h --max-depth=1 /var/www/html 2>/dev/null | sort -h"
    exit 1
  fi
}

validate_moodle_admin_password() {
  local pass="$1"

  if [[ ${#pass} -lt 8 ]]; then
    return 1
  fi

  if ! [[ "$pass" =~ [A-Z] ]]; then
    return 1
  fi

  if ! [[ "$pass" =~ [a-z] ]]; then
    return 1
  fi

  if ! [[ "$pass" =~ [0-9] ]]; then
    return 1
  fi

  if ! [[ "$pass" =~ [^a-zA-Z0-9] ]]; then
    return 1
  fi

  return 0
}

ensure_postgresql_16_repo_if_needed() {
  local version_found="0"

  if command -v psql >/dev/null 2>&1; then
    version_found="$(psql --version | awk '{print $3}' | cut -d. -f1 || echo 0)"
  fi

  if [[ "$version_found" =~ ^[0-9]+$ ]] && [[ "$version_found" -ge 16 ]]; then
    echo "PostgreSQL $version_found detectado. No se requiere repo adicional."
    return 0
  fi

  . /etc/os-release

  if [[ "${ID:-}" != "ubuntu" ]]; then
    echo "No se detectó Ubuntu. Se intentará instalar PostgreSQL desde los repositorios actuales."
    return 0
  fi

  step "Agregando repositorio oficial PostgreSQL"

  apt install -y curl ca-certificates gnupg lsb-release

  install -d /usr/share/postgresql-common/pgdg

  curl -fsSL https://www.postgresql.org/media/keys/ACCC4CF8.asc \
    | gpg --dearmor \
    > /usr/share/postgresql-common/pgdg/apt.postgresql.org.gpg

  echo "deb [signed-by=/usr/share/postgresql-common/pgdg/apt.postgresql.org.gpg] https://apt.postgresql.org/pub/repos/apt ${VERSION_CODENAME}-pgdg main" \
    > /etc/apt/sources.list.d/pgdg.list

  apt update -y
}

# ============================================================
# 1) Parámetros + resumen + editar
# ============================================================
SITE_NAME_DEFAULT="Moodle"
WEBSITE_ADDRESS_DEFAULT="localhost"
PROTOCOL_DEFAULT="http"
MOODLE_VERSION_DEFAULT="MOODLE_502_STABLE"

MOODLE_BASE_PATH_DEFAULT="/var/www/html/sites"
MOODLE_CODE_FOLDER_DEFAULT="/var/www/html/sites/moodle"
MOODLE_DATA_FOLDER_DEFAULT="/var/www/data/moodledata"

DB_TYPE="postgresql"
DB_NAME_DEFAULT="moodle"
DB_USER_DEFAULT="moodleuser"
DB_PASS_DEFAULT=""
DB_HOST_DEFAULT="localhost"
DB_PORT_DEFAULT="5432"

ADMIN_USER_DEFAULT="admin"
ADMIN_PASS_DEFAULT=""
ADMIN_EMAIL_DEFAULT="admin@example.com"

while true; do
  SITE_NAME="$(w_input "Parámetros Moodle" "Nombre del sitio Moodle:" "$SITE_NAME_DEFAULT")"

  PROTOCOL="$(w_menu "Protocolo" "Selecciona el protocolo inicial del sitio:" \
    "http" "HTTP" \
    "https" "HTTPS - si ya tienes certificado configurado"
  )"

  WEBSITE_ADDRESS="$(w_input "Parámetros Moodle" "WEBSITE_ADDRESS sin http:// ni https://. Ej: moodle.midominio.com, localhost o IP:" "$WEBSITE_ADDRESS_DEFAULT")"

  MOODLE_VERSION="$(w_input "Versión Moodle" "Rama o tag de Moodle a clonar:" "$MOODLE_VERSION_DEFAULT")"

  MOODLE_BASE_PATH="$(w_input "Rutas Moodle" "Carpeta base del código Moodle:" "$MOODLE_BASE_PATH_DEFAULT")"

  MOODLE_CODE_FOLDER="$(w_input "Rutas Moodle" "Carpeta donde se instalará Moodle:" "$MOODLE_CODE_FOLDER_DEFAULT")"

  MOODLE_DATA_FOLDER="$(w_input "Rutas Moodle" "Carpeta moodledata fuera del DocumentRoot:" "$MOODLE_DATA_FOLDER_DEFAULT")"

  DB_HOST="$(w_input "Base de datos PostgreSQL" "DB_HOST:" "$DB_HOST_DEFAULT")"
  DB_PORT="$(w_input "Base de datos PostgreSQL" "DB_PORT:" "$DB_PORT_DEFAULT")"
  DB_NAME="$(w_input "Base de datos PostgreSQL" "DB_NAME:" "$DB_NAME_DEFAULT")"
  DB_USER="$(w_input "Base de datos PostgreSQL" "DB_USER:" "$DB_USER_DEFAULT")"

  while true; do
    DB_PASS="$(w_password "Base de datos PostgreSQL" "DB_PASS:" "$DB_PASS_DEFAULT")"

    if [[ -z "$DB_PASS" ]]; then
      w_msg "Error" "La clave de la base de datos no puede estar vacía."
      continue
    fi

    break
  done

  ADMIN_USER="$(w_input "Administrador Moodle" "Usuario administrador Moodle:" "$ADMIN_USER_DEFAULT")"

  while true; do
    ADMIN_PASS="$(w_password "Administrador Moodle" "Clave administrador Moodle.

Debe tener:
- Mínimo 8 caracteres
- Al menos una mayúscula
- Al menos una minúscula
- Al menos un número
- Al menos un símbolo

Ejemplo:
Admin1234*" "$ADMIN_PASS_DEFAULT")"

    if [[ -z "$ADMIN_PASS" ]]; then
      w_msg "Error" "La clave del administrador de Moodle no puede estar vacía."
      continue
    fi

    if ! validate_moodle_admin_password "$ADMIN_PASS"; then
      w_msg "Error" "La clave del administrador de Moodle no cumple los requisitos mínimos.

Debe tener:
- Mínimo 8 caracteres
- Al menos una mayúscula
- Al menos una minúscula
- Al menos un número
- Al menos un símbolo

Ejemplo:
Admin1234*"
      continue
    fi

    break
  done

  ADMIN_EMAIL="$(w_input "Administrador Moodle" "Correo administrador Moodle:" "$ADMIN_EMAIL_DEFAULT")"

  SITE_NAME="${SITE_NAME:-$SITE_NAME_DEFAULT}"
  PROTOCOL="${PROTOCOL:-http}"
  WEBSITE_ADDRESS="${WEBSITE_ADDRESS:-$WEBSITE_ADDRESS_DEFAULT}"
  MOODLE_VERSION="${MOODLE_VERSION:-$MOODLE_VERSION_DEFAULT}"
  MOODLE_BASE_PATH="${MOODLE_BASE_PATH:-$MOODLE_BASE_PATH_DEFAULT}"
  MOODLE_CODE_FOLDER="${MOODLE_CODE_FOLDER:-$MOODLE_CODE_FOLDER_DEFAULT}"
  MOODLE_DATA_FOLDER="${MOODLE_DATA_FOLDER:-$MOODLE_DATA_FOLDER_DEFAULT}"
  MOODLE_PUBLIC_FOLDER="${MOODLE_CODE_FOLDER}/public"
  DB_HOST="${DB_HOST:-$DB_HOST_DEFAULT}"
  DB_PORT="${DB_PORT:-$DB_PORT_DEFAULT}"
  DB_NAME="${DB_NAME:-$DB_NAME_DEFAULT}"
  DB_USER="${DB_USER:-$DB_USER_DEFAULT}"
  ADMIN_USER="${ADMIN_USER:-$ADMIN_USER_DEFAULT}"
  ADMIN_EMAIL="${ADMIN_EMAIL:-$ADMIN_EMAIL_DEFAULT}"

  if ! [[ "$DB_PORT" =~ ^[0-9]+$ ]]; then
    w_msg "Error" "DB_PORT debe ser numérico."
    continue
  fi

  if [[ "$MOODLE_CODE_FOLDER" == "$MOODLE_DATA_FOLDER"* || "$MOODLE_DATA_FOLDER" == "$MOODLE_CODE_FOLDER"* ]]; then
    w_msg "Error" "MOODLE_CODE_FOLDER y MOODLE_DATA_FOLDER deben ser carpetas diferentes."
    continue
  fi

  SUMMARY="Se usarán estos valores:

Sitio:
  SITE_NAME:             $SITE_NAME
  PROTOCOL:              $PROTOCOL
  WEBSITE_ADDRESS:       $WEBSITE_ADDRESS
  WWWROOT:               ${PROTOCOL}://${WEBSITE_ADDRESS}

Código:
  MOODLE_VERSION:        $MOODLE_VERSION
  MOODLE_CODE_FOLDER:    $MOODLE_CODE_FOLDER
  MOODLE_PUBLIC_FOLDER:  $MOODLE_PUBLIC_FOLDER
  MOODLE_DATA_FOLDER:    $MOODLE_DATA_FOLDER

Base de datos:
  DB_TYPE:               PostgreSQL
  DB_HOST:               $DB_HOST
  DB_PORT:               $DB_PORT
  DB_NAME:               $DB_NAME
  DB_USER:               $DB_USER
  DB_PASS:               ********

Administrador:
  ADMIN_USER:            $ADMIN_USER
  ADMIN_EMAIL:           $ADMIN_EMAIL
"

  w_msg "Resumen" "$SUMMARY"

  ACTION=$(whiptail --title "Acción" --menu "Selecciona una opción:" 14 60 3 \
    "1" "Continuar" \
    "2" "Editar parámetros" \
    "3" "Cancelar" \
    3>&1 1>&2 2>&3)
  abort_if_cancel $?

  case "$ACTION" in
    1) break ;;
    2) continue ;;
    3) echo "Cancelado por el usuario."; exit 0 ;;
  esac
done

# ============================================================
# 2) Acciones y dependencias
# ============================================================
OPTIONS="$(w_checklist "Acciones y dependencias" "Selecciona qué quieres instalar/configurar en este servidor:" \
  "apt_update"          "Ejecutar apt update / upgrade" OFF \
  "base"                "Instalar base: unzip, git, curl, wget, nano, ca-certificates, lsb-release" ON \
  "apache"              "Instalar Apache y módulos necesarios" ON \
  "apache_conf"         "Crear configuración Apache para Moodle usando /public" ON \
  "disable_default"     "Deshabilitar sitio default 000-default.conf de Apache" OFF \
  "php"                 "Instalar PHP 8.3 FPM/CLI y extensiones Moodle" ON \
  "composer"            "Instalar Composer" ON \
  "graphviz"            "Instalar Graphviz" ON \
  "aspell"              "Instalar Aspell" ON \
  "ghostscript"         "Instalar Ghostscript" ON \
  "pg_repo"             "Agregar repo oficial PostgreSQL si hace falta" ON \
  "postgresql"          "Instalar PostgreSQL 16" ON \
  "create_db"           "Crear base de datos y usuario automáticamente" ON \
  "cli_install"         "Instalar Moodle por CLI y generar config.php" ON \
  "cron"                "Crear cron de Moodle para www-data cada minuto" ON
)"

INSTALL_ANY="yes"
if [[ -z "$OPTIONS" ]]; then
  INSTALL_ANY="no"
fi

CREATE_APACHE_CONF="no"
DISABLE_DEFAULT_SITE="no"
CREATE_DB="no"
RUN_CLI_INSTALL="no"
CREATE_CRON="no"

if option_enabled "$OPTIONS" "apache_conf"; then
  CREATE_APACHE_CONF="yes"
fi

if option_enabled "$OPTIONS" "disable_default"; then
  DISABLE_DEFAULT_SITE="yes"
fi

if option_enabled "$OPTIONS" "create_db"; then
  CREATE_DB="yes"
fi

if option_enabled "$OPTIONS" "cli_install"; then
  RUN_CLI_INSTALL="yes"
fi

if option_enabled "$OPTIONS" "cron"; then
  CREATE_CRON="yes"
fi

# ============================================================
# 3) Instalación
# ============================================================
clear

export WWWROOT="${PROTOCOL}://${WEBSITE_ADDRESS}"

echo "Iniciando instalación de Moodle..."
echo "Sitio: $WWWROOT"
echo "Código: $MOODLE_CODE_FOLDER"
echo "Público Apache: $MOODLE_PUBLIC_FOLDER"
echo "Datos: $MOODLE_DATA_FOLDER"
echo "Base de datos: PostgreSQL / $DB_NAME"

step "Verificando espacio disponible"
check_disk_space "$(dirname "$MOODLE_CODE_FOLDER")" 2500

if [[ "$INSTALL_ANY" == "yes" ]]; then
  if option_enabled "$OPTIONS" "apt_update"; then
    step "Actualizando paquetes"
    apt update -y
    apt upgrade -y
  fi

  if option_enabled "$OPTIONS" "base"; then
    step "Instalando paquetes base"
    apt install -y unzip git curl wget nano ca-certificates lsb-release software-properties-common gnupg
  fi

  if option_enabled "$OPTIONS" "pg_repo"; then
    ensure_postgresql_16_repo_if_needed
  fi

  if option_enabled "$OPTIONS" "apache"; then
    step "Instalando Apache"
    apt install -y apache2 libapache2-mod-fcgid
    a2enmod proxy_fcgi setenvif rewrite headers env dir mime || true
    systemctl enable apache2 || true
    systemctl restart apache2 || true
  fi

  if option_enabled "$OPTIONS" "php"; then
    step "Instalando PHP 8.3 y extensiones Moodle"

    apt install -y \
      php8.3-fpm \
      php8.3-cli \
      php8.3-curl \
      php8.3-zip \
      php8.3-gd \
      php8.3-xml \
      php8.3-intl \
      php8.3-mbstring \
      php8.3-soap \
      php8.3-bcmath \
      php8.3-exif \
      php8.3-ldap \
      php8.3-opcache \
      php8.3-readline \
      php8.3-pgsql

    systemctl enable php8.3-fpm || true
    systemctl restart php8.3-fpm || true
  fi

  if option_enabled "$OPTIONS" "composer"; then
    step "Instalando Composer"
    apt install -y composer
  fi

  if option_enabled "$OPTIONS" "graphviz"; then
    step "Instalando Graphviz"
    apt install -y graphviz
  fi

  if option_enabled "$OPTIONS" "aspell"; then
    step "Instalando Aspell"
    apt install -y aspell aspell-en
  fi

  if option_enabled "$OPTIONS" "ghostscript"; then
    step "Instalando Ghostscript"
    apt install -y ghostscript
  fi

  if option_enabled "$OPTIONS" "postgresql"; then
    step "Instalando PostgreSQL 16"
    apt install -y postgresql-16 postgresql-client-16 postgresql-contrib
    systemctl enable postgresql || true
    systemctl restart postgresql || true

    echo "Versión instalada:"
    psql --version || true
  fi
else
  step "No se seleccionaron dependencias para instalar"
fi

# ============================================================
# 4) Ajustes PHP
# ============================================================
if [[ -f "/etc/php/8.3/fpm/php.ini" && -f "/etc/php/8.3/cli/php.ini" ]]; then
  step "Ajustando configuración PHP"

  for php_ini in /etc/php/8.3/fpm/php.ini /etc/php/8.3/cli/php.ini; do
    sed -i 's/^[[:space:]]*;*[[:space:]]*max_input_vars[[:space:]]*=.*/max_input_vars = 5000/' "$php_ini" || true
    sed -i 's/^[[:space:]]*post_max_size[[:space:]]*=.*/post_max_size = 256M/' "$php_ini" || true
    sed -i 's/^[[:space:]]*upload_max_filesize[[:space:]]*=.*/upload_max_filesize = 256M/' "$php_ini" || true
    sed -i 's/^[[:space:]]*memory_limit[[:space:]]*=.*/memory_limit = 512M/' "$php_ini" || true
    sed -i 's/^[[:space:]]*max_execution_time[[:space:]]*=.*/max_execution_time = 300/' "$php_ini" || true
  done

  systemctl restart php8.3-fpm || true
fi

# ============================================================
# 5) Carpetas
# ============================================================
step "Creando carpetas Moodle"

mkdir -p "$MOODLE_BASE_PATH"
mkdir -p "$(dirname "$MOODLE_DATA_FOLDER")"
mkdir -p "$MOODLE_DATA_FOLDER"

chown -R www-data:www-data "$MOODLE_DATA_FOLDER"
find "$MOODLE_DATA_FOLDER" -type d -exec chmod 700 {} \; || true
find "$MOODLE_DATA_FOLDER" -type f -exec chmod 600 {} \; || true

# ============================================================
# 6) Descargar Moodle
# ============================================================
if [[ -d "$MOODLE_CODE_FOLDER" && ! -d "$MOODLE_CODE_FOLDER/.git" && "$(ls -A "$MOODLE_CODE_FOLDER" 2>/dev/null)" ]]; then
  echo "La carpeta $MOODLE_CODE_FOLDER existe y no está vacía, pero no parece ser un repositorio Git."
  echo "Si es un clon fallido anterior, bórrala con:"
  echo "  sudo rm -rf $MOODLE_CODE_FOLDER"
  exit 1
fi

if [[ -d "$MOODLE_CODE_FOLDER/.git" ]]; then
  step "Moodle ya existe. Actualizando repositorio"
  cd "$MOODLE_CODE_FOLDER"
  git fetch --depth 1 origin "$MOODLE_VERSION"
  git checkout "$MOODLE_VERSION"
  git pull --ff-only || true
else
  step "Clonando Moodle: $MOODLE_VERSION"
  mkdir -p "$(dirname "$MOODLE_CODE_FOLDER")"
  git clone --depth 1 -b "$MOODLE_VERSION" https://github.com/moodle/moodle.git "$MOODLE_CODE_FOLDER"
fi

if [[ ! -d "$MOODLE_PUBLIC_FOLDER" ]]; then
  echo "ERROR: No existe $MOODLE_PUBLIC_FOLDER."
  echo "Moodle 5.1+ requiere carpeta public."
  echo "Verifica que estés usando MOODLE_502_STABLE o una versión 5.1+."
  exit 1
fi

# ============================================================
# 7) Composer
# ============================================================
if command -v composer >/dev/null 2>&1; then
  if [[ -f "$MOODLE_CODE_FOLDER/composer.json" ]]; then
    step "Ejecutando composer install"

    cd "$MOODLE_CODE_FOLDER"
    mkdir -p "$MOODLE_CODE_FOLDER/vendor"

    COMPOSER_ALLOW_SUPERUSER=1 composer install --no-dev --classmap-authoritative || {
      echo "Advertencia: composer install falló. Continuando."
    }
  fi
fi

step "Asignando permisos al código Moodle"
chown -R root:root "$MOODLE_CODE_FOLDER"
find "$MOODLE_CODE_FOLDER" -type d -exec chmod 755 {} \; || true
find "$MOODLE_CODE_FOLDER" -type f -exec chmod 644 {} \; || true

# ============================================================
# 8) Base de datos PostgreSQL
# ============================================================
if [[ "$CREATE_DB" == "yes" ]]; then
  step "Creando base de datos PostgreSQL"

  systemctl restart postgresql || true

  sudo -u postgres psql <<EOF
DO
\$\$
BEGIN
   IF NOT EXISTS (
      SELECT FROM pg_catalog.pg_roles WHERE rolname = '${DB_USER}'
   ) THEN
      CREATE ROLE ${DB_USER} LOGIN PASSWORD '${DB_PASS}';
   END IF;
END
\$\$;
EOF

  if ! sudo -u postgres psql -lqt | cut -d \| -f 1 | grep -qw "$DB_NAME"; then
    sudo -u postgres createdb -E UTF8 -O "$DB_USER" "$DB_NAME"
  fi

  sudo -u postgres psql -d "$DB_NAME" <<EOF
GRANT ALL PRIVILEGES ON DATABASE ${DB_NAME} TO ${DB_USER};
ALTER SCHEMA public OWNER TO ${DB_USER};
GRANT ALL ON SCHEMA public TO ${DB_USER};
EOF
else
  step "Saltando creación de base de datos"
fi

# ============================================================
# 9) Apache
# ============================================================
if [[ "$CREATE_APACHE_CONF" == "yes" ]]; then
  step "Creando configuración Apache para Moodle"

  APACHE_CONF="/etc/apache2/sites-available/moodle.conf"

  cat <<EOF > "$APACHE_CONF"
<VirtualHost *:80>
    ServerName ${WEBSITE_ADDRESS}

    DocumentRoot ${MOODLE_PUBLIC_FOLDER}

    <Directory ${MOODLE_PUBLIC_FOLDER}>
        Options FollowSymLinks
        AllowOverride All
        Require all granted
        DirectoryIndex index.php index.html
    </Directory>

    <Directory ${MOODLE_DATA_FOLDER}>
        Require all denied
    </Directory>

    <FilesMatch "\\.php$">
        SetHandler "proxy:unix:/run/php/php8.3-fpm.sock|fcgi://localhost/"
    </FilesMatch>

    ErrorLog \${APACHE_LOG_DIR}/moodle_error.log
    CustomLog \${APACHE_LOG_DIR}/moodle_access.log combined
</VirtualHost>
EOF

  a2enmod proxy_fcgi setenvif rewrite headers env dir mime || true
  a2ensite moodle.conf

  if [[ "$DISABLE_DEFAULT_SITE" == "yes" ]]; then
    a2dissite 000-default.conf || true
  fi

  echo "ServerName ${WEBSITE_ADDRESS}" > /etc/apache2/conf-available/servername.conf
  a2enconf servername || true

  apache2ctl configtest
  systemctl reload apache2
fi

# ============================================================
# 10) Instalar Moodle por CLI
# ============================================================
if [[ "$RUN_CLI_INSTALL" == "yes" ]]; then
  step "Instalando Moodle por CLI"

  cd "$MOODLE_CODE_FOLDER"

  /usr/bin/php admin/cli/install.php \
    --chmod=2770 \
    --lang=es \
    --wwwroot="$WWWROOT" \
    --dataroot="$MOODLE_DATA_FOLDER" \
    --dbtype="pgsql" \
    --dbhost="$DB_HOST" \
    --dbname="$DB_NAME" \
    --dbuser="$DB_USER" \
    --dbpass="$DB_PASS" \
    --dbport="$DB_PORT" \
    --fullname="$SITE_NAME" \
    --shortname="$SITE_NAME" \
    --adminuser="$ADMIN_USER" \
    --adminpass="$ADMIN_PASS" \
    --adminemail="$ADMIN_EMAIL" \
    --non-interactive \
    --agree-license

  if [[ -f "$MOODLE_CODE_FOLDER/config.php" ]]; then
    chown root:www-data "$MOODLE_CODE_FOLDER/config.php"
    chmod 640 "$MOODLE_CODE_FOLDER/config.php"
  fi
else
  step "Saltando instalación CLI"
  echo "Puedes completar la instalación desde el navegador:"
  echo "$WWWROOT"
fi

# ============================================================
# 11) Cron Moodle
# ============================================================
if [[ "$CREATE_CRON" == "yes" ]]; then
  step "Configurando cron de Moodle"

  CRON_LINE="* * * * * /usr/bin/php ${MOODLE_CODE_FOLDER}/admin/cli/cron.php >/dev/null 2>&1"

  CURRENT_CRON="$(mktemp)"
  crontab -u www-data -l > "$CURRENT_CRON" 2>/dev/null || true

  if ! grep -Fq "$MOODLE_CODE_FOLDER/admin/cli/cron.php" "$CURRENT_CRON"; then
    echo "$CRON_LINE" >> "$CURRENT_CRON"
    crontab -u www-data "$CURRENT_CRON"
  fi

  rm -f "$CURRENT_CRON"
fi

# ============================================================
# 12) Reinicios finales
# ============================================================
step "Reiniciando servicios"

systemctl restart php8.3-fpm || true

if systemctl list-unit-files | grep -q apache2.service; then
  systemctl restart apache2 || true
fi

if systemctl list-unit-files | grep -q postgresql.service; then
  systemctl restart postgresql || true
fi

# ============================================================
# 13) Resultado
# ============================================================
FINAL_MSG="Instalación finalizada.

Sitio:
  $WWWROOT

Código Moodle:
  $MOODLE_CODE_FOLDER

Directorio público Apache:
  $MOODLE_PUBLIC_FOLDER

Moodle data:
  $MOODLE_DATA_FOLDER

Base de datos:
  Tipo: PostgreSQL
  Host: $DB_HOST
  Puerto: $DB_PORT
  Base: $DB_NAME
  Usuario: $DB_USER

Apache config:
  /etc/apache2/sites-available/moodle.conf

Cron:
  Usuario www-data
  ${MOODLE_CODE_FOLDER}/admin/cli/cron.php
"

echo "$FINAL_MSG"

w_msg "Finalizado" "$FINAL_MSG"
