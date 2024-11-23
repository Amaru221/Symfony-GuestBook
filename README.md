# Symfony-GuestBook

Esta aplicación web es un libro de visitas online de conferencias

Instalación

1. Instalar dependencias de composer
$> composer install

2. lanzar aplicación symfony en segundo plano
$> symfony server:start -d

3. Crear contenedores docker para servidor, blackfire y mailer
$> docker compose up -d

4. Instala npm y ejecutar
$> npm install

5. Compilar los assets para dev
$> npm run dev

6. Compitar assets y ver modificaciones
$> npm run watch -d