# Lestari Backend API

[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](./LICENSE)
[![PHP](https://img.shields.io/badge/PHP-8.3%2B-purple.svg)](https://php.net)
[![Laravel](https://img.shields.io/badge/Laravel-12-red.svg)](https://laravel.com)

> REST API untuk platform e-commerce bunga berbasis Laravel 12. Dibangun dengan arsitektur **API-Only**, menggunakan **PostgreSQL**, **RabbitMQ** (queue asinkron), dan **Laravel Reverb** (WebSocket real-time).

---

## Daftar Isi

- [Tech Stack](#tech-stack)
- [Setup Lokal](#setup-lokal)
- [Format Respons](#format-respons)
- [Autentikasi](#autentikasi)
- [Endpoint API](#endpoint-api)
  - [Auth](#-auth)
  - [Katalog Publik](#-katalog-publik)
  - [Keranjang Belanja](#-keranjang-belanja)
  - [Checkout](#-checkout)
  - [Pembayaran (Webhook)](#-pembayaran-webhook)
  - [Admin — Kategori](#-admin--kategori)
  - [Admin — Produk](#-admin--produk)
  - [Admin — Dashboard](#-admin--dashboard)
- [WebSocket Real-Time](#websocket-real-time)
- [Keamanan](#keamanan)
- [Menjalankan Test](#menjalankan-test)

---

## Tech Stack

| Layer | Teknologi |
|---|---|
| Framework | Laravel 12 (API-Only) |
| Database | PostgreSQL |
| Auth | Laravel Sanctum (Token-Based) |
| Queue | RabbitMQ (`vladimir-yuldashev/laravel-queue-rabbitmq`) |
| WebSocket | Laravel Reverb |
| Pembayaran | Midtrans (Sandbox) |
| Email | Brevo SMTP |
| Search | PostgreSQL `pg_trgm` + GIN Index |

---

## Setup Lokal

```bash
# 1. Clone & install dependencies
git clone <repo-url>
cd tokovasbungafloria
composer install

# 2. Copy environment
cp .env.example .env
php artisan key:generate

# 3. Konfigurasi .env (minimal):
# DB_CONNECTION=pgsql, DB_DATABASE, DB_USERNAME, DB_PASSWORD
# QUEUE_CONNECTION=rabbitmq
# BROADCAST_CONNECTION=reverb

# 4. Jalankan migrasi
php artisan migrate

# 5. Buat storage link
php artisan storage:link

# 6. Jalankan services (3 terminal terpisah)
php artisan serve             # API Server
php artisan queue:work        # RabbitMQ Worker (proses checkout)
php artisan reverb:start      # WebSocket Server
```

---

## Format Respons

Semua endpoint mengembalikan format JSON yang seragam.

**Sukses:**
```json
{
  "success": true,
  "message": "Pesan sukses",
  "data": { ... }
}
```

**Error:**
```json
{
  "success": false,
  "message": "Pesan error",
  "errors": { ... }
}
```

**HTTP Status Code yang digunakan:**

| Kode | Arti |
|---|---|
| `200` | OK |
| `201` | Created |
| `202` | Accepted (job diterima, diproses di latar belakang) |
| `400` | Bad Request |
| `401` | Unauthenticated (token tidak ada / tidak valid) |
| `403` | Forbidden (tidak punya akses) |
| `404` | Not Found |
| `422` | Validation Error |
| `429` | Too Many Requests (rate limit) |

---

## Autentikasi

API ini menggunakan **Laravel Sanctum Bearer Token**.

Setelah login, sisipkan token di header setiap request yang memerlukan autentikasi:

```
Authorization: Bearer <token>
```

---

## Endpoint API

### 🔐 Auth

#### `POST /api/v1/auth/register`
Registrasi pengguna baru. Role default adalah `customer`.

**Rate Limit:** 5 request/menit per IP

**Request Body:**
```json
{
  "name": "Budi Santoso",
  "email": "budi@example.com",
  "password": "password123"
}
```

**Response `201`:**
```json
{
  "success": true,
  "message": "Berhasil registrasi",
  "data": {
    "user": { "id": "uuid", "name": "Budi Santoso", "email": "budi@example.com", "role": "customer" },
    "token": "1|xxxxxxxxxxxxxxxx"
  }
}
```

---

#### `POST /api/v1/auth/login`
Login dan dapatkan Bearer Token.

**Rate Limit:** 5 request/menit per IP

**Request Body:**
```json
{
  "email": "budi@example.com",
  "password": "password123"
}
```

**Response `200`:**
```json
{
  "success": true,
  "message": "Berhasil login",
  "data": {
    "user": { "id": "uuid", "name": "Budi Santoso", "role": "customer" },
    "token": "2|xxxxxxxxxxxxxxxx"
  }
}
```

---

#### `POST /api/v1/auth/logout`
Logout dan cabut token saat ini.

**Auth:** ✅ Wajib  
**Request Body:** Tidak ada

**Response `200`:**
```json
{ "success": true, "message": "Berhasil logout", "data": null }
```

---

#### `POST /api/v1/auth/forgot-password`
Kirim link reset password ke email.

**Rate Limit:** 3 request/menit per IP  
**Email Sender:** `Lestari No Reply <no-reply@bilikku.my.id>`

**Request Body:**
```json
{ "email": "budi@example.com" }
```

**Response `200`:**
```json
{ "success": true, "message": "We have emailed your password reset link." }
```

---

#### `POST /api/v1/auth/reset-password`
Reset password menggunakan token dari email.

**Request Body:**
```json
{
  "email": "budi@example.com",
  "token": "token-dari-email",
  "password": "passwordbaru123",
  "password_confirmation": "passwordbaru123"
}
```

**Response `200`:**
```json
{ "success": true, "message": "Your password has been reset." }
```

---

### 📦 Katalog Publik

Endpoint ini **tidak memerlukan autentikasi**.

#### `GET /api/v1/catalog/categories`
Ambil semua kategori (termasuk sub-kategori).

**Response `200`:**
```json
{
  "success": true,
  "data": [
    { "id": 1, "name": "Bunga Segar", "slug": "bunga-segar", "children": [] }
  ]
}
```

---

#### `GET /api/v1/catalog/products`
Ambil daftar produk aktif dengan pagination dan filter.

**Rate Limit:** 30 request/menit per IP (Anti-spam search)

**Query Parameters:**

| Parameter | Tipe | Deskripsi |
|---|---|---|
| `search` | string | Pencarian nama produk (pg_trgm optimized) |
| `category_id` | integer | Filter berdasarkan kategori |
| `page` | integer | Halaman (default: 1, 15 item per halaman) |

**Contoh:**
```
GET /api/v1/catalog/products?search=mawar&category_id=1&page=2
```

**Response `200`:**
```json
{
  "success": true,
  "data": {
    "data": [
      {
        "id": 1,
        "name": "Mawar Merah",
        "slug": "mawar-merah",
        "price": 50000,
        "stock": 20,
        "category": { "id": 1, "name": "Bunga Segar" },
        "images": [{ "image_path": "products/uuid.jpg", "is_primary": true }]
      }
    ],
    "current_page": 1,
    "total": 25
  }
}
```

---

#### `GET /api/v1/catalog/products/{slug}`
Ambil detail satu produk berdasarkan slug.

**Contoh:**
```
GET /api/v1/catalog/products/mawar-merah
```

**Response `200`:** Data produk lengkap beserta gambar dan kategori.

---

### 🛒 Keranjang Belanja

Mendukung dua mode: **User Login** (pakai Bearer Token) dan **Guest** (pakai `X-Guest-Token` header).

> **Penting untuk Guest:** Saat pertama kali menambah item, response akan menyertakan header `X-Guest-Token`. Simpan nilai ini dan kirim di setiap request cart berikutnya sebagai header `X-Guest-Token`.

#### `GET /api/v1/cart`
Lihat isi keranjang belanja.

**Header (pilih salah satu):**
- Login: `Authorization: Bearer <token>`
- Guest: `X-Guest-Token: <guest-token>`

**Response `200`:**
```json
{
  "success": true,
  "data": {
    "id": 5,
    "items": [
      {
        "id": 12,
        "quantity": 2,
        "product": { "id": 1, "name": "Mawar Merah", "price": 50000 }
      }
    ]
  }
}
```

---

#### `POST /api/v1/cart`
Tambah produk ke keranjang. Jika produk sudah ada, quantity akan ditambahkan.

**Header (opsional):** `Authorization: Bearer <token>` atau `X-Guest-Token: <guest-token>`

**Request Body:**
```json
{
  "product_id": 1,
  "quantity": 2
}
```

**Response `201` (Guest — perhatikan header):**
```
X-Guest-Token: xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx
```
```json
{ "success": true, "message": "Berhasil menambah ke keranjang", "data": { ... } }
```

> Produk yang nonaktif (`is_active: false`) tidak bisa ditambah ke keranjang dan akan mengembalikan `404`.

---

#### `DELETE /api/v1/cart/{itemId}`
Hapus satu item dari keranjang.

**Header (pilih salah satu):** `Authorization: Bearer <token>` atau `X-Guest-Token: <guest-token>`

**Response `200`:**
```json
{ "success": true, "message": "Item dihapus dari keranjang", "data": null }
```

**Response `403`:** Jika `itemId` bukan milik keranjang Anda (proteksi IDOR).

---

### 💳 Checkout

**Auth:** ✅ Wajib (Customer)

#### `POST /api/v1/checkout`
Memulai proses checkout secara **asinkron**. API langsung merespons tanpa menunggu proses selesai.

**Alur di latar belakang (RabbitMQ Worker):**
1. Validasi stok dengan Pessimistic Lock (`SELECT FOR UPDATE`)
2. Buat record `Order` dan `OrderItems`
3. Minta `snap_token` ke Midtrans Sandbox
4. Broadcast hasil ke frontend via WebSocket (Reverb)

**Request Body:** Tidak ada (menggunakan cart yang sudah ada)

**Response `202` (Accepted):**
```json
{
  "success": true,
  "message": "Proses checkout sedang berjalan di latar belakang. Silakan tunggu notifikasi.",
  "data": null
}
```

**Response `400`:** Jika keranjang kosong.

> Setelah `202`, frontend harus **mendengarkan WebSocket** di channel `checkout.{userId}` untuk menerima hasil akhirnya. Lihat [WebSocket Real-Time](#websocket-real-time).

---

### 🔔 Pembayaran (Webhook)

#### `POST /api/v1/payments/webhook`
Endpoint untuk menerima notifikasi pembayaran dari **server Midtrans**.

> ⚠️ Endpoint ini **hanya untuk Midtrans** — bukan untuk dipanggil langsung. Signature HMAC SHA-512 diverifikasi di setiap request. Request tanpa signature valid akan ditolak `403`.

**Payload (dikirim otomatis oleh Midtrans):**
```json
{
  "order_id": "INV-20260519-XXXX",
  "status_code": "200",
  "gross_amount": "150000.00",
  "signature_key": "hash-hmac-sha512",
  "transaction_status": "settlement",
  "transaction_id": "midtrans-txn-id",
  "payment_type": "gopay"
}
```

**Perubahan status order berdasarkan `transaction_status`:**

| Midtrans Status | Order Status |
|---|---|
| `settlement` | `paid` |
| `capture` + `accept` | `paid` |
| `cancel` / `deny` / `expire` | `cancelled` |
| `pending` | `pending` |

---

### 🛠️ Admin — Kategori

**Auth:** ✅ Wajib + Role `admin`

#### `GET /api/v1/admin/categories`
Daftar semua kategori.

#### `POST /api/v1/admin/categories`
Buat kategori baru.

```json
{
  "name": "Bunga Segar",
  "parent_id": null
}
```

#### `GET /api/v1/admin/categories/{id}`
Detail satu kategori beserta sub-kategori (`children`).

#### `PUT /api/v1/admin/categories/{id}`
Update kategori.

#### `DELETE /api/v1/admin/categories/{id}`
Hapus kategori.

---

### 🛠️ Admin — Produk

**Auth:** ✅ Wajib + Role `admin`

#### `GET /api/v1/admin/products`
Daftar semua produk (termasuk yang nonaktif).

**Query Parameters:** `search`, `category_id`, `is_active`, `page`

#### `POST /api/v1/admin/products`
Buat produk baru. Gunakan `multipart/form-data` untuk upload gambar.

**Request Body (form-data):**

| Field | Tipe | Wajib | Deskripsi |
|---|---|---|---|
| `category_id` | integer | ✅ | ID kategori |
| `name` | string | ✅ | Nama produk |
| `description` | string | ✅ | Deskripsi produk |
| `price` | numeric | ✅ | Harga (min: 0) |
| `stock` | integer | ✅ | Stok (min: 0) |
| `weight` | integer | ✅ | Berat dalam gram |
| `is_active` | boolean | ❌ | Default: `true` |
| `images[]` | file | ❌ | Maks 5 foto, JPEG/PNG/WebP, maks 2MB per foto |

> Nama file gambar di-generate otomatis (UUID) untuk mencegah path traversal.

#### `GET /api/v1/admin/products/{id}`
Detail produk.

#### `PUT /api/v1/admin/products/{id}`
Update produk (bisa sekaligus ganti gambar).

#### `DELETE /api/v1/admin/products/{id}`
Hapus produk beserta semua gambarnya.

---

### 📊 Admin — Dashboard

**Auth:** ✅ Wajib + Role `admin`

#### `GET /api/v1/admin/dashboard/stats`
Statistik performa toko bulan berjalan.

**Response `200`:**
```json
{
  "success": true,
  "data": {
    "current_month": {
      "revenue": 5000000,
      "orders_count": 42,
      "new_customers": 15
    },
    "alerts": {
      "low_stock_products": [
        { "id": 3, "name": "Tulip Kuning", "stock": 2 }
      ]
    }
  }
}
```

---

## WebSocket Real-Time

Setelah memanggil `POST /api/v1/checkout`, frontend harus berlangganan ke **Private Channel** Reverb untuk menerima hasil checkout tanpa perlu refresh halaman.

**Konfigurasi Reverb (dari `.env`):**
```
REVERB_APP_KEY=...
REVERB_HOST=localhost
REVERB_PORT=8080
```

**Menggunakan Laravel Echo (JavaScript):**
```javascript
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

const echo = new Echo({
    broadcaster: 'reverb',
    key: process.env.NEXT_PUBLIC_REVERB_APP_KEY,
    wsHost: process.env.NEXT_PUBLIC_REVERB_HOST,
    wsPort: process.env.NEXT_PUBLIC_REVERB_PORT,
    forceTLS: false,
    disableStats: true,
});

// Subscribe ke private channel milik user yang sedang login
echo.private(`checkout.${userId}`)
    .listen('CheckoutStatusUpdated', (event) => {
        if (event.status === 'success') {
            // Tampilkan popup pembayaran Midtrans menggunakan snap_token
            const snapToken = event.data.snap_token_midtrans;
            window.snap.pay(snapToken);
        } else {
            // Tampilkan notifikasi error (misal: stok habis)
            alert(event.message);
        }
    });
```

**Struktur Event `CheckoutStatusUpdated`:**
```json
{
  "status": "success",
  "message": "Checkout berhasil. Silakan selesaikan pembayaran.",
  "data": {
    "id": "order-uuid",
    "order_number": "INV-20260519-XXXX",
    "total_amount": 150000,
    "snap_token_midtrans": "...",
    "items": [ ... ]
  }
}
```

---

## Keamanan

API ini dilindungi oleh berbagai lapisan keamanan:

| Lapisan | Detail |
|---|---|
| **HTTP Security Headers** | `X-Frame-Options`, `X-Content-Type-Options`, `Referrer-Policy`, `Permissions-Policy` |
| **Stack Fingerprint Hiding** | Header `Server` dan `X-Powered-By` dihapus dari semua respons |
| **Proxy Header Stripping** | `X-Original-URL`, `X-HTTP-Method-Override`, dll. di-strip untuk mencegah bypass access control |
| **Rate Limiting** | Login: 5/mnt, Register: 5/mnt, Forgot Password: 3/mnt, Global: 60/mnt |
| **XSS Input Sanitization** | Semua input string di-sanitize dengan `strip_tags()` + `htmlspecialchars()` |
| **Timing Attack Prevention** | Login selalu menjalankan `Hash::check()` meski user tidak ditemukan |
| **File Upload Validation** | MIME type divalidasi via `finfo` (magic bytes), nama file UUID |
| **CORS Hardening** | `allowed_origins` dikunci ke domain spesifik via `CORS_ALLOWED_ORIGINS` |
| **IDOR Prevention** | Cart item hanya bisa dihapus oleh pemiliknya |
| **UUID Primary Key** | Tabel `users` dan `orders` memakai UUID untuk mencegah ID enumeration |
| **Pessimistic Locking** | Stok produk dikunci dengan `SELECT FOR UPDATE` saat checkout concurrent |
| **Webhook Signature** | HMAC SHA-512 diverifikasi sebelum memproses notifikasi Midtrans |

---

## Menjalankan Test

```bash
# Jalankan semua test (menggunakan SQLite in-memory)
php artisan test

# Filter test tertentu
php artisan test --filter SecurityTest
php artisan test --filter AuthTest
php artisan test --filter CheckoutTest
php artisan test --filter PaymentWebhookTest
```

**Test Suite (27 tests, 82 assertions):**

| Test Class | Cakupan |
|---|---|
| `Auth/RegisterTest` | Registrasi valid, validasi field, password hash |
| `Auth/LoginTest` | Login sukses, kredensial salah, token abilities |
| `Auth/ForgotPasswordTest` | Kirim reset link, email tidak terdaftar |
| `AdminCategoryTest` | CRUD kategori, proteksi role |
| `AdminProductTest` | Buat produk dengan gambar (`Storage::fake`), validasi harga negatif |
| `AdminDashboardTest` | Stats revenue, RBAC check |
| `CartTest` | Keranjang guest (X-Guest-Token), keranjang user login |
| `CheckoutTest` | Dispatch `ProcessCheckoutJob` ke queue (`Queue::fake`) |
| `PaymentWebhookTest` | Signature valid → update order, signature palsu → 403 |
| `SecurityTest` | HTTP headers, XSS sanitization, rate limit, timing attack, IDOR |

---

## Lisensi

Proyek ini dilisensikan di bawah [MIT License](./LICENSE).

```
Copyright (c) 2026 Rizki Anas Mustakim
```
