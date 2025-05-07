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
sudo chown -R root:root /var/www/catstore.jaimedigitalstudio.com

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
        # IMPORTANTE: Usa la versión 8.3 del socket PHP instalada en el servidor
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
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

### Averiguar Credenciales de MySQL en la VPS

Para encontrar las credenciales de MySQL existentes en tu VPS, puedes:

1. **Revisar archivos de configuración de otros proyectos:**

```bash
# Buscar archivos de configuración PHP que contengan conexiones a MySQL
sudo grep -r "mysqli_connect\|PDO" /var/www/

# Buscar en archivos típicos de configuración
sudo grep -r "DB_" /var/www/ --include="*.php"
sudo grep -r "database" /var/www/ --include="*.js" --include="*.json" --include="*.php"
```

2. **Verificar si existe un archivo .my.cnf para el usuario root:**

```bash
sudo cat /root/.my.cnf
```

3. **Verificar si puedes acceder a MySQL sin contraseña como root:**

```bash
sudo mysql -u root
```

Si funciona, significa que el usuario root está configurado sin contraseña o mediante autenticación socket.

4. **Listar usuarios existentes en MySQL (si puedes entrar):**

```bash
sudo mysql -e "SELECT user, host FROM mysql.user;"
```

5. **Verificar la configuración existente para otros proyectos PM2:**

```bash
# Inspeccionar configuración de otros proyectos similares
cd /path/to/other/pm2/project
cat config/database.php
```

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
sudo nano /etc/php/8.3/fpm/php.ini 
# Versión 8.3.6 instalada en el servidor
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
sudo systemctl restart php8.3-fpm
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
$username = 'root';  // Usar root con la nueva contraseña
$password = 'Jva-Mvc-5171';  // La contraseña que has establecido
?>
```

### Solucionar "Error de conexión a la base de datos"

Si obtienes un error de conexión a la base de datos al intentar iniciar sesión, sigue estos pasos:

1. **Verifica que la base de datos existe:**

```bash
sudo mysql -u root -p -e "SHOW DATABASES;" | grep gatos
```

Ingresa la contraseña cuando se te solicite.

2. **Asegúrate de que los permisos son correctos:**

```bash
# Otorgar permisos completos al usuario root para la base de datos 'gatos'
sudo mysql -u root -p -e "GRANT ALL PRIVILEGES ON gatos.* TO 'root'@'localhost';"
```

Ingresa la contraseña cuando se te solicite.

3. **Verificar que PHP puede conectarse a MySQL:**

Crea un archivo de prueba en el directorio raíz del sitio web:

```bash
sudo nano /var/www/catstore.jaimedigitalstudio.com/db_test.php
```

Con el siguiente contenido:

```php
<?php
$host = 'localhost';
$dbname = 'gatos';
$username = 'root';
$password = 'Jva-Mvc-5171';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    // Establecer el modo de error PDO a excepción
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Conexión exitosa a la base de datos";
} catch(PDOException $e) {
    echo "Error de conexión: " . $e->getMessage();
}
?>
```

Accede a https://catstore.jaimedigitalstudio.com/db_test.php para ver si la conexión funciona.

4. **Verifica los módulos PHP de MySQL:**

```bash
sudo apt-get install php8.3-mysql
sudo systemctl restart php8.3-fpm
```

5. **Debuggea el archivo AuthController.php:**

```bash
sudo nano /var/www/catstore.jaimedigitalstudio.com/controllers/AuthController.php
```

Busca la parte donde se realiza la conexión a la base de datos y actualiza las credenciales:

```php
// Busca algo como esto y actualiza usuario/contraseña
$conexion = new PDO("mysql:host=localhost;dbname=gatos", "root", "Jva-Mvc-5171");
// o
$mysqli = new mysqli("localhost", "root", "Jva-Mvc-5171", "gatos");
```

### Resolver error "Access denied for user 'root'@'localhost'"

Ya has cambiado el método de autenticación de root. Ahora, para cualquier comando MySQL, necesitas incluir la contraseña:

1. **Para ejecutar comandos MySQL desde la terminal:**

```bash
sudo mysql -u root -p
```

Luego ingresa la contraseña cuando se te solicite.

2. **Para ejecutar comandos MySQL directamente:**

```bash
sudo mysql -u root -p"Jva-Mvc-5171" -e "TU COMANDO SQL AQUÍ"
```

Por ejemplo:

```bash
sudo mysql -u root -p"Jva-Mvc-5171" -e "SHOW DATABASES;"
```

3. **Para importar archivos SQL:**

```bash
sudo mysql -u root -p"Jva-Mvc-5171" < /ruta/al/archivo.sql
```

4. **Para crear o actualizar la base de datos:**

```bash
sudo mysql -u root -p"Jva-Mvc-5171" < /var/www/catstore.jaimedigitalstudio.com/crear_bd_fix.sql
```

5. **Para insertar los datos de gatos:**

```bash
sudo mysql -u root -p"Jva-Mvc-5171" < /var/www/catstore.jaimedigitalstudio.com/insertar_gatos_vps.sql
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
sudo tail -f /var/log/php8.3-fpm.log
```

### Verificación del Socket de PHP-FPM

Si ves errores como "No such file or directory" para el socket de PHP:

```bash
# Verifica qué versiones de PHP están instaladas
ls /var/run/php/

# Asegúrate de que el servicio PHP-FPM esté ejecutándose
sudo systemctl status php8.3-fpm

# Si necesitas instalar PHP 8.3-FPM:
sudo apt update
sudo apt install php8.3-fpm
```

### Permisos

Si encuentras problemas de permisos:

```bash
sudo chown -R www-data:www-data /var/www/catstore.jaimedigitalstudio.com
```

### Reinicios de Servicios

```bash
sudo systemctl restart nginx
sudo systemctl restart php8.3-fpm
sudo systemctl restart mysql
``` 