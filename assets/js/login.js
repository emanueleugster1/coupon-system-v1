// Funcionalidades JavaScript simplificadas para el sistema de login

// Función para mostrar/ocultar contraseña
function togglePassword(fieldId = 'password') {
    const passwordField = document.getElementById(fieldId);
    const toggleButton = passwordField.nextElementSibling;
    
    if (passwordField.type === 'password') {
        passwordField.type = 'text';
        toggleButton.innerHTML = '<i class="bi bi-eye-slash"></i>';
    } else {
        passwordField.type = 'password';
        toggleButton.innerHTML = '<i class="bi bi-eye"></i>';
    }
}

// Función para cambiar tema (claro/oscuro)
function toggleTheme() {
    const body = document.body;
    const themeToggle = document.querySelector('.theme-toggle i');
    
    if (body.classList.contains('dark-theme')) {
        body.classList.remove('dark-theme');
        themeToggle.className = 'bi bi-moon';
        localStorage.setItem('theme', 'light');
    } else {
        body.classList.add('dark-theme');
        themeToggle.className = 'bi bi-sun';
        localStorage.setItem('theme', 'dark');
    }
}

// Función para mostrar/ocultar dropdown de idioma
function toggleLanguageDropdown() {
    const dropdown = document.getElementById('languageDropdown');
    dropdown.classList.toggle('show');
}

// Función para cambiar idioma
function changeLanguage(lang) {
    const languageSelector = document.querySelector('.language-selector span');
    const dropdown = document.getElementById('languageDropdown');
    
    if (lang === 'es') {
        languageSelector.textContent = 'Esp';
    } else if (lang === 'en') {
        languageSelector.textContent = 'Eng';
    }
    
    dropdown.classList.remove('show');
    localStorage.setItem('language', lang);
}

// Validación simplificada del formulario - solo verificar campos no vacíos
function setupFormValidation() {
    const form = document.querySelector('.login-form');
    
    if (form) {
        form.addEventListener('submit', function(e) {
            const emailField = document.getElementById('email');
            const passwordField = document.getElementById('password');
            let isValid = true;
            
            // Limpiar errores previos
            clearAllErrors();
            
            // Validar que el email no esté vacío
            if (emailField && emailField.value.trim() === '') {
                showFieldError(emailField, 'Este campo es requerido');
                isValid = false;
            }
            
            // Validar que la contraseña no esté vacía
            if (passwordField && passwordField.value.trim() === '') {
                showFieldError(passwordField, 'Este campo es requerido');
                isValid = false;
            }
            
            // Si hay errores, prevenir el envío
            if (!isValid) {
                e.preventDefault();
                return false;
            }
        });
    }
}

// Función para limpiar todos los errores
function clearAllErrors() {
    const errors = document.querySelectorAll('.field-error');
    errors.forEach(error => error.remove());
    
    const inputs = document.querySelectorAll('.form-input');
    inputs.forEach(input => {
        input.style.borderColor = '';
    });
}

// Función para mostrar error en campo
function showFieldError(field, message) {
    // Remover error anterior si existe
    hideFieldError(field);
    
    const errorDiv = document.createElement('div');
    errorDiv.className = 'field-error';
    errorDiv.style.color = '#d93025';
    errorDiv.style.fontSize = '12px';
    errorDiv.style.marginTop = '4px';
    errorDiv.textContent = message;
    
    field.parentNode.appendChild(errorDiv);
}

// Función para ocultar error en campo
function hideFieldError(field) {
    const existingError = field.parentNode.querySelector('.field-error');
    if (existingError) {
        existingError.remove();
    }
}

// Función para cargar tema guardado
function loadUserPreferences() {
    const savedTheme = localStorage.getItem('theme');
    const savedLanguage = localStorage.getItem('language');
    
    if (savedTheme === 'dark') {
        document.body.classList.add('dark-theme');
        const themeToggle = document.querySelector('.theme-toggle i');
        if (themeToggle) {
            themeToggle.className = 'bi bi-sun';
        }
    }
    
    if (savedLanguage) {
        changeLanguage(savedLanguage);
    }
}



// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    loadUserPreferences();
    setupFormValidation();
    
    // Cerrar dropdown de idioma al hacer clic fuera
    document.addEventListener('click', function(event) {
        const languageWrapper = document.querySelector('.language-selector-wrapper');
        const dropdown = document.getElementById('languageDropdown');
        
        if (languageWrapper && !languageWrapper.contains(event.target)) {
            dropdown.classList.remove('show');
        }
    });
});