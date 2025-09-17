// Funcionalidad para la interfaz de usuario
document.addEventListener('DOMContentLoaded', function() {
    // Referencias a elementos del DOM
    const navItems = document.querySelectorAll('.nav-item');
    const contentSections = document.querySelectorAll('.content-section');
    const couponForm = document.querySelector('.coupon-form');
    const messageDiv = document.querySelector('.message');
    const notificationBtn = document.getElementById('notificationBtn');
    const notificationsModal = document.getElementById('notificationsModal');
    const closeNotifications = document.getElementById('closeNotifications');
    const profileBtn = document.getElementById('profileBtn');
    const profileDropdown = document.getElementById('profileDropdown');
    
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

    // Navegación entre secciones
    navItems.forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            const section = this.getAttribute('data-section');
            
            // Update active nav item
            navItems.forEach(nav => nav.classList.remove('active'));
            this.classList.add('active');
            
            // Show corresponding content section
            contentSections.forEach(content => {
                content.classList.remove('active');
                if (content.id === section) {
                    content.classList.add('active');
                }
            });
            
            // Close mobile menu when clicking on nav items (mobile)
            if (window.innerWidth <= 768 && sidebar && mobileOverlay) {
                sidebar.classList.remove('active');
                mobileOverlay.classList.remove('active');
                const icon = mobileMenuToggle.querySelector('i');
                if (icon) {
                    icon.className = 'bi bi-list';
                }
            }
        });
    });

    // Manejo del formulario de canje de cupones
    if (couponForm) {
        couponForm.addEventListener('submit', function(e) {
            e.preventDefault(); // Prevenir envío tradicional
            
            const submitBtn = this.querySelector('.redeem-btn');
            const codigoInput = this.querySelector('#codigo');
            const codigo = codigoInput.value.trim();
            
            if (!codigo) {
                showModal('Error', 'Por favor, ingresa un código de cupón.', false);
                return;
            }
            
            // Deshabilitar botón y cambiar texto
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.textContent = 'CANJEANDO...';
            }
            
            // Usar la misma función AJAX
            canjearCuponForm(codigo, submitBtn, codigoInput);
        });
    }

    // Notifications dropdown
    const notificationsDropdown = document.getElementById('notificationsDropdown');
    if (notificationBtn && notificationsDropdown) {
        notificationBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            notificationsDropdown.classList.toggle('show');
            // Close profile dropdown if open
            if (profileDropdown) {
                profileDropdown.classList.remove('show');
            }
        });
    }

    // Profile dropdown
    if (profileBtn && profileDropdown) {
        profileBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            profileDropdown.classList.toggle('show');
        });
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function() {
            profileDropdown.classList.remove('show');
            if (notificationsDropdown) {
                notificationsDropdown.classList.remove('show');
            }
        });
    }

    // Auto-ocultar mensajes después de 5 segundos
    if (messageDiv) {
        setTimeout(function() {
            messageDiv.style.opacity = '0';
            setTimeout(function() {
                messageDiv.style.display = 'none';
            }, 300);
        }, 5000);
    }

    // Funcionalidad para marcar notificaciones como leídas
    const markReadBtn = document.querySelector('.mark-read');
    if (markReadBtn) {
        markReadBtn.addEventListener('click', function(e) {
            e.preventDefault();
            const notifications = document.querySelectorAll('.notification-item');
            notifications.forEach(notification => {
                notification.style.opacity = '0.6';
            });
            this.textContent = 'Marcadas como leídas';
            this.style.pointerEvents = 'none';
        });
    }

    // Animaciones suaves para las tarjetas
    const cards = document.querySelectorAll('.category-card, .stat-card, .coupon-card, .benefit-card');
    cards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-2px)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });

    // Event listeners para botones de cupón
    const benefitBtns = document.querySelectorAll('.benefit-btn');
    benefitBtns.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const codigo = this.getAttribute('data-codigo');
            if (codigo) {
                canjearCupon(codigo);
            }
        });
    });

    // Event listener para el botón de aceptar del modal
    const modalAcceptBtn = document.getElementById('modalAcceptBtn');
    if (modalAcceptBtn) {
        modalAcceptBtn.addEventListener('click', function() {
            hideModal();
            location.reload(); // Recargar la página
        });
    }
});

// Función global para mostrar mensajes
function showMessage(message, type = 'info') {
    const messageDiv = document.createElement('div');
    messageDiv.className = `message ${type}`;
    messageDiv.textContent = message;
    
    const content = document.querySelector('.content');
    if (content) {
        content.insertBefore(messageDiv, content.firstChild);
        
        // Auto-ocultar después de 5 segundos
        setTimeout(() => {
            messageDiv.style.opacity = '0';
            setTimeout(() => {
                messageDiv.remove();
            }, 300);
        }, 5000);
    }
}

// Funciones del modal
function showModal(title, message, isSuccess = true) {
    const modal = document.getElementById('couponModal');
    const modalTitle = document.getElementById('modalTitle');
    const modalMessage = document.getElementById('modalMessage');
    const modalIcon = document.getElementById('modalIcon');
    
    modalTitle.textContent = title;
    modalMessage.textContent = message;
    
    // Configurar icono según el tipo
    modalIcon.className = 'modal-icon ' + (isSuccess ? 'success' : 'error');
    modalIcon.textContent = isSuccess ? '✓' : '✕';
    
    modal.style.display = 'flex';
}

function hideModal() {
    const modal = document.getElementById('couponModal');
    modal.style.display = 'none';
}

// Función para canjear cupón via AJAX
function canjearCupon(codigo) {
    fetch('usuario.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=canjear_cupon_ajax&codigo=' + encodeURIComponent(codigo)
    })
    .then(response => response.json())
    .then(data => {
        showModal(
            data.success ? '¡Éxito!' : 'Error',
            data.message,
            data.success
        );
    })
    .catch(error => {
        console.error('Error:', error);
        showModal('Error', 'Ocurrió un error al procesar el cupón', false);
    });
}

// Función específica para el formulario de canje
function canjearCuponForm(codigo, submitBtn, codigoInput) {
    fetch('usuario.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=canjear_cupon_ajax&codigo=' + encodeURIComponent(codigo)
    })
    .then(response => response.json())
    .then(data => {
        // Restaurar botón
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.textContent = 'CANJEAR CUPÓN';
        }
        
        // Limpiar campo si fue exitoso
        if (data.success && codigoInput) {
            codigoInput.value = '';
        }
        
        showModal(
            data.success ? '¡Éxito!' : 'Error',
            data.message,
            data.success
        );
    })
    .catch(error => {
        console.error('Error:', error);
        
        // Restaurar botón en caso de error
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.textContent = 'CANJEAR CUPÓN';
        }
        
        showModal('Error', 'Ocurrió un error al procesar el cupón', false);
    });
}