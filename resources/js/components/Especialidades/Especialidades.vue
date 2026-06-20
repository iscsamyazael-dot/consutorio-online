<template>
  <div class="specialties-page">

    <!-- ===== TOAST DE CONFIRMACIÓN ===== -->
    <!-- Pequeño aviso flotante que aparece abajo a la derecha cuando se 
         actualiza una especialidad. Usa <transition> para animar su 
         entrada/salida con las animaciones "toast-in" / "toast-out" del CSS -->
    <transition name="toast">
      <div v-if="toast.visible" class="toast-success">
        <span class="toast-icon"><i class="fas fa-check-circle"></i></span>
        <span>{{ toast.mensaje }}</span>
      </div>
    </transition>

    <!-- HEADER -->
    <!-- Encabezado de la página: título, subtítulo descriptivo y el botón 
         para abrir el modal de creación de una nueva especialidad -->
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
    <!-- Barra de herramientas con el campo de búsqueda y los filtros 
         por estado (Todas / Activas / Inactivas) -->
    <div class="toolbar-card">
      <!-- Buscador: filtra en vivo por nombre gracias a v-model="search" -->
      <div class="search-bar">
        <i class="fas fa-search"></i>
        <input v-model="search" type="text" class="search-input" placeholder="Buscar especialidad..." />
        <!-- Botón "x" que solo aparece si hay texto escrito, limpia la búsqueda -->
        <button v-if="search" class="btn-clear" @click="search = ''" aria-label="Limpiar búsqueda">
          <i class="fas fa-times"></i>
        </button>
      </div>
      <!-- Píldoras de filtro: cambian la variable "status" usada en el computed filteredSpecialties -->
      <div class="filter-row">
        <button class="filter-pill" :class="{active: status === ''}" @click="status = ''">Todas</button>
        <button class="filter-pill" :class="{active: status === 'Activo'}" @click="status = 'Activo'">Activas</button>
        <button class="filter-pill" :class="{active: status === 'Inactivo'}" @click="status = 'Inactivo'">Inactivas</button>
      </div>
    </div>

    <!-- CONTADOR -->
    <!-- Muestra cuántas especialidades coinciden con el filtro/búsqueda actual,
         con singular/plural dinámico ("especialidad" vs "especialidades") -->
    <p class="count-label">
      <i class="fas fa-stethoscope"></i>
      {{ filteredSpecialties.length }} especialidad{{ filteredSpecialties.length === 1 ? '' : 'es' }}
    </p>

    <!-- GRID -->
    <!-- Cuadrícula de tarjetas, una por cada especialidad filtrada -->
    <div class="specs-grid">
      <div
        v-for="item in filteredSpecialties"
        :key="item.id"
        class="card-body-custom"
        :style="{ '--accent': avatarColor(item.nombre) }"
      >
        <!-- Parte superior de la tarjeta: avatar con iniciales + nombre + chip de estado -->
        <div class="card-top">
          <div class="card-id">
            <span class="avatar">{{ initials(item.nombre) }}</span>
            <span class="card-name">{{ item.nombre }}</span>
          </div>
          <span class="status-chip" :class="item.estado === 'Activo' ? 'active' : 'inactive'">
            <span class="status-dot"></span>{{ item.estado }}
          </span>
        </div>
        <!-- Descripción recortada a 80 caracteres como vista previa -->
        <p class="card-desc">{{ limit(item.descripcion, 80) }}</p>
        <!-- Botones de acción: ver detalle, editar y eliminar la especialidad -->
        <div class="card-actions">
          <button class="action-btn view" @click="ver(item)"><i class="fas fa-eye"></i> Ver</button>
          <button class="action-btn edit" @click="editar(item)"><i class="fas fa-edit"></i> Editar</button>
          <button class="action-btn delete" @click="eliminar(item)"><i class="fas fa-trash"></i> Eliminar</button>
        </div>
      </div>
    </div>

    <!-- EMPTY -->
    <!-- Estado vacío: se muestra cuando ninguna especialidad coincide con 
         la búsqueda/filtro aplicado -->
    <div v-if="filteredSpecialties.length === 0" class="empty-state">
      <div class="empty-icon"><i class="fas fa-folder-open"></i></div>
      <p class="empty-title">No se encontraron especialidades</p>
      <p class="empty-sub">Ajusta la búsqueda o los filtros, o crea una nueva especialidad.</p>
    </div>

    <!-- ===== MODAL VER ===== -->
    <!-- Modal de solo lectura: muestra el detalle completo de la especialidad
         seleccionada (this.selected). Se cierra haciendo click fuera del 
         cuadro (@click.self) o con el botón de la "x" -->
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

        <!-- Doctor -->
        <!-- Si no hay doctor asignado, muestra un guion como valor por defecto -->
        <div class="detail-item">
          <label><i class="fas fa-user-md detail-icon"></i> Doctor responsable</label>
          <p>{{ selected.doctor || '—' }}</p>
        </div>

        <!-- Descripción -->
        <div class="detail-item">
          <label><i class="fas fa-align-left detail-icon"></i> Descripción</label>
          <p>{{ selected.descripcion }}</p>
        </div>

        <!-- Estado -->
        <!-- Badge de color verde/rojo según si la especialidad está activa o inactiva -->
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
    <!-- Modal con el formulario para crear una especialidad nueva.
         Los campos están enlazados a "form" mediante v-model y se guardan 
         llamando al método guardar() -->
    <div v-if="modales.nueva" class="modal-overlay" @click.self="modales.nueva = false">
      <div class="modal-box">

        <div class="modal-header">
          <h2><i class="fas fa-plus-circle"></i> Nueva especialidad</h2>
          <button class="btn-close" @click="modales.nueva = false" aria-label="Cerrar">
            <i class="fas fa-times"></i>
          </button>
        </div>

        <label><i class="fas fa-tag field-icon"></i> Nombre de la especialidad</label>
        <input v-model="form.nombre" type="text" placeholder="Ej. Cardiología" />

        <label><i class="fas fa-user-md field-icon"></i> Doctor responsable</label>
        <input v-model="form.doctor" type="text" placeholder="Nombre del doctor responsable" />

        <label><i class="fas fa-align-left field-icon"></i> Descripción</label>
        <textarea v-model="form.descripcion" placeholder="Describe brevemente esta especialidad"></textarea>

        <label><i class="fas fa-toggle-on field-icon"></i> Estado</label>
        <select v-model="form.estado">
          <option value="Activo">Activo</option>
          <option value="Inactivo">Inactivo</option>
        </select>

        <!-- Botones para cancelar (cierra el modal sin guardar) o confirmar el guardado -->
        <div class="modal-actions">
          <button class="btn-cancel" @click="modales.nueva = false">Cancelar</button>
          <button class="btn-save" @click="guardar">
            <i class="fas fa-save"></i> Guardar
          </button>
        </div>

      </div>
    </div>

    <!-- ===== MODAL EDITAR ===== -->
    <!-- Modal con el mismo formulario que "Nueva", pero precargado con los 
         datos de la especialidad seleccionada (ver método editar()).
         Al guardar, llama a actualizar() en vez de guardar() -->
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

  </div>
</template>

<script>
import axios from "axios";

export default {
  data() {
    return {
      specialties: [],      // Lista completa de especialidades traída del backend
      search: "",            // Texto escrito en el buscador (filtra por nombre)
      status: "",            // Filtro de estado activo: "", "Activo" o "Inactivo"
      selected: {},           // Especialidad actualmente mostrada en el modal "Ver"
      form: { id: null, nombre: "", doctor: "", descripcion: "", estado: "Activo" }, // Datos del formulario (se reutiliza para crear y editar)
      modales: { ver: false, nueva: false, editar: false }, // Controla qué modal está visible

      // ── Toast 
      toast: { visible: false, mensaje: "" }, // Estado del aviso flotante (visible + texto)
      _toastTimer: null,                        // Referencia al setTimeout para poder cancelarlo si se dispara otro toast antes de que termine
    };
  },

  computed: {
    // Devuelve solo las especialidades que coinciden con el texto buscado
    // Y con el filtro de estado seleccionado (combina ambas condiciones con &&)
    filteredSpecialties() {
      return this.specialties.filter((s) => {
        const matchSearch = s.nombre.toLowerCase().includes(this.search.toLowerCase());
        const matchStatus = this.status === "" ? true : s.estado === this.status; // "" significa "Todas", no filtra
        return matchSearch && matchStatus;
      });
    },
  },

  // Al montar el componente, carga las especialidades desde la API
  mounted() {
    this.cargar();
  },

  methods: {
    // Pide al backend la lista de especialidades y la guarda en "specialties"
    cargar() {
      axios.get("/api/specialties").then((res) => {
        this.specialties = res.data;
      });
    },

    // Abre el modal indicado por nombre (ej. abrirModal('nueva') abre modales.nueva)
    abrirModal(name) { this.modales[name] = true; },

    // Guarda la especialidad clickeada en "selected" y abre el modal de detalle
    ver(item) { this.selected = item; this.modales.ver = true; },

    // Copia los datos del item al formulario (para no editar el original directamente)
    // y abre el modal de edición
    editar(item) { this.form = { ...item }; this.modales.editar = true; },

    // Envía el formulario como una especialidad nueva (POST), recarga la lista
    // y cierra el modal de creación
    guardar() {
      axios.post("/specialties", this.form).then(() => {
        this.cargar();
        this.modales.nueva = false;
      });
    },

    // Envía los cambios del formulario para actualizar una especialidad existente (PUT),
    // recarga la lista, cierra el modal de edición y muestra el toast de confirmación
    actualizar() {
      axios.put(`/specialties/${this.form.id}`, this.form).then(() => {
        this.cargar();
        this.modales.editar = false;
        // ── Disparar toast ──
        this.mostrarToast("✓ Actualización guardada");
      });
    },

    // Pide confirmación con un diálogo nativo y, si se acepta, elimina (DELETE)
    // la especialidad y recarga la lista
    eliminar(item) {
      if (confirm("¿Eliminar especialidad?")) {
        axios.delete(`/specialties/${item.id}`).then(() => this.cargar());
      }
    },

    // ── Muestra el toast y lo oculta tras 2.5 s ─────────────
    // Cancela cualquier temporizador previo (por si se llama varias veces rápido)
    // para que el toast no se cierre antes de tiempo
    mostrarToast(mensaje) {
      clearTimeout(this._toastTimer);
      this.toast.mensaje = mensaje;
      this.toast.visible = true;
      this._toastTimer = setTimeout(() => { this.toast.visible = false; }, 2500);
    },

    // Recorta un texto a "n" caracteres y agrega "..." si fue truncado
    // (se usa para la descripción mostrada en las tarjetas)
    limit(text, n) {
      if (!text) return "";
      return text.length > n ? text.substring(0, n) + "..." : text;
    },

    // Genera las iniciales a mostrar en el avatar a partir del nombre
    // Ej: "Cardiología" -> "CA"   |   "Medicina General" -> "MG"
    initials(name) {
      if (!name) return "";
      const parts = name.trim().split(/\s+/);
      if (parts.length === 1) return parts[0].substring(0, 2).toUpperCase();
      return (parts[0][0] + parts[1][0]).toUpperCase();
    },

    // Asigna un color consistente a cada especialidad según su nombre
    // (mismo nombre = mismo color siempre), usado como acento del avatar y la tarjeta
    avatarColor(name) {
      const palette = ["#2563eb", "#0d9488", "#7c3aed", "#0891b2", "#4f46e5", "#0e7490"];
      if (!name) return palette[0];
      let hash = 0;
      // Genera un hash numérico simple a partir de los caracteres del nombre
      for (let i = 0; i < name.length; i++) {
        hash = name.charCodeAt(i) + ((hash << 5) - hash);
      }
      // Usa el hash para elegir siempre el mismo color de la paleta para ese nombre
      return palette[Math.abs(hash) % palette.length];
    },
  },
};
</script>

<style scoped>
/* Importa las tipografías usadas en toda la página: Inter (texto) y Plus Jakarta Sans (títulos) */
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap');

.specialties-page{
    /* ===== VARIABLES CSS (design tokens) ===== */
    /* Centralizan colores, sombras y radios para reutilizarlos en todo el componente */
    --primary:#2563eb;       /* Color principal (azul) */
    --primary-dark:#1d4ed8;  /* Azul más oscuro, usado en hover */
    --primary-soft:#eff6ff;  /* Azul muy claro, usado como fondo suave */
    --ink:#0f172a;           /* Color de texto principal (casi negro) */
    --body-text:#475569;     /* Color de texto secundario/gris */
    --muted:#94a3b8;         /* Color de texto apagado (placeholders, iconos) */
    --surface:#ffffff;       /* Color de fondo de tarjetas/modales (blanco) */
    --bg:#f1f5f9;            /* Color de fondo general de la página */
    --border:#e2e8f0;        /* Color de bordes */
    --success-bg:#dcfce7;    /* Fondo verde claro (estado Activo) */
    --success-text:#15803d;  /* Texto verde (estado Activo) */
    --danger-bg:#fee2e2;     /* Fondo rojo claro (estado Inactivo / eliminar) */
    --danger-text:#b91c1c;   /* Texto rojo (estado Inactivo / eliminar) */
    --radius-lg:18px;        /* Radio de borde grande, usado en tarjetas/modales */
    --shadow-sm:0 1px 2px rgba(15,23,42,.04), 0 1px 3px rgba(15,23,42,.06); /* Sombra sutil */
    --shadow-md:0 8px 24px rgba(15,23,42,.08); /* Sombra media (toolbar) */
    --shadow-lg:0 20px 45px rgba(15,23,42,.16); /* Sombra fuerte (modales, hover de tarjeta) */
    font-family:'Inter',-apple-system,sans-serif;
    padding:36px;
    min-height:100vh;
    /* Fondo con degradados radiales sutiles superpuestos sobre el color base */
    background:
        radial-gradient(1100px 560px at 8% -10%, rgba(37,99,235,.07), transparent 60%),
        radial-gradient(900px 500px at 100% 0%, rgba(13,148,136,.05), transparent 55%),
        var(--bg);
}

/* ===== TOAST ===== */
/* Caja flotante fija en la esquina inferior derecha, con fondo verde de éxito */
.toast-success{
    position:fixed;
    bottom:32px;
    right:32px;
    z-index:99999;
    display:inline-flex;
    align-items:center;
    gap:10px;
    background:#15803d;
    color:#fff;
    padding:14px 22px;
    border-radius:14px;
    font-weight:600;
    font-size:14.5px;
    box-shadow:0 12px 32px rgba(21,128,61,.35);
    pointer-events:none; /* Evita que el toast bloquee clicks en lo que está debajo */
}

.toast-icon{ font-size:18px; line-height:1; }

/* Animación del toast: entra desde abajo, sale hacia abajo */
/* Estas clases las aplica automáticamente Vue gracias a <transition name="toast"> */
.toast-enter-active{ animation: toast-in .35s cubic-bezier(.16,1,.3,1) forwards; }
.toast-leave-active{ animation: toast-out .3s ease forwards; }

@keyframes toast-in{
    from{ opacity:0; transform:translateY(20px) scale(.95); }
    to  { opacity:1; transform:translateY(0)    scale(1);   }
}
@keyframes toast-out{
    from{ opacity:1; transform:translateY(0); }
    to  { opacity:0; transform:translateY(12px); }
}

/* ===== ICONOS EN LABELS ===== */
/* Iconos dentro de los labels del formulario y del detalle */
.field-icon,
.detail-icon{
    color:var(--primary);
    font-size:11px;
    margin-right:5px;
    opacity:.85;
}

/* ===== HEADER ===== */
/* Encabezado: texto a la izquierda, botón "Nueva especialidad" a la derecha.
   flex-wrap permite que el botón baje debajo del texto en pantallas chicas */
.header{ display:flex; justify-content:space-between; align-items:flex-end; margin-bottom:28px; flex-wrap:wrap; gap:18px; }
.eyebrow{ margin:0 0 6px; font-size:12px; font-weight:700; letter-spacing:.08em; text-transform:uppercase; color:var(--primary); } /* Etiqueta pequeña arriba del título */
.page-title{ margin:0; font-family:'Plus Jakarta Sans',sans-serif; font-size:32px; font-weight:800; letter-spacing:-.02em; color:var(--ink); } /* Título principal de la página */
.text-muted{ color:var(--body-text); margin-top:6px; font-size:15px; } /* Subtítulo descriptivo */

/* ===== BOTÓN PRIMARIO ===== */
/* Botón con degradado azul, sombra y efecto de elevación al pasar el mouse */
.btn-primary{ display:inline-flex; align-items:center; gap:8px; background:linear-gradient(135deg, var(--primary), var(--primary-dark)); color:#fff; border:none; border-radius:12px; padding:13px 24px; font-weight:600; font-size:14.5px; cursor:pointer; box-shadow:0 10px 22px rgba(37,99,235,.28); transition:transform .2s ease, box-shadow .2s ease; }
.btn-primary:hover{ background:linear-gradient(135deg, var(--primary-dark), var(--primary-dark)); transform:translateY(-2px); box-shadow:0 14px 28px rgba(37,99,235,.34); }
.btn-primary:active{ transform:translateY(0); } /* Vuelve a su posición al hacer click */

/* ===== TOOLBAR ===== */
/* Tarjeta blanca que contiene el buscador y los filtros, apilados verticalmente */
.toolbar-card{ background:var(--surface); border-radius:var(--radius-lg); padding:18px 22px; box-shadow:var(--shadow-md); margin-bottom:18px; display:flex; flex-direction:column; gap:16px; }
.search-bar{ display:flex; align-items:center; gap:10px; padding-bottom:14px; border-bottom:1px solid var(--border); } /* Línea separadora bajo el buscador */
.search-bar i.fa-search{ color:var(--muted); }
.search-input{ flex:1; border:none; outline:none; font-size:15px; font-family:'Inter',sans-serif; color:var(--ink); background:transparent; }
.search-input::placeholder{ color:var(--muted); }
.btn-clear{ width:28px; height:28px; border-radius:50%; border:none; background:var(--bg); color:var(--body-text); display:flex; align-items:center; justify-content:center; cursor:pointer; transition:.2s; } /* Botón circular para limpiar la búsqueda */
.btn-clear:hover{ background:var(--danger-bg); color:var(--danger-text); }
.filter-row{ display:flex; gap:10px; flex-wrap:wrap; } /* Fila de píldoras de filtro */
.filter-pill{ border:1.5px solid var(--border); background:var(--surface); color:var(--body-text); padding:9px 18px; border-radius:30px; font-weight:600; font-size:13.5px; cursor:pointer; transition:all .2s ease; }
.filter-pill:hover{ border-color:var(--primary); color:var(--primary); }
.filter-pill.active{ background:var(--primary); border-color:var(--primary); color:#fff; box-shadow:0 6px 14px rgba(37,99,235,.28); } /* Estilo del filtro actualmente seleccionado */

/* CONTADOR */
.count-label{ display:flex; align-items:center; gap:8px; margin:4px 0 20px; color:var(--body-text); font-weight:600; font-size:14px; }
.count-label i{ color:var(--primary); font-size:12px; }

/* ===== GRID ===== */
/* Cuadrícula responsiva: tantas columnas como entren con un mínimo de 320px cada una */
.specs-grid{ display:grid; grid-template-columns:repeat(auto-fill,minmax(320px,1fr)); gap:20px; }

/* TARJETA */
/* Tarjeta de cada especialidad. La variable --accent (definida inline desde el template)
   controla el color de la franja superior y del avatar, distinto por especialidad */
.card-body-custom{ position:relative; background:var(--surface); border:1px solid var(--border); border-radius:var(--radius-lg); padding:22px 22px 18px; box-shadow:var(--shadow-sm); overflow:hidden; transition:transform .25s ease, box-shadow .25s ease; }
.card-body-custom::before{ content:''; position:absolute; top:0; left:0; right:0; height:3px; background:var(--accent); } /* Franja de color en el borde superior de la tarjeta */
.card-body-custom:hover{ transform:translateY(-4px); box-shadow:var(--shadow-lg); } /* Efecto de elevación al pasar el mouse */
.card-top{ display:flex; justify-content:space-between; align-items:flex-start; gap:10px; margin-bottom:14px; }
.card-id{ display:flex; align-items:center; gap:12px; min-width:0; }
.avatar{ flex-shrink:0; width:40px; height:40px; border-radius:12px; background:var(--accent); color:#fff; display:flex; align-items:center; justify-content:center; font-family:'Plus Jakarta Sans',sans-serif; font-weight:700; font-size:14px; } /* Cuadro con las iniciales de la especialidad */
.card-name{ font-family:'Plus Jakarta Sans',sans-serif; font-size:17px; font-weight:700; color:var(--ink); overflow:hidden; text-overflow:ellipsis; white-space:nowrap; } /* Recorta el nombre con "..." si es muy largo */
.status-chip{ display:inline-flex; align-items:center; gap:6px; padding:5px 12px; border-radius:30px; font-size:12px; font-weight:600; white-space:nowrap; }
.status-chip.active{ background:var(--success-bg); color:var(--success-text); } /* Chip verde para "Activo" */
.status-chip.inactive{ background:var(--danger-bg); color:var(--danger-text); } /* Chip rojo para "Inactivo" */
.status-dot{ width:6px; height:6px; border-radius:50%; background:currentColor; } /* Punto de color dentro del chip de estado */
.card-desc{ color:var(--body-text); line-height:1.6; font-size:14px; margin:0 0 18px; }
.card-actions{ display:flex; gap:6px; padding-top:14px; border-top:1px solid var(--border); } /* Fila de botones Ver/Editar/Eliminar */
.action-btn{ flex:1; display:flex; align-items:center; justify-content:center; gap:6px; border:none; background:transparent; border-radius:9px; padding:9px; font-size:13px; font-weight:600; color:var(--body-text); cursor:pointer; transition:.2s; }
.action-btn.view:hover{ background:var(--primary-soft); color:var(--primary); } /* Hover azul para "Ver" */
.action-btn.edit:hover{ background:#fef3c7; color:#b45309; } /* Hover ámbar para "Editar" */
.action-btn.delete:hover{ background:var(--danger-bg); color:var(--danger-text); } /* Hover rojo para "Eliminar" */

/* EMPTY */
/* Mensaje centrado que se muestra cuando no hay resultados */
.empty-state{ background:var(--surface); padding:60px 30px; text-align:center; border-radius:var(--radius-lg); box-shadow:var(--shadow-sm); margin-top:10px; }
.empty-icon{ width:64px; height:64px; border-radius:50%; background:var(--primary-soft); color:var(--primary); display:flex; align-items:center; justify-content:center; font-size:24px; margin:0 auto 18px; }
.empty-title{ font-family:'Plus Jakarta Sans',sans-serif; font-weight:700; font-size:17px; color:var(--ink); margin:0 0 6px; }
.empty-sub{ color:var(--muted); font-size:14px; margin:0; }

/* ===== MODAL ===== */
/* Fondo oscuro semitransparente con blur que cubre toda la pantalla (overlay) */
.modal-overlay{ position:fixed; inset:0; background:rgba(15,23,42,.55); backdrop-filter:blur(4px); display:flex; justify-content:center; align-items:center; z-index:9999; padding:20px; }
/* Cuadro blanco centrado donde va el contenido del modal (formulario o detalle) */
.modal-box{ background:var(--surface); width:480px; max-width:100%; border-radius:20px; padding:26px; box-shadow:var(--shadow-lg); animation:modal-in .25s cubic-bezier(.16,1,.3,1); }
.modal-header{ display:flex; justify-content:space-between; align-items:center; margin-bottom:22px; padding-bottom:16px; border-bottom:1px solid var(--border); }
.modal-header h2{ display:flex; align-items:center; gap:10px; margin:0; font-family:'Plus Jakarta Sans',sans-serif; font-size:19px; font-weight:700; color:var(--ink); }
.modal-header h2 i{ color:var(--primary); font-size:16px; }
.btn-close{ width:32px; height:32px; border-radius:50%; border:none; background:var(--bg); color:var(--body-text); display:flex; align-items:center; justify-content:center; cursor:pointer; transition:.2s; } /* Botón circular "x" para cerrar el modal */
.btn-close:hover{ background:var(--primary-soft); color:var(--primary); }

/* Estilos compartidos para todos los campos del formulario dentro de los modales */
.modal-box label{ display:block; font-size:12px; font-weight:600; letter-spacing:.04em; text-transform:uppercase; color:var(--body-text); margin-bottom:6px; }
.modal-box input, .modal-box textarea, .modal-box select{ width:100%; padding:12px 14px; margin-bottom:16px; border:1.5px solid var(--border); border-radius:10px; outline:none; font-family:'Inter',sans-serif; font-size:14.5px; color:var(--ink); background:var(--surface); transition:.2s; }
.modal-box input:focus, .modal-box textarea:focus, .modal-box select:focus{ border-color:var(--primary); box-shadow:0 0 0 3px rgba(37,99,235,.12); } /* Resalta el campo activo con un halo azul */
.modal-box textarea{ resize:vertical; min-height:110px; }
.modal-actions{ display:flex; justify-content:flex-end; gap:10px; margin-top:6px; } /* Fila de botones Cancelar/Guardar alineada a la derecha */
.btn-save, .btn-cancel{ display:inline-flex; align-items:center; gap:8px; border:none; border-radius:10px; padding:12px 20px; font-weight:600; font-size:14px; cursor:pointer; transition:.2s; }
.btn-save{ background:var(--primary); color:#fff; }
.btn-save:hover{ background:var(--primary-dark); }
.btn-cancel{ background:var(--bg); color:var(--body-text); }
.btn-cancel:hover{ background:#e2e8f0; }

/* DETALLE (MODAL VER) */
/* Cada bloque "label + valor" dentro del modal de solo lectura */
.detail-item{ margin-bottom:18px; }
.detail-item label{ display:block; font-size:11px; font-weight:700; letter-spacing:.06em; text-transform:uppercase; color:var(--muted); margin-bottom:6px; }
.detail-item p{ margin:0; font-size:16px; color:var(--ink); line-height:1.5; }
.badge{ display:inline-flex; align-items:center; padding:6px 14px; border-radius:20px; font-size:13px; font-weight:600; } /* Etiqueta de estado dentro del detalle */
.badge.active{ background:var(--success-bg); color:var(--success-text); }
.badge.inactive{ background:var(--danger-bg); color:var(--danger-text); }

/* ===== ANIMACIONES ===== */
/* Animación de entrada del modal: aparece con un ligero zoom y desplazamiento */
@keyframes modal-in{ from{ opacity:0; transform:scale(.94) translateY(8px); } to{ opacity:1; transform:scale(1) translateY(0); } }

/* Respeta la preferencia del usuario de reducir animaciones (accesibilidad) */
@media (prefers-reduced-motion: reduce){
    .card-body-custom, .btn-primary, .modal-box{ animation:none !important; transition:none !important; }
}

/* Estilo de foco visible para navegación por teclado (accesibilidad) */
.specialties-page :focus-visible{ outline:2px solid var(--primary); outline-offset:2px; }

/* ===== RESPONSIVE (pantallas pequeñas) ===== */
@media(max-width:768px){
    .specialties-page{ padding:20px; }
    .header{ flex-direction:column; align-items:flex-start; } /* Apila título y botón verticalmente */
    .btn-primary{ width:100%; justify-content:center; } /* Botón a ancho completo */
    .specs-grid{ grid-template-columns:1fr; } /* Una sola columna de tarjetas */
    .filter-row{ width:100%; }
    .filter-pill{ flex:1; text-align:center; } /* Filtros repartidos en todo el ancho */
    .card-actions{ flex-direction:column; } /* Botones de acción apilados */
    .modal-box{ padding:20px; }
    .toast-success{ bottom:16px; right:16px; left:16px; justify-content:center; } /* Toast a ancho completo en móvil */
}
</style>