#!/usr/bin/env bash
# =============================================================================
# install-server.sh — First-Time Server Setup — Lestari API
# Stack: OpenLiteSpeed 1.8.5 + lsphp83 + FrankenPHP Octane + Supervisor
# =============================================================================
# Usage (sebagai root):
#   chmod +x scripts/install-server.sh
#   sudo ./scripts/install-server.sh
# =============================================================================

set -euo pipefail

# ─── Konfigurasi Stack ────────────────────────────────────────────────────────
APP_DIR="/var/www/lestari"
APP_USER="lsadm"                                         # User OLS default
APP_GROUP="lsadm"
LOG_DIR="/var/log/supervisor"
LSPHP_DIR="/usr/local/lsws/lsphp83/bin"                 # Path lsphp83 OLS
PHP_BIN="$LSPHP_DIR/php"                                 # Binary PHP untuk artisan
COMPOSER_BIN="/usr/local/bin/composer"
FRANKEN_VERSION="v1.4.4"                                 # Update dari releases FrankenPHP

# ─── Warna output ─────────────────────────────────────────────────────────────
RED='\033[0;31m'; GREEN='\033[0;32m'; YELLOW='\033[1;33m'
BLUE='\033[0;34m'; BOLD='\033[1m'; NC='\033[0m'

log_info()    { echo -e "${BLUE}[INFO]${NC}  $1"; }
log_success() { echo -e "${GREEN}[OK]${NC}    $1"; }
log_warn()    { echo -e "${YELLOW}[WARN]${NC}  $1"; }
log_error()   { echo -e "${RED}[ERROR]${NC} $1" >&2; exit 1; }

# ─── Cek root ─────────────────────────────────────────────────────────────────
[[ "$EUID" -ne 0 ]] && log_error "Jalankan sebagai root: sudo ./scripts/install-server.sh"

echo ""
echo -e "${BOLD}╔══════════════════════════════════════════════════════╗${NC}"
echo -e "${BOLD}║   Lestari API — Server Setup (OLS + lsphp83)         ║${NC}"
echo -e "${BOLD}╚══════════════════════════════════════════════════════╝${NC}"
echo ""

# ─── Validasi: OLS & lsphp83 sudah terinstall ────────────────────────────────
log_info "Validasi OLS dan lsphp83..."
[[ ! -f "$PHP_BIN" ]] && log_error "lsphp83 tidak ditemukan di $PHP_BIN. Pastikan OLS 1.8.5 + lsphp83 sudah terinstall!"
[[ ! -d "/usr/local/lsws" ]] && log_error "OpenLiteSpeed tidak ditemukan di /usr/local/lsws"
log_success "OLS dan lsphp83 ditemukan: $($PHP_BIN -r 'echo PHP_VERSION;')"

# ─── Validasi: user lsadm ─────────────────────────────────────────────────────
id -u "$APP_USER" &>/dev/null || log_error "User '$APP_USER' tidak ditemukan. Pastikan OLS terinstall dengan benar."
log_success "User lsadm ditemukan"

# ─── Step 1: Install Supervisor ───────────────────────────────────────────────
log_info "Step 1/7 | Install supervisor..."
if command -v supervisord &>/dev/null; then
    log_warn "Supervisor sudah terinstall, skip install"
else
    apt-get update -qq
    apt-get install -y -qq supervisor curl unzip git
    log_success "Supervisor terinstall"
fi

# ─── Step 1b: Konfigurasi Unix Socket Redis ───────────────────────────────────
log_info "Step 1b/7 | Konfigurasi Unix socket Redis..."
REDIS_CONF="/etc/redis/redis.conf"
REDIS_SOCK="run/redis/redis.sock"

if [[ -f "$REDIS_CONF" ]]; then
    # Aktifkan Unix socket di redis.conf jika belum ada
    if ! grep -q "^unixsocket $REDIS_SOCK" "$REDIS_CONF"; then
        echo "" >> "$REDIS_CONF"
        echo "# Unix socket — dikonfigurasi oleh install-server.sh Lestari" >> "$REDIS_CONF"
        echo "unixsocket $REDIS_SOCK" >> "$REDIS_CONF"
        echo "unixsocketperm 770" >> "$REDIS_CONF"
        log_success "Unix socket Redis ditambahkan ke $REDIS_CONF"
    else
        log_warn "Unix socket Redis sudah dikonfigurasi di $REDIS_CONF"
    fi

    # Restart Redis untuk apply config
    systemctl restart redis-server 2>/dev/null || systemctl restart redis 2>/dev/null || \
        log_warn "Gagal restart Redis, lakukan manual: sudo systemctl restart redis"

    # Beri akses socket ke user lsadm
    # Redis socket group biasanya 'redis', tambahkan lsadm ke group redis
    if getent group redis &>/dev/null; then
        usermod -aG redis "$APP_USER"
        log_success "User $APP_USER ditambahkan ke group 'redis' (akses socket)"
    else
        log_warn "Group 'redis' tidak ditemukan, cek permission socket manual: ls -la $REDIS_SOCK"
    fi

    # Verifikasi socket aktif
    sleep 1
    if [[ -S "$REDIS_SOCK" ]]; then
        log_success "Redis socket aktif: $REDIS_SOCK"
    else
        log_warn "Socket Redis belum aktif di $REDIS_SOCK — cek: sudo systemctl status redis"
    fi
else
    log_warn "Redis tidak terinstall di server ini. Install dulu: sudo apt-get install -y redis-server"
    log_warn "Atau jika Redis ada di server lain, ubah REDIS_HOST ke TCP di .env"
fi

# ─── Step 1c: Verifikasi Unix Socket PostgreSQL ───────────────────────────────
log_info "Step 1c/7 | Verifikasi Unix socket PostgreSQL..."
PG_SOCK_DIR="/var/run/postgresql"
if ls "$PG_SOCK_DIR"/.s.PGSQL.5432 &>/dev/null; then
    log_success "PostgreSQL socket tersedia: $PG_SOCK_DIR/.s.PGSQL.5432"
    # Pastikan lsadm bisa akses socket PostgreSQL
    # User postgres perlu allow lsadm via pg_hba.conf atau group
    if [[ -d "$PG_SOCK_DIR" ]]; then
        chmod g+x "$PG_SOCK_DIR" 2>/dev/null || true
        log_warn "Pastikan di /etc/postgresql/*/main/pg_hba.conf ada entry:"
        log_warn "  local   all   $APP_USER   trust"
        log_warn "  (atau md5 jika pakai password)"
    fi
else
    log_warn "Socket PostgreSQL tidak ditemukan di $PG_SOCK_DIR"
    log_warn "Pastikan PostgreSQL berjalan: sudo systemctl start postgresql"
fi


# Ekstensi PHP yang mungkin belum ada di lsphp83 (opsional, cek dulu)
log_info "Cek ekstensi PHP lsphp83..."
MISSING_EXT=()
for ext in pdo_pgsql mbstring xml curl zip bcmath intl pcntl redis; do
    $PHP_BIN -m 2>/dev/null | grep -q "^$ext$" || MISSING_EXT+=("lsphp83-$ext")
done
if [[ ${#MISSING_EXT[@]} -gt 0 ]]; then
    log_warn "Ekstensi lsphp83 yang mungkin perlu diinstall: ${MISSING_EXT[*]}"
    log_warn "Jalankan: sudo apt-get install ${MISSING_EXT[*]}"
else
    log_success "Semua ekstensi PHP OK"
fi

# ─── Step 2: Install FrankenPHP ───────────────────────────────────────────────
log_info "Step 2/7 | Download FrankenPHP $FRANKEN_VERSION..."
if command -v frankenphp &>/dev/null; then
    log_warn "FrankenPHP sudah ada ($(frankenphp version 2>&1 | head -1)), skip download"
else
    ARCH=$(uname -m)
    case "$ARCH" in
        x86_64)  FRANKEN_ARCH="linux_x86_64" ;;
        aarch64) FRANKEN_ARCH="linux_arm64"  ;;
        *)        log_error "Arsitektur $ARCH tidak didukung oleh FrankenPHP" ;;
    esac

    FRANKEN_URL="https://github.com/dunglas/frankenphp/releases/download/${FRANKEN_VERSION}/frankenphp-${FRANKEN_ARCH}"
    log_info "Download dari: $FRANKEN_URL"
    curl -L --progress-bar "$FRANKEN_URL" -o /usr/local/bin/frankenphp
    chmod +x /usr/local/bin/frankenphp
    log_success "FrankenPHP terinstall: $(frankenphp version 2>&1 | head -1)"
fi

# ─── Step 3: Install Composer ─────────────────────────────────────────────────
log_info "Step 3/7 | Cek Composer..."
if command -v composer &>/dev/null; then
    log_warn "Composer sudah ada, skip"
else
    log_info "Install Composer via lsphp83..."
    curl -sS https://getcomposer.org/installer | "$PHP_BIN" -- --install-dir=/usr/local/bin --filename=composer
    log_success "Composer terinstall"
fi

# ─── Step 4: Setup App Directory ──────────────────────────────────────────────
log_info "Step 4/7 | Setup direktori aplikasi..."
mkdir -p "$APP_DIR" "$LOG_DIR" /var/log/frankenphp

# Ownership ke lsadm (user OLS)
chown -R "$APP_USER:$APP_GROUP" "$APP_DIR" "$LOG_DIR" /var/log/frankenphp
chmod -R 775 "$APP_DIR"
log_success "Direktori $APP_DIR siap (owner: $APP_USER:$APP_GROUP)"

# ─── Step 5: Setup Supervisord ────────────────────────────────────────────────
log_info "Step 5/7 | Install konfigurasi supervisord..."
mkdir -p /etc/supervisor/conf.d

cp "$APP_DIR/deploy/supervisor/supervisord.conf" /etc/supervisor/supervisord.conf 2>/dev/null || \
    log_warn "supervisord.conf tidak ditemukan di project, gunakan default"

cp "$APP_DIR/deploy/supervisor/conf.d/"*.conf /etc/supervisor/conf.d/ 2>/dev/null || \
    log_warn "Tidak ada conf.d files ditemukan di project"

systemctl enable supervisor
systemctl start supervisor || true
supervisorctl reread
supervisorctl update
log_success "Supervisord configured"

# ─── Step 6: OLS Virtual Host Info ───────────────────────────────────────────
log_info "Step 6/7 | Instruksi konfigurasi OLS Virtual Host..."
echo ""
echo -e "  ${YELLOW}======================================================${NC}"
echo -e "  ${YELLOW}  OLS Reverse Proxy ke FrankenPHP — Setup Manual      ${NC}"
echo -e "  ${YELLOW}======================================================${NC}"
echo ""
echo "  Buka WebAdmin OLS: https://[server-ip]:7080"
echo "  (default login: admin / admin)"
echo ""
echo "  Atau copy config otomatis:"
VHOST_NAME="lestari"
OLS_CONF_DIR="/usr/local/lsws/conf/vhosts/$VHOST_NAME"
if [[ -f "$APP_DIR/deploy/ols/vhconf.conf" ]]; then
    mkdir -p "$OLS_CONF_DIR"
    cp "$APP_DIR/deploy/ols/vhconf.conf" "$OLS_CONF_DIR/vhconf.conf"
    chown -R lsadm:lsadm "$OLS_CONF_DIR"
    log_success "OLS vhconf.conf dicopy ke $OLS_CONF_DIR"
    log_warn "Jangan lupa tambahkan virtual host '$VHOST_NAME' di httpd_config.conf OLS!"
else
    log_warn "deploy/ols/vhconf.conf tidak ditemukan, skip"
fi
echo ""

# ─── Step 7: .env Setup ───────────────────────────────────────────────────────
log_info "Step 7/7 | Setup file .env..."
if [[ ! -f "$APP_DIR/.env" ]]; then
    cp "$APP_DIR/.env.example" "$APP_DIR/.env"
    cd "$APP_DIR"
    "$PHP_BIN" artisan key:generate --force --quiet
    log_success ".env dibuat dan APP_KEY di-generate"
    log_warn "WAJIB edit .env sebelum deploy: DB_PASSWORD, MIDTRANS_*, MAIL_*, dll!"
else
    log_warn ".env sudah ada, tidak ditimpa"
fi

# ─── Summary ──────────────────────────────────────────────────────────────────
echo ""
echo -e "${BOLD}${GREEN}╔══════════════════════════════════════════════════════╗${NC}"
echo -e "${BOLD}${GREEN}║      Server setup selesai!                           ║${NC}"
echo -e "${BOLD}${GREEN}╚══════════════════════════════════════════════════════╝${NC}"
echo ""
echo "  Stack aktif:"
echo "    PHP binary : $PHP_BIN ($($PHP_BIN -r 'echo PHP_VERSION;'))"
echo "    FrankenPHP : $(frankenphp version 2>&1 | head -1)"
echo "    User       : $APP_USER"
echo "    App dir    : $APP_DIR"
echo ""
echo "  Langkah selanjutnya:"
echo "  1. Edit $APP_DIR/.env"
echo "  2. cd $APP_DIR"
echo "  3. sudo -u lsadm $COMPOSER_BIN install --no-dev --optimize-autoloader"
echo "  4. sudo -u lsadm $PHP_BIN artisan octane:install --server=frankenphp"
echo "  5. sudo -u lsadm $PHP_BIN artisan migrate --force"
echo "  6. sudo supervisorctl start all"
echo "  7. Tambahkan OLS Virtual Host via WebAdmin :7080"
echo ""
supervisorctl status 2>/dev/null || true
echo ""
