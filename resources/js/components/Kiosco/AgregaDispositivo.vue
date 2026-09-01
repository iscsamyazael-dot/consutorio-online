<template>
  <div class="dispositivo-page">
    <header class="page-header">
      <div class="header-icon">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
          <rect x="5" y="2" width="14" height="20" rx="2" ry="2"/>
          <path d="M12 18h.01" stroke-linecap="round"/>
        </svg>
      </div>
      <div class="header-text">
        <span class="eyebrow">Configuración del sistema</span>
        <h1>Dispositivos</h1>
        <p class="subtitle">Vincula y administra los kioscos o TVs conectados a tu cuenta.</p>
      </div>
    </header>

    <!-- ===================== NUEVO: Dispositivos vinculados ===================== -->
    <section class="list-card">
      <div class="list-card__header">
        <h2>Dispositivos vinculados</h2>
        <button class="btn-outline" @click="cargarDispositivos" :disabled="cargando">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M21 2v6h-6M3 22v-6h6M3.51 9a9 9 0 0 1 14.85-3.36L21 8M3 16l2.64 2.36A9 9 0 0 0 20.49 15" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
          Actualizar
        </button>
      </div>

      <div v-if="cargando" class="estado-vacio">Cargando dispositivos…</div>

      <div v-else-if="dispositivos.length === 0" class="estado-vacio">
        Aún no tienes dispositivos vinculados. Genera un código abajo para agregar el primero.
      </div>

      <div v-else class="tabla-wrapper">
        <table class="tabla-dispositivos">
          <thead>
            <tr>
              <th>Nombre</th>
              <th>Tipo</th>
              <th>Token</th>
              <th>Última conexión</th>
              <th class="th-acciones">Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="d in dispositivos" :key="d.id">
              <td>
                <span class="celda-nombre">{{ d.nombre_dispositivo }}</span>
                <span class="badge" :class="d.activo ? 'badge--online' : 'badge--offline'">
                  {{ d.activo ? 'Conectado' : 'Sin conexión' }}
                </span>
              </td>
              <td>{{ d.tipo === 'tv' ? 'TV' : 'Kiosco' }}</td>
              <td>
                <div class="celda-token">
                  <code>{{ tokenVisible[d.id] ? d.token : ocultarToken(d.token) }}</code>
                  <button class="icon-btn" @click="alternarToken(d.id)" title="Mostrar/ocultar token">
                    <svg v-if="!tokenVisible[d.id]" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                      <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                      <circle cx="12" cy="12" r="3"/>
                    </svg>
                    <svg v-else width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                      <path d="M17.94 17.94A10.94 10.94 0 0 1 12 20c-7 0-11-8-11-8a18.6 18.6 0 0 1 5.06-5.94M9.9 4.24A10.6 10.6 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19M14.12 14.12a3 3 0 1 1-4.24-4.24" stroke-linecap="round" stroke-linejoin="round"/>
                      <path d="M1 1l22 22" stroke-linecap="round"/>
                    </svg>
                  </button>
                  <button class="icon-btn" @click="copiarToken(d.token)" title="Copiar token">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                      <rect x="9" y="9" width="13" height="13" rx="2"/>
                      <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>
                    </svg>
                  </button>
                </div>
              </td>
              <td class="celda-fecha">{{ formatearFecha(d.ultima_conexion) }}</td>
              <td class="th-acciones">
                <button class="btn-mini" :disabled="regenerando[d.id]" @click="regenerarToken(d)">
                  {{ regenerando[d.id] ? 'Generando…' : 'Regenerar token' }}
                </button>
                <button class="btn-mini btn-mini--danger" :disabled="desvinculando[d.id]" @click="confirmarDesvincular(d)">
                  {{ desvinculando[d.id] ? 'Quitando…' : 'Desvincular' }}
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </section>
    <!-- =================== FIN NUEVO: Dispositivos vinculados =================== -->

    <section class="form-card">
      <h2 class="form-card__title">Agregar nuevo dispositivo</h2>

      <form v-if="!codigoGenerado" @submit.prevent="generarCodigo">
        <div class="field">
          <label for="nombre_dispositivo">Nombre del dispositivo</label>
          <input
            id="nombre_dispositivo"
            v-model="form.nombre_dispositivo"
            type="text"
            placeholder="Ej. Tablet recepción, TV sala de espera"
            maxlength="100"
            required
          />
        </div>

        <div class="field">
          <label for="tipo">Tipo de dispositivo</label>
          <select id="tipo" v-model="form.tipo" required>
            <option value="kiosco">Kiosco (tablet de autoregistro)</option>
            <option value="tv">TV (pantalla de sala de espera)</option>
          </select>
        </div>

        <button type="submit" class="btn-primary" :disabled="generando">
          {{ generando ? 'Generando…' : 'Generar código' }}
        </button>
      </form>

      <div v-else class="codigo-resultado">
        <p class="codigo-label">Código de emparejamiento</p>
        <p class="codigo-numero">{{ codigoFormateado }}</p>
        <p class="codigo-ayuda">
          En el dispositivo nuevo, abre <strong>/kiosco</strong> y escribe este código.
        </p>
        <p class="codigo-expira" :class="{ 'codigo-expira--urgente': segundosRestantes <= 60 }">
          Expira en {{ minutosSegundos }}
        </p>
        <button class="btn-secondary" @click="reiniciar">Generar otro código</button>
      </div>
    </section>
  </div>
</template>

<script>
import ApiService from '../../services/ApiService.js';

export default {
  name: 'AgregaDispositivo',
  data() {
    return {
      // ---- lista de dispositivos ----
      dispositivos: [],
      cargando: false,
      tokenVisible: {},
      regenerando: {},
      desvinculando: {},
      polling: null,

      // ---- generación de código (existente) ----
      form: {
        nombre_dispositivo: '',
        tipo: 'kiosco',
      },
      generando: false,
      codigoGenerado: null,
      segundosRestantes: 0,
      temporizador: null,
    };
  },
  computed: {
    codigoFormateado() {
      if (!this.codigoGenerado) return '';
      return this.codigoGenerado.replace(/(\d{3})(\d{3})/, '$1 $2');
    },
    minutosSegundos() {
      const m = Math.floor(this.segundosRestantes / 60);
      const s = this.segundosRestantes % 60;
      return `${m}:${s.toString().padStart(2, '0')}`;
    },
  },
  mounted() {
    this.cargarDispositivos();
  },
  methods: {
    // ---------------- Listado ----------------
    async cargarDispositivos() {
      this.cargando = true;
      try {
        const { data } = await ApiService.get('/dispositivos');
        this.dispositivos = data.dispositivos || data;
      } catch (error) {
        window.Swal.fire({
          toast: true,
          position: 'top-end',
          icon: 'error',
          title: error.response?.data?.message || 'No se pudieron cargar los dispositivos',
          showConfirmButton: false,
          timer: 3000,
        });
      } finally {
        this.cargando = false;
      }
    },

    ocultarToken(token) {
      if (!token) return '';
      if (token.length <= 8) return '••••••••';
      return `${token.slice(0, 4)}••••••${token.slice(-4)}`;
    },

    alternarToken(id) {
      this.tokenVisible = { ...this.tokenVisible, [id]: !this.tokenVisible[id] };
    },

    async copiarToken(token) {
      try {
        await navigator.clipboard.writeText(token);
        window.Swal.fire({
          toast: true,
          position: 'top-end',
          icon: 'success',
          title: 'Token copiado',
          showConfirmButton: false,
          timer: 1500,
        });
      } catch (error) {
        window.Swal.fire({
          toast: true,
          position: 'top-end',
          icon: 'error',
          title: 'No se pudo copiar el token',
          showConfirmButton: false,
          timer: 2000,
        });
      }
    },

    formatearFecha(fecha) {
      if (!fecha) return 'Nunca';
      const f = new Date(fecha);
      return f.toLocaleString('es-GT', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
      });
    },

    // ---------------- Regenerar token ----------------
    async regenerarToken(dispositivo) {
      const confirmacion = await window.Swal.fire({
        title: '¿Regenerar token?',
        text: `El dispositivo "${dispositivo.nombre_dispositivo}" perderá la conexión hasta que ingreses el nuevo token.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, regenerar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#0B7285',
      });
      if (!confirmacion.isConfirmed) return;

      this.regenerando = { ...this.regenerando, [dispositivo.id]: true };
      try {
        const { data } = await ApiService.post(`/dispositivos/${dispositivo.id}/regenerar-token`);
        dispositivo.token = data.token;
        this.tokenVisible = { ...this.tokenVisible, [dispositivo.id]: true };
        window.Swal.fire({
          toast: true,
          position: 'top-end',
          icon: 'success',
          title: 'Token regenerado',
          showConfirmButton: false,
          timer: 2000,
        });
      } catch (error) {
        window.Swal.fire({
          toast: true,
          position: 'top-end',
          icon: 'error',
          title: error.response?.data?.message || 'No se pudo regenerar el token',
          showConfirmButton: false,
          timer: 3000,
        });
      } finally {
        this.regenerando = { ...this.regenerando, [dispositivo.id]: false };
      }
    },

    // ---------------- Desvincular ----------------
    async confirmarDesvincular(dispositivo) {
      const confirmacion = await window.Swal.fire({
        title: '¿Desvincular dispositivo?',
        text: `"${dispositivo.nombre_dispositivo}" dejará de tener acceso y deberás generar un código nuevo para volver a vincularlo.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, desvincular',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#B3261E',
      });
      if (!confirmacion.isConfirmed) return;

      this.desvinculando = { ...this.desvinculando, [dispositivo.id]: true };
      try {
        await ApiService.delete(`/dispositivos/${dispositivo.id}`);
        this.dispositivos = this.dispositivos.filter((d) => d.id !== dispositivo.id);
        window.Swal.fire({
          toast: true,
          position: 'top-end',
          icon: 'success',
          title: 'Dispositivo desvinculado',
          showConfirmButton: false,
          timer: 2000,
        });
      } catch (error) {
        window.Swal.fire({
          toast: true,
          position: 'top-end',
          icon: 'error',
          title: error.response?.data?.message || 'No se pudo desvincular el dispositivo',
          showConfirmButton: false,
          timer: 3000,
        });
      } finally {
        this.desvinculando = { ...this.desvinculando, [dispositivo.id]: false };
      }
    },

    // ---------------- Generar código (existente) ----------------
    async generarCodigo() {
      this.generando = true;
      try {
        const { data } = await ApiService.post('/dispositivos/generar-codigo', this.form);
        this.codigoGenerado = data.codigo;
        this.iniciarCuentaRegresiva(data.expira_en);
      } catch (error) {
        window.Swal.fire({
          toast: true,
          position: 'top-end',
          icon: 'error',
          title: error.response?.data?.message || 'No se pudo generar el código',
          showConfirmButton: false,
          timer: 3000,
        });
      } finally {
        this.generando = false;
      }
    },
    iniciarCuentaRegresiva(expiraEn) {
      const expira = new Date(expiraEn).getTime();
      clearInterval(this.temporizador);
      this.temporizador = setInterval(() => {
        const restante = Math.floor((expira - Date.now()) / 1000);
        this.segundosRestantes = Math.max(restante, 0);
        if (this.segundosRestantes <= 0) {
          clearInterval(this.temporizador);
          this.reiniciar();
        }
      }, 1000);
    },
    iniciarPolling() {
      clearInterval(this.polling);
      this.polling = setInterval(async () => {
        try {
          const { data } = await ApiService.get(`/dispositivos/verificar-codigo/${this.codigoGenerado}`);
          if (data.vinculado) {
            clearInterval(this.polling);
            clearInterval(this.temporizador);

            await window.Swal.fire({
              icon: 'success',
              title: 'Dispositivo vinculado',
              text: `"${data.nombre_dispositivo}" se conectó correctamente.`,
              confirmButtonColor: '#0B7285',
            });

            this.reiniciar();
          }
        } catch (error) {
          // Silencioso: si falla una consulta de polling no interrumpimos
          // al usuario, simplemente se reintenta en el siguiente tick.
        }
      }, 3000);
    },
    reiniciar() {
      clearInterval(this.temporizador);
      clearInterval(this.polling);
      this.codigoGenerado = null;
      this.form.nombre_dispositivo = '';
      this.form.tipo = 'kiosco';
      // refrescamos la lista por si el dispositivo ya se emparejó
      this.cargarDispositivos();
    },
  },
  beforeUnmount() {
    clearInterval(this.temporizador);
    clearInterval(this.polling);
  },
};
</script>

<style scoped>
.dispositivo-page {
  --accent: #0B7285;
  --accent-dark: #095F6E;
  --accent-soft: #E4F3F5;
  --danger: #B3261E;
  --danger-soft: #FBEAE9;
  --text-main: #14202A;
  --text-muted: #6B7B80;
  --border: #E7ECEE;
  --surface: #FFFFFF;
  --surface-muted: #F8FAFB;
  --page-bg: #EEF2F3;

  font-family: 'Inter', sans-serif;
  color: var(--text-main);
  max-width: 760px;
  margin: 0 auto;
  padding: 36px 24px 72px;
}

.page-header {
  display: flex;
  gap: 16px;
  margin-bottom: 24px;
  padding: 24px 22px;
  border-radius: 16px;
  background: linear-gradient(135deg, var(--accent-dark), var(--accent));
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
}

.eyebrow {
  font-size: 11.5px;
  font-weight: 700;
  letter-spacing: 0.09em;
  text-transform: uppercase;
  color: rgba(255, 255, 255, 0.8);
}

.page-header h1 {
  font-size: 24px;
  font-weight: 700;
  margin: 4px 0 6px;
  color: #fff;
}

.subtitle {
  font-size: 13.5px;
  color: rgba(255, 255, 255, 0.85);
  margin: 0;
}

/* ---------- Lista de dispositivos ---------- */
.list-card {
  background: var(--surface);
  border-radius: 16px;
  padding: 22px 22px 8px;
  margin-bottom: 20px;
  box-shadow: 0 2px 4px rgba(16, 24, 40, 0.05), 0 10px 26px rgba(16, 24, 40, 0.08);
}

.list-card__header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 14px;
}

.list-card__header h2 {
  font-size: 16px;
  font-weight: 700;
  margin: 0;
}

.btn-outline {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  background: var(--surface-muted);
  border: 1.5px solid var(--border);
  border-radius: 8px;
  padding: 7px 12px;
  font-size: 12.5px;
  font-weight: 600;
  color: var(--text-main);
  cursor: pointer;
}

.btn-outline:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.estado-vacio {
  padding: 24px 4px 28px;
  font-size: 13.5px;
  color: var(--text-muted);
  text-align: center;
}

.tabla-wrapper {
  overflow-x: auto;
  padding-bottom: 12px;
}

.tabla-dispositivos {
  width: 100%;
  border-collapse: collapse;
  font-size: 13px;
}

.tabla-dispositivos thead th {
  text-align: left;
  font-size: 11px;
  font-weight: 700;
  letter-spacing: 0.04em;
  text-transform: uppercase;
  color: var(--text-muted);
  padding: 8px 10px;
  border-bottom: 1.5px solid var(--border);
  white-space: nowrap;
}

.tabla-dispositivos td {
  padding: 12px 10px;
  border-bottom: 1px solid var(--border);
  vertical-align: middle;
}

.celda-nombre {
  display: block;
  font-weight: 600;
  margin-bottom: 4px;
}

.badge {
  display: inline-block;
  font-size: 10.5px;
  font-weight: 700;
  padding: 2px 8px;
  border-radius: 999px;
}

.badge--online {
  background: #E3F6EA;
  color: #1E7A43;
}

.badge--offline {
  background: var(--surface-muted);
  color: var(--text-muted);
}

.celda-token {
  display: flex;
  align-items: center;
  gap: 6px;
}

.celda-token code {
  font-family: 'SFMono-Regular', Consolas, monospace;
  font-size: 12.5px;
  background: var(--surface-muted);
  padding: 3px 7px;
  border-radius: 6px;
  white-space: nowrap;
}

.icon-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 26px;
  height: 26px;
  border: none;
  background: transparent;
  color: var(--text-muted);
  cursor: pointer;
  border-radius: 6px;
}

.icon-btn:hover {
  background: var(--surface-muted);
  color: var(--accent-dark);
}

.celda-fecha {
  color: var(--text-muted);
  white-space: nowrap;
}

.th-acciones {
  white-space: nowrap;
  text-align: right;
}

.btn-mini {
  font-size: 12px;
  font-weight: 600;
  padding: 6px 10px;
  border-radius: 7px;
  border: 1.5px solid var(--border);
  background: var(--surface-muted);
  color: var(--text-main);
  cursor: pointer;
  margin-left: 6px;
}

.btn-mini:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.btn-mini--danger {
  border-color: #F3CFCC;
  background: var(--danger-soft);
  color: var(--danger);
}

/* ---------- Formulario generar código (existente) ---------- */
.form-card {
  background: var(--surface);
  border-radius: 16px;
  padding: 26px 24px;
  box-shadow: 0 2px 4px rgba(16, 24, 40, 0.05), 0 10px 26px rgba(16, 24, 40, 0.08);
}

.form-card__title {
  font-size: 16px;
  font-weight: 700;
  margin: 0 0 18px;
}

.field {
  display: flex;
  flex-direction: column;
  gap: 7px;
  margin-bottom: 18px;
}

.field label {
  font-size: 12.5px;
  font-weight: 600;
}

.field input,
.field select {
  font-size: 14px;
  padding: 11px 13px;
  border: 1.5px solid var(--border);
  border-radius: 9px;
  background: var(--surface-muted);
  color: var(--text-main);
}

.field input:focus,
.field select:focus {
  outline: none;
  border-color: var(--accent);
  background: var(--surface);
  box-shadow: 0 0 0 4px var(--accent-soft);
}

.btn-primary {
  width: 100%;
  background: linear-gradient(145deg, var(--accent), var(--accent-dark));
  color: #fff;
  border: none;
  border-radius: 9px;
  padding: 12px 20px;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
}

.btn-primary:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.btn-secondary {
  width: 100%;
  margin-top: 16px;
  background: var(--surface-muted);
  border: 1.5px solid var(--border);
  border-radius: 9px;
  padding: 10px 18px;
  font-size: 13.5px;
  font-weight: 600;
  color: var(--text-main);
  cursor: pointer;
}

.codigo-resultado {
  text-align: center;
  padding: 12px 0;
}

.codigo-label {
  font-size: 12.5px;
  font-weight: 600;
  color: var(--text-muted);
  text-transform: uppercase;
  letter-spacing: 0.05em;
  margin: 0 0 8px;
}

.codigo-numero {
  font-size: 48px;
  font-weight: 700;
  letter-spacing: 0.08em;
  color: var(--accent-dark);
  margin: 0 0 12px;
  font-variant-numeric: tabular-nums;
}

.codigo-ayuda {
  font-size: 13.5px;
  color: var(--text-muted);
  margin: 0 0 10px;
}

.codigo-expira {
  font-size: 13px;
  font-weight: 600;
  color: var(--text-muted);
  margin: 0 0 4px;
}

.codigo-expira--urgente {
  color: var(--danger);
}
</style>