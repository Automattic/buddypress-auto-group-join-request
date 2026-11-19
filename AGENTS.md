# BuddyPress Auto Group Join Request

Automatically processes BuddyPress group join requests.

## Project Knowledge

| Property | Value |
|----------|-------|
| **Main file** | `buddypress-auto-group-join-request.php` |
| **Text domain** | `buddypress-auto-group-join-request` |
| **Namespace** | `Automattic\BuddyPressAutoGroupJoinRequest` |
| **Source directory** | Root level (single-file plugin) |
| **Version** | 1.0.0 |
| **Requires PHP** | 8.2+ |

### Directory Structure

```
buddypress-auto-group-join-request/
├── tests/
│   ├── Unit/               # Unit tests
│   └── Integration/        # Integration tests (wp-env)
├── languages/              # Translation files
├── .github/workflows/      # CI: cs-lint, integration
└── .phpcs.xml.dist         # PHPCS configuration
```

### Dependencies

- **Runtime**: BuddyPress (required — plugin does nothing without it)
- **Dev**: `automattic/vipwpcs`, `yoast/wp-test-utils`

## Commands

```bash
composer cs                # Check code standards (PHPCS)
composer cs-fix            # Auto-fix code standard violations
composer lint              # PHP syntax lint
composer test:unit         # Run unit tests
composer test:integration  # Run integration tests (requires wp-env)
composer test:integration-ms  # Run multisite integration tests
composer coverage          # Run tests with HTML coverage report
```

## Conventions

See `CONTRIBUTING.md` for setup, checks and the contribution workflow. Key points:

- **Commits**: Every commit must be signed; `develop` and `main` reject unsigned commits. Use a [Conventional Commits](https://www.conventionalcommits.org/) prefix (`fix:`, `feat:`, `test:`, `docs:`, `ci:`, `chore:` and so on) with an imperative subject of about 50 characters, and a body that explains why the change was needed rather than what changed. One concern per commit.
- **PRs**: Branch from `develop` and open the PR against it. The title describes the outcome in sentence case, without a conventional commit prefix. The description explains the problem first, then how the PR solves it. PRs land as merge commits (squash and rebase merging are disabled), so every commit message reaches `develop` as written.
- **Testing**: Write integration tests for WordPress-dependent behavior, unit tests for isolated logic. Integration tests extend `Tests\Integration\TestCase` (built on `Yoast\WPTestUtils\WPIntegration\TestCase`); unit tests extend `Tests\Unit\TestCase` (built on `Yoast\WPTestUtils\BrainMonkey\TestCase`).
- **Code style**: WordPress coding standards via PHPCS. Tabs for indentation.
- **i18n**: All user-facing strings must use the `buddypress-auto-group-join-request` text domain.

## Architectural Decisions

- **Single-file plugin**: All plugin logic lives in the main PHP file at the root. This is intentional given the plugin's small scope — do not split into multiple classes unless complexity genuinely warrants it.
- **BuddyPress dependency**: The plugin hooks into BuddyPress group join workflows. It assumes BuddyPress is active and does not provide fallback behaviour without it.
- **Incremental modernization**: The plugin needs modernization work. Improvements are welcome but should be incremental.

## Common Pitfalls

- Do not edit WordPress core files or BuddyPress core files.
- This plugin depends on BuddyPress — test environments must have BuddyPress installed and active.
- Run `composer cs` before committing. CI will reject code standard violations.
- Integration tests require `npx wp-env start` running first.
- Do not add complex class hierarchies to a single-file plugin. If the plugin grows significantly, propose a restructure rather than bolting on classes.
