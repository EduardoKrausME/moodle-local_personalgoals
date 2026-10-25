# Changelog

This changelog documents **only the two most recent published GitHub releases** of `local_personalgoals`. Each release entry is based on the exact Git diff between its tag and the immediately preceding **published release** tag. No earlier or unreleased changes are attributed to these releases.

## [1.0.6] - 2026-10-19

Published: 2026-10-19 15:03:06 UTC.

Comparison: [1.0.5...1.0.6](https://github.com/EduardoKrausME/moodle-local_personalgoals/compare/1.0.5...1.0.6)

### Fixed: course-completion goal progress

- Updated `classes/service/course_progress.php` to check the authoritative Moodle course-completion record before using the potentially request-cached value returned by `core_completion\progress::get_course_progress_percentage()`.
- When course completion is enabled, the user is tracked for completion, and `completion_info::is_course_complete($userid)` reports a completed course, the progress service now returns **100.0%** immediately.
- This fixes the case in which an earlier progress read was cached in the same request before the course was marked complete, potentially preventing a course-completion goal from reaching its intended completed state.

### Tests

- Extended `tests/goal_api_test.php` to prime the course progress cache at **0.0%**, mark the course complete, and assert that the progress service returns **100.0%** and the associated course-completion goal is completed.

### Release workflow and metadata

- Added a `concurrency` configuration to `.github/workflows/release.yml` with `group: release-${{ github.ref }}`, `cancel-in-progress: false`, and `queue: max`.
- Updated `version.php` from `$plugin->version = 2026101500` / `$plugin->release = '1.0.5'` to `2026101900` / `1.0.6`.

### Scope

The comparison includes three commits and modifies only `.github/workflows/release.yml`, `classes/service/course_progress.php`, `tests/goal_api_test.php` and `version.php`. The minimum Moodle build (`2022112800`) is unchanged. No additional goal types, settings or user interface changes are evidenced by this tag diff.

## [1.0.5] - 2026-10-15

Published: 2026-10-15 20:31:53 UTC.

Comparison: [1.0.4...1.0.5](https://github.com/EduardoKrausME/moodle-local_personalgoals/compare/1.0.4...1.0.5)

### Continuous integration

- Updated the PostgreSQL GitHub Actions CI service image from `postgres:16` to **`postgres:17`**.
- Updated the MariaDB CI service image from `mariadb:10` to **`mariadb:11.4`**.
- Switched the MariaDB environment to `MARIADB_ALLOW_EMPTY_ROOT_PASSWORD=1` and replaced the `mysqladmin ping` readiness check with `healthcheck.sh --connect --innodb_initialized`, adjusting its retry count.

### Release metadata

- Updated `version.php` from plugin version `2026101200` / release `1.0.4` to `2026101500` / release `1.0.5`.

### Scope

The comparison contains four commits and changes **only** `.github/workflows/ci.yml` and `version.php`. It contains no runtime plugin source changes or new goal features. The minimum Moodle build (`2022112800`) is unchanged. Updated CI database images do not, by themselves, establish new production database requirements.

---

Entries reflect confirmed differences between published release tags. No tests were executed or releases modified in preparing this changelog.
