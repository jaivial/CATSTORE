#!/bin/bash

# Script de inicio automático para Cat Store con XAMPP
# Este script configura la base de datos y prepara el entorno usando XAMPP en macOS

echo "================================================"
echo "      INICIANDO CAT STORE - TIENDA DE GATOS     "
echo "                VERSIÓN XAMPP                   "
echo "================================================"
echo ""

# Verificar si XAMPP está instalado
if [ ! -d "/Applications/XAMPP" ]; then
    echo "❌ XAMPP no está instalado. Por favor instala XAMPP primero."
    echo "   Puedes descargarlo desde: https://www.apachefriends.org/"
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

# Encontrar un puerto disponible para Apache
APACHE_PORT=$(find_available_port 8080)
echo "🔍 Usando puerto $APACHE_PORT para Apache"

# Verificar si XAMPP está en ejecución
echo "🔍 Verificando si XAMPP está en ejecución..."
if pgrep -x "httpd" > /dev/null || ps aux | grep -v grep | grep "xampp" | grep "httpd" > /dev/null; then
    echo "✅ Servidor web en ejecución"
else
    echo "⚠️ XAMPP parece no estar en ejecución."
    echo "   Intentando iniciar XAMPP..."
    
    # Intentar iniciar XAMPP
    if [ -f "/Applications/XAMPP/xamppfiles/xampp" ]; then
        # Modificar el puerto de Apache en XAMPP
        HTTPD_CONF="/Applications/XAMPP/xamppfiles/etc/httpd.conf"
        if [ -f "$HTTPD_CONF" ]; then
            echo "🔧 Configurando Apache para usar el puerto $APACHE_PORT..."
            sudo cp "$HTTPD_CONF" "$HTTPD_CONF.backup"
            sudo sed -i '' "s/Listen 80/Listen $APACHE_PORT/g" "$HTTPD_CONF"
            sudo sed -i '' "s/<VirtualHost \*:80>/<VirtualHost *:$APACHE_PORT>/g" "$HTTPD_CONF"
            echo "✅ Puerto de Apache configurado correctamente"
        fi
        
        sudo /Applications/XAMPP/xamppfiles/xampp start
    else
        echo "❌ No se pudo encontrar el script de inicio de XAMPP."
        echo "   Por favor inicia XAMPP manualmente y ejecuta este script nuevamente."
        exit 1
    fi
    
    echo "⏳ Esperando a que XAMPP inicie (5 segundos)..."
    sleep 5
    
    # Verificar nuevamente
    if pgrep -x "httpd" > /dev/null || ps aux | grep -v grep | grep "xampp" | grep "httpd" > /dev/null; then
        echo "✅ XAMPP iniciado correctamente"
    else
        echo "❌ No se pudo iniciar XAMPP automáticamente."
        echo "   Por favor inicia XAMPP manualmente y ejecuta este script nuevamente."
        exit 1
    fi
fi

# Verificar la ubicación del proyecto
CURRENT_DIR=$(pwd)
XAMPP_DIR="/Applications/XAMPP/xamppfiles/htdocs"

echo ""
echo "🔍 Verificando ubicación del proyecto..."

if [[ "$CURRENT_DIR" != *"/Applications/XAMPP/xamppfiles/htdocs"* ]]; then
    echo "⚠️ El proyecto no está en la carpeta htdocs de XAMPP."
    echo "   Ubicación actual: $CURRENT_DIR"
    echo "   Ubicación recomendada: $XAMPP_DIR/CATSTORE"
    
    # Preguntar si desea copiar el proyecto
    read -p "¿Deseas copiar el proyecto a la carpeta htdocs de XAMPP? (s/n): " copy_project
    if [[ $copy_project == "s" || $copy_project == "S" ]]; then
        echo "🔧 Copiando proyecto a $XAMPP_DIR/CATSTORE..."
        
        # Crear directorio si no existe
        sudo mkdir -p "$XAMPP_DIR/CATSTORE"
        
        # Copiar archivos
        sudo cp -R ./* "$XAMPP_DIR/CATSTORE/"
        
        # Ajustar permisos
        sudo chmod -R 755 "$XAMPP_DIR/CATSTORE"
        sudo chown -R $(whoami) "$XAMPP_DIR/CATSTORE"
        
        echo "✅ Proyecto copiado correctamente"
        echo "🔄 Cambiando al directorio $XAMPP_DIR/CATSTORE"
        cd "$XAMPP_DIR/CATSTORE"
    else
        echo "⚠️ Continuando con la ubicación actual. Es posible que la aplicación no funcione correctamente."
    fi
fi

# Crear la base de datos
echo ""
echo "🔍 Configurando la base de datos..."

# Configuración de XAMPP MySQL
MYSQL_USER="root"
MYSQL_PASSWORD=""  # Contraseña por defecto de XAMPP (vacía)
MYSQL_PATH="/Applications/XAMPP/xamppfiles/bin"

echo "🔧 Usando configuración de XAMPP:"
echo "   - Usuario MySQL: $MYSQL_USER"
echo "   - Contraseña MySQL: [vacía]"

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
"$MYSQL_PATH/mysql" -u "$MYSQL_USER" < crear_bd_fix.sql

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
    
    "$MYSQL_PATH/mysql" -u "$MYSQL_USER" < insertar_gatos_fix.sql
    
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
define('DB_USER', '$MYSQL_USER');
define('DB_PASS', '$MYSQL_PASSWORD');
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
echo "- URL: http://localhost:$APACHE_PORT/CATSTORE"
echo "- Usuario administrador: javial"
echo "- Contraseña: 12"
echo ""
echo "🚀 ¡Disfruta de tu tienda de gatos!"
echo "" 