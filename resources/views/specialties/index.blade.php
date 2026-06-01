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
        $key    = strtolower(trim($specialty->nombre));
        $estilo = $iconos[$key] ?? ['icon' => 'fas fa-notes-medical', 'bg' => '#E6F1FB', 'color' => '#185FA5'];

        $fotosBD = [
            'cardiología'      => 'https://images.unsplash.com/photo-1628348068343-c6a848d2b6dd?w=600&q=80',
            'pediatría'        => 'https://images.unsplash.com/photo-1584820927498-cfe5211fd8bf?w=600&q=80',
            'neurología'       => 'https://images.unsplash.com/photo-1559757175-5700dde675bc?w=600&q=80',
            'traumatología'    => 'https://images.unsplash.com/photo-1530026405186-ed1f139313f8?w=600&q=80',
            'oftalmología'     => 'https://images.unsplash.com/photo-1516574187841-cb9cc2ca948b?w=600&q=80',
            'dermatología'     => 'https://images.unsplash.com/photo-1612349317150-e413f6a5b16d?w=600&q=80',
            'ginecología'      => 'https://images.unsplash.com/photo-1576091160550-2173dba999ef?w=600&q=80',
            'medicina general' => 'https://images.unsplash.com/photo-1506126613408-eca07ce68773?w=600&q=80',
        ];

        if ($specialty->imagen) {
            $foto = asset('storage/' . $specialty->imagen);
        } else {
            $foto = $fotosBD[$key] ?? null;
        }
    @endphp

    <div class="spec-card">

        {{-- IMAGEN --}}
        @if($foto)
            <img src="{{ $foto }}" alt="{{ $specialty->nombre }}" class="card-img">
        @else
            <div class="card-img-placeholder" style="background:{{ $estilo['bg'] }}; color:{{ $estilo['color'] }};">
                <i class="{{ $estilo['icon'] }}" style="font-size:48px;"></i>
            </div>
        @endif

        {{-- CUERPO --}}
        <div class="card-body-custom">
            <div class="card-top">
                <span class="card-name">{{ $specialty->nombre }}</span>
                <span class="status-chip status-active">Activa</span>
            </div>

            <p class="card-desc">{{ Str::limit($specialty->descripcion, 80) }}</p>

            <div class="card-actions">

                <button type="button" class="btn-action"
                    onclick="abrirModalVer(
                        '{{ addslashes($specialty->nombre) }}',
                        '{{ addslashes($specialty->descripcion) }}',
                        '{{ $foto ?? '' }}',
                        '{{ $estilo['icon'] }}',
                        '{{ $estilo['bg'] }}',
                        '{{ $estilo['color'] }}'
                    )">
                    <i class="fas fa-eye"></i> Ver
                </button>

                <button type="button" class="btn-action btn-edit"
                    onclick="abrirModalEditar(
                        {{ $specialty->id }},
                        '{{ addslashes($specialty->nombre) }}',
                        '{{ addslashes($specialty->descripcion) }}',
                        '{{ route('specialties.update', $specialty) }}'
                    )">
                    <i class="fas fa-edit"></i> Editar
                </button>

                <form action="{{ route('specialties.destroy', $specialty) }}" method="POST"
                    class="d-inline" id="form-delete-{{ $specialty->id }}">
                    @csrf @method('DELETE')
                    <button type="button" class="btn-action btn-del"
                        onclick="confirmarEliminar({{ $specialty->id }}, '{{ addslashes($specialty->nombre) }}')">
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
    </div>
    @endforelse
</div>

{{-- PAGINACIÓN --}}
@if($specialties->hasPages())
<div class="pagination-wrapper mt-4">
    {{ $specialties->withQueryString()->links() }}
</div>
@endif


{{-- MODAL NUEVA --}}
<div class="modal-overlay" id="modalNueva" onclick="cerrarModal('modalNueva')">
    <div class="modal-box modal-box-lg" onclick="event.stopPropagation()">
        <button class="modal-close" onclick="cerrarModal('modalNueva')"><i class="fas fa-times"></i></button>

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
            <form id="formNueva" method="POST" action="{{ route('specialties.store') }}" enctype="multipart/form-data">
                @csrf

                <div class="field-group mb-3">
                    <label class="field-label">
                        <i class="fas fa-tag"></i> Nombre de la especialidad <span class="req">*</span>
                    </label>
                    <input type="text" name="nombre" class="field-input"
                           placeholder="Ej: Cardiología, Pediatría…" required>
                </div>

                <div class="field-group mb-3">
                    <label class="field-label">
                        <i class="fas fa-user-md"></i> Nombre del doctor
                    </label>
                    <input type="text" name="Nombre del Doctor" class="field-input"
                           placeholder="Ej: Dr. Juan Pérez">
                </div>

                <div class="field-group mb-3">
                    <label class="field-label">
                        <i class="fas fa-align-left"></i> Descripción
                    </label>
                    <textarea name="descripcion" class="field-input field-textarea" rows="3"
                              placeholder="Breve descripción de la especialidad…"></textarea>
                </div>

                <div class="field-group mb-3">
                    <label class="field-label">
                        <i class="fas fa-image"></i> Imagen
                    </label>
                    <input type="file" name="imagen" class="field-input" accept="image/*">
                    <small class="text-muted">Opcional. Máximo 2MB.</small>
                </div>

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


{{-- MODAL VER --}}
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


{{-- MODAL EDITAR --}}
<div class="modal-overlay" id="modalEditar" onclick="cerrarModal('modalEditar')">
    <div class="modal-box modal-box-lg" onclick="event.stopPropagation()">
        <button class="modal-close" onclick="cerrarModal('modalEditar')"><i class="fas fa-times"></i></button>

        <div class="modal-header-bar">
            <i class="fas fa-edit modal-header-icon"></i>
            <h2 class="modal-title">Editar Especialidad</h2>
        </div>

        <div class="modal-body-inner">
            <form id="formEditar" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="field-group mb-3">
                    <label class="field-label">Nombre</label>
                    <input type="text" id="editNombre" name="nombre" class="field-input" required>
                </div>

                <div class="field-group mb-3">
                    <label class="field-label">Descripción</label>
                    <textarea id="editDescripcion" name="descripcion" class="field-input field-textarea" rows="4"></textarea>
                </div>

                <div class="field-group mb-3">
                    <label class="field-label">
                        <i class="fas fa-image"></i> Nueva imagen
                    </label>
                    <input type="file" name="imagen" class="field-input" accept="image/*">
                    <small class="text-muted">Deja vacío para mantener la imagen actual.</small>
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
    .btn-action:hover          { background: #f5f5f5; color: #333; }
    .btn-action.btn-edit:hover { background: #FAEEDA; color: #854F0B; border-color: #EF9F27; }
    .btn-action.btn-del:hover  { background: #FCEBEB; color: #A32D2D; border-color: #F09595; }

    .empty-state {
        grid-column: 1 / -1; text-align: center;
        padding: 4rem 1rem; color: #bbb;
    }
    .empty-state p { font-size: 15px; margin-top: 8px; }
    .pagination-wrapper .pagination { justify-content: center; }

    .main-sidebar { position: fixed !important; height: 100vh !important; overflow-y: auto; }
    .content-wrapper, .main-footer, .main-header { margin-left: 250px !important; }
    .wrapper { overflow-x: hidde

        .main-sidebar { 
        position: fixed !important; 
        height: 100vh !important; 
        overflow-y: auto; 
    }

    .content-wrapper, 
    .main-footer, 
    .main-header { 
        margin-left: 250px !important; 
    }

    .wrapper { 
        overflow-x: hidden; 
    }
    

    /* MODALES */
    .modal-overlay{
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,.55);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 9999;
        padding: 20px;
    }

    .modal-overlay.active{
        display: flex;
    }

    .modal-box{
        background: #fff;
        width: 100%;
        max-width: 520px;
        border-radius: 18px;
        overflow: hidden;
        position: relative;
        animation: modalShow .2s ease;
    }

    .modal-box-lg{
        max-width: 700px;
    }

    @keyframes modalShow{
        from{
            opacity:0;
            transform: translateY(10px) scale(.98);
        }
        to{
            opacity:1;
            transform: translateY(0) scale(1);
        }
    }

    .modal-close{
        position: absolute;
        top: 14px;
        right: 14px;
        border: none;
        width: 34px;
        height: 34px;
        border-radius: 50%;
        background: #f3f3f3;
        cursor: pointer;
        z-index: 10;
    }

    .modal-close:hover{
        background: #e9e9e9;
    }

    .modal-header-bar{
        padding: 22px 24px;
        display: flex;
        align-items: center;
        gap: 14px;
        border-bottom: 1px solid #f1f1f1;
    }

    .modal-header-nueva{
        background: linear-gradient(135deg,#185FA5,#0C447C);
        color: white;
    }

    .modal-header-icon-wrap{
        width: 54px;
        height: 54px;
        border-radius: 14px;
        background: rgba(255,255,255,.15);
        display:flex;
        align-items:center;
        justify-content:center;
        font-size:22px;
    }

    .modal-header-icon{
        font-size: 22px;
        color:#185FA5;
    }

    .modal-title{
        font-size: 1.2rem;
        font-weight: 600;
        margin: 0;
    }

    .modal-subtitle{
        margin: 2px 0 0;
        opacity: .9;
        font-size: 13px;
    }

    .modal-body-inner{
        padding: 24px;
    }

    .field-group{
        display:flex;
        flex-direction:column;
    }

    .field-label{
        font-size:13px;
        font-weight:600;
        margin-bottom:8px;
        color:#444;
    }

    .field-input{
        width:100%;
        border:1px solid #ddd;
        border-radius:10px;
        padding:12px 14px;
        font-size:14px;
        transition:.2s;
    }

    .field-input:focus{
        border-color:#185FA5;
        outline:none;
        box-shadow:0 0 0 3px rgba(24,95,165,.1);
    }

    .field-textarea{
        resize:none;
    }

    .req{
        color:#d62828;
    }

    .status-selector{
        display:flex;
        gap:10px;
        flex-wrap:wrap;
    }

    .status-opt input{
        display:none;
    }

    .status-opt-box{
        border:1px solid #ddd;
        border-radius:10px;
        padding:10px 16px;
        display:flex;
        align-items:center;
        gap:8px;
        cursor:pointer;
        font-size:13px;
        transition:.2s;
    }

    .status-opt input:checked + .status-opt-box{
        border-color:#185FA5;
        background:#EEF5FC;
        color:#185FA5;
    }

    .modal-footer-actions{
        display:flex;
        justify-content:flex-end;
        gap:10px;
    }

    .btn-modal-cancel{
        border:none;
        background:#ececec;
        color:#444;
        padding:10px 16px;
        border-radius:10px;
        cursor:pointer;
    }

    .btn-modal-save{
        border:none;
        background:#185FA5;
        color:#fff;
        padding:10px 18px;
        border-radius:10px;
        cursor:pointer;
    }

    .btn-modal-save:hover{
        background:#0C447C;
    }

    .btn-modal-save-green{
        background:#198754;
    }

    .btn-modal-save-green:hover{
        background:#146c43;
    }

    .ver-header{
        display:flex;
        justify-content:space-between;
        align-items:center;
        margin-bottom:14px;
    }

    .modal-label{
        font-size:13px;
        font-weight:600;
        color:#666;
        margin-bottom:6px;
    }

    .modal-text{
        color:#555;
        line-height:1.6;
    }

    #verImgWrap img{
        width:100%;
        height:240px;
        object-fit:cover;
    }

    @media(max-width:768px){

        .content-wrapper,
        .main-footer,
        .main-header{
            margin-left:0 !important;
        }

        .specs-grid{
            grid-template-columns:1fr;
        }

        .card-actions{
            flex-direction:column;
        }

        .modal-footer-actions{
            flex-direction:column;
        }

        .btn-modal-save,
        .btn-modal-cancel{
            width:100%;
        }
    }
</style>
@stop

@section('js')
<script>

    function abrirModal(id){
        document.getElementById(id).classList.add('active');
    }

    function cerrarModal(id){
        document.getElementById(id).classList.remove('active');
    }

    function abrirModalVer(nombre, descripcion, foto, icono, bg, color){

        document.getElementById('verNombre').innerText = nombre;
        document.getElementById('verDescripcion').innerText = descripcion || 'Sin descripción disponible';

        let wrap = document.getElementById('verImgWrap');

        if(foto){
            wrap.innerHTML = `
                <img src="${foto}" alt="${nombre}">
            `;
        }else{
            wrap.innerHTML = `
                <div class="card-img-placeholder" style="height:240px;background:${bg};color:${color};">
                    <i class="${icono}" style="font-size:60px;"></i>
                </div>
            `;
        }

        abrirModal('modalVer');
    }

    function abrirModalEditar(id, nombre, descripcion, action){

        document.getElementById('editNombre').value = nombre;
        document.getElementById('editDescripcion').value = descripcion;

        document.getElementById('formEditar').action = action;

        abrirModal('modalEditar');
    }

    function confirmarEliminar(id, nombre){

        if(confirm('¿Deseas eliminar la especialidad "' + nombre + '"?')){
            document.getElementById('form-delete-' + id).submit();
        }
    }

    function applyFilter(status){

        const url = new URL(window.location.href);

        if(status){
            url.searchParams.set('status', status);
        }else{
            url.searchParams.delete('status');
        }

        window.location.href = url.toString();
    }

</script>
@stop