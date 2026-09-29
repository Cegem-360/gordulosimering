# Attribútumszűrők a termékkategóriák és a terméklista oldalán

Dátum: 2026-09-29

## Cél

A `/termekkategoriak` (és a közös szűrősávon keresztül a `/termekek`) oldalon a vevő
szűrhessen méret (tól–ig), márka, anyag és termékcsoport szerint. Kézi adatfeltöltés
nélkül: az attribútumokat a meglévő termékadatokból (név, méret, Integra-csoport)
számoljuk ki, és minden Integra-szinkron után frissek.

## Háttér

- A termékeken nincs strukturált attribútum; az Integra7-ben sincs (csak név, méret,
  csoport- és típuskód).
- A `size` mező ~81%-ban kitöltött; 19 288 web-látható termék `d×D×B` formátumú
  (pl. `25X47X8`), ~800 kételemű, ~2 900 szabad szöveg.
- A márka a név első szava (SKF, KOYO, INA, FAG…), az anyag a névben szerepel
  (NBR, VITON, EPDM, PTFE, rozsdamentes).
- A termékcsoport (`group_code`) és neve (`discount_groups.name`) 2026-09-29 óta az
  Integra7-ből jön (`025183a`).

## 1. Adatréteg

### Új oszlopok a `products` táblán

Mind nullable és indexelt:

| Oszlop | Típus | Forrás |
|---|---|---|
| `inner_diameter` | decimal(10,3) | méret 1. száma |
| `outer_diameter` | decimal(10,3) | méret 2. száma (csak háromelemű méretnél) |
| `width` | decimal(10,3) | méret 3. száma (csak háromelemű méretnél) |
| `brand` | string | név első szava, fix márkalistából |
| `material` | string | névben talált anyag |

### `App\Services\ProductAttributeExtractor`

Egyetlen feladat: a `name` és `size` alapján visszaadja a fenti öt értéket
(`array{inner_diameter: ?float, outer_diameter: ?float, width: ?float, brand: ?string, material: ?string}`).

**Méret**
- A méret elejéről olvas: `szám X szám [X szám]`, kis/nagy `x` egyaránt, a
  tizedesvessző pontnak számít.
- Háromelemű: d, D, B. Pl. `25X47X8` → 25 / 47 / 8; `60,32X82,55X9,52` → 60.32 / 82.55 / 9.52.
- Minden szám után a toldalék elhagyva: `65X85X7/7,5` → B = 7;
  `25X62X25 DOMBORÚ` → 25 / 62 / 25; `100X157X42/34 (VKHB 2032)` → 100 / 157 / 42.
- Kételemű (`18X1,2`, `32X4`, `10X24`): csak d, mert a második szám termékenként
  mást jelent (vastagság vagy külső átmérő).
- Minden más (`A28,5`, `PHP 4SPA315TB`, üres): mind a három üres.
- Befejezetlen harmadik elem (`85,725X136,525X`) kételeműként kezelendő.

**Márka**
- A név első szava (vesszőig/szóközig), nagybetűsítve, egy fix listához illesztve.
- Lista: SKF, KOYO, INA, FAG, ZKL, NTN, TIMKEN, NSK, IKO, SNR, CORTECO, SIMRIT,
  SEEGER, LOCTITE, NORMA, TENTE, EZO, NACHI, REXROTH, HIWIN, BETA, BAHCO, AMES,
  NILOS, BECO, KS, WSW, STIEBER, DURACELL.
- Összevonások: `ZKL/ZVL` → ZKL, `SKF/Ewellix` → SKF (a `/` előtti rész számít).
- Nem listázott első szó (KELETI, Gumiházas, O-gyűrű…) → üres.

**Anyag** (az első találat, kis/nagybetű-független, egész szóra)

| Névben | Érték |
|---|---|
| NBR | NBR |
| VITON, FKM | FKM (Viton) |
| EPDM | EPDM |
| PTFE | PTFE |
| rozsdamentes | Rozsdamentes |

### Frissítés

- A `Product` modell `saving` eseménye újraszámolja az öt oszlopot, ha a `name` vagy
  a `size` változott (vagy új a termék). Az `Integra7Syncer` `save()`-et hív, így a
  szinkron után is frissek.
- `app:extract-product-attributes` parancs: a meglévő termékek visszamenőleges
  feltöltése 1000-es darabokban, csak a változott sorokat írja; kiírja a frissített
  darabszámot. Élesen deploy után egyszer kell lefuttatni.

## 2. Szűrők

A közös `App\Livewire\Concerns\FiltersProducts` trait bővül, így mindkét oldal
(`Products\Index`, `Products\Categories\Index`) megkapja.

### Sorrend a szűrősávban

1. Készlet (meglévő)
2. Kategória (meglévő)
3. **Termékcsoport** (új)
4. **Méretek (mm)** (új)
5. Méret (meglévő, szöveges keresés)
6. **Márka** (új)
7. **Anyag** (új)

### Jelölőnégyzetes szűrők: Márka, Anyag, Termékcsoport

- A `selectedFilters` új kulcsai: `group`, `brand`, `material`.
- A meglévő mintát követik: darabszámmal, darabszám szerint csökkenően, az első 5
  látszik, a többit az „Összes mutatása” nyitja.
- Csak a legalább egy termékkel rendelkező értékek jelennek meg.
- Termékcsoport: az érték a csoport **neve**; az azonos nevű kódok (S2, S3, S5 =
  „SKF csapágy”) egy tételt adnak, a szűrés az összes hozzá tartozó kódra megy
  (`whereIn('group_code', …)`). A „Megszűnt termék” csoport kimarad, a név nélküli
  csoportok is.

### Méretek (mm)

- Új `public array $dimensionRanges` állapot:
  `['inner_diameter' => ['min' => null, 'max' => null], 'outer_diameter' => […], 'width' => […]]`.
- Soronként két számmező (tól / ig), `wire:model.live.debounce.500ms`; a
  helykitöltő a szűretlen lista legkisebb és legnagyobb értéke.
- Üres mező = nincs határ; csak alsó vagy csak felső határ is megadható.
- Érvénytelen, nem szám vagy negatív érték figyelmen kívül marad, hibaüzenet nélkül.
- Tartomány megadásakor a méretadat nélküli termékek kiesnek (`whereBetween` /
  `>=` / `<=` a nem null értékeken).
- Változáskor a lapozás az első oldalra ugrik (mint a többi szűrőnél).

### Aktív szűrők és törlés

- Az aktív szűrő címkék a Márka/Anyag/Termékcsoport értékeit a meglévő módon
  mutatják; a tartományok külön címkét kapnak („Belső átmérő: 20–30 mm”,
  „Szélesség: 10 mm-től”), egyenként törölhetők.
- A `resetProductFilters()` a tartományokat is üríti, így a „Szűrők törlése” gomb
  mindent alaphelyzetbe állít.

### Darabszámok

A meglévő viselkedés marad: a darabszám a szűretlen alaplistából számolódik (nem a
többi kiválasztott szűrő szerint szűkítve). A szűkülő darabszám nincs a hatókörben.

## Hatókörön kívül

- Kézi attribútum-szerkesztés az adminban, általános attribútumtábla.
- Termék adatlapján az attribútumok megjelenítése.
- Szűrők a `/termekkategoriak/{slug}` kategória-oldalon.
- URL-ben megosztható szűrőállapot.
- O-gyűrűk méretének kinyerése a termékkódból (a legtöbbnek üres a `size`-a).

## Tesztelés

- Unit: `ProductAttributeExtractor` – datasetekkel a fenti méret-, márka- és
  anyagmintákra, köztük a szélső esetekre (üres, szabad szöveg, kételemű,
  befejezetlen, toldalékos, `ZKL/ZVL`, `SKF/Ewellix`, KELETI).
- Feature: a `Product` mentése kitölti/frissíti az oszlopokat; az
  `app:extract-product-attributes` feltölti a meglévő termékeket és a változatlanokat
  nem írja.
- Livewire: `Products\Categories\Index` és `Products\Index` – szűrés márkára,
  anyagra, termékcsoportra (azonos nevű kódok összevonva), tartományra (alsó, felső,
  mindkettő, érvénytelen érték); az opciók darabszáma; címkék; a „Szűrők törlése”
  a tartományt is üríti.
