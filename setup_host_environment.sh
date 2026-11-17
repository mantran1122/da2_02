#!/usr/bin/env bash
# ==============================================================================
# SCRIPT CÀI ĐẶT MÔI TRƯỜNG CHẠY THẬT CHO DỰ ÁN QR ORDER & KDS TRÊN ZORIN OS / UBUNTU
# ==============================================================================

set -e

echo "=================================================================="
echo "  HỆ THỐNG GỌI MÓN QR ORDER & QUẢN LÝ BẾP (KDS)                    "
echo "  Cài đặt môi trường máy chủ và biên dịch dependencies dự án       "
echo "=================================================================="

# Kiểm tra quyền root
if [ "$EUID" -ne 0 ]; then
  echo "[LỖI] Vui lòng chạy script này với quyền sudo:"
  echo "       sudo ./setup_host_environment.sh"
  exit 1
fi

CURRENT_USER=${SUDO_USER:-$USER}
USER_HOME=$(eval echo "~$CURRENT_USER")
PROJECT_DIR="/home/mantran1507/Desktop/02_qr_order_quan_ly_bep"

echo "[1/4] Cập nhật danh mục gói APT và cài đặt các gói hệ thống cơ bản..."
apt-get update -y
apt-get install -y curl wget git unzip zip ca-certificates gnupg lsb-release

echo "[2/4] Cài đặt Docker & Docker Compose (Khuyên dùng - nhanh nhất)..."
if ! command -v docker &> /dev/null; then
    install -m 0755 -d /etc/apt/keyrings
    curl -fsSL https://download.docker.com/linux/ubuntu/gpg | gpg --dearmor -o /etc/apt/keyrings/docker.gpg --yes
    chmod a+r /etc/apt/keyrings/docker.gpg

    UBUNTU_CODENAME=$(lsb_release -cs)
    # Zorin OS 17/18 dựa trên Ubuntu Jammy hoặc Noble
    if [ "$UBUNTU_CODENAME" = "jammy" ] || [ "$UBUNTU_CODENAME" = "noble" ]; then
        DOCKER_CODENAME=$UBUNTU_CODENAME
    else
        DOCKER_CODENAME="jammy"
    fi

    echo "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.gpg] https://download.docker.com/linux/ubuntu $DOCKER_CODENAME stable" | tee /etc/apt/sources.list.d/docker.list > /dev/null
    apt-get update -y
    apt-get install -y docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin
    usermod -aG docker $CURRENT_USER
    echo ">> Cài đặt Docker hoàn tất!"
else
    echo ">> Docker đã có sẵn trên máy."
fi

echo "[3/4] Cài đặt PHP 8.2, Composer và Node.js 20 LTS (Native fallback)..."
# 1. Cài PHP 8.2 qua PPA ondrej/php
if ! command -v php &> /dev/null; then
    apt-get install -y software-properties-common
    add-apt-repository -y ppa:ondrej/php
    apt-get update -y
    apt-get install -y php8.2-cli php8.2-fpm php8.2-mysql php8.2-redis php8.2-xml php8.2-mbstring php8.2-curl php8.2-gd php8.2-zip php8.2-bcmath php8.2-intl
fi

# 2. Cài Composer
if ! command -v composer &> /dev/null; then
    curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
fi

# 3. Cài Node.js 20 LTS
if ! command -v node &> /dev/null; then
    curl -fsSL https://deb.nodesource.com/setup_20.x | bash -
    apt-get install -y nodejs
fi

# 4. Cài Redis và MySQL
if ! command -v redis-server &> /dev/null; then
    apt-get install -y redis-server
    systemctl enable redis-server
    systemctl start redis-server
fi

if ! command -v mysql &> /dev/null; then
    apt-get install -y mysql-server
    systemctl enable mysql
    systemctl start mysql
    # Tạo database mẫu
    mysql -e "CREATE DATABASE IF NOT EXISTS qr_order_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
    mysql -e "ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password BY '';" || true
fi

echo "[4/4] Khởi tạo dependencies cho dự án..."
cd "$PROJECT_DIR/code/backend"
if [ ! -f .env ]; then
    cp .env.example .env
fi

# Phân quyền thư mục storage và bootstrap/cache
chmod -R 777 storage bootstrap/cache

echo "=================================================================="
echo "  CÀI ĐẶT HOÀN TẤT! BÂY GIỜ BẠN CÓ THỂ CHẠY HỆ THỐNG BẰNG 2 CÁCH:   "
echo "=================================================================="
echo "Cách 1: Chạy bằng Docker (Khuyên dùng):"
echo "  cd $PROJECT_DIR/code"
echo "  docker compose up -d"
echo ""
echo "Cách 2: Chạy native trực tiếp:"
echo "  cd $PROJECT_DIR/code"
echo "  ./start-all.sh"
echo "=================================================================="
