# Repository Guidelines

## Project Structure & Module Organization
- `src/` holds the library code under the `ALI\\TextTemplate\\` namespace (e.g., `TemplateResolver/`, `MessageFormat/`, `TextTemplateFactory.php`).
- `tests/unit/` contains PHPUnit tests; `tests/_bootstrap.php` wires test bootstrap.
- `guides/` documents template/function syntax (`guides/FUNCTIONS_SYNTAX.md`).
- `composer.json` defines dependencies and PSR-4 autoloading; treat it as the source of truth for PHP requirements.

## Build, Test, and Development Commands
No build step is required; this is a Composer-managed PHP library.
```bash
composer install            # install dev dependencies
./vendor/bin/phpunit        # run the full test suite
```
If you change autoloaded classes, re-run tests to ensure the resolver and handlers still pass.

## Coding Style & Naming Conventions
Follow existing PSR-12-ish formatting: 4-space indents, class-per-file, and namespaces mirroring the `src/` tree. Use typed properties and type hints where already established, but do not introduce `declare(strict_types=1)` unless the file already uses it. Class names are StudlyCase and match filenames; tests end with `*Test` and live in `tests/unit` under the `ALI\\TextTemplate\\Tests` namespace.

## Testing Guidelines
Tests use PHPUnit (`phpunit.xml.dist`). Name test methods with `test...` and keep each test focused on a single behavior (e.g., a handler edge case or template parsing rule). When you add a new handler or resolver, add unit coverage under the matching directory path in `tests/unit`.

## Commit & Pull Request Guidelines
Recent commits use short, single-line summaries that start with a verb (e.g., “added”, “fixed”, “updated”, “refactoring”), often in lowercase and sometimes with backticks for identifiers. Match that style and keep messages concise.
For PRs, include a clear summary, test command(s) run, and any behavior or syntax changes. If you touch template syntax or handlers, update `README.md` and `guides/FUNCTIONS_SYNTAX.md` as needed.

## Configuration Notes
Runtime requires PHP `>=7.4 <8.5` and the `ext-intl` extension. Note any locale-sensitive behavior when changing pluralization or language-specific handlers.
