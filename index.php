<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AKSA TOPTAN - Esans ve B2B Platformu</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;1,400&family=Inter:wght@300;400;600&display=swap"
        rel="stylesheet">
    <style>
        :root {
            --primary-bg: #0a0a0c;
            --accent-gold: #d4af37;
            --accent-gold-dark: #aa8c2c;
            --text-light: #fefefe;
            --text-muted: #a0a0a0;
            --card-bg: rgba(255, 255, 255, 0.03);
            --border-color: rgba(212, 175, 55, 0.2);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--primary-bg);
            color: var(--text-light);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            overflow-x: hidden;
            position: relative;
        }

        /* Ambient Background Glow */
        body::before {
            content: '';
            position: absolute;
            top: -20%;
            left: -10%;
            width: 50vw;
            height: 50vw;
            background: radial-gradient(circle, rgba(212, 175, 55, 0.1) 0%, rgba(10, 10, 12, 0) 70%);
            z-index: -1;
            pointer-events: none;
        }

        body::after {
            content: '';
            position: absolute;
            bottom: -20%;
            right: -10%;
            width: 60vw;
            height: 60vw;
            background: radial-gradient(circle, rgba(212, 175, 55, 0.08) 0%, rgba(10, 10, 12, 0) 70%);
            z-index: -1;
            pointer-events: none;
        }

        /* Header */
        header {
            padding: 30px 50px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            animation: fadeInDown 1s ease forwards;
            opacity: 0;
            transform: translateY(-20px);
        }

        .logo {
            font-family: 'Playfair Display', serif;
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--text-light);
            text-decoration: none;
            letter-spacing: 2px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .logo span {
            color: var(--accent-gold);
        }

        /* Main Content */
        main {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            padding: 20px;
            z-index: 1;
        }

        .hero-text {
            max-width: 800px;
        }

        h1 {
            font-family: 'Playfair Display', serif;
            font-size: clamp(2.5rem, 8vw, 5rem);
            font-weight: 700;
            margin-bottom: 20px;
            line-height: 1.1;
            animation: fadeInUp 1s ease forwards 0.3s;
            opacity: 0;
            transform: translateY(30px);
        }

        h1 span {
            background: linear-gradient(135deg, #f5d76e 0%, var(--accent-gold) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        p.subtitle {
            font-size: 1.2rem;
            color: var(--text-muted);
            margin-bottom: 50px;
            font-weight: 300;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
            line-height: 1.6;
            animation: fadeInUp 1s ease forwards 0.6s;
            opacity: 0;
            transform: translateY(30px);
        }

        /* Action Buttons */
        .actions {
            display: flex;
            gap: 20px;
            justify-content: center;
            flex-wrap: wrap;
            animation: fadeInUp 1s ease forwards 0.9s;
            opacity: 0;
            transform: translateY(30px);
        }

        .btn {
            padding: 15px 40px;
            font-size: 1.1rem;
            font-weight: 600;
            border-radius: 50px;
            text-decoration: none;
            transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
            position: relative;
            overflow: hidden;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }

        .btn-primary {
            background-color: var(--accent-gold);
            color: #000;
            box-shadow: 0 4px 15px rgba(212, 175, 55, 0.3);
        }

        .btn-primary:hover {
            background-color: var(--accent-gold-dark);
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(212, 175, 55, 0.4);
        }

        .btn-outline {
            background-color: transparent;
            color: var(--text-light);
            border: 1px solid var(--border-color);
            backdrop-filter: blur(10px);
        }

        .btn-outline:hover {
            border-color: var(--accent-gold);
            color: var(--accent-gold);
            transform: translateY(-3px);
            background-color: rgba(212, 175, 55, 0.05);
        }

        /* Features Section */
        .features {
            display: flex;
            gap: 30px;
            margin-top: 80px;
            justify-content: center;
            flex-wrap: wrap;
            max-width: 1000px;
            animation: fadeIn 1s ease forwards 1.2s;
            opacity: 0;
        }

        .feature-card {
            background: var(--card-bg);
            border: 1px solid rgba(255, 255, 255, 0.05);
            padding: 30px;
            border-radius: 16px;
            flex: 1;
            min-width: 250px;
            text-align: left;
            backdrop-filter: blur(10px);
            transition: transform 0.3s ease, border-color 0.3s ease;
        }

        .feature-card:hover {
            transform: translateY(-10px);
            border-color: var(--border-color);
        }

        .feature-card h3 {
            font-family: 'Playfair Display', serif;
            font-size: 1.3rem;
            margin-bottom: 10px;
            color: var(--accent-gold);
        }

        .feature-card p {
            font-size: 0.95rem;
            color: var(--text-muted);
            line-height: 1.5;
        }

        /* Animations */
        @keyframes fadeInUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeInDown {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeIn {
            to {
                opacity: 1;
            }
        }

        /* Footer */
        footer {
            text-align: center;
            padding: 20px;
            color: #555;
            font-size: 0.9rem;
            border-top: 1px solid rgba(255, 255, 255, 0.05);
            margin-top: auto;
        }

        /* Responsive */
        @media (max-width: 768px) {
            body {
                padding-bottom: 20px;
            }

            header {
                flex-direction: column;
                gap: 15px;
                padding: 20px 15px;
            }

            .logo {
                font-size: 1.5rem;
            }

            p.subtitle {
                font-size: 1rem;
                padding: 0 15px;
            }

            .actions {
                flex-direction: column;
                width: 100%;
                padding: 0 20px;
            }

            .btn {
                width: 100%;
                justify-content: center;
                padding: 12px 20px;
            }

            .features {
                margin-top: 50px;
                flex-direction: column;
                gap: 20px;
                padding: 0 15px;
            }
        }
    </style>
</head>

<body>

    <header>
        <a href="#" class="logo">AKSA<span>TOPTAN</span></a>
    </header>

    <main>
        <div class="hero-text">
            <h1>Kalitenin Kokusu,<br><span>Toptan Ayrıcalığı.</span></h1>
            <p class="subtitle">Türkiye'nin öncü esans tedarikçisi Aksa Toptan B2B Portalı'na hoş geldiniz. İşletmeniz
                için en seçkin kokuları hemen sipariş verebilirsiniz.</p>

            <div class="actions">
                <a href="portal.php" class="btn btn-primary">
                    Müşteri Girişi (B2B)
                </a>
            </div>
        </div>

        <div class="features">
            <div class="feature-card">
                <h3>Geniş Ürün Yelpazesi</h3>
                <p>En modern tasarım kokularından klasik esanslara kadar yüzlerce çeşit ürün portföyümüze anında erişim
                    sağlayın.</p>
            </div>
            <div class="feature-card">
                <h3>Güvenilir Tedarik</h3>
                <p>Müşterilerinize sunacağınız ürünler için kesintisiz stok ve zamanında teslimat güvencesiyle
                    siparişlerinizi yönetin.</p>
            </div>
            <div class="feature-card">
                <h3>Kolay Sipariş Yönetimi</h3>
                <p>Gelişmiş B2B portalımız üzerinden siparişlerinizi saniyeler içinde oluşturun ve anlık durum takibi
                    yapın.</p>
            </div>
        </div>
    </main>

    <footer>
        &copy; 2026 AKSA Toptan Esans. Tüm hakları gizlidir.
    </footer>

</body>

</html>