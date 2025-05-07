# Guía de Despliegue de CatStore en VPS

Esta guía detalla los pasos para desplegar el proyecto CatStore (PHP/MySQL) en el VPS con dirección 178.16.130.178, accesible mediante `catstore.jaimedigitalstudio.com`.

## 1. Configuración DNS

Añade el siguiente registro DNS para el subdominio catstore:

```
Type    Name       Value             TTL
A       catstore   178.16.130.178    1800
```

## 2. Preparación del Servidor

### Clonar el Repositorio

```bash
# Crear el directorio para el proyecto
sudo mkdir -p /var/www/catstore.jaimedigitalstudio.com

# Asignar permisos (ajustar usuario según sea necesario)
sudo chown -R $USER:$USER /var/www/catstore.jaimedigitalstudio.com

# Clonar el repositorio
git clone https://github.com/RUTA_DEL_REPOSITORIO /var/www/catstore.jaimedigitalstudio.com

# Asignar permisos adecuados
sudo chmod -R 755 /var/www/catstore.jaimedigitalstudio.com
```

### Crear directorio para imágenes

```bash
sudo mkdir -p /var/www/catstore.jaimedigitalstudio.com/assets/img
sudo chmod -R 755 /var/www/catstore.jaimedigitalstudio.com/assets/img
```

## 3. Configuración de Nginx

Crea un nuevo archivo de configuración de Nginx:

```bash
sudo nano /etc/nginx/sites-available/catstore.jaimedigitalstudio.com
```

Añade la siguiente configuración:

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name catstore.jaimedigitalstudio.com;
    root /var/www/catstore.jaimedigitalstudio.com;
    index index.php index.html index.htm;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php7.4-fpm.sock; # Ajustar a tu versión de PHP
    }

    location ~ /\.ht {
        deny all;
    }
}
```

Activa el sitio y reinicia Nginx:

```bash
sudo ln -s /etc/nginx/sites-available/catstore.jaimedigitalstudio.com /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

## 4. Configuración SSL con Certbot

```bash
sudo certbot --nginx -d catstore.jaimedigitalstudio.com
```

## 5. Configuración de la Base de Datos

### Crear la Base de Datos y Tablas

```bash
sudo mysql < /var/www/catstore.jaimedigitalstudio.com/crear_bd_fix.sql
```

### Insertar Datos de Gatos

Primero, edita el archivo de inserción para ajustar las rutas de las imágenes:

```bash
sudo cp /var/www/catstore.jaimedigitalstudio.com/insertar_gatos.sql /var/www/catstore.jaimedigitalstudio.com/insertar_gatos_vps.sql
sudo nano /var/www/catstore.jaimedigitalstudio.com/insertar_gatos_vps.sql
```

Reemplaza todas las rutas de imágenes `/Users/usuario/Documents/PROYECTOS/PROYECTOS_PUBLICADOS/FLORIDA-CATS/img/` por `/var/www/catstore.jaimedigitalstudio.com/assets/img/`.

Verifica la configuración de MySQL para cargar archivos:

```bash
sudo mysql -e "SHOW VARIABLES LIKE 'secure_file_priv';"
```

Si `secure_file_priv` no está vacío, necesitarás ajustar la configuración de MySQL:

```bash
sudo nano /etc/mysql/mysql.conf.d/mysqld.cnf
```

Añade o modifica la línea:

```
secure_file_priv = ""
```

Reinicia MySQL:

```bash
sudo systemctl restart mysql
```

Ejecuta el script para insertar los datos:

```bash
sudo mysql < /var/www/catstore.jaimedigitalstudio.com/insertar_gatos_vps.sql
```

## 6. Configuración de PHP

Asegúrate de que PHP esté configurado correctamente:

```bash
sudo nano /etc/php/7.4/fpm/php.ini  # Ajusta la versión según corresponda
```

Ajusta los siguientes valores:

```
upload_max_filesize = 20M
post_max_size = 20M
memory_limit = 256M
max_execution_time = 300
```

Reinicia PHP-FPM:

```bash
sudo systemctl restart php7.4-fpm  # Ajusta la versión según corresponda
```

## 7. Permisos de Archivos

```bash
# Establecer permisos adecuados
sudo find /var/www/catstore.jaimedigitalstudio.com -type f -exec chmod 644 {} \;
sudo find /var/www/catstore.jaimedigitalstudio.com -type d -exec chmod 755 {} \;

# Asignar usuario del servicio web
sudo chown -R www-data:www-data /var/www/catstore.jaimedigitalstudio.com
```

## 8. Configuración de Conexión a Base de Datos

Edita el archivo de configuración de conexión a la base de datos en tu proyecto (ajusta la ruta según la estructura de tu proyecto):

```bash
sudo nano /var/www/catstore.jaimedigitalstudio.com/config/database.php
```

Asegúrate de que contenga los datos correctos:

```php
<?php
$host = 'localhost';
$dbname = 'gatos';
$username = 'tu_usuario';  // Ajusta según tu configuración
$password = 'tu_password'; // Ajusta según tu configuración
?>
```

## 9. Prueba Final

Visita `https://catstore.jaimedigitalstudio.com` en tu navegador para verificar que todo funciona correctamente.

## Solución de Problemas

### Logs de Nginx

```bash
sudo tail -f /var/log/nginx/error.log
sudo tail -f /var/log/nginx/access.log
```

### Logs de PHP

```bash
sudo tail -f /var/log/php7.4-fpm.log  # Ajusta la versión según corresponda
```

### Permisos

Si encuentras problemas de permisos:

```bash
sudo chown -R www-data:www-data /var/www/catstore.jaimedigitalstudio.com
```

### Reinicios de Servicios

```bash
sudo systemctl restart nginx
sudo systemctl restart php7.4-fpm  # Ajusta la versión según corresponda
sudo systemctl restart mysql
``` 