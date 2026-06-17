<template>
  <div class="specialties-page">

    <!-- HEADER: título de la página y botón para crear una nueva especialidad -->
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

    <!-- TOOLBAR: buscador por nombre + filtros por estado (Todas / Activas / Inactivas) -->
    <div class="toolbar-card">
      <div class="search-bar">
        <i class="fas fa-search"></i>

        <!-- v-model="search" alimenta el computed filteredSpecialties -->
        <input
          v-model="search"
          type="text"
          class="search-input"
          placeholder="Buscar especialidad..."
        />

        <!-- Botón para limpiar el texto de búsqueda rápidamente -->
        <button v-if="search" class="btn-clear" @click="search = ''" aria-label="Limpiar búsqueda">
          <i class="fas fa-times"></i>
        </button>
      </div>

      <!-- Cada botón asigna un valor a "status"; la clase "active" resalta el filtro elegido -->
      <div class="filter-row">
        <button class="filter-pill" :class="{active: status === ''}" @click="status = ''">
          Todas
        </button>

        <button class="filter-pill" :class="{active: status === 'Activo'}" @click="status = 'Activo'">
          Activas
        </button>

        <button class="filter-pill" :class="{active: status === 'Inactivo'}" @click="status = 'Inactivo'">
          Inactivas
        </button>
      </div>
    </div>

    <!-- CONTADOR: muestra cuántas especialidades cumplen el filtro/búsqueda actual -->
    <p class="count-label">
      <i class="fas fa-stethoscope"></i>
      <!-- Singular/plural simple: "especialidad" vs "especialidades" -->
      {{ filteredSpecialties.length }} especialidad{{ filteredSpecialties.length === 1 ? '' : 'es' }}
    </p>

    <!-- GRID: una tarjeta por cada especialidad filtrada -->
    <div class="specs-grid">

      <div
        v-for="item in filteredSpecialties"
        :key="item.id"
        class="card-body-custom"
        :style="{ '--accent': avatarColor(item.nombre) }"
      >
        <div class="card-top">
          <div class="card-id">
            <!-- Avatar con iniciales y color generado a partir del nombre (ver método avatarColor) -->
            <span class="avatar">{{ initials(item.nombre) }}</span>
            <span class="card-name">{{ item.nombre }}</span>
          </div>

          <!-- Chip de estado: verde si está "Activo", rojo si está "Inactivo" -->
          <span class="status-chip" :class="item.estado === 'Activo' ? 'active' : 'inactive'">
            <span class="status-dot"></span>{{ item.estado }}
          </span>
        </div>

        <!-- Descripción recortada a 80 caracteres (método limit) -->
        <p class="card-desc">
          {{ limit(item.descripcion, 80) }}
        </p>

        <!-- Acciones de la tarjeta: ver detalle, editar o eliminar -->
        <div class="card-actions">

          <button class="action-btn view" @click="ver(item)">
            <i class="fas fa-eye"></i> Ver
          </button>

          <button class="action-btn edit" @click="editar(item)">
            <i class="fas fa-edit"></i> Editar
          </button>

          <button class="action-btn delete" @click="eliminar(item)">
            <i class="fas fa-trash"></i> Eliminar
          </button>

        </div>
      </div>

    </div>

    <!-- EMPTY: se muestra solo cuando no hay resultados que coincidan con el filtro/búsqueda -->
    <div v-if="filteredSpecialties.length === 0" class="empty-state">
      <div class="empty-icon"><i class="fas fa-folder-open"></i></div>
      <p class="empty-title">No se encontraron especialidades</p>
      <p class="empty-sub">Ajusta la búsqueda o los filtros, o crea una nueva especialidad.</p>
    </div>

    <!-- MODAL VER: muestra el detalle de la especialidad seleccionada (solo lectura) -->
    <!-- @click.self cierra el modal si se hace clic en el fondo, fuera de la tarjeta -->
    <div v-if="modales.ver" class="modal-overlay" @click.self="modales.ver = false">
      <div class="modal-box">

        <div class="modal-header">
          <h2><i class="fas fa-stethoscope"></i> Detalle de la especialidad</h2>

          <button class="btn-close" @click="modales.ver = false" aria-label="Cerrar">
            <i class="fas fa-times"></i>
          </button>
        </div>

        <div class="detail-item">
          <label>Nombre</label>
          <p>{{ selected.nombre }}</p>
        </div>

        <div class="detail-item">
          <label>Descripción</label>
          <p>{{ selected.descripcion }}</p>
        </div>

        <div class="detail-item">
          <label>Estado</label>

          <div>
            <span
              class="badge"
              :class="selected.estado === 'Activo' ? 'active' : 'inactive'"
            >
              {{ selected.estado }}
            </span>
          </div>
        </div>
      </div>
    </div>

    <!-- MODAL NUEVA: formulario para crear una especialidad (usa "form" y el método guardar) -->
    <div v-if="modales.nueva" class="modal-overlay" @click.self="modales.nueva = false">
      <div class="modal-box">

        <div class="modal-header">
          <h2><i class="fas fa-plus-circle"></i> Nueva especialidad</h2>

          <button class="btn-close" @click="modales.nueva = false" aria-label="Cerrar">
            <i class="fas fa-times"></i>
          </button>
        </div>

        <label>Nombre de la especialidad</label>
        <input
          v-model="form.nombre"
          type="text"
          placeholder="Ej. Cardiología"
        />

        <label>Doctor responsable</label>
        <input
          v-model="form.doctor"
          type="text"
          placeholder="Nombre del doctor responsable"
        />

        <label>Descripción</label>
        <textarea
          v-model="form.descripcion"
          placeholder="Describe brevemente esta especialidad"
        ></textarea>

        <label>Estado</label>
        <select v-model="form.estado">
          <option value="Activo">Activo</option>
          <option value="Inactivo">Inactivo</option>
        </select>

        <div class="modal-actions">
          <button class="btn-cancel" @click="modales.nueva = false">
            Cancelar
          </button>

          <button class="btn-save" @click="guardar">
            <i class="fas fa-save"></i> Guardar
          </button>
        </div>
      </div>
    </div>

    <!-- MODAL EDITAR: mismo formulario que "Nueva", pero precargado con los datos del item (método editar) -->
    <div v-if="modales.editar" class="modal-overlay" @click.self="modales.editar = false">
      <div class="modal-box">

        <div class="modal-header">
          <h2><i class="fas fa-edit"></i> Editar especialidad</h2>

          <button class="btn-close" @click="modales.editar = false" aria-label="Cerrar">
            <i class="fas fa-times"></i>
          </button>
        </div>

        <label>Nombre de la especialidad</label>
        <input v-model="form.nombre" type="text" />

        <label>Doctor responsable</label>
        <input v-model="form.doctor" type="text" />

        <label>Descripción</label>
        <textarea v-model="form.descripcion"></textarea>

        <label>Estado</label>
        <select v-model="form.estado">
          <option value="Activo">Activo</option>
          <option value="Inactivo">Inactivo</option>
        </select>

        <div class="modal-actions">
          <button class="btn-cancel" @click="modales.editar = false">
            Cancelar
          </button>

          <button class="btn-save" @click="actualizar()">
            <i class="fas fa-save"></i> Actualizar
          </button>
        </div>
      </div>
    </div>

  </div>
</template>

<script>
// Componente: listado y administración (CRUD) de Especialidades Médicas.
// Permite buscar, filtrar por estado, ver el detalle, crear, editar y eliminar especialidades.
import axios from "axios";

export default {
  data() {
    return {
      specialties: [],   // Lista completa de especialidades traída desde la API
      search: "",        // Texto escrito en el buscador (filtra por nombre)
      status: "",         // Filtro de estado activo: "" (todas), "Activo" o "Inactivo"
      selected: {},        // Especialidad actualmente mostrada en el modal "Ver"

      // Modelo del formulario compartido por los modales "Nueva" y "Editar"
      form: {
        id: null,
        nombre: "",
        doctor: "",
        descripcion: "",
        estado: "Activo",
      },

      // Banderas que controlan la visibilidad de cada modal
      modales: {
        ver: false,
        nueva: false,
        editar: false,
      },
    };
  },

  computed: {
    // Aplica el texto de búsqueda y el filtro de estado sobre "specialties"
    filteredSpecialties() {
      return this.specialties.filter((s) => {

        // Coincide si el nombre contiene el texto buscado (sin importar mayúsculas/minúsculas)
        const matchSearch =
          s.nombre.toLowerCase().includes(this.search.toLowerCase());

        // Si no hay filtro de estado seleccionado, todas las especialidades cuentan como coincidencia
        const matchStatus =
          this.status === ""
            ? true
            : s.estado === this.status;

        return matchSearch && matchStatus;
      });
    },
  },

  mounted() {
    // Carga inicial de especialidades al montar el componente
    this.cargar();
  },

  methods: {

    // Obtiene la lista de especialidades desde la API y la guarda en "specialties"
    cargar() {
      axios.get("/api/specialties").then((res) => {
        console.log(res.data); // <-- Agrega esto
        this.specialties = res.data;
      });
    },

    // Abre el modal indicado por nombre: "ver", "nueva" o "editar"
    abrirModal(name) {
      this.modales[name] = true;
    },

    // Guarda el item elegido en "selected" y abre el modal de detalle (solo lectura)
    ver(item) {
      this.selected = item;
      this.modales.ver = true;
    },

    // Copia los datos del item al formulario y abre el modal de edición
    editar(item) {
      this.form = { ...item };
      this.modales.editar = true;
    },

    // Envía el formulario como una especialidad nueva, recarga la lista y cierra el modal
    guardar() {
      axios.post("/specialties", this.form).then(() => {
        this.cargar();
        this.modales.nueva = false;
      });
    },

    // Envía el formulario para actualizar la especialidad en edición, recarga la lista y cierra el modal
    actualizar() {
      axios.put(`/specialties/${this.form.id}`, this.form).then(() => {
        this.cargar();
        this.modales.editar = false;
      });
    },

    // Pide confirmación antes de borrar y, si se confirma, elimina la especialidad y recarga la lista
    eliminar(item) {
      if (confirm("¿Eliminar especialidad?")) {
        axios.delete(`/specialties/${item.id}`).then(() => {
          this.cargar();
        });
      }
    },

    // Recorta un texto a "n" caracteres y agrega "..." si quedó más largo que eso
    limit(text, n) {
      if (!text) return "";
      return text.length > n ? text.substring(0, n) + "..." : text;
    },

    // --- Helpers puramente visuales para dar identidad a cada especialidad (avatar + color) ---

    // Devuelve hasta 2 iniciales del nombre, para mostrarlas dentro del avatar de la tarjeta
    initials(name) {
      if (!name) return "";
      const parts = name.trim().split(/\s+/);
      if (parts.length === 1) return parts[0].substring(0, 2).toUpperCase();
      return (parts[0][0] + parts[1][0]).toUpperCase();
    },

    // Genera siempre el mismo color para el mismo nombre (hash simple sobre el texto),
    // usado en el avatar y en la barra de acento superior de cada tarjeta
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
/* Fuentes: Plus Jakarta Sans para títulos/marca, Inter para texto general */
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap');

/* ===== TOKENS =====
   Variables de diseño (color, radios, sombras) centralizadas aquí.
   Se usan con var(--nombre) en el resto del archivo para mantener consistencia. */
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
    /* Fondo: degradados sutiles azul/teal sobre un gris muy claro, para dar profundidad sin saturar */
    background:
        radial-gradient(1100px 560px at 8% -10%, rgba(37,99,235,.07), transparent 60%),
        radial-gradient(900px 500px at 100% 0%, rgba(13,148,136,.05), transparent 55%),
        var(--bg);
}

/* ===== HEADER ===== */
.header{
    display:flex;
    justify-content:space-between;
    align-items:flex-end;
    margin-bottom:28px;
    flex-wrap:wrap;
    gap:18px;
}

.eyebrow{
    margin:0 0 6px;
    font-size:12px;
    font-weight:700;
    letter-spacing:.08em;
    text-transform:uppercase;
    color:var(--primary);
}

.page-title{
    margin:0;
    font-family:'Plus Jakarta Sans',sans-serif;
    font-size:32px;
    font-weight:800;
    letter-spacing:-.02em;
    color:var(--ink);
}

.text-muted{
    color:var(--body-text);
    margin-top:6px;
    font-size:15px;
}

/* ===== BOTÓN PRIMARIO ===== */
.btn-primary{
    display:inline-flex;
    align-items:center;
    gap:8px;
    background:linear-gradient(135deg, var(--primary), var(--primary-dark));
    color:#fff;
    border:none;
    border-radius:12px;
    padding:13px 24px;
    font-weight:600;
    font-size:14.5px;
    cursor:pointer;
    box-shadow:0 10px 22px rgba(37,99,235,.28);
    transition:transform .2s ease, box-shadow .2s ease;
}

.btn-primary:hover{
    background:linear-gradient(135deg, var(--primary-dark), var(--primary-dark));
    transform:translateY(-2px);
    box-shadow:0 14px 28px rgba(37,99,235,.34);
}

.btn-primary:active{ transform:translateY(0); }

/* ===== TOOLBAR (BUSCADOR + FILTROS) ===== */
.toolbar-card{
    background:var(--surface);
    border-radius:var(--radius-lg);
    padding:18px 22px;
    box-shadow:var(--shadow-md);
    margin-bottom:18px;
    display:flex;
    flex-direction:column;
    gap:16px;
}

.search-bar{
    display:flex;
    align-items:center;
    gap:10px;
    padding-bottom:14px;
    border-bottom:1px solid var(--border);
}

.search-bar i.fa-search{ color:var(--muted); }

.search-input{
    flex:1;
    border:none;
    outline:none;
    font-size:15px;
    font-family:'Inter',sans-serif;
    color:var(--ink);
    background:transparent;
}

.search-input::placeholder{ color:var(--muted); }

.btn-clear{
    width:28px;
    height:28px;
    border-radius:50%;
    border:none;
    background:var(--bg);
    color:var(--body-text);
    display:flex;
    align-items:center;
    justify-content:center;
    cursor:pointer;
    transition:.2s;
}

.btn-clear:hover{ background:var(--danger-bg); color:var(--danger-text); }

/* FILTROS: pastillas de estado (Todas / Activas / Inactivas) */
.filter-row{
    display:flex;
    gap:10px;
    flex-wrap:wrap;
}

.filter-pill{
    border:1.5px solid var(--border);
    background:var(--surface);
    color:var(--body-text);
    padding:9px 18px;
    border-radius:30px;
    font-weight:600;
    font-size:13.5px;
    cursor:pointer;
    transition:all .2s ease;
}

.filter-pill:hover{ border-color:var(--primary); color:var(--primary); }

/* La clase "active" la agrega Vue según el filtro seleccionado (ver template) */
.filter-pill.active{
    background:var(--primary);
    border-color:var(--primary);
    color:#fff;
    box-shadow:0 6px 14px rgba(37,99,235,.28);
}

/* CONTADOR */
.count-label{
    display:flex;
    align-items:center;
    gap:8px;
    margin:4px 0 20px;
    color:var(--body-text);
    font-weight:600;
    font-size:14px;
}

.count-label i{ color:var(--primary); font-size:12px; }

/* ===== GRID ===== */
.specs-grid{
    display:grid;
    grid-template-columns:repeat(auto-fill,minmax(320px,1fr));
    gap:20px;
}

/* TARJETA */
.card-body-custom{
    position:relative;
    background:var(--surface);
    border:1px solid var(--border);
    border-radius:var(--radius-lg);
    padding:22px 22px 18px;
    box-shadow:var(--shadow-sm);
    overflow:hidden;
    transition:transform .25s ease, box-shadow .25s ease;
}

/* Barra de color en la parte superior de la tarjeta.
   "--accent" llega como variable inline desde el template (ver avatarColor) */
.card-body-custom::before{
    content:'';
    position:absolute;
    top:0; left:0; right:0;
    height:3px;
    background:var(--accent);
}

.card-body-custom:hover{
    transform:translateY(-4px);
    box-shadow:var(--shadow-lg);
}

.card-top{
    display:flex;
    justify-content:space-between;
    align-items:flex-start;
    gap:10px;
    margin-bottom:14px;
}

.card-id{
    display:flex;
    align-items:center;
    gap:12px;
    min-width:0;
}

/* Avatar circular con las iniciales; usa el mismo color "--accent" que la barra superior */
.avatar{
    flex-shrink:0;
    width:40px;
    height:40px;
    border-radius:12px;
    background:var(--accent);
    color:#fff;
    display:flex;
    align-items:center;
    justify-content:center;
    font-family:'Plus Jakarta Sans',sans-serif;
    font-weight:700;
    font-size:14px;
}

.card-name{
    font-family:'Plus Jakarta Sans',sans-serif;
    font-size:17px;
    font-weight:700;
    color:var(--ink);
    overflow:hidden;
    text-overflow:ellipsis;
    white-space:nowrap;
}

/* Chip de estado: el color final (verde/rojo) lo decide la clase .active/.inactive del template */
.status-chip{
    display:inline-flex;
    align-items:center;
    gap:6px;
    padding:5px 12px;
    border-radius:30px;
    font-size:12px;
    font-weight:600;
    white-space:nowrap;
}

.status-chip.active{ background:var(--success-bg); color:var(--success-text); }
.status-chip.inactive{ background:var(--danger-bg); color:var(--danger-text); }
.status-dot{ width:6px; height:6px; border-radius:50%; background:currentColor; }

.card-desc{
    color:var(--body-text);
    line-height:1.6;
    font-size:14px;
    margin:0 0 18px;
}

/* BOTONES DE ACCIÓN: cada uno cambia de color al pasar el mouse según su función */
.card-actions{
    display:flex;
    gap:6px;
    padding-top:14px;
    border-top:1px solid var(--border);
}

.action-btn{
    flex:1;
    display:flex;
    align-items:center;
    justify-content:center;
    gap:6px;
    border:none;
    background:transparent;
    border-radius:9px;
    padding:9px;
    font-size:13px;
    font-weight:600;
    color:var(--body-text);
    cursor:pointer;
    transition:.2s;
}

.action-btn.view:hover{ background:var(--primary-soft); color:var(--primary); }
.action-btn.edit:hover{ background:#fef3c7; color:#b45309; }
.action-btn.delete:hover{ background:var(--danger-bg); color:var(--danger-text); }

/* EMPTY: estado vacío cuando el filtro/búsqueda no encuentra resultados */
.empty-state{
    background:var(--surface);
    padding:60px 30px;
    text-align:center;
    border-radius:var(--radius-lg);
    box-shadow:var(--shadow-sm);
    margin-top:10px;
}

.empty-icon{
    width:64px;
    height:64px;
    border-radius:50%;
    background:var(--primary-soft);
    color:var(--primary);
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:24px;
    margin:0 auto 18px;
}

.empty-title{
    font-family:'Plus Jakarta Sans',sans-serif;
    font-weight:700;
    font-size:17px;
    color:var(--ink);
    margin:0 0 6px;
}

.empty-sub{ color:var(--muted); font-size:14px; margin:0; }

/* ===== MODAL =====
   Estilos compartidos por los 3 modales: Ver, Nueva y Editar */
.modal-overlay{
    position:fixed;
    inset:0;
    background:rgba(15,23,42,.55);
    backdrop-filter:blur(4px);
    display:flex;
    justify-content:center;
    align-items:center;
    z-index:9999;
    padding:20px;
}

.modal-box{
    background:var(--surface);
    width:480px;
    max-width:100%;
    border-radius:20px;
    padding:26px;
    box-shadow:var(--shadow-lg);
    animation:modal-in .25s cubic-bezier(.16,1,.3,1);
}

.modal-header{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:22px;
    padding-bottom:16px;
    border-bottom:1px solid var(--border);
}

.modal-header h2{
    display:flex;
    align-items:center;
    gap:10px;
    margin:0;
    font-family:'Plus Jakarta Sans',sans-serif;
    font-size:19px;
    font-weight:700;
    color:var(--ink);
}

.modal-header h2 i{ color:var(--primary); font-size:16px; }

.btn-close{
    width:32px;
    height:32px;
    border-radius:50%;
    border:none;
    background:var(--bg);
    color:var(--body-text);
    display:flex;
    align-items:center;
    justify-content:center;
    cursor:pointer;
    transition:.2s;
}

.btn-close:hover{ background:var(--primary-soft); color:var(--primary); }

.modal-box label{
    display:block;
    font-size:12px;
    font-weight:600;
    letter-spacing:.04em;
    text-transform:uppercase;
    color:var(--body-text);
    margin-bottom:6px;
}

.modal-box input,
.modal-box textarea,
.modal-box select{
    width:100%;
    padding:12px 14px;
    margin-bottom:16px;
    border:1.5px solid var(--border);
    border-radius:10px;
    outline:none;
    font-family:'Inter',sans-serif;
    font-size:14.5px;
    color:var(--ink);
    background:var(--surface);
    transition:.2s;
}

.modal-box input:focus,
.modal-box textarea:focus,
.modal-box select:focus{
    border-color:var(--primary);
    box-shadow:0 0 0 3px rgba(37,99,235,.12);
}

.modal-box textarea{ resize:vertical; min-height:110px; }

.modal-actions{
    display:flex;
    justify-content:flex-end;
    gap:10px;
    margin-top:6px;
}

.btn-save,
.btn-cancel{
    display:inline-flex;
    align-items:center;
    gap:8px;
    border:none;
    border-radius:10px;
    padding:12px 20px;
    font-weight:600;
    font-size:14px;
    cursor:pointer;
    transition:.2s;
}

.btn-save{ background:var(--primary); color:#fff; }
.btn-save:hover{ background:var(--primary-dark); }

.btn-cancel{ background:var(--bg); color:var(--body-text); }
.btn-cancel:hover{ background:#e2e8f0; }

/* DETALLE (MODAL VER) */
.detail-item{ margin-bottom:18px; }

.detail-item label{
    display:block;
    font-size:11px;
    font-weight:700;
    letter-spacing:.06em;
    text-transform:uppercase;
    color:var(--muted);
    margin-bottom:6px;
}

.detail-item p{
    margin:0;
    font-size:16px;
    color:var(--ink);
    line-height:1.5;
}

.badge{
    display:inline-flex;
    align-items:center;
    padding:6px 14px;
    border-radius:20px;
    font-size:13px;
    font-weight:600;
}

.badge.active{ background:var(--success-bg); color:var(--success-text); }
.badge.inactive{ background:var(--danger-bg); color:var(--danger-text); }

/* ===== ANIMACIÓN de entrada del modal (fade + escala leve) ===== */
@keyframes modal-in{
    from{ opacity:0; transform:scale(.94) translateY(8px); }
    to{ opacity:1; transform:scale(1) translateY(0); }
}

/* Respeta a quienes prefieren menos movimiento en pantalla (accesibilidad) */
@media (prefers-reduced-motion: reduce){
    .card-body-custom, .btn-primary, .modal-box{ animation:none !important; transition:none !important; }
}

/* Resalta con un anillo azul el elemento enfocado con teclado (accesibilidad) */
.specialties-page :focus-visible{
    outline:2px solid var(--primary);
    outline-offset:2px;
}

/* ===== RESPONSIVE: ajustes para pantallas angostas (celulares) ===== */
@media(max-width:768px){

    .specialties-page{ padding:20px; }

    .header{
        flex-direction:column;
        align-items:flex-start;
    }

    .btn-primary{ width:100%; justify-content:center; }

    .specs-grid{ grid-template-columns:1fr; }

    .filter-row{ width:100%; }
    .filter-pill{ flex:1; text-align:center; }

    .card-actions{ flex-direction:column; }

    .modal-box{ padding:20px; }
}
</style>