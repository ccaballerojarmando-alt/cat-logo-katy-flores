# Katy Flores — catálogo web sencillo

Sistema sencillo de catálogo y administración para una tienda de ropa de fardo. Utiliza PHP, MySQL, HTML, CSS y JavaScript sin dependencias de Node.js ni Composer.

## Requisitos

- PHP 7.4 o superior con PDO MySQL y Fileinfo habilitados.
- MySQL 5.7+ o MariaDB 10.3+.
- Apache (XAMPP, InfinityFree u otro hosting PHP).

## Instalación en XAMPP

1. Descomprime la carpeta `catalogo_katy_flores` dentro de `C:\xampp\htdocs\`.
2. Inicia Apache y MySQL desde el panel de XAMPP.
3. Abre `http://localhost/phpmyadmin`, crea una base de datos llamada `catalogo_katy_flores` con cotejamiento `utf8mb4_unicode_ci` e importa `database/catalogo_katy_flores.sql`.
4. Edita `config/database.php`: usuario MySQL `root`, contraseña vacía por defecto en XAMPP, y `APP_BASE_PATH` como `/catalogo_katy_flores`.
5. Visita `http://localhost/catalogo_katy_flores/setup.php` y crea la cuenta del propietario. La instalación inicial se cierra automáticamente al crearla.
6. Entra desde el enlace **Administración** del pie de página o visita `/admin/login.php`.

## Publicación en InfinityFree

1. Crea una base de datos MySQL desde el panel de InfinityFree y anota el host, nombre, usuario y contraseña que te asignen. El nombre normalmente incluye un prefijo de cuenta.
2. En phpMyAdmin, selecciona esa base de datos e importa `database/catalogo_katy_flores.sql`. El archivo SQL no intenta crear la base, para admitir los permisos habituales del hosting compartido.
3. Sube el contenido de esta carpeta a `htdocs` o `public_html` mediante el administrador de archivos o FTP.
4. Edita `config/database.php` con las credenciales asignadas y deja `APP_BASE_PATH` como `''` si el proyecto está directamente en la raíz del sitio. Si está en una subcarpeta, usa por ejemplo `/catalogo_katy_flores`.
5. Abre `https://tu-dominio/setup.php` para crear la cuenta del propietario y luego elimina `setup.php` del servidor.
6. Inicia sesión en Administración → Configuración y registra el WhatsApp con código de país y solo dígitos (por ejemplo `59171234567`).

## Uso

- El catálogo es público; los visitantes no crean cuentas.
- El administrador puede publicar, editar y eliminar productos, categorías y tallas.
- Se admiten hasta cinco imágenes JPG, PNG o WebP por producto, de máximo 5 MB cada una.
- La búsqueda y los filtros por categoría, talla, precio y disponibilidad se combinan en el servidor.
- Los botones de consulta generan un enlace `wa.me` con la información de la prenda.
- La portada muestra un QR para compartir la dirección pública. Configúrala en Administración → Configuración después de publicar.
- `index.html` redirige a `index.php` como entrada de compatibilidad. El catálogo necesita PHP y MySQL, por lo que no funciona en un hosting exclusivamente estático; InfinityFree sí permite ejecutar la versión PHP.

## Código QR

El QR se crea a partir del enlace público configurado o, si se deja vacío, de la dirección actual del sitio. La imagen QR se obtiene desde QRServer; si ese servicio no está disponible, el catálogo sigue funcionando y el enlace público se muestra debajo del código.

## Estructura

- `database/catalogo_katy_flores.sql`: tablas, relaciones y datos iniciales de categorías, tallas y tienda.
- `config/database.php`: conexión y rutas del sitio.
- `setup.php`: creación de la primera cuenta administrativa.
- `admin/`: acceso privado y gestión.
- `includes/`: conexión, seguridad y elementos compartidos.
- `uploads/productos/`: fotografías cargadas por el administrador.

## Notas de seguridad y operación

- Antes de publicar, cambia las credenciales de base de datos y configura el WhatsApp de la tienda.
- El archivo `setup.php` solo permite crear un usuario cuando aún no hay ninguno; en producción, bórralo tras el primer acceso.
- No subas `config/database.php` a repositorios públicos con credenciales reales.
- Mantén una copia de seguridad de la base de datos y de `uploads/productos/`.
- Si el hosting limita las subidas, revisa sus valores `upload_max_filesize` y `post_max_size`; no hace falta modificar el sistema.

### Si Apache muestra “Prohibido” al abrir `index.php`

El `.htaccess` de la carpeta principal debe permitir que Apache ejecute archivos PHP. No copies allí el `.htaccess` que está dentro de `uploads/`, porque ese archivo bloquea intencionalmente la ejecución de PHP dentro de la carpeta de fotografías. El `.htaccess` principal de este paquete ya tiene la configuración correcta. Si el error continúa en XAMPP, reemplaza el archivo `C:\xampp\htdocs\catalogo_katy_flores\.htaccess` con el `.htaccess` principal incluido en el ZIP y vuelve a cargar la página.
