# Repository Guidelines

## Project Structure & Module Organization
This repository currently contains planning docs only:
- `PRD.md` product requirements
- `milestones.md` delivery plan
- `AGENTS.md` contributor guide

No source code, tests, or assets are present yet. When code is added, use:
- `src/` for PHP application code
- `public/` for web entrypoint and static files
- `assets/` for design assets
- `tests/` for automated tests

## Build, Test, and Development Commands
There are no build or test commands defined yet. When tooling is added, document it here and in a README. Example local run pattern (only if `public/` exists): `php -S localhost:8000 -t public`.

## Coding Style & Naming Conventions
- Documentation is Hungarian and ASCII (no accents). Keep headings short and consistent.
- PHP: PSR-12, 4-space indent, `declare(strict_types=1)` where practical.
- JS: 2-space indent, semicolons, `camelCase` variables and `PascalCase` classes.
- Files: `kebab-case` for filenames; DB columns: `snake_case`.

## Testing Guidelines
No testing framework is configured. When tests are introduced, place them under `tests/` and follow naming: `*Test.php` for PHP and `*.spec.js` for JS. Include test steps in PRs when automated tests are not yet available.

## Commit & Pull Request Guidelines
No git history is available to infer conventions. Use Conventional Commits (e.g., `feat:`, `fix:`, `docs:`). PRs should include a concise summary, scope of changes, testing notes, and screenshots for UI changes.

## Security & Configuration Tips
Store secrets in `.env` and keep it out of version control. Do not commit receipt images or OCR outputs; store them in runtime storage outside the repo and document retention rules.
