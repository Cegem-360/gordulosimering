# Akciós ár a webshopban – terv

Dátum: 2026-09-25 · Állapot: jóváhagyott terv

## Cél

Az ERP-ben akciósnak jelölt termékeket a vevő akciós áron lássa és vásárolja meg. Ma az akció
csak egy százalék-címke a termékoldalon; a kártya, a kosár, a pénztár és a rendelés a teljes
nettó árral számol.

Élesen 7511 web-látható termék akciós (2026-09-25): 30% – 2434, 52% – 1826, 20% – 853,
50% – 829, … Akciós, de 0%-os termék jelenleg nincs.

## Üzleti szabály

- Egy termék akkor akciós, ha `is_on_sale = true` (az ERP „Akciós ?” oszlopa).
- Az érvényes kedvezmény:
  - akciós és `sale_percentage` 0 vagy üres → **10%**;
  - akciós és `sale_percentage` > 0 → az ERP-ben megadott százalék;
  - nem akciós → 0%.
- Az akciós nettó egységár: `net_selling_price × (1 − kedvezmény / 100)`, **egész forintra
  kerekítve** (kerekítés a legközelebbi egészre, `round()`).
- A számítás nettó áron történik; a „+ÁFA” megjelenítés változatlan.

## Megoldás

### Egyetlen forrás: `App\Models\Product`

Három számított attribútum (Eloquent `Attribute::get`), adatbázis-oszlop nélkül:

| Attribútum | Jelentés |
|---|---|
| `effective_sale_percentage` | Az üzleti szabály szerinti kedvezmény (0, 10 vagy az ERP-érték). |
| `sale_price` | Akciós nettó egységár, egész forintra kerekítve; nem akciósnál `null`. |
| `unit_price` | A fizetendő nettó egységár: akciósnál `sale_price`, egyébként `net_selling_price`. |

A szabály nincs mentve, így az ERP-szinkron (`app:sync-products`) nem írja felül, és a
százalék változása azonnal érvényes.

### Számítás

Minden vevőoldali ár a `unit_price`-ból jön:

- `App\Services\CartService` – kosár végösszeg;
- `App\Models\Cart`, `App\Livewire\Cart`, `App\Livewire\CartIcon` – kosár összegek;
- `App\Livewire\CheckOut` – végösszeg, és a rendelési tétel mentése: `total` = `unit_price`,
  `subtotal` = `unit_price × quantity`.

A rendelési tétel így megőrzi a vásárláskori (akciós) egységárat.

### Megjelenítés

Új Blade-komponens: `<x-product-price :product="$product" />` (opcionálisan `:quantity`).
Akciós terméknél: az eredeti ár áthúzva, mellette kiemelve az akciós ár és egy „-30%” címke;
nem akciósnál csak az ár. Felhasználási helyek:

- `livewire/product-card.blade.php`
- `livewire/products/show.blade.php` (a meglévő százalék-címke helyett)
- `livewire/live-search.blade.php`
- `livewire/cart-item.blade.php` (tételár és részösszeg)

A kosár és a pénztár összesítője kap egy „Megtakarítás: X Ft” sort, ha a megtakarítás > 0.
A megtakarítás = Σ (`net_selling_price` − `unit_price`) × mennyiség.

## Nem része

- Admin rendelésnézet (a mentett tételárat mutatja, nem kell módosítani).
- Az akciós termékek főoldali blokkja (külön feladat).
- Bruttó ár számítása akcióval (a webshop nettó árat mutat).

## Tesztelés

- Egységteszt a `Product` attribútumaira: 0% → 10%, üres → 10%, 30% → 30%, nem akciós →
  teljes ár, kerekítés (pl. 999 Ft × 52% kedvezmény → 480 Ft).
- Funkcionális tesztek: kosár végösszeg akciós áron; a rendelés akciós egységárat és
  részösszeget ment; a kártya és a termékoldal mutatja az áthúzott és az akciós árat;
  a kosár mutatja a megtakarítást.
