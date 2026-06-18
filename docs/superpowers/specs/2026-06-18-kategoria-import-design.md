# Kategória-import (WEB_2.0.tsv) — terv

**Dátum:** 2026-06-18
**Állapot:** jóváhagyott terv, implementáció előtt

## Cél

A `WEB_2.0.tsv` egy változó mélységű (2–5 szintű) termék-kategóriafát tartalmaz. Importáljuk
a fát a `product_categories` táblába (szülő-gyermek kapcsolattal), és kössük hozzá a
`termekek.tsv`-ből betöltött termékeket a kategórialevelekhez név-alapú (substring) párosítással.

## Forrásadat: WEB_2.0.tsv

- 2711 adatsor, **5 oszlop**, tab-elválasztás, **CRLF (`\r\n`) sorvég**.
- Változó mélység: a sor "csomópontja" a legmélyebb kitöltött oszlop; a bal oldali oszlopok
  öröklődnek a fentebbi sorokból. Egy új érték az `i`. oszlopban érvényteleníti a mélyebb
  (`i+1..5`) örökölt értékeket.
- 16 főkategória. A **FORGALMAZOTT MÁRKÁINK** ág különleges: csak márkanevek a 2. oszlopban.
- **15 teljesen üres sor** (csak tabok/`\r`) — al-blokk elválasztók; ki kell hagyni őket.
- Minden mezőt trimmelni kell (`\r` és whitespace).

## Forrásadat: termekek.tsv

- ~50 532 termék, fejléccel, tab-elválasztás. A terméknév a 4. oszlop (index 3).
- A `ProductSeeder` tölti be (már létező minta). A linkeléshez vissza kell állítani
  (`git restore database/data/termekek.tsv`), mert jelenleg törölve van a working tree-ből.

## 1. Séma-javítások (előfeltétel)

A `product_categories` tábla jelenleg **nem létezik** (a migráció rossz névvel, `categories`-ként
hozta létre, ami nem egyezik a modellel). Mivel nincs éles adat, a **meglévő migrációkat
módosítjuk** és `migrate:fresh`-sel állítjuk helyre.

1. **`0001_01_01_000002_create_product_categories_table.php`**
   - `Schema::create('categories', ...)` → `Schema::create('product_categories', ...)`.
   - `down()` már `product_categories`-t dropol — marad.
2. **`App\Models\Category`**
   - `parentCategory()`: explicit FK → `belongsTo(self::class, 'category_id')`.
   - Új `children()` reláció: `hasMany(self::class, 'category_id')`.
3. **Új pivot migráció** — `category_product` tábla:
   - `foreignId('category_id')->constrained('product_categories')->cascadeOnDelete()`
   - `foreignId('product_id')->constrained()->cascadeOnDelete()`
   - `unique(['category_id', 'product_id'])`
   - A `belongsToMany` alapértelmezett pivot-neve (`category_product`) és FK-i (`category_id`,
     `product_id`) ezzel egyeznek.

## 2. Adatfájl áthelyezése

- `WEB_2.0.tsv` → `database/data/web_kategoriak.tsv` (a `termekek.tsv` mintájára), git mv-vel.

## 3. Kategóriafa felépítése

Outline-parser a `database/data/web_kategoriak.tsv` fölött:

- Tartsunk fenn egy 5 elemű `path` tömböt (oszloponkénti aktuális csomópont-modell).
- Soronként (CRLF/whitespace trim után):
  - Ha a sor teljesen üres → kihagyjuk (elválasztó).
  - Balról jobbra: ha az `i`. oszlopnak van értéke, az új csomópont ezen a szinten,
    és a `path[i+1..5]` érvénytelenné válik.
  - Az adott sor csomópontja = a legmélyebb kitöltött oszlop. Szülője = a tőle balra eső
    legközelebbi kitöltött szint csomópontja (`null`, ha főkategória).
- Minden csomópont egy `product_categories` rekord:
  - `name` = a cella szövege.
  - `slug` = a **teljes útvonalból** képzett egyedi slug (pl.
    `csapagyak-golyos-csapagy-melyhornyu-...`), hogy az azonos nevű csomópontok különböző
    ágakban ne ütközzenek. Slug-ütközés esetén `-2`, `-3` … utótag.
  - `category_id` = szülő rekord id-ja.
- **Idempotens**: `updateOrCreate(['slug' => ...], [...])`, így a parancs újrafuttatható.
- A FORGALMAZOTT MÁRKÁINK ág is kategóriaként importálódik (márkák levélként).

## 4. Termék-összekötés (substring)

Csak `--link` kapcsolóval fut.

- Csak a **levélkategóriákhoz** (gyermek nélküli csomópont) kötünk terméket.
- Egy termék a levélhez kerül, ha **kis-/nagybetű-érzéketlenül**:
  a terméknév tartalmazza a levél nevét, **vagy** a levél neve tartalmazza a terméknevet.
- Teljesítmény: **levelenként egy DB `LIKE` lekérdezés** a `products.name` fölött
  (~levélszám lekérdezés, nem 50e×levélszám összehasonlítás). A "fordított" eset
  (terméknév ⊂ levélnév) a 381 pontos és a hosszú-levél eseteket fedi; ezt is `LIKE`-kal
  vagy a lekérdezett halmazon belüli szűréssel kezeljük.
- Pivot feltöltése `syncWithoutDetaching`-gel (egy termék több kategóriához is tartozhat;
  újrafuttatható).
- A FORGALMAZOTT MÁRKÁINK ágra **nem** futtatunk linkelést (jövőbeli lépés: `supplier` mező
  alapú párosítás).

## 5. Megvalósítás formája

- Dedikált artisan parancs: **`php artisan app:import-categories`**
  - Opció: `--link` — a kategóriafa után a termék-összekötést is lefuttatja.
  - Haladásjelzés a `ProductSeeder` stílusában (`$this->info`/progress).
  - Laravel 12: a parancs auto-regisztrálódik az `app/Console/Commands/` alól.
- A parser-logika kiszervezhető testreható egységbe (pl. külön metódus/akció), hogy a
  fixture-alapú teszt közvetlenül hívhassa.

## 6. Tesztelés (Pest, kötelező)

Feature teszt kis fixture-TSV-vel (`tests`-ből generált ideiglenes fájl):

- Helyes fa-építés: csomópontok száma, `name`/`slug`/`category_id` értékek.
- Szülő-gyermek linkek (parentCategory/children).
- Változó mélység és öröklődés helyessége (új érték törli a mélyebb szinteket).
- Üres elválasztó sorok kihagyása; CRLF trimmelés.
- Slug-egyediség azonos nevű, különböző ágú csomópontoknál.
- Substring-linkelés: illeszkedő termék bekerül, nem illeszkedő nem; csak levélhez köt.
- Idempotencia: kétszeri futtatás nem duplikál.

## Nyitott pontok / jövőbeli munka

- FORGALMAZOTT MÁRKÁINK termék-linkelése a `supplier` mező alapján.
- A substring-párosítás zajos lehet (egy termék több kategóriába kerülhet); a fa minőségének
  átnézése után finomítható (pl. csak a legspecifikusabb illeszkedés).
