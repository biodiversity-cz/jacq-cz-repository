FROM ghcr.io/biodiversity-cz/jacq-repository-base:main@sha256:8964a4c75a78c3da9f0c82cde201e746d3e4376fdb8eaedacc61c19ef6c8804d

MAINTAINER Petr Novotný <novotp@natur.cuni.cz>
LABEL org.opencontainers.image.source=https://github.com/biodiversity-cz/jacq-repository
LABEL org.opencontainers.image.description="specimen image repository JACQ herabrium consortium"
ARG GIT_TAG
ENV GIT_TAG=$GIT_TAG

# devoted for Kubernetes, where the app has to be copied into final destination (/srv) after the container starts
COPY  --chown=www:www htdocs /app
RUN chmod -R 777 /app/temp
