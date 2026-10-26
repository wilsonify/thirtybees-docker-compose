# Third-party notices

This repository builds a ThirtyBees image. The application source is acquired at
build time from the pinned upstream revision documented in
[`src/thirtybees/fetch-thirtybees.sh`](src/thirtybees/fetch-thirtybees.sh) and is
not redistributed as source in this repository.

| Component | Version / revision | License | Where the license text is kept |
| --- | --- | --- | --- |
| ThirtyBees | 1.5.1, commit `e456bb60c0b1f7bf2a29734d5f4fc073e4fe4d47` | Open Software License 3.0 | `LICENSES` in the fetched tree; also in the image at `/var/www/default/LICENSES` |
| ThirtyBees modules and themes | revisions in `src/thirtybees/thirtybees-submodules.lock` | per submodule (OSL 3.0 and others) | each fetched submodule's `LICENSE*` file |
| Composer packages | versions in `src/thirtybees/overlay/composer/php8.3/composer.lock` | per package | `vendor/*/*/LICENSE*` in the image |
| Composer CLI | 2.10.3 | MIT | <https://github.com/composer/composer/blob/main/LICENSE> |
| memcached PHP extension | 3.4.0 | PHP License 3.01 | package `LICENSE` in the PECL archive |
| PHP | 8.3.36 | PHP License 3.01 | base image |
| MariaDB | 11.4.3 | GPLv2 | base image |

The Docker image includes the upstream `LICENSES` file and the installed
Composer packages, so the notices travel with the binary distribution. If you
publish the image, keep those files and the per-module license files intact.

The previous, source-vendored copy of ThirtyBees also included analyzer-driven
edits to upstream files. Those edits are not part of the clean upstream
distribution and are not covered by this notice; they remain only in git
history.
