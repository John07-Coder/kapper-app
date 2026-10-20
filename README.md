# Kapsalon De Knip

Website voor een kapsalon waar klanten online een knipafspraak maken. De kapper bevestigt, annuleert of herstelt die afspraken in een eigen dashboard.

![Homepage](docs/images/home.png)

## Functies

- Klanten kiezen een vrije dag en een tijdslot van 30 minuten, tot 30 dagen vooruit.
- Openingstijden, tarief en salongegevens staan in `config/salon.php`.
- Na het boeken krijgt de klant een bevestiging op het scherm en per e-mail.
- Het dashboard toont de agenda, openstaande aanvragen en de verwachte omzet van vandaag.
- Een contactformulier stuurt een bericht naar de salon.

![Afspraak maken](docs/images/afspraak.png)

![Over ons](docs/images/over-ons.png)

## Techniek

Laravel 13, Livewire 4, Tailwind CSS 4, SQLite en PHPUnit. Lokaal gaat e-mail naar `storage/logs/laravel.log`.

## Clonen en starten

Je hebt Git, PHP 8.3 of nieuwer, Composer en Node.js 20 of nieuwer nodig.

Clone de repository en ga de map in:

```bash
git clone https://github.com/John07-Coder/kapper-app.git
cd kapper-app
```

Installeer de afhankelijkheden, maak `.env` en de SQLite-database, en bouw de frontend:

```bash
composer setup
```

Dat commando doet dit zelf: `composer install`, `.env.example` kopiëren naar `.env`, een `APP_KEY` genereren, `database/database.sqlite` aanmaken, migreren, en `npm install` plus `npm run build`.

Open daarna `.env` en kies zelf een beheerderswachtwoord van minimaal 12 tekens. Er is geen standaardwachtwoord. Zonder wachtwoord stopt `db:seed`.

```env
ADMIN_EMAIL=admin@kapsalondeknip.nl
ADMIN_PASSWORD=kies-zelf-een-lang-wachtwoord
```

Zet het beheerdersaccount en een paar voorbeeld-afspraken in de database, en start de site:

```bash
php artisan db:seed
php artisan serve
```

De site staat op [http://127.0.0.1:8000](http://127.0.0.1:8000). Inloggen voor de kapper: [http://127.0.0.1:8000/admin/login](http://127.0.0.1:8000/admin/login), met het e-mailadres en wachtwoord uit `.env`.

Tijdens het ontwikkelen kun je de pagina’s automatisch laten verversen met `npm run dev`. Bevestigingsmails gaan in de wachtrij. Lokaal schrijft de mailer ze naar `storage/logs/laravel.log` zodra je `php artisan queue:work` draait.

## Tests

```bash
composer test
```

GitHub Actions draait bij elke push en pull request `composer ci:check`: Pint en de testsuite.

## Beveiliging

- De bevestiging na het boeken hangt aan de sessie. Het afspraak-id is geen publieke Livewire-property, en het bevestigingstoken is vergrendeld.
- Een actieve boeking krijgt een unieke slot-sleutel. Twee aanvragen voor hetzelfde tijdstip kunnen niet allebei worden opgeslagen. Annuleren maakt het slot weer vrij.
- `/admin` vereist een ingelogde beheerder. Een gewoon account komt er niet in.
- Inloggen is beperkt tot vijf pogingen per minuut per e-mailadres en IP-adres.
