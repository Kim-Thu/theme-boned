#!/bin/bash
set -e

echo "🚀 Đang chuẩn bị môi trường PHP..."
composer install --no-progress --prefer-dist --quiet 2>/dev/null || true

echo "📦 Cài đặt WordPress Stubs để PHPStan hiểu hàm WP..."
composer require --dev php-stubs/wordpress-stubs --quiet --no-interaction 2>/dev/null || true

echo "🔍 Đang tìm các file PHP cần format..."

# Tìm tất cả file PHP, loại trừ vendor/ và node_modules/
mapfile -t php_files < <(find . -type f -name "*.php" \
  -not -path "./vendor/*" \
  -not -path "./node_modules/*" \
  -not -path "./.git/*")

total=${#php_files[@]}
echo "📋 Tìm thấy $total file PHP cần kiểm tra"

# Format từng file
count=0
fixed=0
for file in "${php_files[@]}"; do
  count=$((count + 1))
  echo -n "[$count/$total] 🎨 Đang format: $file ... "
  
  # Chạy PHPCBF cho từng file, không fail nếu có lỗi không fix được
  if vendor/bin/phpcbf --standard=./phpcs.xml \
    --extensions=php \
    "$file" --quiet 2>/dev/null; then
    echo "✅ OK"
    fixed=$((fixed + 1))
  else
    # PHPCBF trả về code khác 0 nếu có lỗi không auto-fix được
    # Đây là bình thường, không cần báo lỗi
    echo "⚠️  Cần review"
  fi
done

echo ""
echo "📊 Tổng kết: Đã xử lý $count file, $fixed file được auto-fix thành công"

echo "🧪 Đang chạy PHPStan kiểm tra lỗi logic (chỉ báo, không sửa)..."
vendor/bin/phpstan analyse \
  --configuration=phpstan.neon \
  --level=5 \
  ./ \
  --no-progress \
  --error-format=table || true

echo ""
echo "✅ Hoàn tất!"
echo "👉 Bước tiếp theo:"
echo "   git add ."
echo "   git commit -m 'chore: auto-format PHP files with PHPCBF'"
echo "   git push"