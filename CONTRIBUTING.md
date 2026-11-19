# Contributing to BuddyPress Auto Group Join Request

Thank you for your interest in contributing. This document covers setting up a local environment, running the checks, and proposing a change.

## Code of Conduct

This project follows the [Automattic Code of Conduct](https://automattic.com/code-of-conduct/).

## Development Setup

### Prerequisites

- [Node.js](https://nodejs.org/) LTS or later
- [Docker](https://www.docker.com/products/docker-desktop)
- [Composer](https://getcomposer.org/)
- [wp-env](https://www.npmjs.com/package/@wordpress/env), installed globally: `npm install -g @wordpress/env`

### Setup

1. Clone the repository, keeping the default `buddypress-auto-group-join-request` directory name. The integration test scripts look for the plugin under that name inside wp-env.
2. Install dependencies:
   ```bash
   composer install
   ```
3. Start the WordPress environment:
   ```bash
   wp-env start
   ```
4. Visit http://localhost:8888 and log in with `admin` / `password`.

### Checks

```bash
composer lint                # PHP syntax
composer cs                  # Coding standards
composer test:integration    # Integration tests (single site)
composer test:integration-ms # Integration tests (multisite)
```

## Workflow

1. Create a branch from `develop`:
   ```bash
   git checkout develop
   git pull origin develop
   git checkout -b fix/short-description
   ```
2. Make your change, with tests.
3. Run the checks above.
4. Commit with a [Conventional Commits](https://www.conventionalcommits.org/) prefix (`fix:`, `feat:`, `test:`, `docs:` and so on) and a body that explains why the change was needed.
5. Push your branch and open a pull request against `develop`.

## Code Standards

We follow the [WordPress VIP Coding Standards](https://github.com/Automattic/VIP-Coding-Standards), configured in `.phpcs.xml.dist`. Run `composer cs-fix` to fix what PHPCS can fix automatically.

## Tests

Integration tests live in `tests/Integration/` and run inside wp-env.

- New behavior should come with tests.
- A bug fix should come with a test that fails without the fix.
- Name tests after the behavior they check, for example `test_matching_profile_value_sends_join_request()`.

## Pull Requests

- Keep each pull request to one change. Title it with the outcome, in sentence case, and explain the problem first, then how the change solves it.
- Reference related issues, for example `Fixes #123`.
- Make sure CI passes before requesting a review.
- **Sign your commits.** `develop` and `main` only accept commits with a verified signature, so a pull request with an unsigned commit cannot be merged until it is re-signed and force-pushed. See GitHub's guide to [signing commits](https://docs.github.com/en/authentication/managing-commit-signature-verification/signing-commits).
- Pull requests are merged with a merge commit, so each commit message reaches `develop` as written.

## Releases

Maintainers cut releases from a `release/x.y.z` branch into `main`.
