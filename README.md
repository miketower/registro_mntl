# Registro MntlGym · ISATUM 2026

Formulario público y listado privado en PHP 8.2 o posterior con PDO MySQL y MySQL o MariaDB. El formulario guarda nombre, correo, celular y ocupación. La confirmación informa que MntlGym enviará la liga en unos días. El envío del cuestionario se gestiona por separado.

## Publicación en cPanel

1. Selecciona PHP 8.2 o posterior con la extensión `pdo_mysql` y habilita HTTPS para el dominio del registro.
2. Crea una base de datos y un usuario MySQL en cPanel. Importa `schema.sql` en esa base con phpMyAdmin. Concede al usuario que usará PHP permisos de `INSERT` y `SELECT` sobre la tabla `registros`.
3. Sube el proyecto fuera de `public_html`, por ejemplo a `/home/CUENTA/registro_mntl`. En el dominio o subdominio del registro configura la raíz pública como `/home/CUENTA/registro_mntl/public`. Si el plan exige una raíz bajo `public_html`, sube solo el contenido de `public/` allí y ajusta la ruta de `app.php` en ambos puntos de entrada PHP para que apunte al directorio privado.
4. Copia `config.example.php` a `config.local.php` junto a `app.php`. Configura el DSN y las credenciales de MySQL con el prefijo que asignó cPanel. Define un usuario administrativo y un hash de contraseña. Desde Terminal en cPanel, o en un equipo con PHP y Bash, genera el hash sin guardar la contraseña en el historial:

   ```bash
   read -rsp 'Contraseña del listado: ' clave; printf '\n'
   printf '%s' "$clave" | php -r 'echo password_hash(stream_get_contents(STDIN), PASSWORD_DEFAULT), PHP_EOL;'
   unset clave
   ```

   Pega el hash como cadena entre comillas simples en `admin_password_hash`. Mantén `config.local.php` fuera de la raíz pública y con acceso limitado a la cuenta que ejecuta PHP.
5. Abre la raíz del dominio para probar el formulario. Consulta `/registros.php` con el usuario administrativo. El listado muestra primero los registros más recientes.

## Desarrollo local

Importa `schema.sql` en una base local y configura `config.local.php` como en cPanel. Desde la raíz del proyecto ejecuta `php -S 127.0.0.1:8080 -t public`. Verifica el envío de un registro de prueba y su aparición en `/registros.php` después de iniciar sesión.
