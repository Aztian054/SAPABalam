<laravel-boost-guidelines>
# Laravel Boost Guidelines

## Foundation
- Laravel app on PHP 8.3. Use APIs matching the installed major version; confirm with `composer show --direct` / `composer show <vendor/package>` for PHP and `package.json` for JS.
- Activate relevant project skills in `**/skills/**` whenever working in that domain.
- Follow existing conventions; check sibling files for structure/naming. Use descriptive names (`isRegisteredForDiscounts`, not `discount()`). Reuse existing components.
- Do not create verification scripts or tinker when tests cover the behavior. Prefer unit/feature tests.
- Stick to the existing directory structure; do not add base folders or change dependencies without approval.
- Frontend changes not visible? Ask user to run `npm run build`, `npm run dev`, or `composer run dev`.
- Only create documentation files when explicitly requested. Be concise in replies.

## Boost Tools
- Prefer Boost MCP tools over shell/manual alternatives.
- `database-query`: read-only DB queries. `database-schema`: inspect tables before migrations/models. `get-absolute-url`: resolve project URLs before sharing. `browser-logs`: recent browser errors only.
- `search-docs` before changes depending on Laravel ecosystem APIs/behavior/version syntax; skip for copy-only edits. Pass `packages` scoping when known. Use broad topic queries without package names (e.g. `test resource table`). Query syntax: words = AND, `"quoted phrase"`, mix both, multiple queries for OR.

## Project Rules
- If `.ai/rules` exists: before planning or editing, read `.ai/rules/index.md`, every rule file covering the target path(s), and run `grep -rin 'keyword' .ai/rules`. Follow them before writing code.
- Record a rule with `record-rule` **only** when the user explicitly asks. Never record on your own initiative or as a byproduct.
- Artisan: run directly (`php artisan route:list`, `php artisan list`, `--help`). Read config via `php artisan config:show key` or `config/`.
- Tinker: use single quotes: `php artisan tinker --execute 'User::where("active", true)->count();'`. Do not create models without approval; prefer factories/tests.

## PHP
- Always use curly braces for control structures.
- Use PHP 8 constructor property promotion; no empty zero-parameter `__construct()` unless private.
- Explicit return types and parameter type hints.
- TitleCase Enum keys.
- Prefer PHPDoc blocks over inline comments (inline only for complex logic). Use array shape PHPDoc.

## Deployment
- Deploy via Laravel Cloud; activate `deploying-to-cloud` skill for Cloud deployment/config/CLI/troubleshooting.

## Laravel Way
- Use `php artisan make:` for new files; `make:class` for generic classes. Pass `--no-interaction` and correct `--options`.
- New models: also create factories/seeders (check `make:model --help`).
- APIs: default to Eloquent API Resources + versioning unless existing routes follow another convention.
- Links: prefer named routes and `route()`.
- Tests: use model factories and their states; use `$this->faker->...` or `fake()->...` per existing convention; create with `php artisan make:test [options] {name}`, `--unit` for unit (most tests should be feature).
- Vite manifest error: run `npm run build` or ask user for `npm run dev` / `composer run dev`.

## Pint
- After modifying PHP files, run `vendor/bin/pint --dirty --format agent`. Do not run `--test`; just fix.

## PHPUnit
- PHPUnit project. Create tests with `php artisan make:test --phpunit {name}`. Exclude suite dir from name (`SomeFeatureTest`, not `Feature/SomeFeatureTest`).
- Read `testing-best-practices` skill for coverage, naming, structure, isolation, review.
- Run narrowest coverage: `php artisan test --compact <file>` or `--filter=testName`; rerun after each change. Direct runner: `vendor/bin/phpunit` accepts same args.
</laravel-boost-guidelines>
