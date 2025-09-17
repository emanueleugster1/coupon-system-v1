// Admin Panel JavaScript
// Funcionalidades para el panel administrativo de 25Watts

// Variables globales
let currentView = 'grid';
let currentFilter = '';
let searchTerm = '';

// Inicialización cuando el DOM está listo
document.addEventListener('DOMContentLoaded', function() {
    initializeAdminPanel();
    
    // Referencias para el menú móvil
    const mobileMenuToggle = document.getElementById('mobileMenuToggle');
    const sidebar = document.getElementById('sidebar');
    const mobileOverlay = document.getElementById('mobileOverlay');

    // Funcionalidad del menú móvil
    if (mobileMenuToggle && sidebar && mobileOverlay) {
        // Toggle mobile menu
        function toggleMobileMenu() {
            sidebar.classList.toggle('active');
            mobileOverlay.classList.toggle('active');
            
            // Change hamburger icon
            const icon = mobileMenuToggle.querySelector('i');
            if (sidebar.classList.contains('active')) {
                icon.className = 'bi bi-x';
            } else {
                icon.className = 'bi bi-list';
            }
        }
        
        // Event listeners para menú móvil
        mobileMenuToggle.addEventListener('click', toggleMobileMenu);
        mobileOverlay.addEventListener('click', toggleMobileMenu);
        
        // Handle window resize
        window.addEventListener('resize', function() {
            if (window.innerWidth > 768) {
                sidebar.classList.remove('active');
                mobileOverlay.classList.remove('active');
                const icon = mobileMenuToggle.querySelector('i');
                icon.className = 'bi bi-list';
            }
        });
    }
});

// Función principal de inicialización
function initializeAdminPanel() {
    initializeSearch();
    initializeFilters();
    initializeFormValidation();
    initializeImageUpload();
    initializePreviewUpdates();
    initializeTableInteractions();
    initializeUrlFilters(); // Inicializar filtros desde URL
    
    console.log('Panel administrativo inicializado correctamente');
}

// === FUNCIONES DE MENÚ MÓVIL (LEGACY - MANTENIDAS PARA COMPATIBILIDAD) ===
// Estas funciones se mantienen para compatibilidad con código existente
// La funcionalidad principal del menú móvil ahora está en el DOMContentLoaded

function initializeMobileMenu() {
    // Función legacy mantenida para compatibilidad
    console.log('initializeMobileMenu: Funcionalidad movida a DOMContentLoaded');
}

function toggleMobileMenu() {
    // Función legacy mantenida para compatibilidad
    const sidebar = document.getElementById('sidebar');
    const mobileOverlay = document.getElementById('mobileOverlay');
    const mobileMenuToggle = document.getElementById('mobileMenuToggle');
    
    if (sidebar && mobileOverlay && mobileMenuToggle) {
        sidebar.classList.toggle('active');
        mobileOverlay.classList.toggle('active');
        
        const icon = mobileMenuToggle.querySelector('i');
        if (sidebar.classList.contains('active')) {
            icon.className = 'bi bi-x';
        } else {
            icon.className = 'bi bi-list';
        }
    }
}

function openMobileMenu() {
    // Función legacy mantenida para compatibilidad
    const sidebar = document.getElementById('sidebar');
    const mobileOverlay = document.getElementById('mobileOverlay');
    const mobileMenuToggle = document.getElementById('mobileMenuToggle');
    
    if (sidebar && mobileOverlay && mobileMenuToggle) {
        sidebar.classList.add('active');
        mobileOverlay.classList.add('active');
        const icon = mobileMenuToggle.querySelector('i');
        if (icon) icon.className = 'bi bi-x';
    }
}

function closeMobileMenu() {
    // Función legacy mantenida para compatibilidad
    const sidebar = document.getElementById('sidebar');
    const mobileOverlay = document.getElementById('mobileOverlay');
    const mobileMenuToggle = document.getElementById('mobileMenuToggle');
    
    if (sidebar && mobileOverlay && mobileMenuToggle) {
        sidebar.classList.remove('active');
        mobileOverlay.classList.remove('active');
        const icon = mobileMenuToggle.querySelector('i');
        if (icon) icon.className = 'bi bi-list';
    }
}

// === FUNCIONES DE BÚSQUEDA ===
function initializeSearch() {
    const searchField = document.getElementById('searchField');
    if (searchField) {
        searchField.addEventListener('input', function(e) {
            searchTerm = e.target.value.toLowerCase();
            filtrarTabla();
        });
    }
}

// === FUNCIONES DE FILTRADO ===
function initializeFilters() {
    // Filtros de estado
    const filterTags = document.querySelectorAll('.filter-tag');
    filterTags.forEach(tag => {
        tag.addEventListener('click', function() {
            // Remover clase active de todos los tags
            filterTags.forEach(t => t.classList.remove('active'));
            // Agregar clase active al tag clickeado
            this.classList.add('active');
        });
    });
    
    // Inicializar dropdowns personalizados
    initializeCustomDropdowns();
}

// Función para manejar dropdowns personalizados
function initializeCustomDropdowns() {
    // Cerrar dropdowns al hacer click fuera
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.custom-dropdown')) {
            document.querySelectorAll('.custom-dropdown').forEach(dropdown => {
                dropdown.classList.remove('open');
            });
        }
    });
    
    // Manejar checkboxes de estado
    document.querySelectorAll('#estadoDropdown .checkbox-option').forEach(option => {
        option.addEventListener('click', function(e) {
            e.preventDefault();
            const checkbox = this.querySelector('input[type="checkbox"]');
            const isChecked = checkbox.checked;
            const optionText = this.querySelector('.option-text').textContent;
            
            // Si es "Todos", desmarcar otros
            if (optionText === 'Todos') {
                if (!isChecked) {
                    // Marcar "Todos" y desmarcar otros
                    checkbox.checked = true;
                    this.classList.add('active');
                    this.closest('.dropdown-options').querySelectorAll('.checkbox-option').forEach(opt => {
                        if (opt !== this) {
                            opt.querySelector('input[type="checkbox"]').checked = false;
                            opt.classList.remove('active');
                        }
                    });
                }
            } else {
                // Si se marca una opción específica, desmarcar "Todos"
                const todosOption = this.closest('.dropdown-options').querySelector('.checkbox-option');
                if (todosOption && todosOption.querySelector('.option-text').textContent === 'Todos') {
                    todosOption.querySelector('input[type="checkbox"]').checked = false;
                    todosOption.classList.remove('active');
                }
                
                // Toggle la opción actual
                checkbox.checked = !isChecked;
                this.classList.toggle('active', !isChecked);
            }
        });
    });
    
    // Manejar checkboxes de ordenamiento
    document.querySelectorAll('#ordenDropdown .checkbox-option').forEach(option => {
        option.addEventListener('click', function(e) {
            e.preventDefault();
            const checkbox = this.querySelector('input[type="checkbox"]');
            const isChecked = checkbox.checked;
            
            // Permitir múltiples opciones de ordenamiento
            checkbox.checked = !isChecked;
            this.classList.toggle('active', !isChecked);
        });
    });
    
    // Agregar listener para Enter en toda la página
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            aplicarFiltros();
        }
    });
}

// Función para toggle de dropdowns
function toggleDropdown(dropdownId) {
    const dropdown = document.getElementById(dropdownId);
    const customDropdown = dropdown.closest('.custom-dropdown');
    
    // Cerrar otros dropdowns
    document.querySelectorAll('.custom-dropdown').forEach(dd => {
        if (dd !== customDropdown) {
            dd.classList.remove('open');
        }
    });
    
    // Toggle el dropdown actual
    customDropdown.classList.toggle('open');
}

// Nueva función para aplicar filtros
function aplicarFiltros() {
    const estadosSeleccionados = [];
    const ordenSeleccionado = [];
    
    // Obtener estados seleccionados
    document.querySelectorAll('#estadoDropdown .checkbox-option').forEach(option => {
        const checkbox = option.querySelector('input[type="checkbox"]');
        const optionText = option.querySelector('.option-text').textContent;
        
        if (checkbox.checked && optionText !== 'Todos') {
            if (optionText === 'Activos') {
                estadosSeleccionados.push('activo');
            } else if (optionText === 'Inactivos') {
                estadosSeleccionados.push('inactivo');
            } else if (optionText === 'Canjeados') {
                estadosSeleccionados.push('canjeado');
            }
        }
    });
    
    // Obtener ordenamiento seleccionado
    document.querySelectorAll('#ordenDropdown .checkbox-option').forEach(option => {
        const checkbox = option.querySelector('input[type="checkbox"]');
        const optionText = option.querySelector('.option-text').textContent;
        
        if (checkbox.checked) {
            if (optionText === 'Fecha') {
                ordenSeleccionado.push('fecha');
            } else if (optionText === 'Nombre') {
                ordenSeleccionado.push('nombre');
            } else if (optionText === 'Valor') {
                ordenSeleccionado.push('valor');
            }
        }
    });
    
    // Construir URL con parámetros preservando los existentes
    const currentUrl = new URL(window.location);
    const params = new URLSearchParams(currentUrl.search);
    
    // Asegurar que action esté establecido
    params.set('action', 'list');
    
    if (estadosSeleccionados.length > 0) {
        params.set('estados', estadosSeleccionados.join(','));
    } else {
        params.delete('estados');
    }
    
    if (ordenSeleccionado.length > 0) {
        params.set('orden', ordenSeleccionado.join(','));
    } else {
        params.delete('orden');
    }
    
    // Recargar página con filtros
    window.location.href = '?' + params.toString();
}

function filtrarPorEstado(estado) {
    currentFilter = estado;
    filtrarTabla();
    
    // Actualizar filtros visuales
    const filterTags = document.querySelectorAll('.filter-tag');
    filterTags.forEach(tag => tag.classList.remove('active'));
    
    if (estado === '') {
        document.querySelector('.filter-tag[onclick="filtrarPorEstado(\'\')"').classList.add('active');
    } else if (estado === 'activo') {
        document.querySelector('.filter-tag[onclick="filtrarPorEstado(\'activo\')"').classList.add('active');
    } else if (estado === 'inactivo') {
        document.querySelector('.filter-tag[onclick="filtrarPorEstado(\'inactivo\')"').classList.add('active');
    }
}

function filtrarTabla() {
    const filas = document.querySelectorAll('.benefits-table tbody tr');
    
    filas.forEach(fila => {
        const estado = fila.getAttribute('data-estado');
        const textoFila = fila.textContent.toLowerCase();
        
        let mostrarPorEstado = currentFilter === '' || estado === currentFilter;
        let mostrarPorBusqueda = searchTerm === '' || textoFila.includes(searchTerm);
        
        if (mostrarPorEstado && mostrarPorBusqueda) {
            fila.style.display = '';
        } else {
            fila.style.display = 'none';
        }
    });
}

// === FUNCIONES DE VISTA ===
function cambiarVista(vista) {
    currentView = vista;
    
    // Actualizar botones de vista
    const viewBtns = document.querySelectorAll('.view-btn');
    viewBtns.forEach(btn => btn.classList.remove('active'));
    
    if (vista === 'grid') {
        document.querySelector('.view-btn[onclick="cambiarVista(\'grid\')"').classList.add('active');
    } else {
        document.querySelector('.view-btn[onclick="cambiarVista(\'list\')"').classList.add('active');
    }
    
    // Aquí se podría implementar el cambio de vista si fuera necesario
    console.log('Vista cambiada a:', vista);
}

// === FUNCIONES CRUD ===
function editarCupon(id) {
    if (id) {
        window.location.href = `?action=edit&id=${id}`;
    }
}

function eliminarCupon(id, codigo) {
    if (confirm(`¿Estás seguro de que deseas eliminar el cupón "${codigo}"?\n\nEsta acción no se puede deshacer.`)) {
        // Crear formulario para envío POST
        const form = document.createElement('form');
        form.method = 'POST';
        form.style.display = 'none';
        
        const accionInput = document.createElement('input');
        accionInput.type = 'hidden';
        accionInput.name = 'accion';
        accionInput.value = 'eliminar';
        
        const idInput = document.createElement('input');
        idInput.type = 'hidden';
        idInput.name = 'id';
        idInput.value = id;
        
        form.appendChild(accionInput);
        form.appendChild(idInput);
        document.body.appendChild(form);
        
        form.submit();
    }
}

function confirmarEliminacion(id, codigo) {
    if (confirm(`¿Estás seguro de que deseas eliminar el cupón "${codigo}"?\n\nEsta acción no se puede deshacer y eliminará permanentemente este beneficio.`)) {
        // Crear formulario para envío POST
        const form = document.createElement('form');
        form.method = 'POST';
        form.style.display = 'none';
        
        const accionInput = document.createElement('input');
        accionInput.type = 'hidden';
        accionInput.name = 'accion';
        accionInput.value = 'eliminar';
        
        const idInput = document.createElement('input');
        idInput.type = 'hidden';
        idInput.name = 'id';
        idInput.value = id;
        
        form.appendChild(accionInput);
        form.appendChild(idInput);
        document.body.appendChild(form);
        
        form.submit();
    }
}

// === FUNCIONES DE FORMULARIO ===
function initializeFormValidation() {
    const form = document.getElementById('coupon-form');
    if (form) {
        form.addEventListener('submit', function(e) {
            if (!validarFormulario()) {
                e.preventDefault();
                return false;
            }
        });
    }
}

function validarFormulario() {
    const codigo = document.getElementById('codigo');
    const descripcion = document.getElementById('descripcion');
    const valor = document.getElementById('valor');
    const estado = document.getElementById('estado');
    
    let esValido = true;
    let errores = [];
    
    // Validar código
    if (!codigo || codigo.value.trim() === '') {
        errores.push('El código es obligatorio');
        esValido = false;
    }
    
    // Validar descripción
    if (!descripcion || descripcion.value.trim() === '') {
        errores.push('La descripción es obligatoria');
        esValido = false;
    }
    
    // Validar valor/puntos
    if (!valor || valor.value === '' || parseFloat(valor.value) <= 0) {
        errores.push('Los puntos deben ser un número mayor a 0');
        esValido = false;
    }
    
    // Validar estado
    if (!estado || estado.value === '') {
        errores.push('Debe seleccionar un estado');
        esValido = false;
    }
    
    if (!esValido) {
        alert('Por favor corrige los siguientes errores:\n\n' + errores.join('\n'));
    }
    
    return esValido;
}

// === FUNCIONES DE IMAGEN ===
function initializeImageUpload() {
    const uploadPlaceholder = document.querySelector('.upload-placeholder');
    const fileInput = document.querySelector('input[type="file"][name="imagen"]');
    
    if (uploadPlaceholder && fileInput) {
        uploadPlaceholder.addEventListener('click', function() {
            fileInput.click();
        });
        
        fileInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    uploadPlaceholder.innerHTML = `
                        <img src="${e.target.result}" alt="Preview" style="max-width: 100%; max-height: 100px; object-fit: cover;">
                        <span class="upload-text">Cambiar imagen</span>
                    `;
                };
                reader.readAsDataURL(file);
            }
        });
    }
}

// === FUNCIONES DE VISTA PREVIA ===
function initializePreviewUpdates() {
    // Actualizar vista previa en tiempo real
    const campos = ['codigo', 'descripcion', 'valor', 'estado'];
    
    campos.forEach(campo => {
        const input = document.getElementById(campo);
        if (input) {
            input.addEventListener('input', actualizarVistaPrevia);
            input.addEventListener('change', actualizarVistaPrevia);
        }
    });
}

function actualizarVistaPrevia() {
    const codigo = document.getElementById('codigo')?.value || 'Código';
    const descripcion = document.getElementById('descripcion')?.value || 'Descripción del servicio';
    const valor = document.getElementById('valor')?.value || '0';
    const estado = document.getElementById('estado')?.value || 'activo';
    
    // Actualizar elementos de vista previa
    const previewDiscount = document.getElementById('preview-discount');
    const previewService = document.getElementById('preview-service');
    const previewPoints = document.getElementById('preview-points');
    const previewStatus = document.getElementById('preview-status');
    
    if (previewDiscount) previewDiscount.textContent = valor + '%';
    if (previewService) previewService.textContent = descripcion;
    if (previewPoints) previewPoints.textContent = 'Puntos: ' + valor;
    
    if (previewStatus) {
        previewStatus.textContent = estado.toUpperCase();
        previewStatus.className = 'preview-status ' + (estado === 'activo' ? 'active' : 'inactive');
    }
}

// === FUNCIONES DE TOGGLE ===
function toggleStatus() {
    const toggleBtn = document.querySelector('.btn-toggle');
    const estadoSelect = document.getElementById('estado');
    
    if (toggleBtn && estadoSelect) {
        const isOn = !toggleBtn.classList.contains('off');
        
        if (isOn) {
            toggleBtn.classList.add('off');
            toggleBtn.textContent = 'OFF';
            estadoSelect.value = 'inactivo';
        } else {
            toggleBtn.classList.remove('off');
            toggleBtn.textContent = 'ON';
            estadoSelect.value = 'activo';
        }
        
        // Actualizar vista previa
        actualizarVistaPrevia();
    }
}

// === FUNCIONES DE TABLA ===
function initializeTableInteractions() {
    // Hover effects para filas de tabla
    const filas = document.querySelectorAll('.benefits-table tbody tr');
    filas.forEach(fila => {
        fila.addEventListener('mouseenter', function() {
            this.style.backgroundColor = 'var(--color-background)';
        });
        
        fila.addEventListener('mouseleave', function() {
            this.style.backgroundColor = '';
        });
    });
}

// === FUNCIONES DE UTILIDAD ===
function mostrarMensaje(mensaje, tipo = 'info') {
    // Crear elemento de mensaje
    const mensajeDiv = document.createElement('div');
    mensajeDiv.className = `mensaje ${tipo}`;
    mensajeDiv.textContent = mensaje;
    
    // Insertar al inicio del contenido
    const contenido = document.querySelector('.admin-content');
    if (contenido) {
        contenido.insertBefore(mensajeDiv, contenido.firstChild);
        
        // Remover después de 5 segundos
        setTimeout(() => {
            mensajeDiv.remove();
        }, 5000);
    }
}

function formatearFecha(fecha) {
    const opciones = {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit'
    };
    return new Date(fecha).toLocaleDateString('es-ES', opciones);
}

function formatearNumero(numero) {
    return new Intl.NumberFormat('es-ES').format(numero);
}

// === FUNCIONES DE NAVEGACIÓN ===
function irADashboard() {
    window.location.href = '?action=dashboard';
}

function irABeneficios() {
    window.location.href = '?action=list';
}

function crearNuevoBeneficio() {
    window.location.href = '?action=create';
}

// === FUNCIONES DE TEMA (PLACEHOLDER) ===
function toggleTheme() {
    // Placeholder para funcionalidad de tema oscuro
    console.log('Cambio de tema - funcionalidad pendiente');
}

// === MANEJO DE ERRORES ===
window.addEventListener('error', function(e) {
    console.error('Error en admin.js:', e.error);
});

// === FUNCIONES DE EXPORTACIÓN PARA USO GLOBAL ===
window.adminPanel = {
    filtrarPorEstado,
    cambiarVista,
    editarCupon,
    eliminarCupon,
    confirmarEliminacion,
    toggleStatus,
    actualizarVistaPrevia,
    mostrarMensaje,
    irADashboard,
    irABeneficios,
    crearNuevoBeneficio,
    toggleDropdown
};

// Hacer toggleDropdown disponible globalmente
window.toggleDropdown = toggleDropdown;

console.log('Admin.js cargado correctamente - Panel Administrativo 25Watts v0.1.0');

// Función para inicializar filtros desde parámetros URL
function initializeUrlFilters() {
    const urlParams = new URLSearchParams(window.location.search);
    
    // Inicializar estados desde URL
    const estados = urlParams.get('estados');
    if (estados) {
        const estadosArray = estados.split(',');
        estadosArray.forEach(estado => {
            const checkbox = document.querySelector(`#estadoDropdown input[type="checkbox"][data-value="${estado}"]`);
            if (!checkbox) {
                // Buscar por texto si no encuentra por data-value
                const options = document.querySelectorAll('#estadoDropdown .checkbox-option');
                options.forEach(option => {
                    const optionText = option.querySelector('.option-text').textContent;
                    const checkbox = option.querySelector('input[type="checkbox"]');
                    if ((estado === 'activo' && optionText === 'Activos') || 
                        (estado === 'inactivo' && optionText === 'Inactivos') ||
                        (estado === 'canjeado' && optionText === 'Canjeados')) {
                        checkbox.checked = true;
                    }
                });
            } else {
                checkbox.checked = true;
            }
        });
    }
    
    // Inicializar orden desde URL
    const orden = urlParams.get('orden');
    if (orden) {
        const ordenArray = orden.split(',');
        ordenArray.forEach(ordenItem => {
            const options = document.querySelectorAll('#ordenDropdown .checkbox-option');
            options.forEach(option => {
                const optionText = option.querySelector('.option-text').textContent;
                const checkbox = option.querySelector('input[type="checkbox"]');
                if ((ordenItem === 'fecha' && optionText === 'Fecha') || 
                    (ordenItem === 'nombre' && optionText === 'Nombre') ||
                    (ordenItem === 'valor' && optionText === 'Valor')) {
                    checkbox.checked = true;
                }
            });
        });
    }
}