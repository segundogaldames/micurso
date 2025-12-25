document.addEventListener('DOMContentLoaded', function () {
        // Verificamos que la librería se haya cargado en el objeto global
        if (typeof DataTable !== 'undefined') {
            new DataTable('#myTable', {
                responsive: true,
                language: {
                    // Traducción oficial al español
                    url: 'https://cdn.datatables.net/plug-ins/2.1.8/i18n/es-ES.json'
                }
            });
            console.log("DataTable listo.");
        } else {
            console.error("Error: DataTable no está definido. Revisa el orden de los scripts.");
        }
    });