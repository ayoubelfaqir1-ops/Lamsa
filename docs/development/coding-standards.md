# Coding Standards & Contribution Guidelines

> Code style, static analysis, Git branching workflows, and PR requirements for the Lamsa.ma engineering team.

---

## 1. Code Style & Formatting (Laravel Pint)

Lamsa strictly enforces **PSR-12** and Laravel standard conventions using **Laravel Pint**.

### Running the Linter
Before committing any PHP code, check for formatting issues:
```bash
# Check formatting without modifying files (used in CI)
vendor/bin/pint --test

# Automatically fix all formatting and whitespace issues
vendor/bin/pint
```

### Core Style Principles:
- **Strict Typing:** Always declare parameter types and return types on methods and functions:
  ```php
  public function findBySlug(string $slug): ?Product
  ```
- **Readonly Properties:** Prefer `private readonly` dependency injection in service and controller constructors.
- **Short Closures:** Use short arrow functions (`fn () => ...`) when single-expression logic is readable.
- **No Unused Imports:** Unused `use` statements are automatically flagged and stripped by Pint.

---

## 2. Static Analysis (PHPStan / Larastan)

We use **Larastan** (PHPStan wrapper for Laravel) to catch type errors, undefined model attributes, and null-pointer bugs before runtime:

```bash
vendor/bin/phpstan analyse --memory-limit=2G
```

- Target Level: Level 5 or higher.
- Never suppress type errors with `@phpstan-ignore` unless accompanied by an explanatory code comment.

---

## 3. Git Workflow & Branching Strategy

We follow a structured **Feature-Branch Workflow**:

```
main (Production-ready branch)
  ▲
  │ Pull Request (passes CI: Pint + PHPStan + Tests)
  │
feature/production-readiness
```

### Branch Naming Conventions:
- `feature/<short-description>`: New domain feature or API capability.
- `fix/<issue-description>`: Bug fix or edge-case correction.
- `refactor/<target-area>`: Code restructuring with zero functional changes.
- `docs/<topic>`: Documentation updates or new guides.
- `ops/<task>`: Infrastructure, Nginx, CI/CD, or deployment configurations.

---

## 4. Conventional Commits

Commit messages must follow the [Conventional Commits](https://www.conventionalcommits.org/) specification:

```
<type>(<scope>): <short imperative description>
```

### Supported Types:
- `feat`: A new user-facing or API feature (e.g. `feat(auction): add proxy bidding engine`).
- `fix`: A bug fix (e.g. `fix(store): resolve slug collision counter`).
- `docs`: Documentation changes (e.g. `docs(ops): add production checklist`).
- `style`: Formatting changes that do not affect code logic (Pint fixes).
- `refactor`: Code change that neither fixes a bug nor adds a feature.
- `test`: Adding missing tests or correcting existing tests.
- `perf`: Code change that improves performance or query efficiency.

---

## 5. Pull Request (PR) Checklist

Before submitting a PR to merge into `main`, verify:

- [ ] All 201 automated tests pass: `php artisan test`
- [ ] Code is formatted with Pint: `vendor/bin/pint --test`
- [ ] Larastan passes with 0 errors: `vendor/bin/phpstan analyse`
- [ ] Any new API endpoint is covered by a corresponding Feature test in `Modules/<Domain>/tests/Feature`.
- [ ] Relevant documentation under `docs/` or `Modules/<Domain>/README.md` is updated.

