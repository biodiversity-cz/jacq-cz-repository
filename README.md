[![Build Status](https://github.com/biodiversity-cz/jacq-cz-repository/actions/workflows/publish.yml/badge.svg)](https://github.com/krkabol/jacq-image-curator/actions/workflows/publish.yml?query=branch%3Amain++)
[![GitHub Container Registry](https://ghcr-badge.egpl.dev/biodiversity-cz/jacq-cz-repository/latest_tag?trim=major&label=latest)](https://github.com/krkabol/jacq-image-curator/pkgs/container/jacq-image-curator)
[![Build Status](https://github.com/biodiversity-cz/jacq-cz-repository/actions/workflows/tests.yml/badge.svg)](https://github.com/krkabol/jacq-image-curator/actions/workflows/tests.yml?query=branch%3Amain++)
[![codecov](https://codecov.io/gh/biodiversity-cz/jacq-cz-repository/branch/main/graph/badge.svg?token=YOUR_TOKEN)](https://codecov.io/gh/krkabol/jacq-image-curator)

[//]: # (![PHPStan]&#40;https://img.shields.io/badge/style-level%207-brightgreen.svg?&label=phpstan&#41;)


# jacq-repository
JACQ CZ repository handles primary data management of specimen scans from herbaria in the Czech Republic. A develop version can be found at [https://herbarium.dyn.cloud.e-infra.cz/](https://herbarium.dyn.cloud.e-infra.cz/).

## Key points
* Archive Master File (scanned photo in the highest quality) is available through proxy of this app. There is no access restriction, the S3 ACL is not turned on.
* Bucket versioning is not turned on.
* IIIF manifest only in v2


