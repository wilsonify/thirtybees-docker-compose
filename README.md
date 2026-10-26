Thirty Bees Docker Compose
==========================

Start playing, selling or developing with Thirty Bees in a breeze.

The stack includes PHP-FPM, nginx, MariaDB and memcached. The application image
is built from a pinned upstream ThirtyBees release plus a small, project-owned
overlay. ThirtyBees itself is **not committed**; it is acquired before the
Docker build.

Quick start
-----------

Prerequisites: Docker, git, and bash (Git Bash on Windows).

```sh
cd deploy/dev
make deps     # fetch and verify ThirtyBees 1.5.1
make php      # build ghcr.io/wilsonify/thirtybees:1.0.0
make up       # start the stack
```

Without `make`, run the same steps directly:

```sh
cd src/thirtybees
./fetch-thirtybees.sh
docker build --platform=amd64 -t ghcr.io/wilsonify/thirtybees:1.0.0 .
```

The development stack listens on <http://localhost:40080>. Decrypt
`deploy/dev/.env.enc` with SOPS, or create `deploy/dev/.env` with the variables
referenced by `docker-compose.yml`, before starting.

Dependency provenance
---------------------

| Item | Source | Pin | Integrity |
| --- | --- | --- | --- |
| ThirtyBees core | `https://github.com/thirtybees/thirtybees.git` | commit `e456bb60c0b1f7bf2a29734d5f4fc073e4fe4d47` (tag `1.5.1`) | Git object hash plus an explicit `HEAD` check in the fetch script |
| 60 ThirtyBees modules/themes | Submodules recorded by that commit | one commit per path in [`src/thirtybees/thirtybees-submodules.lock`](src/thirtybees/thirtybees-submodules.lock) | every checked-out submodule revision is compared with the lock |
| PHP 8.3 composer manifest/lock | Project-owned overlay | [`src/thirtybees/overlay/composer/php8.3`](src/thirtybees/overlay/composer/php8.3) | `composer.lock` is committed and used with `composer install` |
| Composer CLI | getcomposer.org | `2.10.3` | SHA-256 checked in the Dockerfile |
| memcached extension | PECL | `3.4.0` | SHA-256 checked in the Dockerfile |
| Base images | Docker Hub / GHCR | `php:8.3.36-fpm-bookworm`, `mariadb:11.4.3`, `memcached:1.6.29` | image tag plus registry digest when deployed |

The fetched tree lives in `src/thirtybees/www/thirtybees/`, which is ignored by
git. Re-running `make deps` is a no-op when the recorded commit matches; use
`make refresh-deps` after changing the pin or the overlay.

### Updating ThirtyBees

1. Choose a new upstream tag and read its release notes.
2. Update `UPSTREAM_TAG` and `UPSTREAM_COMMIT` in
   `src/thirtybees/fetch-thirtybees.sh`; use the commit ID, not a branch.
3. Regenerate `thirtybees-submodules.lock` from the new commit (the script
   verifies each path and revision).
4. Run `make refresh-deps`, then `make php`.
5. Run the application smoke test from `deploy/dev` and review the diff in the
   overlay, if any.

Never point the build at a branch, `master`, or a moving tag.

Local modifications
-------------------

The overlay contains only build configuration required to run ThirtyBees 1.5.1
on PHP 8.3. Earlier versions of this repository also committed the full
application and a set of analyzer-driven edits to upstream JavaScript and PHP
files (commits `0bb7ea6..6726906`). Those edits were not product requirements;
they remain in git history for audit but are intentionally not applied to the
clean upstream tree. Put any future local patch in
`src/thirtybees/overlay/` (preferred) or a documented `.patch` applied by the
fetch script.

Licensing
---------

ThirtyBees is distributed under the Open Software License 3.0; the upstream
`LICENSES` file and per-module license files are included in the fetched tree
and therefore in the built image at `/var/www/default/LICENSES`. Composer
dependencies retain their own licenses in `vendor/`. See
[`THIRD_PARTY_NOTICES.md`](THIRD_PARTY_NOTICES.md) for the summary.

Development notes
-----------------

- `deploy/prod/` deploys the prebuilt image to a KIND cluster and is independent
  of the source fetch.
- The image build needs network access for the core clone, submodules, Composer
  packages, and PECL. It fails closed on a hash or revision mismatch.
- `src/thirtybees/www/.gitkeep` keeps the build context directory present in a
  clean checkout; the fetch script replaces `www/thirtybees/`.
