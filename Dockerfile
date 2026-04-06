FROM ghcr.io/biodiversity-cz/jacq-repository-base:main@sha256:e5e8a3b6fd37b3794c9124f6d85712e0169f5756cd226ab1e257b27d9e8c286c

MAINTAINER Petr Novotný <novotp@natur.cuni.cz>
LABEL org.opencontainers.image.source=https://github.com/biodiversity-cz/jacq-repository
LABEL org.opencontainers.image.description="specimen image repository JACQ herabrium consortium"
ARG GIT_TAG
ENV GIT_TAG=$GIT_TAG

# devoted for Kubernetes, where the app has to be copied into final destination (/srv) after the container starts
COPY  --chown=www:www htdocs /app
RUN chmod -R 777 /app/temp && \
    rm -rf /app/tests

