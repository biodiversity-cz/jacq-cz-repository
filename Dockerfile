FROM ghcr.io/biodiversity-cz/jacq-repository-base:main@sha256:fad694bc05a72a5d57818237056b37a0281e8c3aad8a8e859082a84f8621d196

MAINTAINER Petr Novotný <novotp@natur.cuni.cz>
LABEL org.opencontainers.image.source=https://github.com/biodiversity-cz/jacq-repository
LABEL org.opencontainers.image.description="specimen image repository JACQ herabrium consortium"
ARG GIT_TAG
ENV GIT_TAG=$GIT_TAG

# devoted for Kubernetes, where the app has to be copied into final destination (/srv) after the container starts
COPY  --chown=www:www htdocs /app
RUN chmod -R 777 /app/temp && \
    rm -rf /app/tests

