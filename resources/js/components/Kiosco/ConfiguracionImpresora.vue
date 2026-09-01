<template>
  <div class="dispositivo-page">
    <header class="page-header">
      <div class="header-icon">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
          <path d="M6 9V2h12v7" stroke-linecap="round" stroke-linejoin="round"/>
          <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2" stroke-linecap="round" stroke-linejoin="round"/>
          <rect x="6" y="14" width="12" height="8"/>
        </svg>
      </div>
      <div class="header-text">
        <span class="eyebrow">Configuración del sistema</span>
        <h1>Impresora de tickets</h1>
        <p class="subtitle">Configura la impresora térmica de red usada para imprimir el turno en el kiosco.</p>
      </div>
    </header>

    <section class="form-card">
      <h2 class="form-card__title">Datos de conexión</h2>

      <div v-if="cargando" class="estado-vacio">Cargando configuración…</div>

      <form v-else @submit.prevent="guardar">
        <div class="field">
          <label for="nombre">Nombre de la impresora</label>
          <input
            id="nombre"
            v-model="form.nombre"
            type="text"
            placeholder="Ej. Impresora recepción"
            maxlength="100"
            required
          />
        </div>

        <div class="field">
          <label for="ip">Dirección IP</label>
          <input
            id="ip"
            v-model="form.ip"
            type="text"
            placeholder="Ej. 192.168.0.150"
            pattern="^(\d{1,3}\.){3}\d{1,3}$"
            required
          />
        </div>

        <div class="field">
          <label for="puerto">Puerto</label>
          <input
            id="puerto"
            v-model.number="form.puerto"
            type="number"
            min="1"
            max="65535"
            placeholder="9100"
            required
          />
        </div>

        <div class="field">
          <label for="ancho_papel">Ancho de papel</label>
          <select id="ancho_papel" v-model.number="form.ancho_papel_mm" required>
            <option :value="58">58 mm</option>
            <option :value="80">80 mm</option>
          </select>
        </div>

        <button type="submit" class="btn-primary" :disabled="guardando">
          {{ guardando ? 'Guardando…' : 'Guardar configuración' }}
        </button>
      </form>
    </section>
  </div>
</template>

<script>
import ApiService from '../../services/ApiService.js';

export default {
  name: 'ConfiguracionImpresora',
  data() {
    return {
      cargando: true,
      guardando: false,
      form: {
        nombre: '',
        ip: '',
        puerto: 9100,
        ancho_papel_mm: 58,
      },
    };
  },
  mounted() {
    this.cargarConfiguracion();
  },
  methods: {
    async cargarConfiguracion() {
      this.cargando = true;
      try {
        const { data } = await ApiService.get('/configuracion-impresora');
        if (data.configuracion) {
          this.form = {
            nombre: data.configuracion.nombre,
            ip: data.configuracion.ip,
            puerto: data.configuracion.puerto,
            ancho_papel_mm: data.configuracion.ancho_papel_mm,
          };
        }
      } catch (error) {
        window.Swal.fire({
          toast: true,
          position: 'top-end',
          icon: 'error',
          title: error.response?.data?.message || 'No se pudo cargar la configuración',
          showConfirmButton: false,
          timer: 3000,
        });
      } finally {
        this.cargando = false;
      }
    },

    async guardar() {
      this.guardando = true;
      try {
        await ApiService.post('/configuracion-impresora', this.form);
        window.Swal.fire({
          toast: true,
          position: 'top-end',
          icon: 'success',
          title: 'Configuración guardada',
          showConfirmButton: false,
          timer: 2000,
        });
      } catch (error) {
        window.Swal.fire({
          toast: true,
          position: 'top-end',
          icon: 'error',
          title: error.response?.data?.message || 'No se pudo guardar la configuración',
          showConfirmButton: false,
          timer: 3000,
        });
      } finally {
        this.guardando = false;
      }
    },
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

.estado-vacio {
  padding: 24px 4px 28px;
  font-size: 13.5px;
  color: var(--text-muted);
  text-align: center;
}

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
</style>