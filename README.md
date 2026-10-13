# Buscador seguro de productos con PHP y PDO

Actividad: reemplazar la consulta vulnerable del ZIP por una API PHP que usa PDO y consultas preparadas, conectada a una interfaz con JavaScript `fetch`.

## Qué hacía el ejemplo vulnerable

El archivo original armaba SQL concatenando directamente el valor `q` dentro de la cadena de consulta. Una entrada con comillas podía alterar la consulta. También devolvía errores internos de conexión al navegador y el archivo de conexión del ZIP tenía una línea mal formada.

En esta versión, la búsqueda y el alta usan `PDO::prepare()` con parámetros enlazados. El valor del usuario se trata como dato y no como parte del código SQL. Las validaciones de longitud y formato ayudan a mantener datos válidos, pero la protección contra inyección SQL proviene de las consultas preparadas, no de “limpiar” caracteres manualmente. En el navegador, los valores devueltos se agregan con `textContent` para que texto ingresado por usuarios no se interprete como HTML.

## Requisitos

- XAMPP (Apache, PHP y MySQL/MariaDB) con las extensiones PDO y PDO MySQL habilitadas.
- phpMyAdmin.

## Instalación local

1. Copiá la carpeta `buscador-seguro-pdo` dentro de `C:\xampp\htdocs\`.
2. Iniciá Apache y MySQL desde el panel de XAMPP.
3. Abrí phpMyAdmin, seleccioná **Importar** y ejecutá `database.sql` (o pegá su contenido en la pestaña SQL).
4. Revisá las credenciales en `config/conexion.php`. Por defecto, esta configuración usa `localhost`, base `clase_backend`, usuario `root` y contraseña `root`, como el ZIP recibido. Si tu XAMPP tiene otra contraseña, cambiá `$pass` o configurá las variables de entorno `DB_HOST`, `DB_NAME`, `DB_USER` y `DB_PASS`.
5. Abrí `http://localhost/buscador-seguro-pdo/`.

La página carga el catálogo al abrirse. El formulario de búsqueda usa `GET`; el formulario de alta envía JSON mediante `POST`. Ambos acceden a `api/productos.php`.

## Resumen de la API

- `GET /api/productos.php?q=texto`: busca por nombre o categoría; `q` puede omitirse para listar todo.
- `POST /api/productos.php`: recibe `{ "nombre": "...", "categoria": "...", "precio": 123.45, "stock": 2 }` y agrega un producto.

## Publicar en GitHub

Creá un repositorio vacío en tu cuenta de GitHub, luego abrí una terminal en esta carpeta y ejecutá:

```bash
git init
git add .
git commit -m "Implementa buscador seguro con PDO"
git branch -M main
git remote add origin https://github.com/TU-USUARIO/TU-REPOSITORIO.git
git push -u origin main
```

Reemplazá `TU-USUARIO` y `TU-REPOSITORIO` por los datos del repositorio. Para entregar, compartí la URL de ese repositorio. El backend PHP necesita un servidor PHP/MySQL; GitHub Pages por sí solo no ejecuta PHP.
