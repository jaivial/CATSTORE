#!/bin/bash

# Script de inicio automático para Cat Store
# Este script configura la base de datos y prepara el entorno

echo "================================================"
echo "      INICIANDO CAT STORE - TIENDA DE GATOS     "
echo "================================================"
echo ""

# Verificar si MySQL está instalado
if ! command -v mysql &> /dev/null; then
    echo "❌ MySQL no está instalado. Por favor instala MySQL primero."
    exit 1
fi

# Verificar si PHP está instalado
if ! command -v php &> /dev/null; then
    echo "❌ PHP no está instalado. Por favor instala PHP primero."
    exit 1
fi

# Verificar si el servidor web está en ejecución
echo "🔍 Verificando si el servidor web está en ejecución..."
if curl -s http://localhost &> /dev/null; then
    echo "✅ Servidor web en ejecución"
else
    echo "⚠️ El servidor web parece no estar en ejecución."
    echo "   Por favor inicia Apache/Nginx antes de continuar."
    
    # Preguntar si desea continuar
    read -p "¿Deseas continuar de todos modos? (s/n): " continue_anyway
    if [[ $continue_anyway != "s" && $continue_anyway != "S" ]]; then
        echo "❌ Instalación cancelada."
        exit 1
    fi
fi

# Crear la base de datos
echo ""
echo "🔍 Configurando la base de datos..."

# Solicitar credenciales de MySQL
read -p "Usuario MySQL (default: root): " mysql_user
mysql_user=${mysql_user:-root}

read -s -p "Contraseña MySQL (dejar en blanco si no tiene): " mysql_password
echo ""

# Crear la base de datos y tablas
echo "🔧 Creando base de datos y tablas..."
if [ -z "$mysql_password" ]; then
    mysql -u "$mysql_user" < crear_bd.sql
else
    mysql -u "$mysql_user" -p"$mysql_password" < crear_bd.sql
fi

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
    if [ -z "$mysql_password" ]; then
        mysql -u "$mysql_user" gatos < insertar_gatos.sql
    else
        mysql -u "$mysql_user" -p"$mysql_password" gatos < insertar_gatos.sql
    fi
    
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
chmod -R 777 assets/img/cats

echo "✅ Permisos actualizados"

# Información final
echo ""
echo "================================================"
echo "      CAT STORE CONFIGURADO CORRECTAMENTE       "
echo "================================================"
echo ""
echo "📝 INFORMACIÓN DE ACCESO:"
echo "- URL: http://localhost/CATSTORE"
echo "- Usuario administrador: javial"
echo "- Contraseña: 12"
echo ""
echo "🚀 ¡Disfruta de tu tienda de gatos!"
echo "" 