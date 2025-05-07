# Plan de Desarrollo: Cat Store

## Estructura de la Base de Datos
La base de datos ya está definida con las siguientes tablas:

- **usuario**: Almacena información de usuarios (username, contraseña, nombre, apellido, email)
- **animal**: Almacena información de los gatos (id, nombre, tipo, color, sexo, precio, foto, fecha_añadido)
- **carrito**: Relaciona usuarios con productos en su carrito (id_animal, username_usuario)
- **compra**: Registra las compras realizadas (fecha, id_animal, username_usuario)

## Estructura de Directorios

```
cat-store/
├── assets/
│   ├── css/
│   │   ├── styles.css
│   │   ├── auth.css
│   │   └── admin.css
│   ├── js/
│   │   ├── auth.js
│   │   ├── store.js
│   │   ├── cart.js
│   │   ├── profile.js
│   │   └── admin.js
│   └── img/
│       ├── logo.png
│       ├── icons/
│       └── cats/
├── config/
│   └── db_config.php
├── includes/
│   ├── header.php
│   ├── footer.php
│   ├── navbar.php
│   └── auth_middleware.php
├── models/
│   ├── User.php
│   ├── Animal.php
│   ├── Cart.php
│   └── Purchase.php
├── controllers/
│   ├── AuthController.php
│   ├── StoreController.php
│   ├── CartController.php
│   ├── ProfileController.php
│   └── AdminController.php
├── views/
│   ├── auth/
│   │   ├── login.php
│   │   └── register.php
│   ├── store/
│   │   ├── index.php
│   │   └── filters.php
│   ├── cart/
│   │   ├── cart_drawer.php
│   │   └── checkout.php
│   ├── profile/
│   │   ├── user_info.php
│   │   └── purchase_history.php
│   └── admin/
│       ├── products.php
│       └── product_form.php
├── api/
│   ├── auth.php
│   ├── store.php
│   ├── cart.php
│   ├── profile.php
│   └── admin.php
├── database/
│   ├── create_db.sql
│   └── seed_data.sql
├── index.php
└── README.md
```

## [PLAN AUTOMÁTICO]

- [x] **Paso 1:** Configuración inicial del proyecto
    - Archivo: `config/db_config.php`
    - Elemento: `Configuración de conexión a la base de datos`
    - Tokens estimados: 500

- [x] **Paso 2:** Implementación del middleware de autenticación
    - Archivo: `includes/auth_middleware.php`
    - Elemento: `Middleware para gestionar sesiones y cookies`
    - Tokens estimados: 1,200

- [x] **Paso 3:** Desarrollo del sistema de autenticación
    - Archivo: `models/User.php`
    - Elemento: `Clase User para gestión de usuarios`
    - Tokens estimados: 1,500
    - Archivo: `controllers/AuthController.php`
    - Elemento: `Controlador de autenticación`
    - Tokens estimados: 1,800
    - Archivo: `views/auth/login.php`
    - Elemento: `Vista de inicio de sesión`
    - Tokens estimados: 1,200
    - Archivo: `views/auth/register.php`
    - Elemento: `Vista de registro`
    - Tokens estimados: 1,200
    - Archivo: `assets/js/auth.js`
    - Elemento: `JavaScript para autenticación`
    - Tokens estimados: 1,000
    - Archivo: `assets/css/auth.css`
    - Elemento: `Estilos para autenticación`
    - Tokens estimados: 800

- [x] **Paso 4:** Implementación de componentes comunes
    - Archivo: `includes/header.php`
    - Elemento: `Cabecera común`
    - Tokens estimados: 500
    - Archivo: `includes/footer.php`
    - Elemento: `Pie de página común`
    - Tokens estimados: 300
    - Archivo: `includes/navbar.php`
    - Elemento: `Barra de navegación`
    - Tokens estimados: 1,000
    - Archivo: `assets/css/styles.css`
    - Elemento: `Estilos comunes`
    - Tokens estimados: 1,500

- [x] **Paso 5:** Desarrollo del escaparate principal
    - Archivo: `models/Animal.php`
    - Elemento: `Clase Animal para gestión de productos`
    - Tokens estimados: 1,500
    - Archivo: `controllers/StoreController.php`
    - Elemento: `Controlador de la tienda`
    - Tokens estimados: 1,800
    - Archivo: `views/store/index.php`
    - Elemento: `Vista principal de la tienda`
    - Tokens estimados: 1,500
    - Archivo: `views/store/filters.php`
    - Elemento: `Componente de filtros`
    - Tokens estimados: 1,000
    - Archivo: `assets/js/store.js`
    - Elemento: `JavaScript para la tienda`
    - Tokens estimados: 1,800

- [x] **Paso 6:** Implementación del carrito de compra
    - Archivo: `models/Cart.php`
    - Elemento: `Clase Cart para gestión del carrito`
    - Tokens estimados: 1,500
    - Archivo: `controllers/CartController.php`
    - Elemento: `Controlador del carrito`
    - Tokens estimados: 1,800
    - Archivo: `views/cart/checkout.php`
    - Elemento: `Vista de checkout`
    - Tokens estimados: 1,200
    - Archivo: `assets/js/cart.js`
    - Elemento: `JavaScript para el carrito`
    - Tokens estimados: 1,800
    - Archivo: `assets/css/cart.css`
    - Elemento: `Estilos para el carrito`
    - Tokens estimados: 1,800
    - Archivo: `api/cart.php`
    - Elemento: `API del carrito`
    - Tokens estimados: 1,200

- [x] **Paso 7:** Desarrollo del perfil de usuario
    - Archivo: `models/Purchase.php`
    - Elemento: `Clase Purchase para gestión de compras`
    - Tokens estimados: 1,200
    - Archivo: `controllers/ProfileController.php`
    - Elemento: `Controlador del perfil`
    - Tokens estimados: 1,500
    - Archivo: `views/profile/user_info.php`
    - Elemento: `Vista de información del usuario`
    - Tokens estimados: 1,000
    - Archivo: `views/profile/purchase_history.php`
    - Elemento: `Vista del historial de compras`
    - Tokens estimados: 1,200
    - Archivo: `assets/js/profile.js`
    - Elemento: `JavaScript para el perfil`
    - Tokens estimados: 1,200
    - Archivo: `api/profile.php`
    - Elemento: `API del perfil`
    - Tokens estimados: 1,000

- [x] **Paso 8:** Implementación del área de administración
    - Archivo: `controllers/AdminController.php`
    - Elemento: `Controlador de administración`
    - Tokens estimados: 1,800
    - Archivo: `views/admin/products.php`
    - Elemento: `Vista de gestión de productos`
    - Tokens estimados: 1,500
    - Archivo: `views/admin/product_form.php`
    - Elemento: `Formulario de producto`
    - Tokens estimados: 1,200
    - Archivo: `assets/js/admin.js`
    - Elemento: `JavaScript para administración`
    - Tokens estimados: 1,800
    - Archivo: `assets/css/admin.css`
    - Elemento: `Estilos para administración`
    - Tokens estimados: 1,000
    - Archivo: `api/admin.php`
    - Elemento: `API de administración`
    - Tokens estimados: 1,200

- [x] **Paso 9:** Desarrollo de APIs para interacción frontend-backend
    - Archivo: `api/auth.php`
    - Elemento: `API de autenticación`
    - Tokens estimados: 1,200
    - Archivo: `api/store.php`
    - Elemento: `API de la tienda`
    - Tokens estimados: 1,200
    - Archivo: `api/profile.php`
    - Elemento: `API del perfil`
    - Tokens estimados: 1,000
    - Archivo: `api/admin.php`
    - Elemento: `API de administración`
    - Tokens estimados: 1,200

- [x] **Paso 10:** Implementación de la página principal y scripts iniciales
    - Archivo: `index.php`
    - Elemento: `Página principal`
    - Tokens estimados: 500
    - Archivo: `database/create_db.sql`
    - Elemento: `Script de creación de base de datos`
    - Tokens estimados: 800
    - Archivo: `database/seed_data.sql`
    - Elemento: `Script de datos iniciales`
    - Tokens estimados: 1,000
    - Archivo: `README.md`
    - Elemento: `Documentación del proyecto`
    - Tokens estimados: 1,000

## Detalles Técnicos

### Autenticación y Middleware
- Uso de cookies para mantener la sesión durante 1 semana
- Middleware para verificar la autenticación en cada página protegida
- Funciones para registro, inicio de sesión y cierre de sesión

### Escaparate Principal
- Hero section con título "Cat Store"
- Grid responsive para mostrar los productos (gatos)
- Componente de filtros con drawer lateral
- Cards de productos con foto, nombre, características y botón de añadir al carrito

### Carrito de Compra
- Indicador de número de elementos en el carrito
- Drawer lateral para previsualización rápida
- Página de checkout con resumen de productos y precio total
- Botón para finalizar compra

### Perfil de Usuario
- Visualización de información personal
- Opción para editar datos
- Tabla con historial de compras

### Área de Administración
- Grid/lista de productos con opciones de edición
- Formulario para añadir nuevos productos
- Gestión completa de productos (CRUD)

### Tecnologías a Utilizar
- **Frontend**: HTML5, CSS3, JavaScript (ES6+)
- **Backend**: PHP 8+
- **Base de datos**: MySQL
- **Librerías JS**: No se requieren frameworks pesados, se implementará con JavaScript vanilla
- **CSS**: Se utilizará un enfoque modular con variables CSS para mantener consistencia

## Consideraciones de Seguridad
- Validación de datos en frontend y backend
- Protección contra SQL Injection
- Almacenamiento seguro de contraseñas (hash + salt)
- Protección de rutas mediante middleware de autenticación
- Validación de permisos para área de administración

## [TAREA COMPLETADA]
- Total pasos ejecutados: 10/10
- Tokens máximos usados por edición: ~2,000
- Componentes creados/modificados: 35
- Líneas máximas por componente: 200 