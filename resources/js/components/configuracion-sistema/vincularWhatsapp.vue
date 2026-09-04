<template>
  <div class="container-fluid p-4">
    <div class="card-config-wrapper" style="max-width: 600px; margin: 0 auto;">

      <!-- Encabezado -->
      <div style="background: #0f4c5c; color: #fff; padding: 20px; border-radius: 12px 12px 0 0; display: flex; align-items: center; gap: 15px;">
            <i class="fab fa-whatsapp" style="font-size: 28px; color: #fff;"></i>
        <div>
          <h4 style="margin: 0; font-size: 1.1rem; font-weight: 600;">WhatsApp del Consultorio</h4>
          <p style="margin: 0; font-size: 0.85rem; opacity: 0.85;">Vincula el número de WhatsApp para el envío automático de recordatorios y confirmaciones.</p>
        </div>
      </div>

      <div style="background: #fff; padding: 40px 30px; border-radius: 0 0 12px 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); text-align: center;">

        <!-- Estado: Conectado -->
        <div v-if="estado === 'WORKING'" style="margin-bottom: 25px;">
          <div style="width: 60px; height: 60px; background: #d1fae5; color: #059669; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 15px auto; font-size: 24px;">
            ✓
          </div>
          <h5 style="color: #1e293b; margin-bottom: 5px; font-weight: 600;">WhatsApp vinculado exitosamente</h5>
          <p style="color: #64748b; font-size: 0.9rem; margin: 0;">{{ numeroConectado }}</p>
        </div>

        <!-- Estado: Esperando escaneo de QR -->
        <div v-else-if="qr" style="margin-bottom: 25px;">
          <h5 style="color: #1e293b; margin-bottom: 10px; font-weight: 600;">Escanea el código QR</h5>
          <p style="color: #64748b; font-size: 0.85rem; margin-bottom: 15px;">Abre WhatsApp en el teléfono del consultorio → Dispositivos vinculados → Vincular dispositivo.</p>
          <img :src="qr" alt="QR WhatsApp" style="width: 240px; height: 240px; border: 1px solid #e2e8f0; border-radius: 8px; padding: 10px;" />
          <p style="color: #94a3b8; font-size: 0.8rem; margin-top: 10px;">Esperando escaneo...</p>
        </div>

        <!-- Estado: Nada vinculado -->
        <div v-else style="margin-bottom: 25px;">
            <div style="width: 60px; height: 60px; background: #25D366; color: #fff; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 15px auto;">
                <i class="fab fa-whatsapp" style="font-size: 32px;"></i>
            </div>
          <h5 style="color: #1e293b; margin-bottom: 5px; font-weight: 600;">Ningún número conectado</h5>
          <p style="color: #64748b; font-size: 0.9rem; margin: 0;">Vincula el WhatsApp del consultorio para habilitar los mensajes automáticos.</p>
        </div>

        <!-- Alerta: vinculado pero desconectado -->
        <div v-if="estado && estado !== 'WORKING' && estado !== 'SCAN_QR_CODE' && wahaConfigurado" style="background:#fef3c7; color:#92400e; padding:12px; border-radius:8px; margin-bottom:20px; font-size:0.85rem;">
          ⚠️ La sesión de WhatsApp está desconectada ({{ estado }}). Los mensajes automáticos no se están enviando.
        </div>

        <!-- Botones -->
        <button v-if="!estado || (estado !== 'WORKING' && estado !== 'SCAN_QR_CODE')" @click="iniciarVinculacion" :disabled="cargando" class="btn" style="background:#059669; color:#fff; border:none; padding:12px 24px; border-radius:8px; font-weight:600; cursor:pointer;">
          {{ cargando ? 'Generando QR...' : (wahaConfigurado ? 'Reconectar WhatsApp' : 'Vincular WhatsApp') }}
        </button>

        <button v-if="estado === 'WORKING'" @click="iniciarVinculacion" :disabled="cargando" class="btn" style="background:#fee2e2; color:#991b1b; border:none; padding:10px 20px; border-radius:8px; font-weight:600; cursor:pointer;">
          Vincular otro número
        </button>

      </div>
    </div>
  </div>
</template>

<script>
import ApiService from '../../services/ApiService.js'

export default {
  data() {
    return {
      estado: null,          // WORKING, SCAN_QR_CODE, STOPPED, etc.
      numeroConectado: '',
      wahaConfigurado: false,
      qr: null,
      cargando: false,
      pollingInterval: null,
    };
  },
  created() {
    this.verificarEstatus();
  },
  beforeUnmount() {
    this.detenerPolling();
  },
  methods: {
    async verificarEstatus() {
      try {
        const response = await ApiService.get('waha/estatus');
        this.estado = response.data.status;
        this.wahaConfigurado = response.data.status === 'WORKING';
        if (response.data.status === 'WORKING') {
          this.numeroConectado = response.data.numero || '';
          this.qr = null;
          this.detenerPolling();
        }
      } catch (error) {
        console.error('Error al verificar estatus de WhatsApp', error);
      }
    },

    async iniciarVinculacion() {
      this.cargando = true;
      try {
        await ApiService.post('waha/iniciar');
        await this.obtenerQr();
        this.iniciarPolling();
      } catch (error) {
        console.error('Error al iniciar sesión de WhatsApp', error);
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: 'No se pudo iniciar la vinculación de WhatsApp.',
          confirmButtonColor: '#0f4c5c'
        });
      } finally {
        this.cargando = false;
      }
    },

    async obtenerQr() {
      try {
        const response = await ApiService.get('waha/qr');
        this.qr = response.data.qr;
        this.estado = 'SCAN_QR_CODE';
      } catch (error) {
        console.error('Error al obtener el QR', error);
      }
    },

    iniciarPolling() {
      this.detenerPolling();
      this.pollingInterval = setInterval(async () => {
        await this.verificarEstatus();
        if (this.estado === 'WORKING') {
          this.detenerPolling();
          Swal.fire({
            icon: 'success',
            title: 'Listo',
            text: 'WhatsApp vinculado correctamente.',
            confirmButtonColor: '#0f4c5c'
          });
        }
      }, 4000); // cada 4 segundos
    },

    detenerPolling() {
      if (this.pollingInterval) {
        clearInterval(this.pollingInterval);
        this.pollingInterval = null;
      }
    },
  }
};
</script>