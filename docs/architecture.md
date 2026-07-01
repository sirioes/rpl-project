# Arsitektur & Design Patterns — Mijn Amor Tour and Travel

## 1. Pola Arsitektur: Layered Architecture

Sistem dibangun menggunakan **Layered Architecture** yang memisahkan kode menjadi empat lapisan yang bersih dan independen.

```
Presentation Layer  →  Business Logic Layer  →  Data Access Layer  →  Database
   (Controller)            (Service)           (Repository + Model)    (MySQL)
```

### Penjelasan Tiap Layer

| Layer | Tanggung Jawab | Lokasi File |
|---|---|---|
| **Presentation** | Menerima HTTP request, mengembalikan response/view | `app/Http/Controllers/` |
| **Business Logic** | Logika bisnis, orkestrasi proses, tidak menyentuh database langsung | `app/Services/` |
| **Data Access** | Query database melalui interface abstrak | `app/Repositories/` |
| **Model** | Representasi tabel database (Eloquent ORM) | `app/Models/` |

### Prinsip Utama

- **Controller tidak query database langsung** — hanya memanggil Service atau Repository via Interface
- **Service tidak bergantung pada Eloquent** — hanya tahu Interface, bukan implementasinya
- **Repository Interface sebagai kontrak** — sehingga implementasi bisa diganti tanpa mengubah layer di atasnya

### Contoh Alur: Proses Checkout

```
CheckoutController
    → CheckoutService::initiateCheckout()
        → ProductRepositoryInterface::findById()       (cek produk & kuota)
        → BookingRepositoryInterface::create()         (simpan booking)
        → Stripe::createCheckoutSession()              (buat sesi pembayaran)
```

---

## 2. Design Patterns (GoF)

Sistem mengimplementasikan **3 Design Pattern** dari rumpun Gang of Four (GoF).

---

### Pattern 1 — Observer Pattern

**Kategori:** Behavioral

**Tujuan:** Memisahkan aksi "setelah booking berhasil dibayar" dari controller, sehingga controller tidak perlu tahu siapa yang bereaksi terhadap event tersebut.

**Cara Kerja:**
Ketika Stripe webhook mengkonfirmasi pembayaran, sistem hanya perlu men-dispatch satu event `BookingPaid`. Semua listener akan otomatis bereaksi tanpa controller perlu memanggilnya satu per satu.

```php
// StripeWebhookController.php — hanya dispatch event
BookingPaid::dispatch($booking);

// SendBookingConfirmationMail.php — listener bereaksi otomatis
public function handle(BookingPaid $event): void
{
    Mail::to($event->booking->contact_email)
        ->send(new BookingConfirmationMail($event->booking));
}
```

**Lokasi File:**

| File | Keterangan |
|---|---|
| `app/Events/BookingPaid.php` | Event yang di-dispatch setelah pembayaran sukses |
| `app/Listeners/SendBookingConfirmationMail.php` | Listener yang mengirim email konfirmasi |
| `app/Providers/AppServiceProvider.php` | Registrasi event-listener mapping |

---

### Pattern 2 — Singleton Pattern

**Kategori:** Creational

**Tujuan:** Memastikan `DeepLService` hanya dibuat satu instance selama satu request lifecycle, sehingga koneksi ke DeepL API tidak dibuat berulang kali secara boros.

**Cara Kerja:**
Laravel Service Container mendaftarkan `DeepLService` sebagai singleton. Setiap kali kode membutuhkan `DeepLService`, container memberikan instance yang sama — bukan membuat yang baru.

```php
// AppServiceProvider.php
$this->app->singleton(DeepLService::class, function () {
    return new DeepLService();
});
```

**Lokasi File:**

| File | Keterangan |
|---|---|
| `app/Services/DeepLService.php` | Service untuk terjemahan otomatis via DeepL API |
| `app/Providers/AppServiceProvider.php` | Registrasi singleton di Laravel container |

---

### Pattern 3 — Factory Pattern

**Kategori:** Creational

**Tujuan:** Memisahkan pembuatan objek Repository dari kode yang memakainya. Controller dan Service hanya tahu Interface — bukan implementasi konkritnya. `RepositoryServiceProvider` bertindak sebagai factory yang menentukan implementasi mana yang digunakan.

**Cara Kerja:**
```php
// RepositoryServiceProvider.php — factory binding
$this->app->bind(ProductRepositoryInterface::class, EloquentProductRepository::class);
$this->app->bind(BookingRepositoryInterface::class, EloquentBookingRepository::class);
$this->app->bind(UserRepositoryInterface::class, EloquentUserRepository::class);
$this->app->bind(MessageRepositoryInterface::class, EloquentMessageRepository::class);
$this->app->bind(TrackRecordRepositoryInterface::class, EloquentTrackRecordRepository::class);

// ProductController.php — hanya tahu Interface, tidak tahu EloquentProductRepository
public function __construct(ProductRepositoryInterface $productRepository)
```

**Lokasi File:**

| File | Keterangan |
|---|---|
| `app/Repositories/Contracts/*RepositoryInterface.php` | Interface (kontrak) untuk setiap repository |
| `app/Repositories/Eloquent/Eloquent*Repository.php` | Implementasi konkrit menggunakan Eloquent |
| `app/Providers/RepositoryServiceProvider.php` | Factory — binding interface ke implementasi |

---

## 3. Diagram

### Class Diagram
> Visualisasi seluruh layer, interface, dan design pattern

![Class Diagram](diagrams/class-diagram.png)

### Entity Relationship Diagram (ERD)
> Relasi antar tabel database

![ERD](diagrams/erd.png)

### Sequence Diagram — Alur Checkout
> Alur proses checkout end-to-end: dari user klik Book Now hingga email konfirmasi terkirim

![Sequence Diagram](diagrams/sequence-diagram.png)
