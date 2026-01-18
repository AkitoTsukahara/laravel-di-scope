FROM php:8.4-cli

# 必要なパッケージをインストール
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    zip \
    libzip-dev \
    && docker-php-ext-install zip \
    && rm -rf /var/lib/apt/lists/*

# Composerをインストール
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 作業ディレクトリ
WORKDIR /app

# Composerのキャッシュディレクトリを設定
ENV COMPOSER_HOME=/tmp/composer

# 非rootユーザーを作成
ARG UID=1000
ARG GID=1000
RUN groupadd -g ${GID} developer \
    && useradd -u ${UID} -g developer -m developer

USER developer