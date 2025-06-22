# --------------------------
# Stage 1: Builder
# --------------------------
FROM php:8.2-apache as builder

# Instala dependências do sistema e extensões PHP necessárias para o BUILD
# Inclui zlib1g-dev e default-libmysqlclient-dev para compilação de extensões
RUN apt-get update && apt-get install -y --no-install-recommends \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libonig-dev \
    libzip-dev \
    zlib1g-dev \
    default-libmysqlclient-dev \
    zip \
    git \
    unzip \
    curl \
  && docker-php-ext-configure gd --with-freetype --with-jpeg \
  && docker-php-ext-configure zip \
  && docker-php-ext-install pdo pdo_mysql gd mbstring zip \
  && apt-get clean && rm -rf /var/lib/apt/lists/*

# Instala Node.js e npm no estágio de BUILD
RUN curl -fsSL https://deb.nodesource.com/setup_18.x | bash - \
  && apt-get install -y nodejs \
  && apt-get clean && rm -rf /var/lib/apt/lists/*

# Copia o Composer a partir da imagem oficial
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Define o diretório de trabalho
WORKDIR /var/www/html

# Copia os arquivos de manifesto de dependência (para cache)
COPY composer.json composer.lock ./

# Instala as dependências do Composer, incluindo as de desenvolvimento, mas SEM executar os scripts do Laravel ainda.
# Isso evita erros de "artisan not found" antes que todo o código esteja presente.
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
# Stage 2: Imagem Final (para Produção e Desenvolvimento em Runtime)
# --------------------------
FROM php:8.2-apache

# Ajusta o ServerName do Apache para eliminar o aviso e habilita mod_rewrite
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf \
    && a2enmod rewrite

# Copia a configuração do Virtual Host do Apache
# Isso garante que o DocumentRoot aponte para /public e que as permissões estejam corretas.
COPY 000-default.conf /etc/apache2/sites-available/000-default.conf

# Instala dependências, compila extensões, instala Node.js e limpa em uma única camada
RUN apt-get update && apt-get install -y --no-install-recommends \
    # Runtime libraries for PHP extensions (to keep them)
    libpng16-16 \
    libjpeg62-turbo \
    libfreetype6 \
    libonig5 \
    libzip4 \
    # Build dependencies for PHP extensions & Node.js (serão purgadas)
    gnupg \
    ca-certificates \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libonig-dev \
    libzip-dev \
    zlib1g-dev \
    default-libmysqlclient-dev \
    git \
    curl \
    # Other runtime utilities
    zip \
    unzip \
  # Compila extensões PHP
  && docker-php-ext-configure gd --with-freetype --with-jpeg \
  && docker-php-ext-configure zip \
  && docker-php-ext-install pdo pdo_mysql gd mbstring zip \
  # Instala Node.js e npm na imagem final
  && curl -fsSL https://deb.nodesource.com/setup_18.x | bash - \
  && apt-get install -y nodejs \
  # Limpa as dependências de build para manter a imagem leve
  && apt-get purge -y --auto-remove \
    gnupg \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libonig-dev \
    libzip-dev \
    zlib1g-dev \
    default-libmysqlclient-dev \
    git \
    curl \
  && apt-get clean && rm -rf /var/lib/apt/lists/*

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
