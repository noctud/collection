---
searchTitle: Versioning, stability and backward compatibility
---

# Versioning & Stability

**Noctud Collection is stable and production-ready.** The `0.x` version number does not mean beta. Each `0.MINOR` series is a stable major version: `0.2.x` is what other projects would call `2.x`, and nothing within a series breaks your code.

## How Releases Work

Composer already treats the minor number of a `0.x` package as the major one. The constraint `^0.2` resolves to `>=0.2.0 <0.3.0`, exactly like `^2.0` resolves to `>=2.0.0 <3.0.0`.

| Change | Example | What it can contain |
|---|---|---|
| New series | `0.1` → `0.2` | Breaking changes, each listed in an upgrade guide |
| Release within a series | `0.2.0` → `0.2.1` | New features and bug fixes, never a breaking change |

`composer require noctud/collection` writes `^0.2` into your `composer.json`, so `composer update` only installs releases that keep your code working.

## Breaking Changes

Breaking changes happen only between series, and they never come as a surprise:

- Every break is listed with before/after code in an upgrade guide, such as [UPGRADE-0.2.md](https://github.com/noctud/collection/blob/0.2.x/UPGRADE-0.2.md).
- The last release of the old series ships the replacement APIs and deprecation notices. On `0.1.8` you can fix everything the 0.2 upgrade guide lists, then bump the constraint to `^0.2` with no further changes.
- PHPStan reports the affected calls, so you don't have to search for them by hand.

## Track Record

- **0.1.x**: nine releases, from `0.1.0` in March 2026 to `0.1.8` in October 2026, with no breaking changes.
- **0.2.x**: `0.2.0` was released in October 2026 and follows the same rules.

Over 6,900 tests run on PHP 8.4, 8.5 and 8.6, and the code passes PHPStan level 9.

## Supported Versions

| Series | Status |
|---|---|
| `0.2.x` | Active — new features and bug fixes |
| `0.1.x` | No longer maintained, see the [upgrade guide](https://github.com/noctud/collection/blob/0.2.x/UPGRADE-0.2.md) |

## When Is 1.0?

`1.0` is planned for mid 2027. It follows the same rules as every series so far: any breaking changes come with an upgrade guide, and the last `0.x` release ships the new APIs first. Until then, `0.2.x` is the stable series to use.
