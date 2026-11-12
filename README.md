# Aangan — Property Accounts

A calm, warm accounting workspace for a small property portfolio: buildings, tenants,
monthly rent bills, and a cashbook in one place.

This repository currently contains **Stage 3 — Cashbook, Expenses and Monthly
Balances**. Buildings, flats, tenants, tenancies, monthly bills, payments, starting
balances, and expenses are stored in the database. The Dashboard and Cashbook use the
same live financial calculation, and export controls remain visibly disabled.

---

## Stack

| Layer | Version |
| --- | --- |
| PHP | 8.3 |
| Laravel | 13.x |
| Livewire | 4.x |
| Tailwind CSS | 4.x |
| Alpine.js | bundled with Livewire |
| Vite | 8.x |
| Font | Inter Variable (self-hosted via `@fontsource-variable/inter`) |

All assets are bundled locally — no CDN calls, no external font requests.

---

## Run locally (Windows)

```powershell
# 1. Install PHP and JS dependencies
composer install
npm install

# 2. Create your environment file and app key
Copy-Item .env.example .env
php artisan key:generate

# 3. Create the SQLite database and load the demonstration portfolio
php artisan migrate --seed

# 4. Build the front-end assets
npm run build

# 5. Serve the app
php artisan serve --host=0.0.0.0 --port=8123
```

To rebuild the database from scratch at any time:

```powershell
php artisan migrate:fresh --seed
```

Open <http://127.0.0.1:8123>.

> Port `8000` is commonly used by other local apps. If it is free you can omit
> `--port=8123`, but the commands above are what this project was verified against.

### Development mode (hot reload)

```powershell
composer run dev
```

or run the two processes separately:

```powershell
php artisan serve --host=0.0.0.0 --port=8123
npm run dev
```

> **Dev mode vs. LAN testing:** `npm run dev` writes `public/hot`, which tells
> Laravel to load CSS/JS from the Vite server. That only works in a browser on
> this machine. Before testing on a phone, stop the dev server (`Ctrl+C`) and run
> `npm run build` so the app serves the compiled assets.

### Access from a phone on the same Wi-Fi

1. Start the server bound to all interfaces: `php artisan serve --host=0.0.0.0 --port=8123`
2. Allow PHP through Windows Firewall for private networks when prompted.
3. Open `http://<your-lan-ip>:8123` on the phone, e.g. `http://10.148.120.14:8123`.

Find the LAN IP with:

```powershell
Get-NetIPAddress -AddressFamily IPv4 | Where-Object { $_.IPAddress -notlike '127.*' }
```

If the UI looks stale after a CSS/JS change, rebuild assets with `npm run build`
(or keep `npm run dev` running).

### Troubleshooting: page renders unstyled

If every screen suddenly loses its colours, fonts, and layout, Laravel is probably
pointing at a Vite dev server that is not reachable. Check for `public/hot`:

```powershell
if (Test-Path public\hot) { Get-Content public\hot }
```

If it exists and no dev server is running, delete it and rebuild:

```powershell
Remove-Item public\hot
npm run build
```

The Vite config pins the hot file to `http://localhost:5173` (browsers reject
`0.0.0.0`), so a normal `npm run dev` session works. Never commit `public/hot`.

---

## Screens

| Screen | Route | What it shows |
| --- | --- | --- |
| Dashboard | `/` | Live month/year view: opening and closing cash & bank, rent received, total expenses, net movement, and the combined closing balance as the primary highlight. Starting-balances modal saves manual adjustments with a previous-closing warning. |
| Buildings | `/buildings` | Live records: property cards with name, address, total flats, and occupied count. Add/Edit forms with unit registration. |
| Tenants | `/tenants` | Live records: name, building, flat, monthly house rent, contact number, charge defaults, notes, search, Add/Edit, and End tenancy. |
| Rent | `/rent` | Live billing: month/year selector, building filter, generated monthly bills, editable charges, status, and a bill drawer for Cash/Bank payments, payment dates, partial payments, and payment history. |
| Cashbook | `/cashbook` | Live cash, bank, and combined balances; chronological expense table with month filter; Add/Edit/Delete expense paid from Cash or Bank; disabled PDF/Excel export controls. |

Navigation is limited to these five screens, with a desktop sidebar and a mobile
bottom navigation bar.

---

## Design language

- Cream page background `#ede7c7`, espresso sidebar/text `#200e01`
- Deep red actions `#8b0000`, burgundy feature cards `#5b0202`
- Glass/translucent surfaces, refined borders, soft shadows, generous spacing
- Inter throughout, tabular numerals for money
- 180–250 ms transitions, animated sidebar indicator, animated balances,
  view-transition page changes, `prefers-reduced-motion` respected
- Dialogs and drawers trap focus, lock scroll, restore focus on close, and become
  bottom sheets on mobile

---

## Demonstration data

`database/seeders/PortfolioSeeder.php` loads three fictional buildings, 26 flats,
eight tenants, their November 2026 bills, five payments, the November opening
balances, and eight expenses. All figures are real database records — edits,
collections, and expenses persist. The default month is **November 2026**.

The Dashboard and Cashbook share `app/Support/Financials.php` for every monthly
calculation. Rent is recognised on its actual payment date; expenses on their expense
date; and unsaved months roll forward from the previous closing balance.

Seeded November 2026 figures:

| Item | Amount |
| --- | --- |
| Starting cash | ৳142,000 |
| Starting bank | ৳586,000 |
| Rent received | ৳202,120 |
| Total expenses | ৳71,790 |
| Closing cash | ৳226,660 |
| Closing bank | ৳631,670 |
| **Combined closing balance** | **৳858,330** |

---

## Tests

```powershell
php artisan test --compact
```

- `tests/Feature/StageOneScreensTest.php` covers all five screens, navigation,
  dashboard figures and month switching, the starting-balances modal, seeded
  building and tenant records, rent filtering and the bill drawer, cashbook
  actions, and the disabled export controls.
- `tests/Feature/StageTwoPortfolioTest.php` covers building registration and unit
  syncing, tenant registration and charge defaults, duplicate active tenancy
  prevention, ending a tenancy, rent totals, idempotent monthly billing, full and
  partial payments, later-month payment dates, invoice locking, and invoice
  snapshots.
- `tests/Feature/StageThreeFinancialsTest.php` covers manual starting balances,
  monthly rollover, Cash and Bank rent, Cash and Bank expenses, backdated
  transactions, expense create/edit/delete, historical adjustments, the dashboard
  save/warning flow, chronological month filtering, and the combined closing balance.

Code style:

```powershell
vendor/bin/pint --format agent
```

---

## Stage status

See [PROGRESS.md](PROGRESS.md) for the stage-by-stage log. Stages 1–3 are complete:
the visual layer, Buildings/Tenants/Rent Collection, and the live Cashbook,
expenses, and monthly balances. PDF/Excel export, receipt download, and
authentication are not implemented.
