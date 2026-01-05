# PRD — Egyszeru csaladi PWA koltsegvetes-keszito es koveto (PHP 8.4, JS)

## 1. Cel es hatter
Egy egyszeru, gyorsan hasznalhato, csaladi fokuszu PWA-t tervezunk, amely segit a haztartas minden tagjanak a koltsegek rogziteseben, koveteseben es elemzeseben. A kulonlegesseg az AI-tamogatott blokkolvasas: foto alapjan automatikus vasarlasi blokkok feldolgozasa, a tetelkategoriak felajanlasa, valamint koltsegtrendek es grafikonok megjelenitese.

## 2. Celcsoport
- Csaladok es haztartasok, ahol tobben rogzitenek kiadasokat.
- Maganszemelyek, akik egyszeruen szeretnek havi koltsegvetest kovetni csaladi szinten.
- Olyan felhasznalok, akik mobilon szeretnek rogziteni blokkokat.

## 3. Fobb celok (KPI iranyok)
- 1 perc alatt letrehozhato es rogzitheto egy blokk alapjan 1 kiadasi bejegyzes.
- 80%+ OCR pontossag alap tetelnev, osszeg es datum mezokben.
- 70%+ felhasznalo heti visszateres 1 honapon belul.
- A csaladi tagok legalabb 2/3-a aktiv havi rogzito.

## 4. Fobb funkciok (MVP)
- Regisztracio es bejelentkezes (email + jelszo)
- Csaladi haztartas letrehozas, egyszeru meghivo link vagy QR-kod alapjan csatlakozas csaladtagoknak
- Szerepkorok: owner / editor / viewer (haztartason beluli jogosultsagok)
- Koltsegek rogzitese: manualis + blokk foto feltoltes
- AI tamogatott blokk feldolgozas: tetelnev, datum, osszeg, elado, es kategoriak javaslata
- Kategoriak: testreszabhato (pl. elelmiszer, kozlekedes, lakas, stb.) csaladi szinten
- Koltsegkovetes grafikonokon: havi bontas, kategoriak szerint, csaladi szurok
- Alap koltsegelemzes: top kategoriak, trendek, szokasos kiadasok
- Offline mod (PWA): legutobbi adatok elerese, uj bejegyzes sorba allitasa

## 5. Kiegeszito funkciok (Nice-to-have)
- Kiadasi limit beallitas es figyelmeztetes (csaladi szinten)
- Ismetlodo kiadasok kezelese
- CSV export
- AI tamogatott megtakaritasi javaslatok (anonimizalt trend alapjan)
- Csaladtagi profilok (gyerek mod, csak rogzites, limitelt nezeti jog)

## 6. Folyamatok
### 6.1. Koltseg rogzites blokkal
1. Felhasznalo feltolti blokk fotojat
2. AI feldolgozas: OCR + tetel elemzes
3. Felhasznalo ellenorzi / szerkeszti teteladatokat
4. Ment es rogzites

### 6.2. Koltseg rogzites manualisan
1. Osszeg, kategoria, datum kitoltese
2. Ment es rogzites

### 6.3. Csaladtag csatlakozasa
1. Haztartas tulajdonos megoszt egy meghivo linket vagy QR-kodot
2. Csaladtag elfogadja es csatlakozik a haztartashoz

## 7. Technikai javaslat
- Backend: PHP 8.4, Laravel 11 vagy minimalis PHP API
- Frontend: Vanilla JS + Chart.js vagy ApexCharts
- Adatbazis: MySQL / MariaDB
- PWA: Service Worker, offline cache, push ertesites kesobb
- AI blokk feldolgozas: self-hosted OCR (Tesseract) + sajat parsing logika

## 8. Adatmodell (tervezet)
- users: id, email, password, created_at
- households: id, name, owner_user_id, created_at
- household_members: id, household_id, user_id, role, joined_at
- expenses: id, household_id, user_id, amount, category_id, date, description, created_at
- categories: id, household_id, name
- receipts: id, household_id, user_id, image_path, raw_text, parsed_data, created_at

## 9. UI/UX
- Mobil-first design
- Letisztult, elegans, modern vizualis stilus
- Egyszeru dashboard
- Gyors uj koltseg gomb
- Grafikon blokkok a fo oldalon

## 10. Nyitott kerdesek
- MVP-ben nincs banki szinkron; kesobb merlegelheto.

## 11. Meres es analitika
- Heti aktiv felhasznalok (WAU)
- OCR pontossag
- User retention
- Atlagos rogzitesi ido

## 12. Implementacio folyamata (milestone-ok)
- Milestone 0: Scope es alapdontesek (OCR valasztas, szerepkorok, adatvedelem)
- Milestone 1: Alapok (auth, haztartas es tagsag, meghivo link/QR, adatbazis)
- Milestone 2: Alap koltsegrogzites (manualis rogzites, kategoriak, listak, szurok)
- Milestone 3: PWA es offline (service worker, cache, offline sor es szinkron)
- Milestone 4: Grafikonok es elemzes (havi trend, kategoriabontas, top kiadasok)
- Milestone 5: Blokkfoto feldolgozas (feltoltes, OCR, parsing, ellenorzo UI)
- Milestone 6: Finomhangolas es kiadas (UX csiszolas, teljesitmeny, teszteles)
