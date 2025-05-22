# AGENTS.md

## Overview
These instructions guide Codex when reviewing or modifying this repository.

## General style
- Write concise comments and documentation in Spanish.
- Follow PSR‑12 coding standards for PHP files.
- Favor dependency injection over direct instantiation (`new`).

## Code review tasks
- Detect direct `new` instantiations of services, repositories or other components that hinder mocking in tests. Propose refactoring to remove them.
- Look for logic errors in the newly added code that could cause inconsistencies.
- Detect antipatterns or poorly optimized code in the new changes.
- Suggest refactorings when appropriate as long as they do not conflict with existing code.
- Include a short explanation for each finding.

## Testing and CI
- Always run `php artisan test` before committing.
- If a workflow file changes, ensure `.github/workflows/codex-qa.yml` remains valid.

## Project specifics
- Custom Artisan commands reside in `app/Console/Commands/*` and should return proper exit codes instead of calling `exit()`.
- Generated artifacts (e.g., unified code, Swagger specs, test files) go in `storage/app/route-analysis` and must not be committed.
- Architectural rules are defined in `nl_rules.txt`. The Codex reviewer should verify that `bb:app:is-cool` references this file.

## Pull Request messages
Each PR summary should mention:
1. Major changes made.
2. Any new commands or configuration options added.
3. Test results or limitations (e.g., failures due to missing dependencies).

---
