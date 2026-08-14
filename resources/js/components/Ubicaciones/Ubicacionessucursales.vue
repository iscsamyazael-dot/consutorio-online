<template>
  <div class="ubicaciones-page">
    <!-- Toast de notificaciones -->
    <div class="toast-container">
      <transition-group name="toast">
        <div
          v-for="toast in toasts"
          :key="toast.id"
          class="toast"
          :class="`toast--${toast.type}`"
        >
          <div class="toast-icon">
            <svg v-if="toast.type === 'success'" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4">
              <path d="M20 6 9 17l-5-5" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <svg v-else width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
              <circle cx="12" cy="12" r="9"/><path d="M12 8v5M12 16h.01" stroke-linecap="round"/>
            </svg>
          </div>
          <div class="toast-body">
            <p class="toast-title">{{ toast.title }}</p>
            <p class="toast-message" v-if="toast.message">{{ toast.message }}</p>
          </div>
          <button class="toast-close" @click="cerrarToast(toast.id)">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M18 6 6 18M6 6l12 12" stroke-linecap="round"/>
            </svg>
          </button>
        </div>
      </transition-group>
    </div>

    <!-- Encabezado -->
    <header class="page-header">
      <div class="header-icon">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
          <path d="M3 21h18M5 21V7l7-4 7 4v14M9 9h.01M9 13h.01M9 17h.01M15 9h.01M15 13h.01M15 17h.01" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
      </div>
      <div class="header-text">
        <span class="eyebrow">Sucursales</span>
        <h1>Registrar Ubicación</h1>
        <p class="subtitle">Da de alta una nueva sede y consulta las que ya están registradas.</p>
      </div>
      <div class="header-stats">
        <div class="stat-pill">
          <span class="stat-value">{{ animatedTotal }}</span>
          <span class="stat-label">Total</span>
        </div>
        <div class="stat-pill stat-pill--success">
          <span class="stat-value">{{ animatedActivas }}</span>
          <span class="stat-label">Activas</span>
        </div>
        <div class="stat-pill stat-pill--danger">
          <span class="stat-value">{{ animatedInactivas }}</span>
          <span class="stat-label">Inactivas</span>
        </div>
      </div>
    </header>

    <!-- Formulario de registro -->
    <section class="form-card">
      <div class="card-heading">
        <div class="card-heading-icon">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
            <path d="M12 5v14M5 12h14" stroke-linecap="round" />
          </svg>
        </div>
        <div>
          <h2>Nueva ubicación</h2>
          <p class="card-subtext">Completa los datos para registrar una sede</p>
        </div>
      </div>

      <form @submit.prevent="guardarUbicacion">
        <div class="form-grid">
          <div class="field">
            <label for="nombre">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M3 21h18M5 21V7l7-4 7 4v14" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
              Nombre de la sede
            </label>
            <input
              id="nombre"
              v-model="form.nombre"
              type="text"
              placeholder="Ej. Consultorio Centro"
              maxlength="100"
              required
              :class="{ 'has-error': errores.nombre }"
              @input="limpiarError('nombre')"
            />
            <span class="field-error" v-if="errores.nombre">
              <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                <circle cx="12" cy="12" r="9"/><path d="M12 8v5M12 16h.01" stroke-linecap="round"/>
              </svg>
              {{ errores.nombre }}
            </span>
          </div>

          <div class="field field-full">
            <label for="direccion">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M12 21s-7-6.2-7-11a7 7 0 0 1 14 0c0 4.8-7 11-7 11Z" stroke-linejoin="round"/>
                <circle cx="12" cy="10" r="2.5"/>
              </svg>
              Dirección
            </label>
            <input
              id="direccion"
              v-model="form.direccion"
              type="text"
              placeholder="Calle, número, colonia, ciudad"
              maxlength="255"
              required
              :class="{ 'has-error': errores.direccion }"
              @input="limpiarError('direccion')"
            />
            <span class="field-error" v-if="errores.direccion">
              <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                <circle cx="12" cy="12" r="9"/><path d="M12 8v5M12 16h.01" stroke-linecap="round"/>
              </svg>
              {{ errores.direccion }}
            </span>
          </div>
          <div class="field">
            <label for="telefono">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                  stroke="currentColor" stroke-width="2">
                <path d="M22 16.92v3a2 2 0 0 1-2.18 2
                        19.79 19.79 0 0 1-8.63-3.07
                        19.5 19.5 0 0 1-6-6
                        19.79 19.79 0 0 1-3.07-8.67
                        A2 2 0 0 1 4.11 2h3
                        a2 2 0 0 1 2 1.72
                        c.12.89.33 1.76.63 2.6
                        a2 2 0 0 1-.45 2.11L8.09 9.91
                        a16 16 0 0 0 6 6
                        l1.48-1.2a2 2 0 0 1 2.11-.45
                        c.84.3 1.71.51 2.6.63
                        A2 2 0 0 1 22 16.92z"/>
              </svg>
              Teléfono
            </label>

            <input
                id="telefono"
                v-model="form.telefono"
                type="tel"
                maxlength="20"
                placeholder="999 123 4567"
                :class="{ 'has-error': errores.telefono }"
                @input="limpiarError('telefono')"
            />

            <span class="field-error" v-if="errores.telefono">
                {{ errores.telefono }}
            </span>
          </div>

          <div class="field field-full">
            <label for="imagen">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                <circle cx="8.5" cy="8.5" r="1.5"/>
                <path d="M21 15l-5-5L5 21" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
              Logo de la sede
            </label>
            <input
              id="imagen"
              type="file"
              accept="image/png, image/jpeg, image/webp"
              @change="onImagenSeleccionada"
            />
            <img v-if="imagenPreview" :src="imagenPreview" class="logo-preview" alt="Vista previa del logo" />
          </div>

          <div class="field">
            <label for="horario_apertura">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 3" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
              Horario de apertura
            </label>
            <input
              id="horario_apertura"
              v-model="form.horario_apertura"
              type="time"
              required
              :class="{ 'has-error': errores.horario_apertura }"
              @input="limpiarError('horario_apertura')"
            />
            <span class="field-error" v-if="errores.horario_apertura">
              <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                <circle cx="12" cy="12" r="9"/><path d="M12 8v5M12 16h.01" stroke-linecap="round"/>
              </svg>
              {{ errores.horario_apertura }}
            </span>
          </div>

          <div class="field">
            <label for="horario_cierre">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 3" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
              Horario de cierre
            </label>
            <input
              id="horario_cierre"
              v-model="form.horario_cierre"
              type="time"
              required
              :class="{ 'has-error': errores.horario_cierre }"
              @input="limpiarError('horario_cierre')"
            />
            <span class="field-error" v-if="errores.horario_cierre">
              <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                <circle cx="12" cy="12" r="9"/><path d="M12 8v5M12 16h.01" stroke-linecap="round"/>
              </svg>
              {{ errores.horario_cierre }}
            </span>
          </div>
        </div>

        <div class="form-actions">
          <button type="submit" class="btn-primary" :disabled="guardando">
            <svg v-if="!guardando" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M12 5v14M5 12h14" stroke-linecap="round" />
            </svg>
            <svg v-else class="spin" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M21 12a9 9 0 1 1-9-9" stroke-linecap="round"/>
            </svg>
            {{ guardando ? 'Guardando...' : 'Registrar ubicación' }}
          </button>
        </div>
      </form>
    </section>

    <!-- Tabla de ubicaciones registradas -->
    <section class="table-card">
      <div class="table-header">
        <div class="card-heading">
          <div class="card-heading-icon card-heading-icon--muted">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <rect x="3" y="4" width="18" height="16" rx="2"/>
              <path d="M3 10h18M9 4v16" stroke-linecap="round"/>
            </svg>
          </div>
          <div>
            <h2>Ubicaciones registradas</h2>
            <p class="card-subtext">Gestiona el estado de cada sede</p>
          </div>
        </div>
        <span class="count-badge">{{ conteoBadge }}</span>
      </div>

      <!-- Barra de búsqueda y filtros -->
      <div class="table-toolbar">
        <div class="search-box">
          <svg class="search-icon" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="11" cy="11" r="7"/>
            <path d="m21 21-4.3-4.3" stroke-linecap="round"/>
          </svg>
          <input
            type="text"
            v-model="busqueda"
            placeholder="Buscar por folio o nombre..."
          />
          <button v-if="busqueda" class="search-clear" @click="busqueda = ''" title="Limpiar búsqueda">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4">
              <path d="M18 6 6 18M6 6l12 12" stroke-linecap="round"/>
            </svg>
          </button>
        </div>

        <div class="filter-tabs">
          <button
            type="button"
            class="filter-tab"
            :class="{ 'is-active': filtroEstado === 'todas' }"
            @click="filtroEstado = 'todas'"
          >
            Todas
            <span class="tab-count">{{ ubicaciones.length }}</span>
          </button>
          <button
            type="button"
            class="filter-tab"
            :class="{ 'is-active': filtroEstado === 'activas' }"
            @click="filtroEstado = 'activas'"
          >
            Activas
            <span class="tab-count">{{ activasCount }}</span>
          </button>
          <button
            type="button"
            class="filter-tab"
            :class="{ 'is-active': filtroEstado === 'inactivas' }"
            @click="filtroEstado = 'inactivas'"
          >
            Inactivas
            <span class="tab-count">{{ inactivasCount }}</span>
          </button>
        </div>
      </div>

      <div v-if="cargando" class="skeleton-wrap">
        <div class="skeleton-row" v-for="n in 3" :key="n">
          <div class="skeleton-avatar shimmer"></div>
          <div class="skeleton-lines">
            <div class="skeleton-line shimmer" style="width: 38%"></div>
            <div class="skeleton-line shimmer" style="width: 62%"></div>
          </div>
          <div class="skeleton-pill shimmer"></div>
        </div>
      </div>

      <div v-else-if="ubicaciones.length === 0" class="table-state empty">
        <div class="empty-icon">
          <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
            <path d="M12 21s-7-6.2-7-11a7 7 0 0 1 14 0c0 4.8-7 11-7 11Z" stroke-linejoin="round" />
            <circle cx="12" cy="10" r="2.5" />
          </svg>
        </div>
        <p class="empty-title">Todavía no hay ubicaciones registradas</p>
        <p class="empty-subtitle">Las sedes que registres aparecerán aquí</p>
      </div>

      <div v-else-if="ubicacionesFiltradas.length === 0" class="table-state empty">
        <div class="empty-icon">
          <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
            <circle cx="11" cy="11" r="7"/>
            <path d="m21 21-4.3-4.3" stroke-linecap="round"/>
          </svg>
        </div>
        <p class="empty-title">No se encontraron resultados</p>
        <p class="empty-subtitle">Intenta con otro término de búsqueda o cambia el filtro</p>
        <button class="btn-clear-filters" @click="limpiarFiltros">Limpiar filtros</button>
      </div>

      <table v-else class="ubicaciones-table">
        <thead>
          <tr>
            <th>Sede</th>
            <th>Dirección</th>
            <th>Horario</th>
            <th>Estado</th>
            <th class="th-actions">Acciones</th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="(u, i) in ubicacionesFiltradas"
            :key="u.id"
            class="row-enter"
            :style="{ animationDelay: (i * 45) + 'ms' }"
          >
            <td>
              <div class="cell-nombre-wrap">
                <div
                  class="row-avatar"
                  :class="{ 'row-avatar--inactive': !u.activo }"
                  :style="!u.imagen && u.activo ? { background: avatarColor(u.nombre).bg, color: avatarColor(u.nombre).fg } : {}"
                  :title="u.nombre"
                >
                  <img v-if="u.imagen" :src="logoUrl(u.imagen)" :alt="u.nombre" class="row-avatar-img" />
                  <span v-else>{{ inicial(u.nombre) }}</span>
                </div>
                <div class="cell-nombre-info">
                  <span class="cell-nombre">{{ u.nombre }}</span>
                  <span class="cell-folio" v-if="u.folio_sucursal">{{ u.folio_sucursal }}</span>
                </div>
              </div>
            </td>
            <td class="cell-direccion">
              <span class="cell-direccion-inner">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M12 21s-7-6.2-7-11a7 7 0 0 1 14 0c0 4.8-7 11-7 11Z" stroke-linejoin="round"/>
                  <circle cx="12" cy="10" r="2"/>
                </svg>
                {{ u.direccion }}
              </span>
            </td>
            <td class="cell-horario">
              <span class="cell-horario-inner">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 3" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                {{ formatoHora(u.horario_apertura) }} - {{ formatoHora(u.horario_cierre) }}
              </span>
            </td>
            <td>
              <span class="status-badge" :class="u.activo ? 'status-active' : 'status-inactive'">
                <span class="status-dot"></span>
                {{ u.activo ? 'Activo' : 'Inactivo' }}
              </span>
            </td>
            <td class="cell-actions">
              <button class="btn-icon btn-edit" title="Editar ubicación" @click="abrirEdicion(u)">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M11 4H6a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-5" stroke-linecap="round" stroke-linejoin="round"/>
                  <path d="M18.5 2.5a2.12 2.12 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5Z" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <span>Editar</span>
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </section>

    <!-- Modal de edición -->
    <transition name="modal-fade">
      <div v-if="editando" class="modal-overlay" @click.self="cerrarEdicion">
        <transition name="modal-pop" appear>
          <div class="modal-card" v-if="editando">
            <div class="modal-header">
              <div class="modal-header-left">
                <div class="modal-avatar" :class="{ 'modal-avatar--inactive': !ubicacionEditando.activo }">
                  {{ inicial(ubicacionEditando.nombre) }}
                </div>
                <div>
                  <span class="modal-eyebrow" v-if="ubicacionEditando.folio_sucursal">
                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <rect x="5" y="11" width="14" height="9" rx="2" />
                      <path d="M8 11V7a4 4 0 0 1 8 0v4" stroke-linecap="round"/>
                    </svg>
                    {{ ubicacionEditando.folio_sucursal }}
                  </span>
                  <h3>Editar ubicación</h3>
                </div>
              </div>
              <button class="btn-close" @click="cerrarEdicion" title="Cerrar">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M18 6 6 18M6 6l12 12" stroke-linecap="round"/>
                </svg>
              </button>
            </div>

            <div class="modal-body">
              <div class="field">
                <label>
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M3 21h18M5 21V7l7-4 7 4v14" stroke-linecap="round" stroke-linejoin="round"/>
                  </svg>
                  Nombre de la sede
                </label>
                <input
                  v-model="ubicacionEditando.nombre"
                  type="text"
                  maxlength="100"
                  :class="{ 'has-error': erroresEdicion.nombre }"
                  @input="limpiarErrorEdicion('nombre')"
                />
                <span class="field-error" v-if="erroresEdicion.nombre">{{ erroresEdicion.nombre }}</span>
              </div>

              <div class="field">
                <label>
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 21s-7-6.2-7-11a7 7 0 0 1 14 0c0 4.8-7 11-7 11Z" stroke-linejoin="round"/>
                    <circle cx="12" cy="10" r="2.5"/>
                  </svg>
                  Dirección
                </label>
                <input
                  v-model="ubicacionEditando.direccion"
                  type="text"
                  maxlength="255"
                  :class="{ 'has-error': erroresEdicion.direccion }"
                  @input="limpiarErrorEdicion('direccion')"
                />
                <span class="field-error" v-if="erroresEdicion.direccion">{{ erroresEdicion.direccion }}</span>
              </div>

              <div class="field">
                <label>
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                    <circle cx="8.5" cy="8.5" r="1.5"/>
                    <path d="M21 15l-5-5L5 21" stroke-linecap="round" stroke-linejoin="round"/>
                  </svg>
                  Logo de la sede
                </label>
                <input
                  type="file"
                  accept="image/png, image/jpeg, image/webp"
                  @change="onImagenEdicionSeleccionada"
                />
                <img
                  v-if="edicionImagenPreview || ubicacionEditando.imagen"
                  :src="edicionImagenPreview || logoUrl(ubicacionEditando.imagen)"
                  class="logo-preview"
                  alt="Vista previa del logo"
                />
              </div>

              <div class="field-row">
                <div class="field">
                  <label>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 3" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Apertura
                  </label>
                  <input
                    v-model="ubicacionEditando.horario_apertura"
                    type="time"
                    :class="{ 'has-error': erroresEdicion.horario_apertura }"
                    @input="limpiarErrorEdicion('horario_apertura')"
                  />
                  <span class="field-error" v-if="erroresEdicion.horario_apertura">{{ erroresEdicion.horario_apertura }}</span>
                </div>
                  <div class="field">
                      <label>Teléfono</label>

                      <input
                        v-model="ubicacionEditando.telefono"
                        type="tel"
                        maxlength="20"
                        :class="{ 'has-error': erroresEdicion.telefono }"
                        @input="limpiarErrorEdicion('telefono')"
                      />

                      <span class="field-error" v-if="erroresEdicion.telefono">
                        {{ erroresEdicion.telefono }}
                      </span>
                    </div>
                <div class="field">
                  <label>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 3" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Cierre
                  </label>
                  <input
                    v-model="ubicacionEditando.horario_cierre"
                    type="time"
                    :class="{ 'has-error': erroresEdicion.horario_cierre }"
                    @input="limpiarErrorEdicion('horario_cierre')"
                  />
                  <span class="field-error" v-if="erroresEdicion.horario_cierre">{{ erroresEdicion.horario_cierre }}</span>
                </div>
              </div>

              <div class="field">
                <label>Estado de la sede</label>
                <div class="status-selector">
                  <button
                    type="button"
                    class="status-option"
                    :class="{ 'is-selected': ubicacionEditando.activo }"
                    @click="ubicacionEditando.activo = true"
                  >
                    <span class="status-dot status-dot--success"></span>
                    Activo
                  </button>
                  <button
                    type="button"
                    class="status-option"
                    :class="{ 'is-selected': !ubicacionEditando.activo }"
                    @click="ubicacionEditando.activo = false"
                  >
                    <span class="status-dot status-dot--danger"></span>
                    Inactivo
                  </button>
                </div>
              </div>
            </div>

            <div class="modal-footer">
              <button class="btn-secondary" @click="cerrarEdicion">Cancelar</button>
              <button class="btn-primary" :disabled="guardandoEdicion" @click="guardarEdicion">
                <svg v-if="guardandoEdicion" class="spin" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M21 12a9 9 0 1 1-9-9" stroke-linecap="round"/>
                </svg>
                <svg v-else width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                  <path d="M20 6 9 17l-5-5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                {{ guardandoEdicion ? 'Guardando...' : 'Guardar cambios' }}
              </button>
            </div>
          </div>
        </transition>
      </div>
    </transition>

    <!-- Modal de confirmación (éxito / error al guardar) -->
    <transition name="modal-fade">
      <div v-if="modalConfirmacion.visible" class="modal-overlay" @click.self="cerrarModalConfirmacion">
        <transition name="modal-pop" appear>
          <div v-if="modalConfirmacion.visible" class="confirm-modal" :class="`confirm-modal--${modalConfirmacion.tipo}`">
            <div class="confirm-icon-wrap">
              <svg v-if="modalConfirmacion.tipo === 'success'" width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6">
                <path d="M20 6 9 17l-5-5" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
              <svg v-else width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                <circle cx="12" cy="12" r="9"/><path d="M12 8v5M12 16h.01" stroke-linecap="round"/>
              </svg>
            </div>
            <h3 class="confirm-title">{{ modalConfirmacion.titulo }}</h3>
            <p class="confirm-message">{{ modalConfirmacion.mensaje }}</p>
            <button class="btn-primary confirm-btn" @click="cerrarModalConfirmacion">Aceptar</button>
          </div>
        </transition>
      </div>
    </transition>
  </div>
</template>

<script>
import ApiService from '../../services/ApiService.js';

export default {
  name: 'UbicacionCreate',
  data() {
    return {
      form: {
        nombre: '',
        direccion: '',
        telefono:'',
        horario_apertura: '',
        horario_cierre: '',
      },
      errores: {},
      ubicaciones: [],
      cargando: true,
      guardando: false,

      // Imagen (logo) de la sede - registro y edición
      imagenArchivo: null,
      imagenPreview: null,
      edicionImagenArchivo: null,
      edicionImagenPreview: null,

      // Búsqueda y filtros de la tabla
      busqueda: '',
      filtroEstado: 'todas', // 'todas' | 'activas' | 'inactivas'

      // Edición
      editando: false,
      ubicacionEditando: {},
      erroresEdicion: {},
      guardandoEdicion: false,

      // Notificaciones (toast)
      toasts: [],
      toastId: 0,

      // Ventana emergente de confirmación (éxito / error al guardar)
      modalConfirmacion: {
        visible: false,
        tipo: 'success', // 'success' | 'error'
        titulo: '',
        mensaje: '',
      },

      // Contadores animados del encabezado
      animatedTotal: 0,
      animatedActivas: 0,
      animatedInactivas: 0,
    };
  },
  // ----- Computed properties -----
  computed: {
    activasCount() {
      return this.ubicaciones.filter(u => u.activo).length;
    },
    inactivasCount() {
      return this.ubicaciones.filter(u => !u.activo).length;
    },
    filtroActivo() {
      return this.busqueda.trim() !== '' || this.filtroEstado !== 'todas';
    },
    ubicacionesFiltradas() {
      const texto = this.busqueda.toLowerCase().trim();
      let resultado = this.ubicaciones;
// Filtra por estado activo/inactivo
      if (this.filtroEstado === 'activas') {
        resultado = resultado.filter(u => u.activo);
      } else if (this.filtroEstado === 'inactivas') {
        resultado = resultado.filter(u => !u.activo);
      }
// Filtra por texto en nombre o folio_sucursal
      if (texto) {
        resultado = resultado.filter(u =>
          u.nombre.toLowerCase().includes(texto) ||
          (u.folio_sucursal || '').toLowerCase().includes(texto)
        );
      }

      return resultado;
    },
    conteoBadge() {
      const plural = n => (n === 1 ? 'sede' : 'sedes');
      if (this.filtroActivo) {
        return `${this.ubicacionesFiltradas.length} de ${this.ubicaciones.length} ${plural(this.ubicaciones.length)}`;
      }
      return `${this.ubicaciones.length} ${plural(this.ubicaciones.length)}`;
    },
  },
  mounted() {
    this.cargarUbicaciones();
  },
  methods: {
    inicial(nombre) {
      if (!nombre) return '?';

      return nombre
        .trim()
        .substring(0, 2)
        .toUpperCase();
    },

    avatarColor(nombre) {
      const paleta = [
        { bg: '#E4F3F5', fg: '#095F6E' }, // teal
        { bg: '#FBF1DE', fg: '#8A5A16' }, // ocre / fachada amarilla
        { bg: '#FBE8E1', fg: '#9C4A2E' }, // terracota
        { bg: '#EAEAF7', fg: '#4A4A8C' }, // índigo talavera
      ];
      const texto = nombre || '?';
      let suma = 0;
      for (let i = 0; i < texto.length; i++) suma += texto.charCodeAt(i);
      return paleta[suma % paleta.length];
    },
// Limpia los filtros de búsqueda y estado
    limpiarFiltros() {
      this.busqueda = '';
      this.filtroEstado = 'todas';
    },

    // Anima los contadores del encabezado (Total / Activas / Inactivas) de su valor
    // actual al nuevo, en vez de saltar directo al número final.
    animarContadores() {
      const totalDestino = this.ubicaciones.length;
      const activasDestino = this.ubicaciones.filter(u => u.activo).length;
      const inactivasDestino = totalDestino - activasDestino;
      const totalInicial = this.animatedTotal;
      const activasInicial = this.animatedActivas;
      const inactivasInicial = this.animatedInactivas;
      const duracion = 500;
      const inicio = performance.now();
// Función de paso de la animación, llamada en cada frame
      const paso = (ahora) => {
        const progreso = Math.min((ahora - inicio) / duracion, 1);
        const facilitado = 1 - Math.pow(1 - progreso, 3);
        this.animatedTotal = Math.round(totalInicial + (totalDestino - totalInicial) * facilitado);
        this.animatedActivas = Math.round(activasInicial + (activasDestino - activasInicial) * facilitado);
        this.animatedInactivas = Math.round(inactivasInicial + (inactivasDestino - inactivasInicial) * facilitado);
        if (progreso < 1) requestAnimationFrame(paso);
      };
      requestAnimationFrame(paso);
    },
// Convierte cualquier valor de hora al formato HH:MM para mostrarlo en la tabla
    formatoHora(hora) {
      if (!hora) return '--:--';
      return hora.slice(0, 5);
    },

    // Normaliza cualquier valor de hora ("17:30", "17:30:00") al formato
    // estricto HH:MM que Laravel espera (date_format:H:i). Evita que los
    // segundos que vienen de la columna TIME de MySQL rompan la validación,
    // tanto al precargar el modal de edición como al enviar el payload.
    normalizarHora(hora) {
      if (!hora) return '';
      return hora.length > 5 ? hora.slice(0, 5) : hora;
    },

    // ----- Generación de folio_sucursal -----
    // Sigue el patrón UBIC-<año>-<correlativo de 4 dígitos>, ej. UBIC-2026-0001.
    // El correlativo se reinicia cada año y se calcula a partir del folio más alto
    // ya registrado para el año actual.
    generarFolioSucursal() {
      const anioActual = new Date().getFullYear();// Obtiene el año actual
      const numeros = this.ubicaciones.map(u => {
        const match = (u.folio_sucursal || '').match(/^UBIC-(\d{4})-(\d+)$/);// Extrae el año y el número correlativo del folio_sucursal
        if (match && parseInt(match[1], 10) === anioActual) {
          return parseInt(match[2], 10);
        }
        return 0;
      });
      const siguiente = (numeros.length ? Math.max(...numeros) : 0) + 1;
      return `UBIC-${anioActual}-${String(siguiente).padStart(4, '0')}`;
    },

    // ----- Notificaciones (toast) -----
    mostrarToast(type, title, message = '') {
      const id = ++this.toastId;
      this.toasts.push({ id, type, title, message });
      setTimeout(() => this.cerrarToast(id), 4000);
    },
    cerrarToast(id) {
      this.toasts = this.toasts.filter(t => t.id !== id);
    },

    // ----- Ventana emergente de confirmación -----
    mostrarModalConfirmacion(tipo, titulo, mensaje = '') {
      this.modalConfirmacion = { visible: true, tipo, titulo, mensaje };
    },
    cerrarModalConfirmacion() {
      this.modalConfirmacion.visible = false;
    },
//LIMPIAR ERRORES
    limpiarError(campo) {
      if (this.errores[campo]) {
        this.errores = { ...this.errores, [campo]: null };
      }
    },
    limpiarErrorEdicion(campo) {
      if (this.erroresEdicion[campo]) {
        this.erroresEdicion = { ...this.erroresEdicion, [campo]: null };
      }
    },

    // ----- Manejo de imagen (logo) -----
    onImagenSeleccionada(e) {
      const archivo = e.target.files[0];
      if (!archivo) return;
      this.imagenArchivo = archivo;
      this.imagenPreview = URL.createObjectURL(archivo);
    },

    onImagenEdicionSeleccionada(e) {
      const archivo = e.target.files[0];
      if (!archivo) return;
      this.edicionImagenArchivo = archivo;
      this.edicionImagenPreview = URL.createObjectURL(archivo);
    },

    // URL pública del logo ya guardado (carpeta public/personalisarperfil)
    logoUrl(nombreArchivo) {
      return `/personalisarperfil/${nombreArchivo}`;
    },

    // ----- Carga de ubicaciones desde la API -----
    async cargarUbicaciones() {
      this.cargando = true;
      try {
        const { data } = await ApiService.get('/ubicaciones');
        this.ubicaciones = data;
        this.animarContadores();
      } catch (error) {
        console.error('Error al cargar ubicaciones:', error);
      } finally {
        this.cargando = false;
      }
    },
    // ----- Guardado de nueva ubicación -----
    async guardarUbicacion() {
      this.guardando = true;
      this.errores = {};

      const formData = new FormData();
      formData.append('nombre', this.form.nombre);
      formData.append('direccion', this.form.direccion);
      formData.append('telefono', this.form.telefono);
      formData.append('horario_apertura', this.normalizarHora(this.form.horario_apertura));
      formData.append('horario_cierre', this.normalizarHora(this.form.horario_cierre));
      formData.append('folio_sucursal', this.generarFolioSucursal());
      if (this.imagenArchivo) {
        formData.append('imagen', this.imagenArchivo);
      }

      try {
        // No se pasa el header 'Content-Type' manualmente: el interceptor de
        // ApiService detecta el FormData y deja que el navegador arme el
        // boundary correcto (multipart/form-data; boundary=...). Si se fuerza
        // el header aquí, Laravel no puede parsear el archivo.
        await ApiService.post('/ubicaciones', formData);
        this.mostrarModalConfirmacion('success', '¡Sucursal agregada!', `"${this.form.nombre}" se guardó correctamente.`);
        this.form = { nombre: '', direccion: '', horario_apertura: '', horario_cierre: '' };
        this.imagenArchivo = null;
        this.imagenPreview = null;
        await this.cargarUbicaciones();
      } catch (error) {
        if (error.response && error.response.status === 422) {
          const backendErrores = error.response.data.errors || {};
          this.errores = Object.keys(backendErrores).reduce((acc, key) => {
            acc[key] = backendErrores[key][0];
            return acc;
          }, {});
          this.mostrarModalConfirmacion('error', 'Revisa el formulario', 'Hay campos con errores, corrígelos e intenta de nuevo.');
        } else {
          console.error('Error al guardar ubicación:', error);
          this.mostrarModalConfirmacion('error', 'No se pudo guardar', 'Ocurrió un error al registrar la sucursal. Intenta de nuevo.');
        }
      } finally {
        this.guardando = false;
      }
    },
//----- Edición de ubicación -----
    abrirEdicion(ubicacion) {
      this.edicionImagenArchivo = null;
      this.edicionImagenPreview = null;
      this.ubicacionEditando = {
        ...ubicacion,
        horario_apertura: this.normalizarHora(ubicacion.horario_apertura),
        horario_cierre: this.normalizarHora(ubicacion.horario_cierre),
      };
      // Reinicia los errores de edición al abrir el modal
      this.erroresEdicion = {};
      this.editando = true;
    },
    // Cierra el modal de edición y reinicia los datos de edición
    cerrarEdicion() {
      this.editando = false;
      this.ubicacionEditando = {};
      this.erroresEdicion = {};
    },
    // Guarda los cambios de edición de la ubicación
    async guardarEdicion() {
      this.guardandoEdicion = true;
      this.erroresEdicion = {};

      const formData = new FormData();
      formData.append('_method', 'PUT'); // Laravel spoofing: PUT con archivos no se parsea directo
      formData.append('nombre', this.ubicacionEditando.nombre);
      formData.append('direccion', this.ubicacionEditando.direccion);
      formData.append('telefono', this.ubicacionEditando.telefono);
      formData.append('horario_apertura', this.normalizarHora(this.ubicacionEditando.horario_apertura));
      formData.append('horario_cierre', this.normalizarHora(this.ubicacionEditando.horario_cierre));
      formData.append('activo', this.ubicacionEditando.activo ? '1' : '0');
      if (this.edicionImagenArchivo) {
        formData.append('imagen', this.edicionImagenArchivo);
      }

      try {
        // Igual que en guardarUbicacion: sin forzar 'Content-Type' a mano,
        // el interceptor de ApiService deja que el navegador ponga el
        // boundary correcto para que $request->hasFile('imagen') funcione.
        await ApiService.post(`/ubicaciones/${this.ubicacionEditando.id}`, formData);
        this.cerrarEdicion();
        this.edicionImagenArchivo = null;
        this.edicionImagenPreview = null;
        this.mostrarModalConfirmacion('success', 'Cambios guardados', `"${this.ubicacionEditando.nombre}" se actualizó correctamente.`);
        await this.cargarUbicaciones();
      } catch (error) {
        if (error.response && error.response.status === 422) {
          const backendErrores = error.response.data.errors || {};
          this.erroresEdicion = Object.keys(backendErrores).reduce((acc, key) => {
            acc[key] = backendErrores[key][0];
            return acc;
          }, {});
          this.mostrarToast('error', 'Revisa el formulario', 'Hay campos con errores.');
        } else {
          console.error('Error al editar ubicación:', error);
          this.mostrarModalConfirmacion('error', 'No se pudo guardar', 'Ocurrió un error al editar la ubicación. Intenta de nuevo.');
        }
      } finally {
        this.guardandoEdicion = false;
      }
    },
  },
};
</script>

<style scoped>
.ubicaciones-page {
  --accent: #0B7285;
  --accent-dark: #095F6E;
  --accent-soft: #E4F3F5;
  --success: #15803D;
  --success-soft: #E7F6EC;
  --surface: #FFFFFF;
  --surface-muted: #F8FAFB;
  --surface-hover: #F1F5F6;
  --border: #E7ECEE;
  --border-strong: #D6DEE1;
  --text-main: #14202A;
  --text-muted: #6B7B80;
  --text-faint: #9AA6A9;
  --danger: #B3261E;
  --danger-soft: #FCEBEA;

  /* Acentos inspirados en las fachadas coloniales de Izamal, la "Ciudad Amarilla" */
  --ochre: #C2872E;
  --ochre-soft: #FBF1DE;
  --ochre-dark: #8A5A16;

  /* Fondo de página: gris azulado muy claro para que las tarjetas blancas
     tengan contraste y "floten" en vez de fundirse con el fondo. */
  --page-bg: #EEF2F3;

  position: relative;
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
  color: var(--text-main);
  max-width: 980px;
  margin: 0 auto;
  padding: 36px 24px 72px;
  background: var(--page-bg);
  min-height: 100vh;
  -webkit-font-smoothing: antialiased;
}

/* ============ Toast ============ */
.toast-container {
  position: fixed;
  top: 20px;
  right: 20px;
  z-index: 200;
  display: flex;
  flex-direction: column;
  gap: 10px;
  max-width: 360px;
  width: calc(100% - 40px);
}

.toast {
  display: flex;
  align-items: flex-start;
  gap: 10px;
  background: var(--surface);
  border: 1px solid var(--border);
  border-left: 3px solid var(--success);
  border-radius: 12px;
  padding: 14px 14px 14px 16px;
  box-shadow: 0 10px 24px rgba(16, 24, 40, 0.14);
}

.toast--error {
  border-left-color: var(--danger);
}

.toast-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 26px;
  height: 26px;
  flex-shrink: 0;
  border-radius: 50%;
  background: var(--success-soft);
  color: var(--success);
  margin-top: 1px;
}

.toast--error .toast-icon {
  background: var(--danger-soft);
  color: var(--danger);
}

.toast-body {
  flex: 1;
  min-width: 0;
}

.toast-title {
  font-size: 13.5px;
  font-weight: 700;
  color: var(--text-main);
  margin: 0;
}

.toast-message {
  font-size: 12.5px;
  color: var(--text-muted);
  margin: 3px 0 0;
  line-height: 1.4;
}

.toast-close {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 22px;
  height: 22px;
  flex-shrink: 0;
  border: none;
  background: transparent;
  color: var(--text-faint);
  cursor: pointer;
  border-radius: 6px;
  transition: all 0.15s ease;
}

.toast-close:hover {
  background: var(--surface-muted);
  color: var(--text-main);
}

.toast-enter-active {
  transition: transform 0.25s cubic-bezier(0.34, 1.56, 0.64, 1), opacity 0.25s ease;
}
.toast-leave-active {
  transition: transform 0.2s ease, opacity 0.2s ease;
  position: absolute;
  right: 0;
  width: 100%;
}
.toast-enter-from {
  transform: translateX(40px);
  opacity: 0;
}
.toast-leave-to {
  transform: translateX(40px);
  opacity: 0;
}

/* ============ Header ============ */
.page-header {
  display: flex;
  align-items: flex-start;
  gap: 16px;
  margin-bottom: 32px;
  padding: 24px 22px;
  border: none;
  border-radius: 16px;

  background: linear-gradient(135deg, var(--accent-dark), var(--accent) 70%, var(--ochre-dark));

  box-shadow: 0 8px 22px rgba(11, 114, 133, 0.22);
}

.header-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 48px;
  height: 48px;
  flex-shrink: 0;
  border-radius: 13px;
  background: rgba(255, 255, 255, 0.16);
  color: #fff;
  box-shadow: 0 0 0 6px rgba(255, 255, 255, 0.08);
}

.header-text {
  flex: 1;
  min-width: 0;
}

.eyebrow {
  font-size: 11.5px;
  font-weight: 700;
  letter-spacing: 0.09em;
  text-transform: uppercase;
  color: rgba(255, 255, 255, 0.8);
}

.page-header h1 {
  font-family: 'Sora', sans-serif;
  font-size: 27px;
  font-weight: 700;
  letter-spacing: -0.01em;
  margin: 4px 0 6px;
  color: #fff;
}

.subtitle {
  font-size: 14px;
  color: rgba(255, 255, 255, 0.82);
  margin: 0;
}

.header-stats {
  display: flex;
  gap: 10px;
  flex-shrink: 0;
  flex-wrap: wrap;
}

.stat-pill {
  display: flex;
  flex-direction: column;
  align-items: center;
  min-width: 68px;
  padding: 8px 14px;
  border-radius: 12px;
  background: rgba(255, 255, 255, 0.14);
  border: 1px solid rgba(255, 255, 255, 0.18);
}

.stat-pill--success {
  background: rgba(255, 255, 255, 0.22);
  border-color: transparent;
}

.stat-pill--danger {
  background: rgba(255, 255, 255, 0.1);
  border-color: transparent;
}

.stat-value {
  font-family: 'Sora', sans-serif;
  font-size: 18px;
  font-weight: 700;
  color: #fff;
  line-height: 1.2;
}

.stat-pill--success .stat-value {
  color: #fff;
}

.stat-pill--danger .stat-value {
  color: #fff;
}

.stat-label {
  font-size: 10.5px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.04em;
  color: rgba(255, 255, 255, 0.75);
}

/* ============ Card heading ============ */
.card-heading {
  display: flex;
  align-items: flex-start;
  gap: 12px;
  margin-bottom: 20px;
}

.card-heading-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 32px;
  height: 32px;
  flex-shrink: 0;
  border-radius: 9px;
  background: var(--accent);
  color: #fff;
}

.card-heading-icon--muted {
  background: var(--surface-muted);
  color: var(--text-muted);
  border: 1px solid var(--border);
}

.card-heading h2 {
  font-family: 'Sora', sans-serif;
  font-size: 15.5px;
  font-weight: 700;
  margin: 0;
  color: var(--text-main);
}

.card-subtext {
  font-size: 12.5px;
  color: var(--text-faint);
  margin: 2px 0 0;
}

/* ============ Form card ============ */
.form-card {
  position: relative;
  overflow: hidden;

  background: linear-gradient(
    180deg,
    var(--surface),
    #fbfcfd
  );

  border: 1px solid transparent;
  border-radius: 16px;

  padding: 22px 24px 20px;
  margin-bottom: 22px;

  box-shadow:
    0 2px 4px rgba(16, 24, 40, 0.05),
    0 10px 26px rgba(16, 24, 40, 0.09);

  transition: transform 0.18s ease, box-shadow 0.18s ease, border-color 0.18s ease;
}

.form-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 3px;

  background: linear-gradient(
    90deg,
    var(--accent),
    var(--ochre),
    var(--accent)
  );

  background-size: 200% 100%;
  animation: gradientMove 4s ease infinite;
  opacity: 0.9;
}
@keyframes gradientMove {
  0% { background-position: 0% 50%; }
  50% { background-position: 100% 50%; }
  100% { background-position: 0% 50%; }
}

.form-card:focus-within {
  border-color: var(--accent);
  box-shadow: 0 2px 4px rgba(16, 24, 40, 0.04), 0 14px 30px rgba(11, 114, 133, 0.14);
}

.form-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 18px 20px;
}

.field-full {
  grid-column: 1 / -1;
}

.field-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 14px;
}

.field {
  display: flex;
  flex-direction: column;
  gap: 7px;
}

.field label {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 12.5px;
  font-weight: 600;
  color: var(--text-main);
}

.field label svg {
  color: var(--text-faint);
  transition: color 0.15s ease;
}

.field:focus-within label svg {
  color: var(--accent);
}

.field input {
  font-family: 'Inter', sans-serif;
  font-size: 14px;
  padding: 11px 13px;
  border: 1.5px solid var(--border);
  border-radius: 9px;
  background: var(--surface-muted);
  color: var(--text-main);
  transition: border-color 0.15s ease, box-shadow 0.15s ease, background 0.15s ease;
}

.field input::placeholder {
  color: var(--text-faint);
}

.field input:hover {
  border-color: var(--border-strong);
}

.field input:focus {
  outline: none;
  border-color: var(--accent);
  background: var(--surface);
  box-shadow: 0 0 0 4px var(--accent-soft);
}

.field input.has-error {
  border-color: var(--danger);
  background: var(--danger-soft);
}

.field input.has-error:focus {
  box-shadow: 0 0 0 4px rgba(179, 38, 30, 0.12);
}

.field-error {
  display: flex;
  align-items: center;
  gap: 4px;
  font-size: 12px;
  font-weight: 500;
  color: var(--danger);
}

.form-actions {
  display: flex;
  align-items: center;
  justify-content: flex-end;
  gap: 16px;
  margin-top: 22px;
  padding-top: 20px;
  border-top: 1px solid var(--border);
}

.btn-primary {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  background: linear-gradient(145deg, var(--accent), var(--accent-dark));
  color: #fff;
  border: none;
  border-radius: 9px;
  padding: 11px 20px;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  box-shadow: 0 2px 6px rgba(11, 114, 133, 0.22);
  transition: transform 0.12s ease, box-shadow 0.12s ease, opacity 0.12s ease;
}

.btn-primary:hover:not(:disabled) {
  transform: translateY(-1px);
  box-shadow: 0 4px 10px rgba(11, 114, 133, 0.28);
}

.btn-primary:active:not(:disabled) {
  transform: translateY(0) scale(0.97);
}

.btn-primary:disabled {
  opacity: 0.6;
  cursor: not-allowed;
  transform: none;
  box-shadow: none;
}

.btn-secondary {
  background: #FDECEC;
  color: #B91C1C;
  border: 1.5px solid #DC2626;
  border-radius: 9px;
  padding: 10px 18px;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-secondary:hover {
  background: #FEE2E2;
  color: #991B1B;
  border-color: #B91C1C;
  transform: translateY(-1px);
  box-shadow: 0 6px 16px rgba(220, 38, 38, 0.15);
}

.btn-secondary:active {
  transform: scale(0.97);
}

.btn-clear-filters {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  margin-top: 4px;
  padding: 8px 16px;
  border-radius: 9px;
  border: 1.5px solid var(--border-strong);
  background: var(--surface);
  color: var(--accent-dark);
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.15s ease;
}

.btn-clear-filters:hover {
  border-color: var(--accent);
  background: var(--accent-soft);
  transform: translateY(-1px);
}

.btn-clear-filters:active {
  transform: translateY(0) scale(0.97);
}

/* ============ Table card ============ */
.table-card {
  position: relative;
  overflow: hidden;
  background: var(--surface);
  border: 1px solid transparent;
  border-radius: 16px;
  padding: 22px 24px;
  box-shadow: 0 2px 4px rgba(16, 24, 40, 0.05), 0 10px 26px rgba(16, 24, 40, 0.08);
}

.table-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 3px;
  background: linear-gradient(90deg, var(--ochre), var(--accent));
}

.table-header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  margin-bottom: 6px;
}

.table-header .card-heading {
  margin-bottom: 0;
}

.count-badge {
  flex-shrink: 0;
  background: var(--accent-soft);
  color: var(--accent-dark);
  font-size: 12px;
  font-weight: 700;
  padding: 5px 12px;
  border-radius: 999px;
  white-space: nowrap;
}

/* ============ Toolbar: búsqueda + filtros ============ */
.table-toolbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 14px;
  margin: 18px 0 20px;
  padding-bottom: 18px;
  border-bottom: 1px solid var(--border);
  flex-wrap: wrap;
}

.search-box {
  position: relative;
  flex: 1;
  min-width: 220px;
  max-width: 340px;
}

.search-box .search-icon {
  position: absolute;
  left: 12px;
  top: 50%;
  transform: translateY(-50%);
  color: var(--text-faint);
  pointer-events: none;
}

.search-box input {
  width: 100%;
  font-family: 'Inter', sans-serif;
  font-size: 13.5px;
  padding: 10px 32px 10px 34px;
  border: 1.5px solid var(--border);
  border-radius: 9px;
  background: var(--surface-muted);
  color: var(--text-main);
  transition: border-color 0.15s ease, box-shadow 0.15s ease, background 0.15s ease;
}

.search-box input::placeholder {
  color: var(--text-faint);
}

.search-box input:hover {
  border-color: var(--border-strong);
}

.search-box input:focus {
  outline: none;
  border-color: var(--accent);
  background: var(--surface);
  box-shadow: 0 0 0 4px var(--accent-soft);
}

.search-clear {
  position: absolute;
  right: 7px;
  top: 50%;
  transform: translateY(-50%);
  display: flex;
  align-items: center;
  justify-content: center;
  width: 20px;
  height: 20px;
  border: none;
  background: transparent;
  color: var(--text-faint);
  cursor: pointer;
  border-radius: 50%;
  transition: all 0.15s ease;
}

.search-clear:hover {
  background: var(--border);
  color: var(--text-main);
}

.filter-tabs {
  display: inline-flex;
  padding: 3px;
  gap: 2px;
  background: var(--surface-muted);
  border: 1px solid var(--border);
  border-radius: 10px;
  flex-shrink: 0;
}

.filter-tab {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 7px 13px;
  border-radius: 7px;
  border: none;
  background: transparent;
  color: var(--text-muted);
  font-size: 12.5px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.15s ease;
  white-space: nowrap;
}

.filter-tab:hover {
  color: var(--text-main);
}

.filter-tab.is-active {
  background: var(--surface);
  color: var(--accent-dark);
  box-shadow: 0 1px 3px rgba(16, 24, 40, 0.1);
}

.filter-tab .tab-count {
  font-family: 'IBM Plex Mono', monospace;
  font-size: 11px;
  font-weight: 700;
  padding: 1px 6px;
  border-radius: 999px;
  background: var(--border);
  color: var(--text-muted);
}

.filter-tab.is-active .tab-count {
  background: var(--accent-soft);
  color: var(--accent-dark);
}

.table-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 10px;
  text-align: center;
  padding: 48px 0;
  color: var(--text-faint);
  font-size: 14px;
}

/* Skeleton loader */
.skeleton-wrap {
  display: flex;
  flex-direction: column;
  gap: 22px;
  padding: 10px 4px 18px;
}

.skeleton-row {
  display: flex;
  align-items: center;
  gap: 12px;
}

.skeleton-avatar {
  width: 32px;
  height: 32px;
  border-radius: 9px;
  flex-shrink: 0;
}

.skeleton-lines {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.skeleton-line {
  height: 10px;
  border-radius: 6px;
}

.skeleton-pill {
  width: 64px;
  height: 22px;
  border-radius: 999px;
  flex-shrink: 0;
}

.shimmer {
  background: linear-gradient(90deg, var(--surface-muted) 25%, var(--border) 37%, var(--surface-muted) 63%);
  background-size: 400% 100%;
  animation: shimmer 1.4s ease infinite;
}

@keyframes shimmer {
  0% { background-position: 100% 50%; }
  100% { background-position: 0 50%; }
}

.empty-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 56px;
  height: 56px;
  border-radius: 50%;
  background: var(--surface-muted);
  color: var(--text-faint);
  margin-bottom: 4px;
  animation: floatY 3s ease-in-out infinite;
}

@keyframes floatY {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-5px); }
}

.empty-title {
  font-size: 14.5px;
  font-weight: 600;
  color: var(--text-muted);
  margin: 0;
}

.empty-subtitle {
  font-size: 13px;
  color: var(--text-faint);
  margin: 0;
}

.ubicaciones-table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 4px;
}

.ubicaciones-table th {
  text-align: left;
  font-size: 11.5px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  color: var(--text-faint);
  padding: 0 12px 10px;
  border-bottom: 1.5px solid var(--border);
}

.th-actions {
  text-align: right;
}

.ubicaciones-table td {
  padding: 14px 12px;
  font-size: 14px;
  border-bottom: 1px solid var(--border);
  vertical-align: middle;
}

.ubicaciones-table tr:last-child td {
  border-bottom: none;
}

.ubicaciones-table tbody tr {
  transition: background 0.12s ease;
}

.ubicaciones-table tbody tr.row-enter {
  animation: rowIn 0.35s cubic-bezier(0.22, 1, 0.36, 1) both;
}

@keyframes rowIn {
  from { opacity: 0; transform: translateY(6px); }
  to { opacity: 1; transform: translateY(0); }
}

.ubicaciones-table tbody tr:hover td {
  background: #F4FAFC;
}

.ubicaciones-table tbody tr:hover td:first-child {
  box-shadow: inset 3px 0 0 var(--accent);
}

.cell-nombre-wrap {
  display: flex;
  align-items: center;
  gap: 10px;
}

.row-avatar {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 32px;
  height: 32px;
  flex-shrink: 0;
  border-radius: 9px;
  background: var(--accent-soft);
  color: var(--accent-dark);
  font-family: 'Sora', sans-serif;
  font-size: 13px;
  font-weight: 700;
  transition: transform 0.15s ease;
  overflow: hidden;
}

.row-avatar-img {
  width: 100%;
  height: 100%;
  border-radius: 9px;
  object-fit: cover;
}

.ubicaciones-table tbody tr:hover .row-avatar {
  transform: scale(1.06);
}

.row-avatar--inactive {
  background: var(--surface-muted);
  color: var(--text-faint);
}

.cell-nombre-info {
  display: flex;
  flex-direction: column;
  gap: 1px;
  min-width: 0;
}

.cell-nombre {
  font-weight: 600;
  color: var(--text-main);
}

.cell-folio {
  font-family: 'IBM Plex Mono', monospace;
  font-size: 11px;
  font-weight: 600;
  color: var(--text-faint);
  letter-spacing: 0.01em;
}

.cell-direccion-inner,
.cell-horario-inner {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  color: var(--text-muted);
}

.cell-direccion-inner svg,
.cell-horario-inner svg {
  flex-shrink: 0;
  color: var(--text-faint);
}

.cell-horario-inner {
  font-family: 'IBM Plex Mono', monospace;
  font-size: 13px;
  white-space: nowrap;
}

.cell-actions {
  text-align: right;
}

/* Status badge */
.status-badge {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-size: 12px;
  font-weight: 700;
  padding: 5px 11px;
  border-radius: 999px;
}

.status-dot {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  flex-shrink: 0;
}

.status-active {
  background: #e0f2fe;
  color: #0177b6;
}

.status-active .status-dot {
  background: #068a0c;
  box-shadow: 0 0 0 3px rgba(14, 165, 233, 0.2);
  animation: dotPulseActive 2.2s ease-in-out infinite;
}

@keyframes dotPulseActive {
  0%, 100% { box-shadow: 0 0 0 3px rgba(14, 165, 233, 0.2); }
  50% { box-shadow: 0 0 0 7px rgba(14, 165, 233, 0); }
}

.status-inactive {
  background: #f1f5f9;
  color: #64748b;
}

.status-inactive .status-dot {
  background: #94a3b8;
  box-shadow: 0 0 0 3px rgba(148, 163, 184, 0.2);
}

.status-dot--success { background: var(--success); }
.status-dot--danger { background: var(--danger); }

/* Action button */
.btn-icon {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 7px 12px;
  border-radius: 8px;
  border: 1.5px solid var(--border);
  background: var(--surface);
  color: var(--text-muted);
  font-size: 12.5px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.15s ease;
}

.btn-icon:hover {
  border-color: var(--accent);
  color: var(--accent-dark);
  background: var(--accent-soft);
  transform: translateY(-1px);
}

.btn-icon:active {
  transform: translateY(0) scale(0.96);
}

/* Edit button */
.btn-edit {
  border-color: rgba(245, 158, 11, 0.3);
  color: #c2410c;
  background: #FFF7E6 !important;
}

.btn-edit:hover {
  background: #ffa023 !important;
  border-color: #f59e0b;
  color: #9a3412;
  transform: translateY(-1px);
  box-shadow: 0 6px 16px rgba(245, 158, 11, 0.18);
}

.btn-edit:active {
  transform: translateY(0) scale(0.96);
}

/* ============ Modal ============ */
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(12, 20, 24, 0.5);
  backdrop-filter: blur(2px);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
  z-index: 100;
}

.modal-card {
  width: 100%;
  max-width: 440px;
  background: var(--surface);
  border-radius: 16px;
  box-shadow: 0 24px 48px rgba(16, 24, 40, 0.22);
  overflow: hidden;
}

.modal-header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  padding: 22px 24px 18px;
  border-bottom: 1px solid var(--border);
}

.modal-header-left {
  display: flex;
  align-items: center;
  gap: 12px;
}

.modal-avatar {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 38px;
  height: 38px;
  flex-shrink: 0;
  border-radius: 10px;
  background: var(--accent-soft);
  color: var(--accent-dark);
  font-family: 'Sora', sans-serif;
  font-size: 15px;
  font-weight: 700;
}

.modal-avatar--inactive {
  background: var(--surface-muted);
  color: var(--text-faint);
}

.modal-eyebrow {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  font-family: 'IBM Plex Mono', monospace;
  font-size: 11px;
  font-weight: 700;
  color: var(--ochre-dark);
  background: var(--ochre-soft);
  padding: 3px 8px;
  border-radius: 999px;
  letter-spacing: 0.02em;
}

.modal-header h3 {
  font-family: 'Sora', sans-serif;
  font-size: 17px;
  font-weight: 700;
  margin: 3px 0 0;
}

.btn-close {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 30px;
  height: 30px;
  border-radius: 8px;
  border: none;
  background: transparent;
  color: var(--text-faint);
  cursor: pointer;
  transition: all 0.15s ease;
}

.btn-close:hover {
  background: var(--surface-muted);
  color: var(--text-main);
}

.modal-body {
  padding: 22px 24px;
  display: flex;
  flex-direction: column;
  gap: 18px;
}

.modal-footer {
  display: flex;
  align-items: center;
  justify-content: flex-end;
  gap: 10px;
  padding: 16px 24px;
  border-top: 1px solid var(--border);
  background: var(--surface-muted);
}

/* Status selector (segmented) */
.status-selector {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 8px;
}

.status-option {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 7px;
  padding: 10px;
  border-radius: 9px;
  border: 1.5px solid var(--border);
  background: var(--surface);
  color: var(--text-muted);
  font-size: 13.5px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.15s ease;
}

.status-option:hover {
  border-color: var(--border-strong);
}

/* Activo seleccionado */
.status-option.is-selected:first-child {
  background: #E8F8EE;
  border-color: #16A34A;
  color: #15803D;
}

/* Inactivo seleccionado */
.status-option.is-selected:last-child {
  background: #FDECEC;
  border-color: #DC2626;
  color: #B91C1C;
}

/* ============ Modal de confirmación (éxito / error) ============ */
.confirm-modal {
  width: 100%;
  max-width: 360px;
  background: var(--surface);
  border-radius: 18px;
  box-shadow: 0 24px 48px rgba(16, 24, 40, 0.24);
  padding: 32px 28px 26px;
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
}

.confirm-icon-wrap {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 64px;
  height: 64px;
  border-radius: 50%;
  margin-bottom: 16px;
  background: var(--success-soft);
  color: var(--success);
  animation: confirmPop 0.35s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.confirm-modal--error .confirm-icon-wrap {
  background: var(--danger-soft);
  color: var(--danger);
}

@keyframes confirmPop {
  0% { transform: scale(0.5); opacity: 0; }
  100% { transform: scale(1); opacity: 1; }
}

.confirm-title {
  font-family: 'Sora', sans-serif;
  font-size: 18px;
  font-weight: 700;
  margin: 0 0 6px;
  color: var(--text-main);
}

.confirm-message {
  font-size: 13.5px;
  color: var(--text-muted);
  margin: 0 0 22px;
  line-height: 1.5;
}

.confirm-btn {
  width: 100%;
  justify-content: center;
}

.confirm-modal--error .confirm-btn {
  background: linear-gradient(145deg, var(--danger), #8f1e17);
  box-shadow: 0 2px 6px rgba(179, 38, 30, 0.22);
}

/* ============ Animations ============ */
.spin {
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.modal-fade-enter-active, .modal-fade-leave-active {
  transition: opacity 0.18s ease;
}
.modal-fade-enter-from, .modal-fade-leave-to {
  opacity: 0;
}

.modal-pop-enter-active {
  transition: transform 0.2s cubic-bezier(0.34, 1.56, 0.64, 1), opacity 0.2s ease;
}
.modal-pop-leave-active {
  transition: transform 0.15s ease, opacity 0.15s ease;
}
.modal-pop-enter-from {
  transform: scale(0.94) translateY(6px);
  opacity: 0;
}
.modal-pop-leave-to {
  transform: scale(0.97);
  opacity: 0;
}

.logo-preview {
  margin-top: 8px;
  max-width: 120px;
  max-height: 120px;
  border-radius: 9px;
  border: 1.5px solid var(--border);
  object-fit: cover;
}

@media (max-width: 640px) {
  .page-header {
    flex-wrap: wrap;
  }
  .header-stats {
    width: 100%;
    justify-content: flex-start;
  }
  .form-grid,
  .field-row {
    grid-template-columns: 1fr;
  }
  .modal-card {
    max-width: 100%;
  }
  .ubicaciones-table {
    font-size: 13px;
  }
  .toast-container {
    right: 10px;
    left: 10px;
    max-width: none;
    width: auto;
  }
  .table-toolbar {
    flex-direction: column;
    align-items: stretch;
  }
  .search-box {
    max-width: none;
  }
  .filter-tabs {
    justify-content: space-between;
  }
}
</style>