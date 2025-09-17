# Sistema de Gestión de Cupones 🎫

## 📋 Resumen del Proyecto

Sistema web completo para la gestión y canje de cupones desarrollado con **arquitectura MVC** en PHP. Permite a los administradores crear, editar y gestionar cupones, mientras que los usuarios pueden visualizar y canjear cupones disponibles.

### 🛠 Tecnologías Utilizadas
- **Backend:** PHP 8+ con PDO
- **Base de Datos:** MySQL/MariaDB
- **Frontend:** HTML5, CSS3, JavaScript Vanilla
- **Autenticación:** Sesiones PHP
- **Seguridad:** Prepared Statements
- **Servidor Web:** Apache con mod_rewrite

### 🏗 Estructura MVC Implementada

**Models (Modelos):**
- `Cupon.php` - Gestión CRUD de cupones y lógica de canje
- `Usuario.php` - Manejo de usuarios y autenticación
- `Canje.php` - Registro de canjes realizados

**Views (Vistas):**
- `login.php` - Interfaz de autenticación
- `admin.php` - Panel administrativo para gestión de cupones
- `usuario.php` - Panel de usuario para visualizar y canjear cupones

**Controllers (Controladores):**
- `AuthController.php` - Manejo de autenticación y sesiones
- `AdminController.php` - Lógica del panel administrativo
- `UserController.php` - Lógica del panel de usuario

### 📁 Estructura Completa del Proyecto

```
coupon-system/
├── .env.example
├── .gitignore
├── .htaccess
├── README.md
├── assets/
│   ├── css/
│   │   ├── admin.css
│   │   ├── login.css
│   │   └── usuario.css
│   └── js/
│       ├── admin.js
│       ├── login.js
│       └── usuario.js
├── config/
│   └── config.php
├── controllers/
│   ├── AdminController.php
│   ├── AuthController.php
│   └── UserController.php
├── index.php
├── install.php
├── models/
│   ├── Canje.php
│   ├── Cupon.php
│   └── Usuario.php
└── views/
    ├── admin.php
    ├── forgot-password.php
    ├── login.php
    └── usuario.php
```

---

## 📋 Prerrequisitos

Antes de instalar el sistema, asegúrate de tener:

- **PHP 8.0 o superior** con extensiones:
  - PDO
  - PDO_MySQL
  - mbstring
- **MySQL/MariaDB** (versión 5.7 o superior)
- **Servidor web** (Apache, Nginx) o usar el servidor integrado de PHP
- **Composer** (opcional, para dependencias futuras)

### 💻 Instalación de Prerrequisitos

#### macOS (usando Homebrew)
```bash
# Instalar PHP 8+ con extensiones necesarias
brew install php@8.2

# Instalar MySQL
brew install mysql
brew services start mysql

# Instalar Composer (opcional)
brew install composer
```

#### Ubuntu/Debian
```bash
# Actualizar repositorios
sudo apt update

# Instalar PHP 8+ y extensiones
sudo apt install php8.2 php8.2-mysql php8.2-pdo php8.2-mbstring php8.2-cli

# Instalar MySQL
sudo apt install mysql-server
sudo systemctl start mysql
sudo systemctl enable mysql

# Instalar Composer (opcional)
sudo apt install composer
```

#### Windows (usando Chocolatey)
```powershell
# Instalar PHP
choco install php

# Instalar MySQL
choco install mysql

# Instalar Composer (opcional)
choco install composer
```

#### Verificar Instalación
```bash
# Verificar versión de PHP
php --version

# Verificar extensiones de PHP
php -m | grep -E "pdo|mysql|mbstring"

# Verificar MySQL
mysql --version
```

## 🚀 Instalación Local

### 1. Configurar Base de Datos
Crear archivo `.env` en la raíz del proyecto:

```env
DB_HOST=localhost
DB_DATABASE=coupon_system
DB_USERNAME=root
DB_PASSWORD=tu_password
DB_CHARSET=utf8mb4
```

### 2. Levantar Servidor de Desarrollo
En la terminal, navegar al directorio del proyecto y ejecutar:

```bash
# Opción 1: Servidor integrado de PHP (recomendado para desarrollo)
php -S localhost:8000

# Opción 2: Si usas XAMPP/WAMP/MAMP
# Colocar el proyecto en htdocs y acceder via http://localhost/coupon-system
```

### 3. Ejecutar Instalador
Abrir en el navegador: [http://localhost/install.php](http://localhost/install.php)

El instalador automáticamente:
- ✅ Crea la base de datos
- ✅ Crea todas las tablas necesarias
- ✅ Inserta datos de ejemplo

### 4. Acceder al Sistema
Una vez completada la instalación: [http://localhost:8000/views/login.php](http://localhost:8000/views/login.php)

### Credenciales de Prueba
- **Administrador:** admin / admin
- **Usuario:** usuario@test.com / 123456

## 📖 Guía de Uso

### 👨‍💼 Para Administradores
1. **Acceder al sistema** con credenciales `admin / admin`
2. **Panel principal** - Visualizar estadísticas de cupones
3. **Gestión de cupones:**
   - ➕ Crear nuevos cupones con código, descripción y límites
   - ✏️ Editar cupones existentes
   - 🗑️ Eliminar cupones
   - 📊 Ver historial de canjes

### 👤 Para Usuarios
1. **Acceder al sistema** con credenciales `usuario@test.com / 123456`
2. **Visualizar cupones** disponibles para canje
3. **Canjear cupones:**
   - Seleccionar cupón deseado
   - Confirmar canje
   - Ver confirmación y detalles
4. **Historial personal** de cupones canjeados

### 🔄 Flujo del Sistema
1. **Administrador** crea cupones con límites específicos
2. **Usuarios** visualizan cupones disponibles
3. **Sistema** valida disponibilidad antes del canje
4. **Registro** automático de todas las transacciones

---
