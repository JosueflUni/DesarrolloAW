// public/js/main.js

/**
 * Funciones JavaScript Globales para el Sistema Zoológico
 */

// ===============================================
// CONFIGURACIÓN GLOBAL
// ===============================================
const APP_CONFIG = {
    baseURL: '/zoologico',
    apiTimeout: 10000,
    alertDuration: 5000
};

// ===============================================
// UTILIDADES GENERALES
// ===============================================

/**
 * Realizar petición AJAX
 */
async function fetchAPI(url, options = {}) {
    try {
        const response = await fetch(url, {
            ...options,
            headers: {
                'Content-Type': 'application/json',
                ...options.headers
            }
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        return await response.json();
    } catch (error) {
        console.error('Error en fetchAPI:', error);
        showAlert('Error al conectar con el servidor', 'danger');
        throw error;
    }
}

/**
 * Mostrar alerta temporal
 */
function showAlert(message, type = 'info', duration = APP_CONFIG.alertDuration) {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3`;
    alertDiv.style.zIndex = '9999';
    alertDiv.style.minWidth = '300px';
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;

    document.body.appendChild(alertDiv);

    setTimeout(() => {
        alertDiv.remove();
    }, duration);
}

/**
 * Mostrar loading spinner
 */
function showLoading(element) {
    const spinner = document.createElement('div');
    spinner.className = 'text-center my-4';
    spinner.innerHTML = `
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Cargando...</span>
        </div>
    `;
    element.innerHTML = '';
    element.appendChild(spinner);
}

/**
 * Formatear fecha
 */
function formatDate(dateString) {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    return date.toLocaleDateString('es-MX', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
}

/**
 * Formatear fecha y hora
 */
function formatDateTime(dateString) {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    return date.toLocaleString('es-MX', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

/**
 * Capitalizar primera letra
 */
function capitalize(str) {
    return str.charAt(0).toUpperCase() + str.slice(1).toLowerCase();
}

/**
 * Debounce para búsquedas
 */
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// ===============================================
// VALIDACIONES DE FORMULARIOS
// ===============================================

/**
 * Validar email
 */
function validateEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

/**
 * Validar campos requeridos
 */
function validateRequired(value) {
    return value !== null && value !== undefined && value.trim() !== '';
}

/**
 * Validar formulario
 */
function validateForm(formId) {
    const form = document.getElementById(formId);
    if (!form) return false;

    const inputs = form.querySelectorAll('[required]');
    let isValid = true;

    inputs.forEach(input => {
        if (!validateRequired(input.value)) {
            input.classList.add('is-invalid');
            isValid = false;
        } else {
            input.classList.remove('is-invalid');
            input.classList.add('is-valid');
        }
    });

    return isValid;
}

// ===============================================
// MANEJO DE MODALES
// ===============================================

/**
 * Abrir modal con contenido dinámico
 */
function openModal(modalId, title, content) {
    const modal = document.getElementById(modalId);
    if (!modal) return;

    const modalTitle = modal.querySelector('.modal-title');
    const modalBody = modal.querySelector('.modal-body');

    if (modalTitle) modalTitle.textContent = title;
    if (modalBody) modalBody.innerHTML = content;

    const bsModal = new bootstrap.Modal(modal);
    bsModal.show();
}

/**
 * Cerrar modal
 */
function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (!modal) return;

    const bsModal = bootstrap.Modal.getInstance(modal);
    if (bsModal) bsModal.hide();
}

// ===============================================
// MANEJO DE TABLAS
// ===============================================

/**
 * Crear tabla HTML desde datos
 */
function createTable(data, columns) {
    if (!data || data.length === 0) {
        return '<p class="text-muted">No hay datos disponibles</p>';
    }

    let html = '<table class="table table-hover">';
    
    // Header
    html += '<thead><tr>';
    columns.forEach(col => {
        html += `<th>${col.label}</th>`;
    });
    html += '</tr></thead>';

    // Body
    html += '<tbody>';
    data.forEach(row => {
        html += '<tr>';
        columns.forEach(col => {
            const value = row[col.key] || '-';
            html += `<td>${col.format ? col.format(value) : value}</td>`;
        });
        html += '</tr>';
    });
    html += '</tbody></table>';

    return html;
}

/**
 * Filtrar tabla
 */
function filterTable(tableId, searchValue) {
    const table = document.getElementById(tableId);
    if (!table) return;

    const rows = table.querySelectorAll('tbody tr');
    const search = searchValue.toLowerCase();

    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(search) ? '' : 'none';
    });
}

// ===============================================
// CONFIRMACIONES
// ===============================================

/**
 * Confirmar acción
 */
function confirmAction(message, callback) {
    if (confirm(message)) {
        callback();
    }
}

/**
 * Confirmar eliminación
 */
function confirmDelete(itemName, callback) {
    const message = `¿Estás seguro de que deseas eliminar "${itemName}"? Esta acción no se puede deshacer.`;
    confirmAction(message, callback);
}

// ===============================================
// LOCAL STORAGE
// ===============================================

/**
 * Guardar en localStorage
 */
function saveToStorage(key, value) {
    try {
        localStorage.setItem(key, JSON.stringify(value));
        return true;
    } catch (error) {
        console.error('Error al guardar en localStorage:', error);
        return false;
    }
}

/**
 * Obtener de localStorage
 */
function getFromStorage(key, defaultValue = null) {
    try {
        const item = localStorage.getItem(key);
        return item ? JSON.parse(item) : defaultValue;
    } catch (error) {
        console.error('Error al leer de localStorage:', error);
        return defaultValue;
    }
}

/**
 * Eliminar de localStorage
 */
function removeFromStorage(key) {
    try {
        localStorage.removeItem(key);
        return true;
    } catch (error) {
        console.error('Error al eliminar de localStorage:', error);
        return false;
    }
}

// ===============================================
// EVENTOS GLOBALES
// ===============================================

// Inicialización al cargar el DOM
document.addEventListener('DOMContentLoaded', function() {
    console.log('Sistema Zoológico - JavaScript cargado');

    // Auto-cerrar alertas
    const alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
    alerts.forEach(alert => {
        setTimeout(() => {
            const bsAlert = bootstrap.Alert.getInstance(alert) || new bootstrap.Alert(alert);
            bsAlert.close();
        }, APP_CONFIG.alertDuration);
    });

    // Confirmación de logout
    const logoutLinks = document.querySelectorAll('a[href*="logout"]');
    logoutLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            if (!confirm('¿Estás seguro de que deseas cerrar sesión?')) {
                e.preventDefault();
            }
        });
    });

    // Tooltips de Bootstrap
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Popovers de Bootstrap
    const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });
});

// Prevenir envío múltiple de formularios
document.addEventListener('submit', function(e) {
    const form = e.target;
    const submitBtn = form.querySelector('button[type="submit"]');
    
    if (submitBtn && !submitBtn.disabled) {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Enviando...';
        
        setTimeout(() => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = submitBtn.getAttribute('data-original-text') || 'Enviar';
        }, 3000);
    }
});

// ===============================================
// EXPORTAR FUNCIONES GLOBALES
// ===============================================
window.ZooApp = {
    fetchAPI,
    showAlert,
    showLoading,
    formatDate,
    formatDateTime,
    capitalize,
    debounce,
    validateEmail,
    validateRequired,
    validateForm,
    openModal,
    closeModal,
    createTable,
    filterTable,
    confirmAction,
    confirmDelete,
    saveToStorage,
    getFromStorage,
    removeFromStorage
};

console.log('ZooApp utilities loaded successfully');