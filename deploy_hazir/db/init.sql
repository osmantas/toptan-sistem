CREATE TABLE IF NOT EXISTS musteriler (
    id INT AUTO_INCREMENT PRIMARY KEY,
    firma_adi VARCHAR(255) NOT NULL,
    kullanici_adi VARCHAR(50) UNIQUE,
    sifre VARCHAR(255),
    telefon VARCHAR(50)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS urunler (
    id INT AUTO_INCREMENT PRIMARY KEY,
    urun_kodu VARCHAR(50) NOT NULL UNIQUE,
    urun_adi VARCHAR(255) NOT NULL,
    usd_fiyat DECIMAL(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS siparisler (
    id INT AUTO_INCREMENT PRIMARY KEY,
    musteri_id INT NOT NULL,
    tarih TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    durum ENUM('beklemede', 'tamamlandi', 'iptal') DEFAULT 'beklemede',
    guncellendi TINYINT(1) DEFAULT 0,
    FOREIGN KEY (musteri_id) REFERENCES musteriler(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS siparis_detaylari (
    id INT AUTO_INCREMENT PRIMARY KEY,
    siparis_id INT NOT NULL,
    urun_kodu VARCHAR(50) NOT NULL,
    istenen_kg DECIMAL(10,2) NOT NULL,
    teslim_edilen_kg DECIMAL(10,2) DEFAULT 0.00,
    kalan_kg DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (siparis_id) REFERENCES siparisler(id) ON DELETE CASCADE,
    FOREIGN KEY (urun_kodu) REFERENCES urunler(urun_kodu) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS teslimatlar (
    id INT AUTO_INCREMENT PRIMARY KEY,
    musteri_id INT NOT NULL,
    tarih TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    usd_kuru DECIMAL(10,4) NOT NULL,
    toplam_usd DECIMAL(10,2) NOT NULL,
    toplam_tl DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (musteri_id) REFERENCES musteriler(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS teslimat_detaylari (
    id INT AUTO_INCREMENT PRIMARY KEY,
    teslimat_id INT NOT NULL,
    urun_kodu VARCHAR(50) NOT NULL,
    teslim_edilen_kg DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (teslimat_id) REFERENCES teslimatlar(id) ON DELETE CASCADE,
    FOREIGN KEY (urun_kodu) REFERENCES urunler(urun_kodu) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
