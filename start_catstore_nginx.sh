#!/bin/bash

# Script de inicio automático para Cat Store con Nginx
# Este script configura la base de datos y prepara el entorno usando Nginx en macOS

echo "================================================"
echo "      INICIANDO CAT STORE - TIENDA DE GATOS     "
echo "                VERSIÓN NGINX                   "
echo "================================================"
echo ""

# Verificar si Nginx está instalado
if ! command -v nginx &> /dev/null; then
    echo "❌ Nginx no está instalado. Por favor instala Nginx primero."
    echo "   Puedes instalarlo con: brew install nginx"
    exit 1
fi

# Verificar si PHP está instalado
if ! command -v php &> /dev/null; then
    echo "❌ PHP no está instalado. Por favor instala PHP primero."
    echo "   Puedes instalarlo con: brew install php"
    exit 1
fi

# Verificar si MySQL está instalado
if ! command -v mysql &> /dev/null; then
    echo "❌ MySQL no está instalado. Por favor instala MySQL primero."
    echo "   Puedes instalarlo con: brew install mysql"
    exit 1
fi

# Función para verificar si un puerto está en uso
check_port() {
    local port=$1
    if lsof -i :$port > /dev/null; then
        return 0  # Puerto en uso
    else
        return 1  # Puerto libre
    fi
}

# Encontrar un puerto disponible
find_available_port() {
    local base_port=$1
    local port=$base_port
    
    while check_port $port; do
        echo "⚠️ Puerto $port ya está en uso, probando siguiente puerto..."
        port=$((port + 1))
    done
    
    echo $port
}

# Encontrar un puerto disponible para Nginx
NGINX_PORT=$(find_available_port 8081)
echo "🔍 Usando puerto $NGINX_PORT para Nginx"

# Verificar si Nginx está en ejecución
echo "🔍 Verificando si Nginx está en ejecución..."
if pgrep -x "nginx" > /dev/null; then
    echo "✅ Nginx en ejecución"
else
    echo "⚠️ Nginx parece no estar en ejecución."
    echo "   Intentando iniciar Nginx..."
    
    # Intentar iniciar Nginx
    sudo nginx
    
    echo "⏳ Esperando a que Nginx inicie (2 segundos)..."
    sleep 2
    
    # Verificar nuevamente
    if pgrep -x "nginx" > /dev/null; then
        echo "✅ Nginx iniciado correctamente"
    else
        echo "⚠️ No se pudo iniciar Nginx automáticamente."
        echo "   Continuando de todos modos, pero es posible que la aplicación no funcione correctamente."
    fi
fi

# Verificar si PHP-FPM está en ejecución
echo "🔍 Verificando si PHP-FPM está en ejecución..."
if pgrep -x "php-fpm" > /dev/null; then
    echo "✅ PHP-FPM en ejecución"
else
    echo "⚠️ PHP-FPM parece no estar en ejecución."
    echo "   Intentando iniciar PHP-FPM..."
    
    # Intentar iniciar PHP-FPM (versión 8.x)
    if [ -f "/usr/local/opt/php/sbin/php-fpm" ]; then
        sudo /usr/local/opt/php/sbin/php-fpm --daemonize
    else
        # Intentar con diferentes versiones de PHP
        PHP_VERSION=$(php -r 'echo PHP_MAJOR_VERSION.".".PHP_MINOR_VERSION;')
        sudo php-fpm
    fi
    
    echo "⏳ Esperando a que PHP-FPM inicie (2 segundos)..."
    sleep 2
    
    # Verificar nuevamente
    if pgrep -x "php-fpm" > /dev/null; then
        echo "✅ PHP-FPM iniciado correctamente"
    else
        echo "⚠️ No se pudo iniciar PHP-FPM automáticamente."
        echo "   Continuando de todos modos, pero es posible que la aplicación no funcione correctamente."
    fi
fi

# Verificar si MySQL está en ejecución
echo "🔍 Verificando si MySQL está en ejecución..."
if pgrep -x "mysqld" > /dev/null; then
    echo "✅ MySQL en ejecución"
else
    echo "⚠️ MySQL parece no estar en ejecución."
    echo "   Intentando iniciar MySQL..."
    
    # Intentar iniciar MySQL
    if [ -d "/usr/local/opt/mysql/bin" ]; then
        sudo /usr/local/opt/mysql/bin/mysqld_safe --datadir=/usr/local/var/mysql &
    else
        # Intentar con el servicio
        brew services start mysql
    fi
    
    echo "⏳ Esperando a que MySQL inicie (5 segundos)..."
    sleep 5
    
    # Verificar nuevamente
    if pgrep -x "mysqld" > /dev/null; then
        echo "✅ MySQL iniciado correctamente"
    else
        echo "❌ No se pudo iniciar MySQL automáticamente."
        echo "   Por favor inicia MySQL manualmente y ejecuta este script nuevamente."
        exit 1
    fi
fi

# Configurar Nginx para el proyecto
echo ""
echo "🔧 Configurando Nginx para el proyecto..."

# Determinar la ubicación del proyecto
CURRENT_DIR=$(pwd)
PROJECT_NAME="catstore"
NGINX_CONF_DIR="/usr/local/etc/nginx/servers"
NGINX_CONF_FILE="$NGINX_CONF_DIR/$PROJECT_NAME.conf"

# Crear la configuración de Nginx
echo "🔧 Creando configuración de Nginx..."

# Crear directorio de configuración si no existe
sudo mkdir -p "$NGINX_CONF_DIR"

# Crear archivo de configuración
cat > "$PROJECT_NAME.conf.tmp" << EOF
server {
    listen $NGINX_PORT;
    server_name localhost;
    
    root $CURRENT_DIR;
    index index.php index.html;
    
    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }
    
    location ~ \.php$ {
        try_files \$uri =404;
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME \$document_root\$fastcgi_script_name;
        include fastcgi_params;
    }
    
    location ~ /\.ht {
        deny all;
    }
}
EOF

# Mover la configuración al directorio de Nginx
sudo mv "$PROJECT_NAME.conf.tmp" "$NGINX_CONF_FILE"

# Reiniciar Nginx para aplicar la configuración
echo "🔄 Reiniciando Nginx para aplicar la configuración..."
sudo nginx -s reload

if [ $? -eq 0 ]; then
    echo "✅ Configuración de Nginx aplicada correctamente"
else
    echo "❌ Error al aplicar la configuración de Nginx"
    exit 1
fi

# Crear la base de datos
echo ""
echo "🔍 Configurando la base de datos..."

# Solicitar credenciales de MySQL
read -p "Usuario MySQL (default: root): " mysql_user
mysql_user=${mysql_user:-root}

read -s -p "Contraseña MySQL (dejar en blanco si no tiene): " mysql_password
echo ""

# Crear archivo SQL corregido
echo "🔧 Creando script SQL corregido..."
cat > crear_bd_fix.sql << EOF
CREATE DATABASE IF NOT EXISTS gatos;

USE gatos;

CREATE TABLE IF NOT EXISTS usuario (
  username VARCHAR(100) NOT NULL PRIMARY KEY, 
  contrasenya VARCHAR(100) NOT NULL, 
  nombre VARCHAR(100) NOT NULL, 
  apellido VARCHAR(200) NOT NULL, 
  email VARCHAR(200) NOT NULL
); 

CREATE TABLE IF NOT EXISTS animal (
  id INT(11) NOT NULL PRIMARY KEY AUTO_INCREMENT, 
  nombre VARCHAR(50) NOT NULL, 
  tipo VARCHAR(50) NOT NULL, 
  color VARCHAR(20) NOT NULL, 
  sexo BOOLEAN NOT NULL, 
  precio DECIMAL(10,2) NOT NULL, 
  foto LONGBLOB NULL, 
  fecha_anyadido TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
); 

CREATE TABLE IF NOT EXISTS carrito (
  id_animal INT(11) NOT NULL,
  username_usuario VARCHAR(50) NOT NULL,
  FOREIGN KEY (id_animal) REFERENCES animal (id),
  FOREIGN KEY (username_usuario) REFERENCES usuario(username)
);

CREATE TABLE IF NOT EXISTS compra (
  fecha DATETIME NOT NULL,
  id_animal INT(11) NOT NULL,
  username_usuario VARCHAR(100) NOT NULL,
  FOREIGN KEY (id_animal) REFERENCES animal(id),
  FOREIGN KEY (username_usuario) REFERENCES usuario(username)
);

-- Verificar si el usuario administrador ya existe
SET @exists = (SELECT COUNT(*) FROM usuario WHERE username = 'javial');
SET @sql = IF(@exists > 0, 'SELECT "Usuario administrador ya existe"', 'INSERT INTO usuario (username, contrasenya, nombre, apellido, email) VALUES ("javial", "12", "Javier", "Administrador", "admin@floridacats.com")');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
EOF

# Crear la base de datos y tablas
echo "🔧 Creando base de datos y tablas..."
if [ -z "$mysql_password" ]; then
    mysql -u "$mysql_user" < crear_bd_fix.sql
else
    mysql -u "$mysql_user" -p"$mysql_password" < crear_bd_fix.sql
fi

if [ $? -eq 0 ]; then
    echo "✅ Base de datos creada correctamente"
else
    echo "⚠️ Hubo algunos errores al crear la base de datos, pero continuamos..."
    echo "   Esto puede ocurrir si la base de datos ya existe."
fi

# Preguntar si desea insertar datos de muestra
read -p "¿Deseas insertar datos de muestra? (s/n): " insert_sample
if [[ $insert_sample == "s" || $insert_sample == "S" ]]; then
    echo "🔧 Insertando datos de muestra..."
    
    # Modificar el script de inserción para evitar duplicados
    cat > insertar_gatos_fix.sql << EOF
    USE gatos;
    
    -- Insertar datos solo si no existen
    INSERT IGNORE INTO animal (nombre, tipo, color, sexo, precio) 
    VALUES 
    ('Luna', 'Siamés', 'Blanco', 0, 150.00),
    ('Max', 'Persa', 'Gris', 1, 200.00),
    ('Bella', 'Bengalí', 'Atigrado', 0, 250.00),
    ('Leo', 'Maine Coon', 'Marrón', 1, 300.00),
    ('Coco', 'Ragdoll', 'Blanco y gris', 0, 180.00);
EOF
    
    if [ -z "$mysql_password" ]; then
        mysql -u "$mysql_user" < insertar_gatos_fix.sql
    else
        mysql -u "$mysql_user" -p"$mysql_password" < insertar_gatos_fix.sql
    fi
    
    if [ $? -eq 0 ]; then
        echo "✅ Datos de muestra insertados correctamente"
    else
        echo "⚠️ Hubo algunos errores al insertar los datos de muestra, pero continuamos..."
    fi
fi

# Actualizar configuración de la base de datos
echo ""
echo "🔧 Actualizando configuración de conexión a la base de datos..."

# Crear archivo de configuración temporal
cat > config/db_config.php.tmp << EOF
<?php

/**
 * Configuración de la conexión a la base de datos
 * Cat Store - Tienda de Gatos
 */

// Configuración de la base de datos
define('DB_HOST', 'localhost');
define('DB_USER', '$mysql_user');
define('DB_PASS', '$mysql_password');
define('DB_NAME', 'gatos');
define('DB_CHARSET', 'utf8mb4');

// Configuración de cookies
define('COOKIE_DURATION', 60 * 60 * 24 * 7); // 1 semana en segundos
define('COOKIE_PATH', '/');
define('COOKIE_DOMAIN', '');
define('COOKIE_SECURE', false); // Cambiar a true en producción con HTTPS
define('COOKIE_HTTPONLY', true);

// Función para conectar a la base de datos
function connectDB()
{
    try {
        \$dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        \$options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        return new PDO(\$dsn, DB_USER, DB_PASS, \$options);
    } catch (PDOException \$e) {
        // En un entorno de producción, registrar el error en lugar de mostrarlo
        die('Error de conexión: ' . \$e->getMessage());
    }
}
EOF

# Reemplazar el archivo de configuración
mv config/db_config.php.tmp config/db_config.php

echo "✅ Configuración actualizada correctamente"

# Verificar permisos
echo ""
echo "🔧 Verificando permisos de archivos..."
chmod -R 755 .
mkdir -p assets/img/cats
chmod -R 777 assets/img/cats

echo "✅ Permisos actualizados"

# Información final
echo ""
echo "================================================"
echo "      CAT STORE CONFIGURADO CORRECTAMENTE       "
echo "================================================"
echo ""
echo "📝 INFORMACIÓN DE ACCESO:"
echo "- URL: http://localhost:$NGINX_PORT"
echo "- Usuario administrador: javial"
echo "- Contraseña: 12"
echo ""
echo "🚀 ¡Disfruta de tu tienda de gatos!"
echo "" 