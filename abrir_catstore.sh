#!/bin/bash

# Script para abrir Cat Store en el navegador con la URL correcta

echo "================================================"
echo "      ABRIENDO CAT STORE - TIENDA DE GATOS      "
echo "================================================"
echo ""

# Función para verificar si un puerto está en uso
check_port() {
    local port=$1
    if lsof -i :$port > /dev/null; then
        return 0  # Puerto en uso
    else
        return 1  # Puerto libre
    fi
}

# Verificar si Nginx está escuchando en el puerto 8081
if check_port 8081; then
    echo "✅ Nginx detectado en puerto 8081"
    URL="http://localhost:8081"
    echo "🔍 Abriendo Cat Store en: $URL"
    open "$URL"
# Verificar si Nginx está escuchando en el puerto 8080
elif check_port 8080; then
    echo "✅ Nginx detectado en puerto 8080"
    URL="http://localhost:8080"
    echo "🔍 Abriendo Cat Store en: $URL"
    open "$URL"
# Verificar si Apache/XAMPP está escuchando en algún puerto
else
    # Buscar puerto de Apache/XAMPP
    APACHE_PORT=$(lsof -i -P | grep LISTEN | grep -E '(httpd|apache)' | head -1 | awk '{print $9}' | cut -d':' -f2)
    
    if [ -n "$APACHE_PORT" ]; then
        echo "✅ Apache/XAMPP detectado en puerto $APACHE_PORT"
        URL="http://localhost:$APACHE_PORT/CATSTORE"
        echo "🔍 Abriendo Cat Store en: $URL"
        open "$URL"
    else
        echo "❌ No se detectó ningún servidor web en ejecución."
        echo "   Por favor ejecuta primero ./start_catstore_nginx.sh o ./start_catstore_xampp.sh"
        exit 1
    fi
fi

echo ""
echo "📝 INFORMACIÓN DE ACCESO:"
echo "- Usuario administrador: javial"
echo "- Contraseña: 12"
echo ""
echo "🚀 ¡Disfruta de tu tienda de gatos!"
echo "" 