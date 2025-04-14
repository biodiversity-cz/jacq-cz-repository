FROM ghcr.io/biodiversity-cz/jacq-repository-base:main@sha256:b83ee56f4d83ab9d663685d3dd3e31678388790b0f70c80c71c9f44c03b40c2d

MAINTAINER Petr Novotný <novotp@natur.cuni.cz>
LABEL org.opencontainers.image.source=https://github.com/biodiversity-cz/jacq-repository
LABEL org.opencontainers.image.description="specimen image repository JACQ herabrium consortium"
ARG GIT_TAG
ENV GIT_TAG=$GIT_TAG

# devoted for Kubernetes, where the app has to be copied into final destination (/srv) after the container starts
COPY  --chown=www:www htdocs /app
RUN chmod -R 777 /app/temp
