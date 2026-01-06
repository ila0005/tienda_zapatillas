document.addEventListener('DOMContentLoaded', function() {
    const enlacesEliminar = document.querySelectorAll('a[href*="eliminar"]');
    enlacesEliminar.forEach(link => {
        link.addEventListener('click', function(e) {
            if(!confirm('¿Estás seguro de eliminar este registro?')) e.preventDefault();
        });
    });
});
