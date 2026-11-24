# Registro MntlGym · ISATUM 2026

Formulario público y listado privado en PHP 8.2 o posterior con PDO MySQL y MySQL o MariaDB. El formulario guarda nombre, correo, celular y ocupación. La confirmación informa que MntlGym enviará la liga en unos días. El envío del cuestionario se gestiona por separado.

## Publicación en cPanel

1. Selecciona PHP 8.2 o posterior con la extensión `pdo_mysql` y habilita HTTPS para el dominio del registro.
2. Crea una base de datos y un usuario MySQL en cPanel. Importa `schema.sql` en esa base con phpMyAdmin. Concede al usuario que usará PHP permisos de `INSERT` y `SELECT` sobre la tabla `registros`.
3. Sube el proyecto a la carpeta que servirá Apache, por ejemplo `public_html/registro_mntl`. Incluye el archivo oculto `.htaccess`, que selecciona `index.php` y bloquea el acceso web a la configuración y a los archivos internos. El servidor debe permitir las reglas de `.htaccess` y tener `mod_rewrite` activo.
4. Copia `config.example.php` a `config.local.php` junto a `app.php`. Configura el DSN y las credenciales de MySQL con el prefijo que asignó cPanel. Define un usuario administrativo y un hash de contraseña. Desde Terminal en cPanel, o en un equipo con PHP y Bash, genera el hash sin guardar la contraseña en el historial:

   ```bash
   read -rsp 'Contraseña del listado: ' clave; printf '\n'
   printf '%s' "$clave" | php -r 'echo password_hash(stream_get_contents(STDIN), PASSWORD_DEFAULT), PHP_EOL;'
   unset clave
   ```

   Pega el hash como cadena entre comillas simples en `admin_password_hash`. Limita los permisos de `config.local.php` a la cuenta que ejecuta PHP.
5. Abre la raíz del dominio para probar el formulario. Consulta `/registros.php` con el usuario administrativo. El listado muestra primero los registros más recientes.

## Desarrollo local

Importa `schema.sql` en una base local y configura `config.local.php` como en cPanel. En MAMP con Apache, coloca el proyecto en `/Applications/MAMP/htdocs/mntlgym_isatum` y usa `/Applications/MAMP/htdocs` como Document Root. Con los puertos predeterminados de MAMP, abre `http://localhost:8888/mntlgym_isatum/` y luego `http://localhost:8888/mntlgym_isatum/registros.php`. Para MySQL usa `127.0.0.1` y el puerto que muestra MAMP, normalmente `8889`.
