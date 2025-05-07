#!/bin/bash

# Script para iniciar Cat Store en un servidor PHP local en el puerto 8000

echo "================================================"
echo "       INICIANDO CAT STORE - TIENDA DE GATOS    "
echo "================================================"
echo ""

# Detectar la ruta del repositorio (directorio actual)
REPO_PATH=$(pwd)
echo "📂 Repositorio detectado en: $REPO_PATH"

# Comprobar si hay otro proceso usando el puerto 8000
if lsof -i :8000 > /dev/null; then
    echo "⚠️  AVISO: Puerto 8000 ya está en uso."
    echo "   Intenta liberar el puerto o modificar este script para usar otro puerto."
    exit 1
fi

# Iniciar servidor PHP en segundo plano
echo "🚀 Iniciando servidor PHP en el puerto 8000..."
php -S localhost:8000 -t "$REPO_PATH" > /dev/null 2>&1 &
PHP_SERVER_PID=$!

# Verificar que el servidor se inició correctamente
sleep 1
if ! ps -p $PHP_SERVER_PID > /dev/null; then
    echo "❌ Error al iniciar el servidor PHP."
    exit 1
fi

echo "✅ Servidor PHP iniciado con PID: $PHP_SERVER_PID"
echo "   Para detener el servidor, ejecuta: kill $PHP_SERVER_PID"

# Abrir el navegador
echo "🔍 Abriendo Cat Store en el navegador..."
URL="http://localhost:8000"
open "$URL"

echo ""
echo "📝 INFORMACIÓN DE ACCESO:"
echo "- Usuario administrador: javial"
echo "- Contraseña: 12"
echo ""
echo "🚀 ¡Disfruta de tu tienda de gatos!"
echo ""
echo "💡 Presiona Ctrl+C para detener el servidor cuando termines."

# Mantener el script en ejecución para poder ver mensajes y poder usar Ctrl+C
trap "kill $PHP_SERVER_PID; echo '🛑 Servidor detenido.'; exit 0" INT
wait $PHP_SERVER_PID 