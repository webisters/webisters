# Contributing to Webisters Webisters

Thanks for taking the time to contribute. This guide covers how to report issues,
propose changes, and get a pull request merged.

By participating you agree to follow our [Code of Conduct](CODE_OF_CONDUCT.md).

## Ways to contribute

- Report a bug (use the **Bug report** issue template)
- Request a feature (use the **Feature request** issue template)
- Improve documentation (use the **Documentation** issue template)
- Pick up an issue labelled `good first issue` or `help wanted`

Before starting work on an existing issue, comment on it and wait to be assigned so
two people do not do the same work.

## Reporting security issues

Do **not** open a public issue for security vulnerabilities. Follow
[SECURITY.md](SECURITY.md) and email `thewebisters@gmail.com` instead.

## Requirements

- PHP >= 8.2
- Composer 2.x
- Extensions: `intl`, `sodium`, `gd`, `json`, `fileinfo`, `curl`, `mysqli`,
  `simplexml`, `dom`, `libxml`
- MariaDB/MySQL if you are running database-backed tests

## Getting set up

This repository is a standalone Composer package, so all commands run from the
repository root:

```bash
git clone https://github.com/webisters/webisters.git
cd webisters
composer install
```

Run the test suite:

```bash
vendor/bin/phpunit                                  # everything
vendor/bin/phpunit tests/SomeTest.php               # one file
vendor/bin/phpunit --filter testMethodName tests/SomeTest.php
```

Check and fix coding style:

```bash
vendor/bin/php-cs-fixer fix --dry-run --diff        # check only
vendor/bin/php-cs-fixer fix                         # apply fixes
```

Webisters is developed as a monorepo of independently versioned packages. If you
have the full monorepo checked out, projects consume libraries as symlinked path
repositories through `composer.local.json`; set `COMPOSER=composer.local.json`
before running Composer to develop against local sources.

## Coding standard

Style is centralised in the `webisters/coding-standard` package and enforced by
`.php-cs-fixer.dist.php`. Do not hand-format code; run the fixer.

- Every PHP file starts with `<?php declare(strict_types=1);` followed by the
  standard Webisters license header (php-cs-fixer manages the header, leave it alone).
- Library code uses the `Framework\<Component>` namespace; project code uses `App\`,
  tests use `Tests\`.
- Match the style of the surrounding code: naming, comment density, and structure.

## Making a change

1. Fork the repository and create a branch off `main`:
   `git checkout -b fix/short-description`
2. Make focused commits. One logical change per pull request.
3. Add or update tests for any behaviour change.
4. Run the tests and the coding standard fixer before pushing.
5. Update the README or docs if you changed public behaviour.

### Commit messages

Use short, imperative subject lines under 72 characters, optionally prefixed with the
area touched:

```
routing: fix trailing slash handling in RouteCollection

Longer explanation of why the change is needed, wrapped at 72 columns.

Closes #123
```

## Pull requests

- Fill out the pull request template.
- Link the issue the PR closes (`Closes #123`).
- Keep the PR scoped. Unrelated refactors, formatting sweeps, and dependency bumps
  belong in separate PRs.
- CI (tests + coding standard) must be green.
- A maintainer will review; address feedback by pushing new commits rather than force
  pushing over the review history where possible.

## Backwards compatibility

Webisters packages follow semantic versioning. Changes that break a public API
(signatures, removed methods, changed return types) must be called out explicitly in
the pull request description so they can be scheduled for a major release.

## Licence

Contributions are accepted under the MIT licence used by this repository.
