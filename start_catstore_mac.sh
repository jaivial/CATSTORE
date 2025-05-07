#!/bin/bash

# Script de inicio automático para Cat Store en macOS
# Este script configura la base de datos y prepara el entorno usando MAMP

echo "================================================"
echo "      INICIANDO CAT STORE - TIENDA DE GATOS     "
echo "                  VERSIÓN MAC                   "
echo "================================================"
echo ""

# Verificar si MAMP está instalado
if [ ! -d "/Applications/MAMP" ]; then
    echo "❌ MAMP no está instalado. Por favor instala MAMP primero."
    echo "   Puedes descargarlo desde: https://www.mamp.info/"
    exit 1
fi

# Verificar si MAMP está en ejecución
echo "🔍 Verificando si MAMP está en ejecución..."
if pgrep -x "httpd" > /dev/null || pgrep -x "apache2" > /dev/null; then
    echo "✅ Servidor web en ejecución"
else
    echo "⚠️ MAMP parece no estar en ejecución."
    echo "   Intentando iniciar MAMP..."
    
    # Intentar iniciar MAMP
    open /Applications/MAMP/MAMP.app
    
    echo "⏳ Esperando a que MAMP inicie (10 segundos)..."
    sleep 10
    
    # Verificar nuevamente
    if pgrep -x "httpd" > /dev/null || pgrep -x "apache2" > /dev/null; then
        echo "✅ MAMP iniciado correctamente"
    else
        echo "❌ No se pudo iniciar MAMP automáticamente."
        echo "   Por favor inicia MAMP manualmente y ejecuta este script nuevamente."
        exit 1
    fi
fi

# Verificar la ubicación del proyecto
CURRENT_DIR=$(pwd)
MAMP_DIR="/Applications/MAMP/htdocs"

echo ""
echo "🔍 Verificando ubicación del proyecto..."

if [[ "$CURRENT_DIR" != *"/Applications/MAMP/htdocs"* ]]; then
    echo "⚠️ El proyecto no está en la carpeta htdocs de MAMP."
    echo "   Ubicación actual: $CURRENT_DIR"
    echo "   Ubicación recomendada: $MAMP_DIR/CATSTORE"
    
    # Preguntar si desea copiar el proyecto
    read -p "¿Deseas copiar el proyecto a la carpeta htdocs de MAMP? (s/n): " copy_project
    if [[ $copy_project == "s" || $copy_project == "S" ]]; then
        echo "🔧 Copiando proyecto a $MAMP_DIR/CATSTORE..."
        
        # Crear directorio si no existe
        mkdir -p "$MAMP_DIR/CATSTORE"
        
        # Copiar archivos
        cp -R ./* "$MAMP_DIR/CATSTORE/"
        
        echo "✅ Proyecto copiado correctamente"
        echo "🔄 Cambiando al directorio $MAMP_DIR/CATSTORE"
        cd "$MAMP_DIR/CATSTORE"
    else
        echo "⚠️ Continuando con la ubicación actual. Es posible que la aplicación no funcione correctamente."
    fi
fi

# Crear la base de datos
echo ""
echo "🔍 Configurando la base de datos..."

# Configuración de MAMP MySQL
MYSQL_USER="root"
MYSQL_PASSWORD="root"  # Contraseña por defecto de MAMP
MYSQL_PATH="/Applications/MAMP/Library/bin"

echo "🔧 Usando configuración de MAMP:"
echo "   - Usuario MySQL: $MYSQL_USER"
echo "   - Contraseña MySQL: $MYSQL_PASSWORD"

# Crear la base de datos y tablas
echo "🔧 Creando base de datos y tablas..."
"$MYSQL_PATH/mysql" -u "$MYSQL_USER" -p"$MYSQL_PASSWORD" < crear_bd.sql

if [ $? -eq 0 ]; then
    echo "✅ Base de datos creada correctamente"
else
    echo "❌ Error al crear la base de datos"
    exit 1
fi

# Preguntar si desea insertar datos de muestra
read -p "¿Deseas insertar datos de muestra? (s/n): " insert_sample
if [[ $insert_sample == "s" || $insert_sample == "S" ]]; then
    echo "🔧 Insertando datos de muestra..."
    "$MYSQL_PATH/mysql" -u "$MYSQL_USER" -p"$MYSQL_PASSWORD" gatos < insertar_gatos.sql
    
    if [ $? -eq 0 ]; then
        echo "✅ Datos de muestra insertados correctamente"
    else
        echo "❌ Error al insertar datos de muestra"
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

// Puerto de MAMP (8889 por defecto)
define('DB_PORT', '8889');

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
        \$dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
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
echo "- URL: http://localhost:8888/CATSTORE"
echo "- Usuario administrador: javial"
echo "- Contraseña: 12"
echo ""
echo "🚀 ¡Disfruta de tu tienda de gatos!"
echo "" 