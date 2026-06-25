<template>
  <div class="specialties-page">

    <!-- ===== TOAST DE CONFIRMACIÓN ===== -->
    <transition name="toast">
      <div v-if="toast.visible" class="toast-success">
        <span class="toast-icon"><i class="fas fa-check-circle"></i></span>
        <span>{{ toast.mensaje }}</span>
      </div>
    </transition>

    <!-- HEADER -->
    <div class="header">
      <div class="header-text">
        <p class="eyebrow">Consultorio · Gestión clínica</p>
        <h1 class="page-title">Especialidades Médicas</h1>
        <p class="text-muted">Administra las especialidades disponibles en el consultorio</p>
      </div>
      <button class="btn-primary" @click="abrirModal('nueva')">
        <i class="fas fa-plus"></i> Nueva especialidad
      </button>
    </div>

    <!-- TOOLBAR -->
    <div class="toolbar-card">
      <div class="search-bar">
        <i class="fas fa-search"></i>
        <input v-model="search" type="text" class="search-input" placeholder="Buscar especialidad..." />
        <button v-if="search" class="btn-clear" @click="search = ''" aria-label="Limpiar búsqueda">
          <i class="fas fa-times"></i>
        </button>
      </div>
      <div class="filter-row">
        <button class="filter-pill" :class="{active: status === ''}" @click="status = ''">Todas</button>
        <button class="filter-pill" :class="{active: status === 'Activo'}" @click="status = 'Activo'">Activas</button>
        <button class="filter-pill" :class="{active: status === 'Inactivo'}" @click="status = 'Inactivo'">Inactivas</button>
      </div>
    </div>

    <!-- CONTADOR -->
    <p class="count-label">
      <i class="fas fa-stethoscope"></i>
      {{ filteredSpecialties.length }} especialidad{{ filteredSpecialties.length === 1 ? '' : 'es' }}
    </p>

    <!-- GRID -->
    <div class="specs-grid">
      <div
        v-for="item in filteredSpecialties"
        :key="item.id"
        class="card-body-custom"
        :style="{ '--accent': avatarColor(item.nombre) }"
      >
        <!-- Parte superior: avatar + nombre + chip de estado -->
        <div class="card-top">
          <div class="card-id">
            <span class="avatar">{{ initials(item.nombre) }}</span>
            <!-- Nombre y, debajo, doctor + folio -->
            <div class="card-title-group">
              <span class="card-name">{{ item.nombre }}</span>
              <!-- Doctor responsable: solo se muestra si existe en el array medicos -->
              <span v-if="item.medicos && item.medicos.length > 0" class="card-meta">
                <i class="fas fa-user-md"></i> {{ item.medicos[0].nombre }}
              </span>    
              <!-- muestra cuando no hay doctor asignado             -->
              <span v-else class="card-meta card-meta--empty">
                <i class="fas fa-user-md"></i> Sin médico asignado
               </span>
              <!-- Folio: solo se muestra si existe el valor -->
              <span v-if="item.folio" class="card-folio">
                <i class="fas fa-hashtag"></i> {{ item.folio }}
              </span>
            </div>
          </div>
          <span class="status-chip" :class="item.estado === 'Activo' ? 'active' : 'inactive'">
            <span class="status-dot"></span>{{ item.estado }}
          </span>
        </div>

        <!-- Descripción recortada -->
        <p class="card-desc">{{ limit(item.descripcion, 80) }}</p>

        <!-- Botones de acción -->
        <div class="card-actions">
          <button class="action-btn view"   @click="ver(item)"><i class="fas fa-eye"></i> Ver</button>
          <button class="action-btn edit"   @click="editar(item)"><i class="fas fa-edit"></i> Editar</button>
          <button class="action-btn delete" @click="eliminar(item)"><i class="fas fa-trash"></i> Eliminar</button>
        </div>
      </div>
    </div>

    <!-- EMPTY STATE -->
    <div v-if="filteredSpecialties.length === 0" class="empty-state">
      <div class="empty-icon"><i class="fas fa-folder-open"></i></div>
      <p class="empty-title">No se encontraron especialidades</p>
      <p class="empty-sub">Ajusta la búsqueda o los filtros, o crea una nueva especialidad.</p>
    </div>

    <!-- ===== MODAL VER ===== -->
    <div v-if="modales.ver" class="modal-overlay" @click.self="modales.ver = false">
      <div class="modal-box">

        <div class="modal-header">
          <h2><i class="fas fa-stethoscope"></i> Detalle de la especialidad</h2>
          <button class="btn-close" @click="modales.ver = false" aria-label="Cerrar">
            <i class="fas fa-times"></i>
          </button>
        </div>

        <!-- Nombre -->
        <div class="detail-item">
          <label><i class="fas fa-tag detail-icon"></i> Nombre</label>
          <p>{{ selected.nombre }}</p>
        </div>

        <!-- Doctor responsable -->
        <div class="detail-item">
          <label><i class="fas fa-user-md detail-icon"></i> Doctor responsable</label>
          <p>{{ selected.medicos && selected.medicos.length > 0 ? selected.medicos[0].nombre : '—' }}</p>
        </div>

        <!-- ★ Folio — campo nuevo -->
        <div class="detail-item">
          <label><i class="fas fa-hashtag detail-icon"></i> Folio</label>
          <p>{{ selected.folio || '—' }}</p>
        </div>

        <!-- Descripción -->
        <div class="detail-item">
          <label><i class="fas fa-align-left detail-icon"></i> Descripción</label>
          <p>{{ selected.descripcion }}</p>
        </div>

        <!-- Estado -->
        <div class="detail-item">
          <label><i class="fas fa-toggle-on detail-icon"></i> Estado</label>
          <div>
            <span class="badge" :class="selected.estado === 'Activo' ? 'active' : 'inactive'">
              {{ selected.estado }}
            </span>
          </div>
        </div>

      </div>
    </div>

    <!-- ===== MODAL NUEVA ===== -->
    <div v-if="modales.nueva" class="modal-overlay" @click.self="cerrarNueva()">
      <div class="modal-box">

        <div class="modal-header">
          <h2><i class="fas fa-plus-circle"></i> Nueva especialidad</h2>
          <button class="btn-close" @click="cerrarNueva()" aria-label="Cerrar">
            <i class="fas fa-times"></i>
          </button>
        </div>

        <label><i class="fas fa-tag field-icon"></i> Nombre de la especialidad</label>
        <input v-model="form.nombre" type="text" placeholder="Ej. Cardiología" />

        <label><i class="fas fa-user-md field-icon"></i> Doctor responsable</label>
        <input v-model="form.doctor" type="text" placeholder="Nombre del doctor responsable" />

        <!-- ★ Folio — campo nuevo -->
        <label><i class="fas fa-hashtag field-icon"></i> Folio</label>
        <input v-model="form.folio" type="text" placeholder="Ej. ESP-001" />

        <label><i class="fas fa-align-left field-icon"></i> Descripción</label>
        <textarea v-model="form.descripcion" placeholder="Describe brevemente esta especialidad"></textarea>

        <label><i class="fas fa-toggle-on field-icon"></i> Estado</label>
        <select v-model="form.estado">
          <option value="Activo">Activo</option>
          <option value="Inactivo">Inactivo</option>
        </select>

        <div class="modal-actions">
          <button class="btn-cancel" @click="cerrarNueva()">Cancelar</button>
          <button class="btn-save" @click="guardar">
            <i class="fas fa-save"></i> Guardar
          </button>
        </div>

      </div>
    </div>

    <!-- ===== MODAL EDITAR ===== -->
    <div v-if="modales.editar" class="modal-overlay" @click.self="modales.editar = false">
      <div class="modal-box">

        <div class="modal-header">
          <h2><i class="fas fa-edit"></i> Editar especialidad</h2>
          <button class="btn-close" @click="modales.editar = false" aria-label="Cerrar">
            <i class="fas fa-times"></i>
          </button>
        </div>

        <label><i class="fas fa-tag field-icon"></i> Nombre de la especialidad</label>
        <input v-model="form.nombre" type="text" />

        <label><i class="fas fa-user-md field-icon"></i> Doctor responsable</label>
        <input v-model="form.doctor" type="text" />

        <!-- ★ Folio — campo nuevo -->
        <label><i class="fas fa-hashtag field-icon"></i> Folio</label>
        <input v-model="form.folio" type="text" placeholder="Ej. ESP-001" />

        <label><i class="fas fa-align-left field-icon"></i> Descripción</label>
        <textarea v-model="form.descripcion"></textarea>

        <label><i class="fas fa-toggle-on field-icon"></i> Estado</label>
        <select v-model="form.estado">
          <option value="Activo">Activo</option>
          <option value="Inactivo">Inactivo</option>
        </select>

        <div class="modal-actions">
          <button class="btn-cancel" @click="modales.editar = false">Cancelar</button>
          <button class="btn-save" @click="actualizar">
            <i class="fas fa-save"></i> Actualizar
          </button>
        </div>

      </div>
    </div>

    <!-- ===== MODAL ELIMINAR ===== -->
    <div v-if="modales.eliminar" class="modal-overlay" @click.self="modales.eliminar = false">
      <div class="modal-box modal-box--danger">

        <div class="modal-header">
          <h2><i class="fas fa-exclamation-triangle" style="color:#b91c1c;"></i> Eliminar especialidad</h2>
          <button class="btn-close" @click="modales.eliminar = false" aria-label="Cerrar">
            <i class="fas fa-times"></i>
          </button>
        </div>

        <div class="delete-warning">
          <div class="delete-icon-wrap">
            <i class="fas fa-trash-alt"></i>
          </div>
          <p class="delete-question">
            ¿Estás seguro de que deseas eliminar<br>
            <strong>{{ specialtyToDelete?.nombre }}</strong>?
          </p>
          <p class="delete-sub">Esta acción no se puede deshacer.</p>
        </div>

        <div class="modal-actions">
          <button class="btn-cancel" @click="modales.eliminar = false">Cancelar</button>
          <button class="btn-delete" @click="confirmarEliminar">
            <i class="fas fa-trash"></i> Sí, eliminar
          </button>
        </div>

      </div>
    </div>

  </div>
</template>

<script>
import axios from "axios";

export default {
  data() {
    return {
      specialties: [],
      search: "",
      status: "",
      selected: {},
      specialtyToDelete: null,
      // folio agregado al modelo del formulario
      form: { id: null, nombre: "", doctor: "", folio: "", descripcion: "", estado: "Activo" },
      modales: { ver: false, nueva: false, editar: false, eliminar: false },
      toast: { visible: false, mensaje: "" },
      _toastTimer: null,
    };
  },
// Filtra automáticamente las especialidades según el texto de búsqueda y el estado seleccionado.
  computed: {
    filteredSpecialties() {
      return this.specialties.filter((s) => {
        const matchSearch = s.nombre.toLowerCase().includes(this.search.toLowerCase());
        const matchStatus = this.status === "" ? true : s.estado === this.status;
        return matchSearch && matchStatus;
      });
    },
  },

  mounted() {
    this.cargar();
  },

  methods: {
    cargar() {
      axios.get("/api/specialties").then((res) => {
        this.specialties = res.data;
      });
    },

    abrirModal(name) { this.modales[name] = true; },

    ver(item) { this.selected = item; this.modales.ver = true; },

    editar(item) { this.form = { ...item }; this.modales.editar = true; },
// Guarda una nueva especialidad, actualiza la lista, cierra el formulario y muestra un mensaje de éxito.
    guardar() {
      axios.post("/specialties", this.form).then(() => {
        this.cargar();
        this.modales.nueva = false;
        this.resetForm();
        this.mostrarToast("✓ Especialidad guardada correctamente");
      });
    },
// Actualiza los datos de una especialidad, recarga la lista, cierra el formulario y muestra un mensaje de confirmación.
    actualizar() {
      axios.put(`/specialties/${this.form.id}`, this.form).then(() => {
        this.cargar();
        this.modales.editar = false;
        this.mostrarToast("✓ Actualización guardada");
      });
    },
// Guarda la especialidad seleccionada y abre el cuadro de confirmación para eliminarla.
    eliminar(item) {
      this.specialtyToDelete = item;
      this.modales.eliminar = true;
    },
// Elimina la especialidad seleccionada, actualiza la lista y muestra un mensaje de confirmación.
    confirmarEliminar() {
      axios.delete(`/specialties/${this.specialtyToDelete.id}`).then(() => {
        this.cargar();
        this.modales.eliminar = false;
        this.mostrarToast(`✓ "${this.specialtyToDelete.nombre}" eliminada`);
        this.specialtyToDelete = null;
      });
    },
// Cierra el formulario de nueva especialidad y limpia los datos ingresados.
    cerrarNueva() {
      this.modales.nueva = false;
      this.resetForm();
    },

    // folio incluido en el reset
    resetForm() {
      this.form = { id: null, nombre: "", doctor: "", folio: "", descripcion: "", estado: "Activo" };
    },
// Muestra un mensaje tipo toast temporal y lo oculta automáticamente después de unos segundos.
    mostrarToast(mensaje) {
      clearTimeout(this._toastTimer);
      this.toast.mensaje = mensaje;
      this.toast.visible = true;
      this._toastTimer = setTimeout(() => { this.toast.visible = false; }, 2500);
    },
// Limita la longitud de un texto y agrega puntos suspensivos si excede el tamaño permitido.
    limit(text, n) {
      if (!text) return "";
      return text.length > n ? text.substring(0, n) + "..." : text;
    },
// Genera iniciales a partir de un nombre (2 letras si es una palabra o primeras letras de nombre y apellido).
    initials(name) {
      if (!name) return "";
      const parts = name.trim().split(/\s+/);
      if (parts.length === 1) return parts[0].substring(0, 2).toUpperCase();
      return (parts[0][0] + parts[1][0]).toUpperCase();
    },
// Genera un color de avatar basado en el nombre para asignar colores consistentes
    avatarColor(name) {
      const palette = ["#2563eb", "#0d9488", "#7c3aed", "#0891b2", "#4f46e5", "#0e7490"];
      if (!name) return palette[0];
      let hash = 0;
      for (let i = 0; i < name.length; i++) {
        hash = name.charCodeAt(i) + ((hash << 5) - hash);
      }
      return palette[Math.abs(hash) % palette.length];
    },
  },
};
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap');

.specialties-page{
    --primary:#2563eb;
    --primary-dark:#1d4ed8;
    --primary-soft:#eff6ff;
    --ink:#0f172a;
    --body-text:#475569;
    --muted:#94a3b8;
    --surface:#ffffff;
    --bg:#f1f5f9;
    --border:#e2e8f0;
    --success-bg:#dcfce7;
    --success-text:#15803d;
    --danger-bg:#fee2e2;
    --danger-text:#b91c1c;
    --radius-lg:18px;
    --shadow-sm:0 1px 2px rgba(15,23,42,.04), 0 1px 3px rgba(15,23,42,.06);
    --shadow-md:0 8px 24px rgba(15,23,42,.08);
    --shadow-lg:0 20px 45px rgba(15,23,42,.16);
    font-family:'Inter',-apple-system,sans-serif;
    padding:36px;
    min-height:100vh;
    background:
        radial-gradient(1100px 560px at 8% -10%, rgba(37,99,235,.07), transparent 60%),
        radial-gradient(900px 500px at 100% 0%, rgba(13,148,136,.05), transparent 55%),
        var(--bg);
}

/* ===== TOAST ===== */
.toast-success{
    position:fixed; bottom:32px; right:32px; z-index:99999;
    display:inline-flex; align-items:center; gap:10px;
    background:#15803d; color:#fff; padding:14px 22px;
    border-radius:14px; font-weight:600; font-size:14.5px;
    box-shadow:0 12px 32px rgba(21,128,61,.35); pointer-events:none;
}
.toast-icon{ font-size:18px; line-height:1; }
.toast-enter-active{ animation: toast-in .35s cubic-bezier(.16,1,.3,1) forwards; }
.toast-leave-active{ animation: toast-out .3s ease forwards; }
@keyframes toast-in{ from{ opacity:0; transform:translateY(20px) scale(.95); } to{ opacity:1; transform:translateY(0) scale(1); } }
@keyframes toast-out{ from{ opacity:1; transform:translateY(0); } to{ opacity:0; transform:translateY(12px); } }

/* ===== ICONOS EN LABELS ===== */
.field-icon, .detail-icon{ color:var(--primary); font-size:11px; margin-right:5px; opacity:.85; }

/* ===== HEADER ===== */
.header{ display:flex; justify-content:space-between; align-items:flex-end; margin-bottom:28px; flex-wrap:wrap; gap:18px; }
.eyebrow{ margin:0 0 6px; font-size:12px; font-weight:700; letter-spacing:.08em; text-transform:uppercase; color:var(--primary); }
.page-title{ margin:0; font-family:'Plus Jakarta Sans',sans-serif; font-size:32px; font-weight:800; letter-spacing:-.02em; color:var(--ink); }
.text-muted{ color:var(--body-text); margin-top:6px; font-size:15px; }

/* ===== BOTÓN PRIMARIO ===== */
.btn-primary{ display:inline-flex; align-items:center; gap:8px; background:linear-gradient(135deg, var(--primary), var(--primary-dark)); color:#fff; border:none; border-radius:12px; padding:13px 24px; font-weight:600; font-size:14.5px; cursor:pointer; box-shadow:0 10px 22px rgba(37,99,235,.28); transition:transform .2s ease, box-shadow .2s ease; }
.btn-primary:hover{ background:linear-gradient(135deg, var(--primary-dark), var(--primary-dark)); transform:translateY(-2px); box-shadow:0 14px 28px rgba(37,99,235,.34); }
.btn-primary:active{ transform:translateY(0); }

/* ===== TOOLBAR ===== */
.toolbar-card{ background:var(--surface); border-radius:var(--radius-lg); padding:18px 22px; box-shadow:var(--shadow-md); margin-bottom:18px; display:flex; flex-direction:column; gap:16px; }
.search-bar{ display:flex; align-items:center; gap:10px; padding-bottom:14px; border-bottom:1px solid var(--border); }
.search-bar i.fa-search{ color:var(--muted); }
.search-input{ flex:1; border:none; outline:none; font-size:15px; font-family:'Inter',sans-serif; color:var(--ink); background:transparent; }
.search-input::placeholder{ color:var(--muted); }
.btn-clear{ width:28px; height:28px; border-radius:50%; border:none; background:var(--bg); color:var(--body-text); display:flex; align-items:center; justify-content:center; cursor:pointer; transition:.2s; }
.btn-clear:hover{ background:var(--danger-bg); color:var(--danger-text); }
.filter-row{ display:flex; gap:10px; flex-wrap:wrap; }
.filter-pill{ border:1.5px solid var(--border); background:var(--surface); color:var(--body-text); padding:9px 18px; border-radius:30px; font-weight:600; font-size:13.5px; cursor:pointer; transition:all .2s ease; }
.filter-pill:hover{ border-color:var(--primary); color:var(--primary); }
.filter-pill.active{ background:var(--primary); border-color:var(--primary); color:#fff; box-shadow:0 6px 14px rgba(37,99,235,.28); }

/* CONTADOR */
.count-label{ display:flex; align-items:center; gap:8px; margin:4px 0 20px; color:var(--body-text); font-weight:600; font-size:14px; }
.count-label i{ color:var(--primary); font-size:12px; }

/* ===== GRID ===== */
.specs-grid{ display:grid; grid-template-columns:repeat(auto-fill,minmax(320px,1fr)); gap:20px; }

/* TARJETA */
.card-body-custom{ position:relative; background:var(--surface); border:1px solid var(--border); border-radius:var(--radius-lg); padding:22px 22px 18px; box-shadow:var(--shadow-sm); overflow:hidden; transition:transform .25s ease, box-shadow .25s ease; }
.card-body-custom::before{ content:''; position:absolute; top:0; left:0; right:0; height:3px; background:var(--accent); }
.card-body-custom:hover{ transform:translateY(-4px); box-shadow:var(--shadow-lg); }
.card-top{ display:flex; justify-content:space-between; align-items:flex-start; gap:10px; margin-bottom:14px; }
.card-id{ display:flex; align-items:flex-start; gap:12px; min-width:0; }
.avatar{ flex-shrink:0; width:40px; height:40px; border-radius:12px; background:var(--accent); color:#fff; display:flex; align-items:center; justify-content:center; font-family:'Plus Jakarta Sans',sans-serif; font-weight:700; font-size:14px; }

/* ★ Grupo título + meta debajo del nombre */
.card-title-group{ display:flex; flex-direction:column; gap:3px; min-width:0; }
.card-name{ font-family:'Plus Jakarta Sans',sans-serif; font-size:17px; font-weight:700; color:var(--ink); overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
/* Doctor responsable en la tarjeta */
.card-meta{ display:flex; align-items:center; gap:5px; font-size:12.5px; font-weight:500; color:var(--body-text); }
.card-meta i{ color:var(--primary); font-size:11px; flex-shrink:0; }
/* Folio en la tarjeta — ligeramente más apagado que el doctor */
.card-folio{ display:flex; align-items:center; gap:5px; font-size:11.5px; font-weight:600; color:var(--muted); letter-spacing:.03em; }
.card-folio i{ font-size:10px; flex-shrink:0; }

.status-chip{ display:inline-flex; align-items:center; gap:6px; padding:5px 12px; border-radius:30px; font-size:12px; font-weight:600; white-space:nowrap; }
.status-chip.active{ background:var(--success-bg); color:var(--success-text); }
.status-chip.inactive{ background:var(--danger-bg); color:var(--danger-text); }
.status-dot{ width:6px; height:6px; border-radius:50%; background:currentColor; }
.card-desc{ color:var(--body-text); line-height:1.6; font-size:14px; margin:0 0 18px; }
.card-actions{ display:flex; gap:6px; padding-top:14px; border-top:1px solid var(--border); }
.action-btn{ flex:1; display:flex; align-items:center; justify-content:center; gap:6px; border:none; background:transparent; border-radius:9px; padding:9px; font-size:13px; font-weight:600; color:var(--body-text); cursor:pointer; transition:.2s; }
.action-btn.view:hover{ background:var(--primary-soft); color:var(--primary); }
.action-btn.edit:hover{ background:#fef3c7; color:#b45309; }
.action-btn.delete:hover{ background:var(--danger-bg); color:var(--danger-text); }

/* EMPTY */
.empty-state{ background:var(--surface); padding:60px 30px; text-align:center; border-radius:var(--radius-lg); box-shadow:var(--shadow-sm); margin-top:10px; }
.empty-icon{ width:64px; height:64px; border-radius:50%; background:var(--primary-soft); color:var(--primary); display:flex; align-items:center; justify-content:center; font-size:24px; margin:0 auto 18px; }
.empty-title{ font-family:'Plus Jakarta Sans',sans-serif; font-weight:700; font-size:17px; color:var(--ink); margin:0 0 6px; }
.empty-sub{ color:var(--muted); font-size:14px; margin:0; }

/* ===== MODAL ===== */
.modal-overlay{ position:fixed; inset:0; background:rgba(15,23,42,.55); backdrop-filter:blur(4px); display:flex; justify-content:center; align-items:center; z-index:9999; padding:20px; }
.modal-box{ background:var(--surface); width:480px; max-width:100%; border-radius:20px; padding:26px; box-shadow:var(--shadow-lg); animation:modal-in .25s cubic-bezier(.16,1,.3,1); }
.modal-header{ display:flex; justify-content:space-between; align-items:center; margin-bottom:22px; padding-bottom:16px; border-bottom:1px solid var(--border); }
.modal-header h2{ display:flex; align-items:center; gap:10px; margin:0; font-family:'Plus Jakarta Sans',sans-serif; font-size:19px; font-weight:700; color:var(--ink); }
.modal-header h2 i{ color:var(--primary); font-size:16px; }
.btn-close{ width:32px; height:32px; border-radius:50%; border:none; background:var(--bg); color:var(--body-text); display:flex; align-items:center; justify-content:center; cursor:pointer; transition:.2s; }
.btn-close:hover{ background:var(--primary-soft); color:var(--primary); }

/* Modal eliminar */
.modal-box--danger .modal-header h2 i{ color:var(--danger-text); }
.delete-warning{ text-align:center; padding:18px 0 10px; }
.delete-icon-wrap{ width:64px; height:64px; border-radius:50%; background:var(--danger-bg); color:var(--danger-text); display:flex; align-items:center; justify-content:center; font-size:26px; margin:0 auto 18px; }
.delete-question{ font-size:16px; color:var(--ink); margin:0 0 8px; line-height:1.5; }
.delete-question strong{ font-weight:700; color:var(--ink); }
.delete-sub{ font-size:13.5px; color:var(--body-text); margin:0 0 6px; }
.btn-delete{ display:inline-flex; align-items:center; gap:8px; border:none; border-radius:10px; padding:12px 20px; font-weight:600; font-size:14px; cursor:pointer; transition:.2s; background:#b91c1c; color:#fff; }
.btn-delete:hover{ background:#991b1b; }

/* Campos del formulario */
.modal-box label{ display:block; font-size:12px; font-weight:600; letter-spacing:.04em; text-transform:uppercase; color:var(--body-text); margin-bottom:6px; }
.modal-box input, .modal-box textarea, .modal-box select{ width:100%; padding:12px 14px; margin-bottom:16px; border:1.5px solid var(--border); border-radius:10px; outline:none; font-family:'Inter',sans-serif; font-size:14.5px; color:var(--ink); background:var(--surface); transition:.2s; box-sizing:border-box; }
.modal-box input:focus, .modal-box textarea:focus, .modal-box select:focus{ border-color:var(--primary); box-shadow:0 0 0 3px rgba(37,99,235,.12); }
.modal-box textarea{ resize:vertical; min-height:110px; }
.modal-actions{ display:flex; justify-content:flex-end; gap:10px; margin-top:6px; }
.btn-save, .btn-cancel{ display:inline-flex; align-items:center; gap:8px; border:none; border-radius:10px; padding:12px 20px; font-weight:600; font-size:14px; cursor:pointer; transition:.2s; }
.btn-save{ background:var(--primary); color:#fff; }
.btn-save:hover{ background:var(--primary-dark); }
.btn-cancel{ background:var(--bg); color:var(--body-text); }
.btn-cancel:hover{ background:#e2e8f0; }

/* DETALLE (MODAL VER) */
.detail-item{ margin-bottom:18px; }
.detail-item label{ display:block; font-size:11px; font-weight:700; letter-spacing:.06em; text-transform:uppercase; color:var(--muted); margin-bottom:6px; }
.detail-item p{ margin:0; font-size:16px; color:var(--ink); line-height:1.5; }
.badge{ display:inline-flex; align-items:center; padding:6px 14px; border-radius:20px; font-size:13px; font-weight:600; }
.badge.active{ background:var(--success-bg); color:var(--success-text); }
.badge.inactive{ background:var(--danger-bg); color:var(--danger-text); }

/* ===== ANIMACIONES ===== */
@keyframes modal-in{ from{ opacity:0; transform:scale(.94) translateY(8px); } to{ opacity:1; transform:scale(1) translateY(0); } }

@media (prefers-reduced-motion: reduce){
    .card-body-custom, .btn-primary, .modal-box{ animation:none !important; transition:none !important; }
}

.specialties-page :focus-visible{ outline:2px solid var(--primary); outline-offset:2px; }

/* ===== RESPONSIVE ===== */
@media(max-width:768px){
    .specialties-page{ padding:20px; }
    .header{ flex-direction:column; align-items:flex-start; }
    .btn-primary{ width:100%; justify-content:center; }
    .specs-grid{ grid-template-columns:1fr; }
    .filter-row{ width:100%; }
    .filter-pill{ flex:1; text-align:center; }
    .card-actions{ flex-direction:column; }
    .modal-box{ padding:20px; }
    .toast-success{ bottom:16px; right:16px; left:16px; justify-content:center; }
}
</style>