FROM ghcr.io/biodiversity-cz/jacq-repository-base:main@sha256:e7852cba4d31b69a8f05a7d3aecc7a53c3c62fc60610274490070c92d848df53

MAINTAINER Petr Novotný <novotp@natur.cuni.cz>
LABEL org.opencontainers.image.source=https://github.com/biodiversity-cz/jacq-repository
LABEL org.opencontainers.image.description="specimen image repository JACQ herabrium consortium"
ARG GIT_TAG
ENV GIT_TAG=$GIT_TAG

# devoted for Kubernetes, where the app has to be copied into final destination (/srv) after the container starts
COPY  --chown=www:www htdocs /app
RUN chmod -R 777 /app/temp
