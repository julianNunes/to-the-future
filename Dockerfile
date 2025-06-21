FROM php:8.2-apache as builder

# Instala dependências do sistema e extensões PHP necessárias
RUN apt-get update && apt-get install -y --no-install-recommends \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    zip \
    git \
    unzip \
    curl \
    libonig-dev \
    libzip-dev \
  && docker-php-ext-configure gd --with-freetype --with-jpeg \
  && docker-php-ext-configure zip \
  && docker-php-ext-install pdo pdo_mysql gd mbstring zip

# Instala Node.js e npm
RUN curl -fsSL https://deb.nodesource.com/setup_18.x | bash - \
  && apt-get install -y nodejs

# Copia o Composer a partir da imagem oficial
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Define o diretório de trabalho
WORKDIR /var/www/html

# Copia os arquivos de manifesto de dependência (para cache)
COPY composer.json composer.lock ./

# Instala as dependências do Composer, incluindo as de desenvolvimento, mas SEM executar os scripts do Laravel ainda.
RUN composer install --no-interaction --optimize-autoloader --no-scripts

# Copia os arquivos de manifesto do NPM (para cache)
COPY package.json package-lock.json ./

# Instala as dependências do NPM
RUN npm install && npm cache clean --force

# Copia TODO o código da aplicação. Agora o 'artisan' e o resto do Laravel estão presentes.
COPY . .

# Agora que todos os arquivos estão presentes, podemos executar os scripts do Composer e compilar os assets.
RUN composer dump-autoload --optimize
RUN npm run build

# --------------------------
# Stage 2: Imagem Final (para Produção)
# --------------------------
FROM php:8.2-apache

# Ajusta o ServerName do Apache para eliminar o aviso e habilita mod_rewrite
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf \
    && a2enmod rewrite

# Instala as dependências de runtime e compila as extensões PHP.
# Remove os pacotes de desenvolvimento após a compilação para manter a imagem leve.
RUN apt-get update && apt-get install -y --no-install-recommends \
    # Runtime libraries for PHP extensions
    libpng16-16 \
    libjpeg62-turbo \
    libfreetype6 \
    libonig5 \
    libzip4 \
    # Build dependencies (will be purged)
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libonig-dev \
    libzip-dev \
    # Runtime utilities
    zip \
    unzip \
    curl \
  && docker-php-ext-configure gd --with-freetype --with-jpeg \
  && docker-php-ext-configure zip \
  && docker-php-ext-install pdo pdo_mysql gd mbstring zip \
  && apt-get purge -y --auto-remove -o APT::AutoRemove::RecommendsImportant=false \
    libpng-dev libjpeg-dev libfreetype6-dev libonig-dev libzip-dev git curl \
  && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html

# Copia o código já compilado na etapa de builder para a imagem final
COPY --from=builder /var/www/html ./

# Ajusta as permissões para o usuário do Apache
RUN chown -R www-data:www-data /var/www/html

# Copia o script de entrypoint
COPY entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Exposição da porta do Apache
EXPOSE 80

# Define o entrypoint e o comando padrão
ENTRYPOINT ["entrypoint.sh"]
CMD ["apache2-foreground"]
