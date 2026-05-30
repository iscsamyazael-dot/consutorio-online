@extends('adminlte::page')

@section('title', 'Especialidades Médicas')

@section('content_header')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-2">
    <div>
        <h1 class="page-title mb-0">Especialidades Médicas</h1>
        <p class="text-muted mb-0 mt-1">Administra las especialidades disponibles en el consultorio</p>
    </div>
    <div class="mt-3 mt-md-0">
        <button type="button" class="btn-primary-custom" onclick="abrirModal('modalNueva')">
            <i class="fas fa-plus"></i> Nueva Especialidad
        </button>
    </div>
</div>
@stop

@section('content')

{{-- BUSCADOR --}}
<div class="card-custom mb-4">
    <form method="GET" action="{{ route('specialties.index') }}" id="searchForm">
        <div class="search-bar">
            <i class="fas fa-search search-icon"></i>
            <input
                type="text"
                name="search"
                id="searchInput"
                class="search-input"
                placeholder="Buscar especialidad..."
                value="{{ request('search') }}"
                autocomplete="off"
            >
            @if(request('search'))
                <a href="{{ route('specialties.index') }}" class="btn-clear">
                    <i class="fas fa-times"></i>
                </a>
            @endif
        </div>
    </form>
</div>

{{-- FILTROS --}}
<div class="filter-row mb-4">
    <button class="filter-chip {{ !request('status') ? 'active' : '' }}" onclick="applyFilter('')">Todas</button>
    <button class="filter-chip {{ request('status') === 'activa' ? 'active' : '' }}" onclick="applyFilter('activa')">Activas</button>
    <button class="filter-chip {{ request('status') === 'revision' ? 'active' : '' }}" onclick="applyFilter('revision')">En revisión</button>
</div>

{{-- CONTADOR --}}
<p class="count-label mb-3">
    {{ $specialties->total() }} especialidad{{ $specialties->total() !== 1 ? 'es' : '' }}
</p>

{{-- GRID DE TARJETAS --}}
<div class="specs-grid">
    @forelse($specialties as $specialty)

    @php
        $iconos = [
            'cardiología'      => ['icon' => 'fas fa-heartbeat',    'bg' => '#FAECE7', 'color' => '#993C1D'],
            'pediatría'        => ['icon' => 'fas fa-baby',         'bg' => '#E6F1FB', 'color' => '#185FA5'],
            'neurología'       => ['icon' => 'fas fa-brain',        'bg' => '#EAF3DE', 'color' => '#3B6D11'],
            'traumatología'    => ['icon' => 'fas fa-bone',         'bg' => '#E1F5EE', 'color' => '#0F6E56'],
            'oftalmología'     => ['icon' => 'fas fa-eye',          'bg' => '#FBEAF0', 'color' => '#993556'],
            'dermatología'     => ['icon' => 'fas fa-allergies',    'bg' => '#EEEDFE', 'color' => '#3C3489'],
            'ginecología'      => ['icon' => 'fas fa-venus',        'bg' => '#FBEAF0', 'color' => '#72243E'],
            'medicina general' => ['icon' => 'fas fa-stethoscope',  'bg' => '#E6F1FB', 'color' => '#0C447C'],
        ];
        $key    = strtolower(trim($specialty->name));
        $estilo = $iconos[$key] ?? ['icon' => 'fas fa-notes-medical', 'bg' => '#E6F1FB', 'color' => '#185FA5'];

        $fotos = [
            'cardiología'      => 'https://images.unsplash.com/photo-1628348068343-c6a848d2b6dd?w=600&q=80',
            'pediatría'        => 'https://images.unsplash.com/photo-1584820927498-cfe5211fd8bf?w=600&q=80',
            'neurología'       => 'https://images.unsplash.com/photo-1559757175-5700dde675bc?w=600&q=80',
            'traumatología'    => 'https://images.unsplash.com/photo-1530026405186-ed1f139313f8?w=600&q=80',
            'oftalmología'     => 'https://images.unsplash.com/photo-1516574187841-cb9cc2ca948b?w=600&q=80',
            'dermatología'     => 'https://images.unsplash.com/photo-1612349317150-e413f6a5b16d?w=600&q=80',
            'ginecología'      => 'https://images.unsplash.com/photo-1576091160550-2173dba999ef?w=600&q=80',
            'medicina general' => 'https://images.unsplash.com/photo-1506126613408-eca07ce68773?w=600&q=80',
        ];
        $foto = $fotos[$key] ?? null;
    @endphp

    <div class="spec-card">

        {{-- IMAGEN --}}
        @if($foto)
            <img src="{{ $foto }}" alt="{{ $specialty->name }}" class="card-img">
        @else
            <div class="card-img-placeholder" style="background:{{ $estilo['bg'] }}; color:{{ $estilo['color'] }};">
                <i class="{{ $estilo['icon'] }}" style="font-size:48px;"></i>
            </div>
        @endif

        {{-- CUERPO --}}
        <div class="card-body-custom">
            <div class="card-top">
                <span class="card-name">{{ $specialty->name }}</span>
                <span class="status-chip status-active">Activa</span>
            </div>

            <p class="card-desc">{{ Str::limit($specialty->description, 80) }}</p>

            <div class="card-actions">

                {{-- ── VER ── abre modal con la info completa --}}
                <button type="button" class="btn-action"
                    onclick="abrirModalVer(
                        '{{ addslashes($specialty->name) }}',
                        '{{ addslashes($specialty->description) }}',
                        '{{ $foto ?? '' }}',
                        '{{ $estilo['icon'] }}',
                        '{{ $estilo['bg'] }}',
                        '{{ $estilo['color'] }}'
                    )">
                    <i class="fas fa-eye"></i> Ver
                </button>

                {{-- ── EDITAR ── abre modal con formulario --}}
                <button type="button" class="btn-action btn-edit"
                    onclick="abrirModalEditar(
                        {{ $specialty->id }},
                        '{{ addslashes($specialty->name) }}',
                        '{{ addslashes($specialty->description) }}',
                        '{{ route('specialties.update', $specialty) }}'
                    )">
                    <i class="fas fa-edit"></i> Editar
                </button>

                {{-- ── ELIMINAR ── confirmación SweetAlert --}}
                <form action="{{ route('specialties.destroy', $specialty) }}" method="POST"
                    class="d-inline" id="form-delete-{{ $specialty->id }}">
                    @csrf @method('DELETE')
                    <button type="button" class="btn-action btn-del"
                        onclick="confirmarEliminar({{ $specialty->id }}, '{{ addslashes($specialty->name) }}')">
                        <i class="fas fa-trash"></i> Eliminar
                    </button>
                </form>

            </div>
        </div>
    </div>

    @empty
    <div class="empty-state">
        <i class="fas fa-search fa-3x mb-3"></i>
        <p>No se encontraron especialidades</p>
        @if(request('search'))
            <p class="text-muted">para "<strong>{{ request('search') }}</strong>"</p>
        @endif
        <a href="{{ route('specialties.create') }}" class="btn-primary-custom mt-3">
            <i class="fas fa-plus"></i> Agregar especialidad
        </a>
    </div>
    @endforelse
</div>

{{-- PAGINACIÓN --}}
@if($specialties->hasPages())
<div class="pagination-wrapper mt-4">
    {{ $specialties->withQueryString()->links() }}
</div>
@endif


{{-- ══════════════════════════════════════════════
     MODAL NUEVA ESPECIALIDAD
══════════════════════════════════════════════ --}}
<div class="modal-overlay" id="modalNueva" onclick="cerrarModal('modalNueva')">
    <div class="modal-box modal-box-lg" onclick="event.stopPropagation()">
        <button class="modal-close" onclick="cerrarModal('modalNueva')"><i class="fas fa-times"></i></button>

        {{-- Cabecera verde/azul distintiva --}}
        <div class="modal-header-bar modal-header-nueva">
            <div class="modal-header-icon-wrap">
                <i class="fas fa-notes-medical"></i>
            </div>
            <div>
                <h2 class="modal-title">Nueva Especialidad</h2>
                <p class="modal-subtitle">Completa los datos para registrarla</p>
            </div>
        </div>

        <div class="modal-body-inner">
            <form id="formNueva" method="POST" action="{{ route('specialties.store') }}">
                @csrf

                {{-- Fila 1: Nombre de especialidad --}}
                <div class="field-group mb-3">
                    <label class="field-label">
                        <i class="fas fa-tag"></i> Nombre de la especialidad <span class="req">*</span>
                    </label>
                    <input type="text" name="name" class="field-input"
                           placeholder="Ej: Cardiología, Pediatría…" required>
                </div>

                {{-- Fila 2: Nombre del doctor --}}
                <div class="field-group mb-3">
                    <label class="field-label">
                        <i class="fas fa-user-md"></i> Nombre del doctor <span class="req">*</span>
                    </label>
                    <input type="text" name="doctor_name" class="field-input"
                           placeholder="Ej: Dr. Juan Pérez" required>
                </div>

                {{-- Fila 3: Descripción --}}
                <div class="field-group mb-3">
                    <label class="field-label">
                        <i class="fas fa-align-left"></i> Descripción
                    </label>
                    <textarea name="description" class="field-input field-textarea" rows="3"
                              placeholder="Breve descripción de la especialidad y los servicios que ofrece…"></textarea>
                </div>

                {{-- Fila 4: Estado --}}
                <div class="field-group mb-1">
                    <label class="field-label">
                        <i class="fas fa-toggle-on"></i> Estado inicial
                    </label>
                    <div class="status-selector">
                        <label class="status-opt">
                            <input type="radio" name="status" value="activa" checked>
                            <span class="status-opt-box status-opt-activa">
                                <i class="fas fa-check-circle"></i> Activa
                            </span>
                        </label>
                        <label class="status-opt">
                            <input type="radio" name="status" value="revision">
                            <span class="status-opt-box status-opt-revision">
                                <i class="fas fa-clock"></i> En revisión
                            </span>
                        </label>
                    </div>
                </div>

                <div class="modal-footer-actions mt-4">
                    <button type="button" class="btn-modal-cancel" onclick="cerrarModal('modalNueva')">
                        Cancelar
                    </button>
                    <button type="submit" class="btn-modal-save btn-modal-save-green">
                        <i class="fas fa-plus"></i> Crear especialidad
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


{{-- ══════════════════════════════════════════════
     MODAL VER
══════════════════════════════════════════════ --}}
<div class="modal-overlay" id="modalVer" onclick="cerrarModal('modalVer')">
    <div class="modal-box" onclick="event.stopPropagation()">
        <button class="modal-close" onclick="cerrarModal('modalVer')"><i class="fas fa-times"></i></button>

        <div id="verImgWrap"></div>

        <div class="modal-body-inner">
            <div class="ver-header">
                <h2 id="verNombre" class="modal-title"></h2>
                <span class="status-chip status-active">Activa</span>
            </div>
            <p class="modal-label">Descripción</p>
            <p id="verDescripcion" class="modal-text"></p>
        </div>
    </div>
</div>


{{-- ══════════════════════════════════════════════
     MODAL EDITAR
══════════════════════════════════════════════ --}}
<div class="modal-overlay" id="modalEditar" onclick="cerrarModal('modalEditar')">
    <div class="modal-box" onclick="event.stopPropagation()">
        <button class="modal-close" onclick="cerrarModal('modalEditar')"><i class="fas fa-times"></i></button>

        <div class="modal-header-bar">
            <i class="fas fa-edit modal-header-icon"></i>
            <h2 class="modal-title">Editar Especialidad</h2>
        </div>

        <div class="modal-body-inner">
            <form id="formEditar" method="POST">
                @csrf
                @method('PUT')

                <div class="field-group">
                    <label class="field-label">Nombre</label>
                    <input type="text" id="editNombre" name="name" class="field-input" required>
                </div>

                <div class="field-group mt-3">
                    <label class="field-label">Descripción</label>
                    <textarea id="editDescripcion" name="description" class="field-input field-textarea" rows="4"></textarea>
                </div>

                <div class="modal-footer-actions">
                    <button type="button" class="btn-modal-cancel" onclick="cerrarModal('modalEditar')">Cancelar</button>
                    <button type="submit" class="btn-modal-save"><i class="fas fa-save"></i> Guardar cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>


@stop

@section('css')
<style>
    .page-title { font-size: 1.6rem; font-weight: 600; color: #1a1a2e; }

    .btn-primary-custom {
        background: #185FA5; color: #fff;
        border: none; border-radius: 8px;
        padding: 9px 18px; font-size: 14px; font-weight: 500;
        text-decoration: none; display: inline-flex;
        align-items: center; gap: 6px; transition: background .2s;
    }
    .btn-primary-custom:hover { background: #0C447C; color: #fff; }

    .card-custom {
        background: #fff; border: 0.5px solid rgba(0,0,0,0.09);
        border-radius: 12px; overflow: hidden;
    }
    .search-bar { display: flex; align-items: center; gap: 10px; padding: 12px 16px; }
    .search-icon { color: #aaa; font-size: 15px; }
    .search-input {
        flex: 1; border: none; outline: none;
        font-size: 14px; color: #333; background: transparent;
    }
    .search-input::placeholder { color: #bbb; }
    .btn-clear { color: #aaa; text-decoration: none; font-size: 14px; padding: 4px 6px; }
    .btn-clear:hover { color: #A32D2D; }

    .filter-row { display: flex; gap: 8px; flex-wrap: wrap; }
    .filter-chip {
        border: 0.5px solid rgba(0,0,0,0.12); background: #fff;
        color: #666; border-radius: 99px; padding: 5px 16px;
        font-size: 13px; cursor: pointer; transition: all .15s;
    }
    .filter-chip:hover { border-color: #185FA5; color: #185FA5; }
    .filter-chip.active { background: #185FA5; color: #fff; border-color: #185FA5; }

    .count-label { font-size: 13px; color: #888; }

    .specs-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 20px;
    }

    .spec-card {
        background: #fff; border: 0.5px solid rgba(0,0,0,0.09);
        border-radius: 14px; overflow: hidden;
        transition: border-color .2s, transform .15s;
    }
    .spec-card:hover { border-color: #85B7EB; transform: translateY(-2px); }

    .card-img { width: 100%; height: 180px; object-fit: cover; display: block; }
    .card-img-placeholder {
        width: 100%; height: 180px;
        display: flex; align-items: center; justify-content: center;
    }

    .card-body-custom { padding: 16px; }
    .card-top {
        display: flex; justify-content: space-between;
        align-items: flex-start; margin-bottom: 8px;
    }
    .card-name { font-size: 15px; font-weight: 600; color: #1a1a2e; }

    .status-chip {
        font-size: 11px; padding: 3px 10px;
        border-radius: 99px; font-weight: 500; white-space: nowrap;
    }
    .status-active { background: #EAF3DE; color: #3B6D11; }
    .status-review  { background: #FAEEDA; color: #854F0B; }

    .card-desc {
        font-size: 13px; color: #666; line-height: 1.5;
        margin-bottom: 14px; min-height: 38px;
    }

    .card-actions {
        display: flex; gap: 6px;
        border-top: 0.5px solid rgba(0,0,0,0.07);
        padding-top: 12px;
    }
    .btn-action {
        flex: 1; padding: 7px 4px;
        border: 0.5px solid rgba(0,0,0,0.12);
        background: transparent; border-radius: 8px;
        font-size: 12px; color: #555; cursor: pointer;
        display: inline-flex; align-items: center;
        justify-content: center; gap: 4px;
        text-decoration: none; transition: all .15s;
    }
    .btn-action:hover         { background: #f5f5f5; color: #333; }
    .btn-action.btn-edit:hover { background: #FAEEDA; color: #854F0B; border-color: #EF9F27; }
    .btn-action.btn-del:hover  { background: #FCEBEB; color: #A32D2D; border-color: #F09595; }

    .empty-state {
        grid-column: 1 / -1; text-align: center;
        padding: 4rem 1rem; color: #bbb;
    }
    .empty-state p { font-size: 15px; margin-top: 8px; }
    .pagination-wrapper .pagination { justify-content: center; }

    /* ── SIDEBAR FIX ── */
    .main-sidebar { position: fixed !important; height: 100vh !important; overflow-y: auto; }
    .content-wrapper, .main-footer, .main-header { margin-left: 250px !important; }
    .wrapper { overflow-x: hidden; }
    .content-wrapper { min-height: 100vh; }

    /* ══════════════════════════════════════════════
       MODALES
    ══════════════════════════════════════════════ */
    .modal-overlay {
        display: none;
        position: fixed; inset: 0; z-index: 9999;
        background: rgba(10, 20, 40, 0.45);
        backdrop-filter: blur(4px);
        align-items: center; justify-content: center;
        padding: 20px;
        animation: fadeIn .2s ease;
    }
    .modal-overlay.open { display: flex; }

    @keyframes fadeIn  { from { opacity: 0; } to { opacity: 1; } }
    @keyframes slideUp { from { opacity: 0; transform: translateY(24px) scale(.97); }
                         to   { opacity: 1; transform: translateY(0)     scale(1);   } }

    .modal-box {
        background: #fff;
        border-radius: 18px;
        width: 100%; max-width: 480px;
        box-shadow: 0 24px 60px rgba(0,0,0,0.18);
        overflow: hidden;
        position: relative;
        animation: slideUp .25s ease;
    }

    .modal-close {
        position: absolute; top: 14px; right: 14px;
        background: rgba(0,0,0,0.07); border: none;
        border-radius: 50%; width: 30px; height: 30px;
        display: flex; align-items: center; justify-content: center;
        color: #555; cursor: pointer; font-size: 13px;
        transition: background .15s;
        z-index: 2;
    }
    .modal-close:hover { background: rgba(0,0,0,0.14); color: #222; }

    /* Modal Ver – zona imagen */
    #verImgWrap img  { width: 100%; height: 200px; object-fit: cover; display: block; }
    #verImgWrap .ver-placeholder {
        width: 100%; height: 200px;
        display: flex; align-items: center; justify-content: center;
        font-size: 56px;
    }

    .modal-body-inner { padding: 22px 24px 28px; }

    .ver-header { display: flex; align-items: center; gap: 10px; margin-bottom: 14px; }

    .modal-title { font-size: 18px; font-weight: 700; color: #1a1a2e; margin: 0; }
    .modal-label { font-size: 11px; font-weight: 600; color: #aaa; text-transform: uppercase;
                   letter-spacing: .06em; margin-bottom: 4px; }
    .modal-text  { font-size: 14px; color: #555; line-height: 1.6; margin: 0; }

    /* Modal Nueva – cabecera verde */
    .modal-box-lg { max-width: 520px; }

    .modal-header-nueva {
        background: linear-gradient(135deg, #1A6B3C, #0F4D2B) !important;
    }
    .modal-header-icon-wrap {
        width: 44px; height: 44px; border-radius: 12px;
        background: rgba(255,255,255,0.15);
        display: flex; align-items: center; justify-content: center;
        font-size: 20px; color: #fff; flex-shrink: 0;
    }
    .modal-header-bar { align-items: center; gap: 14px; }
    .modal-subtitle {
        color: rgba(255,255,255,0.7); font-size: 12px; margin: 2px 0 0;
    }

    /* Radio estado */
    .status-selector { display: flex; gap: 10px; margin-top: 4px; }
    .status-opt { cursor: pointer; }
    .status-opt input[type=radio] { display: none; }
    .status-opt-box {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 7px 16px; border-radius: 99px; font-size: 13px;
        border: 1.5px solid transparent; font-weight: 500;
        transition: all .15s; cursor: pointer;
        background: #f5f5f5; color: #888;
    }
    .status-opt input[type=radio]:checked + .status-opt-activa {
        background: #EAF3DE; color: #3B6D11; border-color: #7DBF45;
    }
    .status-opt input[type=radio]:checked + .status-opt-revision {
        background: #FAEEDA; color: #854F0B; border-color: #EF9F27;
    }
    .status-opt-activa:hover  { border-color: #7DBF45; }
    .status-opt-revision:hover { border-color: #EF9F27; }

    /* Botón guardar verde */
    .btn-modal-save-green { background: #1A6B3C !important; }
    .btn-modal-save-green:hover { background: #0F4D2B !important; }

    /* Requerido asterisco */
    .req { color: #A32D2D; }

    /* Ícono pequeño en label */
    .field-label i { font-size: 11px; color: #aaa; margin-right: 3px; }

    /* Modal Nueva – cabecera verde */
    .modal-header-bar {
        background: linear-gradient(135deg, #185FA5, #0C447C);
        padding: 20px 24px 16px;
        display: flex; align-items: center; gap: 12px;
    }
    .modal-header-icon { color: rgba(255,255,255,.8); font-size: 18px; }
    .modal-header-bar .modal-title { color: #fff; font-size: 16px; }

    /* Campos del formulario */
    .field-group { display: flex; flex-direction: column; }
    .field-label  { font-size: 12px; font-weight: 600; color: #777; margin-bottom: 5px; text-transform: uppercase; letter-spacing: .05em; }
    .field-input  {
        border: 1.5px solid rgba(0,0,0,0.13); border-radius: 8px;
        padding: 9px 12px; font-size: 14px; color: #1a1a2e;
        outline: none; transition: border-color .2s;
        font-family: inherit;
    }
    .field-input:focus { border-color: #185FA5; }
    .field-textarea { resize: vertical; min-height: 90px; }

    .modal-footer-actions {
        display: flex; justify-content: flex-end; gap: 10px; margin-top: 22px;
    }
    .btn-modal-cancel {
        padding: 9px 18px; border-radius: 8px; font-size: 13px;
        border: 1px solid rgba(0,0,0,0.13); background: #fff;
        color: #666; cursor: pointer; transition: background .15s;
    }
    .btn-modal-cancel:hover { background: #f3f3f3; }
    .btn-modal-save {
        padding: 9px 20px; border-radius: 8px; font-size: 13px;
        background: #185FA5; color: #fff; border: none;
        cursor: pointer; display: inline-flex; align-items: center; gap: 6px;
        transition: background .2s;
    }
    .btn-modal-save:hover { background: #0C447C; }
</style>
@stop

@section('js')
<script>
    /* ── Buscador con debounce ── */
    let searchTimer;
    document.getElementById('searchInput').addEventListener('input', function () {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => document.getElementById('searchForm').submit(), 500);
    });

    /* ── Filtros por status ── */
    function applyFilter(status) {
        const url = new URL(window.location.href);
        if (status) url.searchParams.set('status', status);
        else url.searchParams.delete('status');
        window.location.href = url.toString();
    }

    /* ════════════════════════════════════════
       HELPERS DE MODAL
    ════════════════════════════════════════ */
    function abrirModal(id)  {
        const el = document.getElementById(id);
        el.classList.add('open');
        document.body.style.overflow = 'hidden';
    }
    function cerrarModal(id) {
        document.getElementById(id).classList.remove('open');
        document.body.style.overflow = '';
    }
    // Cerrar con Escape
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') {
            ['modalVer','modalEditar','modalNueva'].forEach(cerrarModal);
        }
    });

    /* ════════════════════════════════════════
       MODAL VER
    ════════════════════════════════════════ */
    function abrirModalVer(nombre, descripcion, foto, icono, bg, color) {
        document.getElementById('verNombre').textContent      = nombre;
        document.getElementById('verDescripcion').textContent = descripcion || 'Sin descripción registrada.';

        const wrap = document.getElementById('verImgWrap');
        if (foto) {
            wrap.innerHTML = `<img src="${foto}" alt="${nombre}">`;
        } else {
            wrap.innerHTML = `
                <div class="ver-placeholder" style="background:${bg}; color:${color};">
                    <i class="${icono}"></i>
                </div>`;
        }
        abrirModal('modalVer');
    }

    /* ════════════════════════════════════════
       MODAL EDITAR
    ════════════════════════════════════════ */
    function abrirModalEditar(id, nombre, descripcion, actionUrl) {
        document.getElementById('editNombre').value      = nombre;
        document.getElementById('editDescripcion').value = descripcion;
        document.getElementById('formEditar').action     = actionUrl;
        abrirModal('modalEditar');
    }

    /* ════════════════════════════════════════
       ELIMINAR – SweetAlert2
    ════════════════════════════════════════ */
    function confirmarEliminar(id, nombre) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: '¿Eliminar especialidad?',
                html: `<span style="color:#555;">Estás a punto de eliminar <strong>${nombre}</strong>.<br>Esta acción <u>no se puede deshacer</u>.</span>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#A32D2D',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="fas fa-trash"></i> Sí, eliminar',
                cancelButtonText: 'Cancelar',
                reverseButtons: true,
                focusCancel: true,
            }).then(r => {
                if (r.isConfirmed) document.getElementById(`form-delete-${id}`).submit();
            });
        } else {
            if (confirm(`¿Eliminar la especialidad "${nombre}"?`))
                document.getElementById(`form-delete-${id}`).submit();
        }
    }
</script>
@stop