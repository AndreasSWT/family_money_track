# Family Money Track

## Overview (EN)
Family Money Track is a planned PWA budget tracker for households. The goal is a simple, modern, and elegant experience for recording expenses, tracking trends, and analyzing categories. A key feature is AI-assisted receipt processing from photos to speed up entry.

Current status: documentation only. See `PRD.md` and `milestones.md` for scope and delivery plan.

## Attekintes (HU)
A Family Money Track egy tervezett, csaladi hasznalatra keszulo PWA koltsegvetes-koveto. Celja az egyszeru, modern, elegans hasznalat, gyors kiadasrogzitessel, grafikonokkal es kategoriak szerinti elemzessel. Kulcs funkcio az AI-tamogatott blokkfoto feldolgozas.

Jelenlegi allapot: csak dokumentacio. Lasd `PRD.md` es `milestones.md`.

## Planned Stack
- Backend: PHP 8.4 (Laravel 11 vagy minimalis API)
- Frontend: Vanilla JS + Charting (Chart.js vagy ApexCharts)
- Database: MySQL/MariaDB
- PWA: Service Worker, offline cache, sync queue

## Repository Notes
- This repository does not yet contain application code.
- Contribution guidelines live in `AGENTS.md`.
- Secrets must go to `.env` and never be committed.

## Next Steps
- Finalize OCR provider and roles.
- Scaffold PHP app structure when ready.
- Add tests and CI once code exists.
