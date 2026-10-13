document.addEventListener('DOMContentLoaded', () => {
    const formBuscar = document.querySelector('#form-buscar');
    const inputBuscar = document.querySelector('#input-buscar');
    const formAlta = document.querySelector('#form-alta');
    const resultados = document.querySelector('#resultados');
    const estado = document.querySelector('#estado');
    const endpoint = 'api/productos.php';

    formBuscar.addEventListener('submit', async (event) => {
        event.preventDefault();
        await buscarProductos(inputBuscar.value.trim());
    });

    formAlta.addEventListener('submit', async (event) => {
        event.preventDefault();
        const datos = Object.fromEntries(new FormData(formAlta).entries());
        datos.precio = Number(datos.precio);
        datos.stock = Number(datos.stock);

        estado.textContent = 'Guardando producto…';
        try {
            const respuesta = await fetch(endpoint, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(datos),
            });
            const resultado = await respuesta.json();
            if (!respuesta.ok) throw new Error(resultado.mensaje || 'No se pudo guardar el producto.');

            formAlta.reset();
            formAlta.elements.stock.value = '0';
            estado.textContent = resultado.mensaje;
            await buscarProductos(inputBuscar.value.trim());
        } catch (error) {
            estado.textContent = error.message || 'No se pudo conectar con el servidor.';
        }
    });

    async function buscarProductos(termino) {
        estado.textContent = 'Buscando…';
        resultados.replaceChildren();
        try {
            const respuesta = await fetch(`${endpoint}?q=${encodeURIComponent(termino)}`);
            const resultado = await respuesta.json();
            if (!respuesta.ok) throw new Error(resultado.mensaje || 'No se pudo realizar la búsqueda.');
            renderizarProductos(resultado.data);
            estado.textContent = `${resultado.total} producto${resultado.total === 1 ? '' : 's'} encontrado${resultado.total === 1 ? '' : 's'}.`;
        } catch (error) {
            estado.textContent = error.message || 'No se pudo conectar con el servidor.';
        }
    }

    function renderizarProductos(productos) {
        const fragmento = document.createDocumentFragment();
        for (const producto of productos) {
            const tarjeta = document.createElement('article');
            tarjeta.className = 'tarjeta';

            const nombre = document.createElement('h3');
            nombre.textContent = producto.nombre;
            const categoria = document.createElement('p');
            categoria.className = 'categoria';
            categoria.textContent = producto.categoria;
            const detalle = document.createElement('p');
            detalle.className = 'detalle';
            detalle.textContent = `Precio: $${Number(producto.precio).toLocaleString('es-AR', { minimumFractionDigits: 2 })} · Stock: ${producto.stock}`;

            tarjeta.append(nombre, categoria, detalle);
            fragmento.append(tarjeta);
        }
        resultados.replaceChildren(fragmento);
    }

    buscarProductos('');
});
