#!/bin/bash
# Script untuk menaikkan timeout nginx biar tidak 504 saat render laporan OJK
# Harus dijalankan dengan sudo / akses root

set -e

NGINX_CONF="/etc/nginx/conf.d/siupk.conf"

if [ ! -f "$NGINX_CONF" ]; then
    echo "ERROR: $NGINX_CONF tidak ditemukan"
    exit 1
fi

echo "Updating $NGINX_CONF dengan fastcgi_read_timeout 300s..."

# Backup
cp "$NGINX_CONF" "${NGINX_CONF}.bak.$(date +%Y%m%d%H%M%S)"

# Tambahkan fastcgi_read_timeout jika belum ada
if ! grep -q "fastcgi_read_timeout" "$NGINX_CONF"; then
    # Sisipkan fastcgi_read_timeout & fastcgi_send_timeout setelah baris 'include fastcgi_params;'
    sed -i '/include fastcgi_params;/a\        fastcgi_read_timeout 300s;\n        fastcgi_send_timeout 300s;\n        fastcgi_connect_timeout 60s;\n        fastcgi_buffer_size 128k;\n        fastcgi_buffers 4 256k;\n        fastcgi_busy_buffers_size 256k;' "$NGINX_CONF"
    echo "OK: fastcgi_read_timeout ditambahkan"
else
    echo "SKIP: fastcgi_read_timeout sudah ada"
fi

# Test config
echo ""
echo "Test nginx config..."
nginx -t

echo ""
echo "Reload nginx..."
systemctl reload nginx
echo "Done!"
