# Lose Your Weight

Ein einfacher, moderner und kostenloser Kalorienzähler, gebaut mit dem TALL-Stack. Das Projekt dient dem Zweck, eine faire und unkomplizierte Alternative zu teuren Diät-Apps zu bieten.

## Über das Projekt

"Lose Your Weight" ist eine Web-Anwendung, die es Benutzern ermöglicht, ihre tägliche Kalorienaufnahme zu verfolgen, Gewichtsziele zu setzen und ihre Fortschritte zu visualisieren. Die App berechnet den individuellen Kalorienbedarf, erlaubt das Speichern von Mahlzeiten und Favoriten und integriert eine Anbindung an die OpenFoodFacts-API zur einfachen Suche von Lebensmitteln.

### Haupt-Features

-   **Intelligente Zielsetzung:** Automatische Berechnung des Kalorienbedarfs und des Defizits basierend auf persönlichen Daten und Zielen.
-   **Tages-Tracker:** Einfaches Loggen von Lebensmitteln über API-Suche, Favoriten oder manuelle Eingabe.
-   **Mahlzeiten-System:** Eigene Mahlzeiten erstellen und mit einem Klick loggen.
-   **Fortschritts-Analyse:** Gewichtsverlauf als Chart und in Tabellenform.
-   **Admin-Backend:** Verwaltung von Benutzern und Lebensmittel-Daten.
-   **Kostenlos:** Alle Kernfunktionen sind gratis nutzbar.

### Gebaut mit

-   [Laravel 12](https://laravel.com/docs/12.x)
-   [Livewire](https://livewire.laravel.com/) + [Volt](https://livewire.laravel.com/docs/volt)
-   [Alpine.js](https://alpinejs.dev/)
-   [Tailwind CSS](https://tailwindcss.com/)
-   [Pest](https://pestphp.com/) für Tests

---

## Getting Started

Folge diesen Schritten, um eine lokale Entwicklungsumgebung aufzusetzen.

### Voraussetzungen

-   PHP >= 8.3
-   Composer
-   Node.js & npm
-   Ein lokaler Datenbankserver (z.B. DBngin oder SQLite)

### Installation

1.  **Repository klonen**

    ```bash
    git clone [https://github.com/zorotl/loseYourWeightv2](https://github.com/zorotl/loseYourWeightv2)
    cd dein-repo-name
    ```

2.  **PHP-Abhängigkeiten installieren**

    ```bash
    composer install
    ```

3.  **`.env`-Datei konfigurieren**

    ```bash
    cp .env.example .env
    ```

    Öffne die `.env`-Datei und konfiguriere deine Datenbankverbindung (`DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`).

4.  **App-Schlüssel generieren**

    ```bash
    php artisan key:generate
    ```

5.  **Datenbank migrieren und Seeden**
    Dieser Befehl baut die Datenbank auf und füllt sie mit nützlichen Testdaten (inkl. `test@example.com` User).

    ```bash
    php artisan migrate:fresh --seed
    ```

6.  **JavaScript-Abhängigkeiten installieren**

    ```bash
    npm install
    ```

7.  **Vite-Server starten**

    ```bash
    npm run dev
    ```

8.  **Lokalen Server starten**
    Öffne ein zweites Terminal und starte den Laravel-Server.
    ```bash
    php artisan serve
    ```

Du kannst dich nun mit `test@example.com` (Passwort: `password`) einloggen.

---

## Tests ausführen

Um die Test-Suite laufen zu lassen, führe folgenden Befehl aus:

```bash
php artisan test
```

## Lizenz

Dieses Projekt ist unter der MIT-Lizenz lizenziert. Siehe die `LICENSE`-Datei für Details.

---
