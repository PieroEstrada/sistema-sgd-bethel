@extends('layouts.app')
@section('title', 'Módulo de Tickets')

@push('styles')
<style>
    /* ===== TEMA CLARO (por defecto) ===== */
    .tickets-module {
        --bg-primary: #f8f9fa;
        --bg-card: #ffffff;
        --bg-table-header: #f1f3f5;
        --bg-table-row-hover: #f8f9fa;
        --text-primary: #212529;
        --text-secondary: #6c757d;
        --border-color: #dee2e6;
        --input-bg: #ffffff;
        --input-border: #ced4da;
        --shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075);
        transition: all 0.3s ease;
    }

    /* ===== TEMA OSCURO ===== */
    .tickets-module.dark-mode {
        --bg-primary: #1a1d21;
        --bg-card: #212529;
        --bg-table-header: #2d3238;
        --bg-table-row-hover: #2d3238;
        --text-primary: #e9ecef;
        --text-secondary: #adb5bd;
        --border-color: #495057;
        --input-bg: #2d3238;
        --input-border: #495057;
        --shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.3);
    }

    .tickets-module {
        background-color: var(--bg-primary);
        color: var(--text-primary);
        min-height: 100vh;
        padding: 1.5rem;
    }

    .tickets-card {
        background-color: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 0.5rem;
        box-shadow: var(--shadow);
    }

    .tickets-header {
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
        align-items: center;
        justify-content: space-between;
        padding: 1.25rem;
        border-bottom: 1px solid var(--border-color);
    }

    .tickets-title {
        font-size: 1.5rem;
        font-weight: 600;
        margin: 0;
        color: var(--text-primary);
    }

    .tickets-controls {
        display: flex;
        flex-wrap: wrap;
        gap: 0.75rem;
        align-items: center;
    }

    .tickets-search {
        background-color: var(--input-bg);
        border: 1px solid var(--input-border);
        color: var(--text-primary);
        border-radius: 0.375rem;
        padding: 0.5rem 1rem;
        width: 280px;
        font-size: 0.875rem;
        transition: border-color 0.15s ease-in-out;
    }

    .tickets-search:focus {
        outline: none;
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.2rem rgba(13,110,253,0.15);
    }

    .tickets-search::placeholder {
        color: var(--text-secondary);
    }

    .btn-refresh {
        background-color: #0d6efd;
        border: none;
        color: white;
        padding: 0.5rem 1rem;
        border-radius: 0.375rem;
        font-size: 0.875rem;
        font-weight: 500;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        transition: background-color 0.15s ease;
    }

    .btn-refresh:hover {
        background-color: #0b5ed7;
    }

    .btn-refresh:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }

    .btn-theme {
        background-color: transparent;
        border: 1px solid var(--border-color);
        color: var(--text-primary);
        padding: 0.5rem 0.75rem;
        border-radius: 0.375rem;
        font-size: 0.875rem;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 0.4rem;
        transition: all 0.15s ease;
    }

    .btn-theme:hover {
        background-color: var(--bg-table-header);
    }

    .tickets-counter {
        font-size: 0.875rem;
        color: var(--text-secondary);
        background-color: var(--bg-table-header);
        padding: 0.5rem 1rem;
        border-radius: 0.375rem;
        font-weight: 500;
    }

    .tickets-table-container {
        overflow-x: auto;
    }

    .tickets-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.875rem;
    }

    .tickets-table thead th {
        background-color: var(--bg-table-header);
        color: var(--text-secondary);
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.5px;
        padding: 1rem 1.25rem;
        text-align: left;
        border-bottom: 1px solid var(--border-color);
    }

    .tickets-table tbody tr {
        border-bottom: 1px solid var(--border-color);
        transition: background-color 0.1s ease;
    }

    .tickets-table tbody tr:hover {
        background-color: var(--bg-table-row-hover);
    }

    .tickets-table tbody tr:last-child {
        border-bottom: none;
    }

    .tickets-table tbody td {
        padding: 1rem 1.25rem;
        color: var(--text-primary);
        vertical-align: middle;
    }

    /* Badges para estados */
    .badge-estado {
        display: inline-block;
        padding: 0.35rem 0.75rem;
        border-radius: 50rem;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }

    /* SOLICITUD NUEVA - Azul */
    .badge-solicitud_nueva {
        background-color: #cfe2ff;
        color: #084298;
    }
    .dark-mode .badge-solicitud_nueva {
        background-color: #084298;
        color: #cfe2ff;
    }

    /* Almacén - Gris */
    .badge-almacen {
        background-color: #e9ecef;
        color: #495057;
    }
    .dark-mode .badge-almacen {
        background-color: #495057;
        color: #e9ecef;
    }

    /* Pendiente - Rojo/Rosado */
    .badge-pendiente {
        background-color: #f8d7da;
        color: #842029;
    }
    .dark-mode .badge-pendiente {
        background-color: #842029;
        color: #f8d7da;
    }

    /* En Proceso - Amarillo */
    .badge-en_proceso {
        background-color: #fff3cd;
        color: #664d03;
    }
    .dark-mode .badge-en_proceso {
        background-color: #664d03;
        color: #fff3cd;
    }

    /* Resuelto - Verde */
    .badge-resuelto {
        background-color: #d1e7dd;
        color: #0f5132;
    }
    .dark-mode .badge-resuelto {
        background-color: #0f5132;
        color: #d1e7dd;
    }

    /* Cerrado - Oscuro */
    .badge-cerrado {
        background-color: #d3d3d4;
        color: #141619;
    }
    .dark-mode .badge-cerrado {
        background-color: #343a40;
        color: #f8f9fa;
    }

    .tickets-empty {
        text-align: center;
        padding: 3rem;
        color: var(--text-secondary);
    }

    .tickets-empty i {
        font-size: 3rem;
        margin-bottom: 1rem;
        opacity: 0.5;
    }

    .btn-new-ticket {
        background-color: #198754;
        border: none;
        color: white;
        padding: 0.5rem 1rem;
        border-radius: 0.375rem;
        font-size: 0.875rem;
        font-weight: 500;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        transition: background-color 0.15s ease;
    }

    .btn-new-ticket:hover {
        background-color: #157347;
        color: white;
    }

    .spinner {
        display: inline-block;
        width: 1rem;
        height: 1rem;
        border: 2px solid transparent;
        border-top-color: currentColor;
        border-radius: 50%;
        animation: spin 0.75s linear infinite;
    }

    @keyframes spin {
        to { transform: rotate(360deg); }
    }

    /* Links en la tabla */
    .tickets-table a {
        color: #0d6efd;
        text-decoration: none;
    }
    .tickets-table a:hover {
        text-decoration: underline;
    }
    .dark-mode .tickets-table a {
        color: #6ea8fe;
    }

    @media (max-width: 768px) {
        .tickets-header {
            flex-direction: column;
            align-items: stretch;
        }
        .tickets-controls {
            flex-direction: column;
        }
        .tickets-search {
            width: 100%;
        }
    }
</style>
@endpush

@section('content')
<div class="tickets-module" id="ticketsModule">
    <div class="tickets-card">
        <!-- Header -->
        <div class="tickets-header">
            <h1 class="tickets-title">
                <i class="fas fa-ticket-alt me-2"></i>Módulo de Tickets
            </h1>
            <div class="tickets-controls">
                <input
                    type="text"
                    class="tickets-search"
                    id="searchInput"
                    placeholder="Buscar en cualquier campo..."
                    autocomplete="off"
                >
                <button type="button" class="btn-refresh" id="btnRefresh">
                    <i class="fas fa-sync-alt" id="refreshIcon"></i>
                    Actualizar
                </button>
                <span class="tickets-counter" id="ticketCounter">0 registro(s)</span>
                <button type="button" class="btn-theme" id="btnTheme">
                    <i class="fas fa-moon" id="themeIcon"></i>
                    <span id="themeText">Modo dark</span>
                </button>
                <a href="{{ route('tickets.create') }}" class="btn-new-ticket">
                    <i class="fas fa-plus"></i>
                    Nuevo
                </a>
            </div>
        </div>

        <!-- Tabla -->
        <div class="tickets-table-container">
            <table class="tickets-table">
                <thead>
                    <tr>
                        <th>Fecha de Ingreso</th>
                        <th>Equipo</th>
                        <th>Servicio</th>
                        <th>Estación</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody id="ticketsTableBody">
                    <tr>
                        <td colspan="5" class="tickets-empty">
                            <i class="fas fa-spinner fa-spin"></i>
                            <p>Cargando tickets...</p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const module = document.getElementById('ticketsModule');
    const searchInput = document.getElementById('searchInput');
    const btnRefresh = document.getElementById('btnRefresh');
    const refreshIcon = document.getElementById('refreshIcon');
    const ticketCounter = document.getElementById('ticketCounter');
    const tableBody = document.getElementById('ticketsTableBody');
    const btnTheme = document.getElementById('btnTheme');
    const themeIcon = document.getElementById('themeIcon');
    const themeText = document.getElementById('themeText');

    // ===== TEMA =====
    function loadTheme() {
        const savedTheme = localStorage.getItem('tickets-theme') || 'light';
        applyTheme(savedTheme);
    }

    function applyTheme(theme) {
        if (theme === 'dark') {
            module.classList.add('dark-mode');
            themeIcon.className = 'fas fa-sun';
            themeText.textContent = 'Modo claro';
        } else {
            module.classList.remove('dark-mode');
            themeIcon.className = 'fas fa-moon';
            themeText.textContent = 'Modo dark';
        }
        localStorage.setItem('tickets-theme', theme);
    }

    function toggleTheme() {
        const isDark = module.classList.contains('dark-mode');
        applyTheme(isDark ? 'light' : 'dark');
    }

    btnTheme.addEventListener('click', toggleTheme);
    loadTheme();

    // ===== DATOS =====
    let debounceTimer = null;

    function fetchTickets(query = '') {
        btnRefresh.disabled = true;
        refreshIcon.className = 'fas fa-spinner fa-spin';

        const url = query
            ? `{{ route('tickets.data') }}?q=${encodeURIComponent(query)}`
            : `{{ route('tickets.data') }}`;

        fetch(url, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(result => {
            renderTable(result.data);
            ticketCounter.textContent = `${result.total} registro(s)`;
        })
        .catch(error => {
            console.error('Error fetching tickets:', error);
            tableBody.innerHTML = `
                <tr>
                    <td colspan="5" class="tickets-empty">
                        <i class="fas fa-exclamation-triangle"></i>
                        <p>Error al cargar los tickets</p>
                    </td>
                </tr>
            `;
        })
        .finally(() => {
            btnRefresh.disabled = false;
            refreshIcon.className = 'fas fa-sync-alt';
        });
    }

    function renderTable(data) {
        if (!data || data.length === 0) {
            tableBody.innerHTML = `
                <tr>
                    <td colspan="5" class="tickets-empty">
                        <i class="fas fa-inbox"></i>
                        <p>No hay tickets registrados</p>
                    </td>
                </tr>
            `;
            return;
        }

        const rows = data.map(ticket => {
            const badgeClass = `badge-estado badge-${ticket.estado}`;
            return `
                <tr>
                    <td>${ticket.fecha_ingreso || '-'}</td>
                    <td>${escapeHtml(ticket.equipo)}</td>
                    <td>${escapeHtml(ticket.servicio || '-')}</td>
                    <td>${escapeHtml(ticket.estacion)}</td>
                    <td><span class="${badgeClass}">${escapeHtml(ticket.estado_label)}</span></td>
                </tr>
            `;
        }).join('');

        tableBody.innerHTML = rows;
    }

    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Debounce para búsqueda
    searchInput.addEventListener('input', function() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            fetchTickets(this.value.trim());
        }, 250);
    });

    // Botón actualizar
    btnRefresh.addEventListener('click', function() {
        fetchTickets(searchInput.value.trim());
    });

    // Carga inicial
    fetchTickets();
});
</script>
@endpush
