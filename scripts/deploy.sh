#!/usr/bin/env bash
# =============================================================================
# deploy.sh — Zero-Downtime Deploy Script — Lestari Backend API
# =============================================================================
# Usage:
#   chmod +x scripts/deploy.sh
#   ./scripts/deploy.sh [--skip-migrate] [--skip-cache]
#
# Prasyarat di server:
#   - FrankenPHP binary tersedia di $PATH atau /usr/local/bin/frankenphp
#   - supervisord berjalan
#   - PHP 8.3+ dengan ekstensi: pgsql, redis, curl, zip, mbstring, xml
# =============================================================================

set -euo pipefail

# ─── Konfigurasi ──────────────────────────────────────────────────────────────
APP_DIR="/var/www/lestari"
APP_USER="lsadm"                                          # User OLS
PHP_BIN="${PHP_BIN:-/usr/local/lsws/lsphp84/bin/php}"    # lsphp84 OLS
COMPOSER_BIN="${COMPOSER_BIN:-/usr/local/bin/composer}"
PHP_ARTISAN="sudo -u $APP_USER $PHP_BIN $APP_DIR/artisan"
SKIP_MIGRATE=false
SKIP_CACHE=false
DEPLOY_BRANCH="api" # Kunci nama branch lu di sini biar jelas
# ─── Warna output ─────────────────────────────────────────────────────────────
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
BOLD='\033[1m'
NC='\033[0m' # No Color

# ─── Fungsi Helper ────────────────────────────────────────────────────────────
log_info()    { echo -e "${BLUE}[INFO]${NC}  $1"; }
log_success() { echo -e "${GREEN}[OK]${NC}    $1"; }
log_warn()    { echo -e "${YELLOW}[WARN]${NC}  $1"; }
log_error()   { echo -e "${RED}[ERROR]${NC} $1" >&2; exit 1; }

# ─── Parse Arguments ──────────────────────────────────────────────────────────
for arg in "$@"; do
    case $arg in
        --skip-migrate) SKIP_MIGRATE=true ;;
        --skip-cache)   SKIP_CACHE=true ;;
        *)              log_warn "Unknown argument: $arg" ;;
    esac
done

# ─── Header ───────────────────────────────────────────────────────────────────
echo ""
echo -e "${BOLD}╔══════════════════════════════════════════════════╗${NC}"
echo -e "${BOLD}║     Lestari API — Zero-Downtime Deployment       ║${NC}"
echo -e "${BOLD}╚══════════════════════════════════════════════════╝${NC}"
echo ""

# ─── Validasi awal ────────────────────────────────────────────────────────────
[[ ! -d "$APP_DIR" ]] && log_error "Direktori $APP_DIR tidak ditemukan!"
[[ ! -f "$PHP_BIN" ]] && log_error "lsphp84 tidak ditemukan di $PHP_BIN. Pastikan OLS+lsphp84 terinstall!"
id -u "$APP_USER" &>/dev/null || log_error "User '$APP_USER' tidak ditemukan!"

cd "$APP_DIR"

# ─── Step 1: Aktifkan Maintenance Mode ───────────────────────────────────────
log_info "Step 1/9 | Aktifkan maintenance mode..."
$PHP_ARTISAN down --refresh=15 --retry=10 --secret="lestari-deploy-secret" \
    || log_warn "Maintenance mode gagal diaktifkan, lanjut..."
log_success "Maintenance mode aktif"

# ─── Step 2: Git Pull ─────────────────────────────────────────────────────────
# ─── Tambahkan di bagian Konfigurasi (Atas) ──────────────────────────


# ─── Ganti Step 2 dengan ini ─────────────────────────────────────────
# ─── Step 2: Git Pull ─────────────────────────────────────────────────
log_info "Step 2/9 | Menarik kode terbaru dari Git (Branch: $DEPLOY_BRANCH)..."
git fetch origin

# Pastikan branch lokal tracking ke remote dengan benar sebelum di-reset
git checkout -B "$DEPLOY_BRANCH" "origin/$DEPLOY_BRANCH" --quiet
git reset --hard "origin/$DEPLOY_BRANCH"

log_success "Kode diperbarui: $(git log --oneline -1)"
sudo composer update
# ─── Step 3: Install Dependencies ────────────────────────────────────────────
log_info "Step 3/9 | Install composer dependencies (production)..."
sudo -u "$APP_USER" $COMPOSER_BIN install \
    --no-dev \
    --optimize-autoloader \
    --classmap-authoritative \
    --no-interaction \
    --quiet
log_success "Composer selesai"

# ─── Step 4: Migrasi Database ─────────────────────────────────────────────────
if [[ "$SKIP_MIGRATE" == "false" ]]; then
    log_info "Step 4/9 | Menjalankan migrasi database..."
    $PHP_ARTISAN migrate --force --no-interaction
    log_success "Migrasi selesai"
else
    log_warn "Step 4/9 | Migrasi dilewati (--skip-migrate)"
fi

# ─── Step 5: Build Cache ──────────────────────────────────────────────────────
if [[ "$SKIP_CACHE" == "false" ]]; then
    log_info "Step 5/9 | Membangun cache config, route, event..."

    # Bersihkan cache lama dulu
    $PHP_ARTISAN config:clear   --quiet
    $PHP_ARTISAN route:clear    --quiet
    $PHP_ARTISAN event:clear    --quiet
    $PHP_ARTISAN view:clear     --quiet

    # Build cache baru
    $PHP_ARTISAN config:cache   --quiet
    $PHP_ARTISAN route:cache    --quiet
    $PHP_ARTISAN event:cache    --quiet

    log_success "Semua cache berhasil dibangun"
else
    log_warn "Step 5/9 | Cache build dilewati (--skip-cache)"
fi

# ─── Step 6: Set Permission ───────────────────────────────────────────────────
log_info "Step 6/9 | Mengatur file permission..."
chown -R lsadm:lsadm storage bootstrap/cache
chmod -R 775 storage bootstrap/cache
log_success "Permission OK (owner: lsadm:lsadm)"

# ─── Step 7: Restart Queue Workers ───────────────────────────────────────────
log_info "Step 7/9 | Restart queue workers..."
$PHP_ARTISAN queue:restart
log_success "Queue workers akan restart setelah job aktif selesai"

# ─── Step 8: Graceful Reload Octane (Zero-Downtime) ──────────────────────────
log_info "Step 8/9 | Graceful reload FrankenPHP Octane..."
# octane:reload mengirim sinyal ke worker untuk reload kode tanpa drop koneksi aktif
$PHP_ARTISAN octane:reload \
    && log_success "Octane gracefully reloaded" \
    || {
        log_warn "octane:reload gagal, restart via supervisorctl..."
        supervisorctl restart lestari-octane \
            && log_success "Octane restarted via supervisorctl" \
            || log_error "Gagal restart Octane! Cek supervisorctl status"
    }

# ─── Step 9: Nonaktifkan Maintenance Mode ────────────────────────────────────
log_info "Step 9/9 | Nonaktifkan maintenance mode..."
$PHP_ARTISAN up
log_success "Maintenance mode dinonaktifkan"

# ─── Summary ──────────────────────────────────────────────────────────────────
echo ""
echo -e "${BOLD}${GREEN}╔══════════════════════════════════════════════════╗${NC}"
echo -e "${BOLD}${GREEN}║         Deploy selesai dengan sukses! 🚀          ║${NC}"
echo -e "${BOLD}${GREEN}╚══════════════════════════════════════════════════╝${NC}"
echo ""
echo -e "  ${BOLD}Commit :${NC} $(git log --oneline -1)"
echo -e "  ${BOLD}Waktu  :${NC} $(date '+%Y-%m-%d %H:%M:%S %Z')"
echo ""

# Tampilkan status supervisord
log_info "Status semua proses:"
supervisorctl status | sed 's/^/  /'
echo ""
