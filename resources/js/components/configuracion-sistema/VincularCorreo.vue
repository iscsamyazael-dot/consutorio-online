<template>
  <div class="container-fluid p-4">
    <!-- Tarjeta principal respetando el estilo de tu sistema -->
    <div class="card-config-wrapper" style="max-width: 600px; margin: 0 auto;">
      
      <!-- Encabezado superior -->
      <div style="background: #0f4c5c; color: #fff; padding: 20px; border-radius: 12px 12px 0 0; display: flex; align-items: center; gap: 15px;">
        <svg viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><path d="M22 6l-10 7L2 6"/></svg>
        <div>
          <h4 style="margin: 0; font-size: 1.1rem; font-weight: 600;">Correo Institucional</h4>
          <p style="margin: 0; font-size: 0.85rem; opacity: 0.85;">Vincula tu cuenta para el envío automatizado de códigos QR y notificaciones.</p>
        </div>
      </div>

      <!-- Cuerpo de la tarjeta con estado y botón -->
      <div style="background: #fff; padding: 40px 30px; border-radius: 0 0 12px 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); text-align: center;">
        
        <!-- Estado: Si YA está vinculado -->
        <div v-if="conectado" style="margin-bottom: 25px;">
          <div style="width: 60px; height: 60px; background: #d1fae5; color: #059669; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 15px auto; font-size: 24px;">
            ✓
          </div>
          <h5 style="color: #1e293b; margin-bottom: 5px; font-weight: 600;">Cuenta vinculada exitosamente</h5>
          <p style="color: #64748b; font-size: 0.9rem; margin: 0;">{{ emailConectado }}</p>
        </div>

        <!-- Estado: Si NO está vinculado (o se omitió en el onboarding) -->
        <div v-else style="margin-bottom: 25px;">
          <div style="width: 60px; height: 60px; background: #f1f5f9; color: #64748b; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 15px auto;">
            <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg>
          </div>
          <h5 style="color: #1e293b; margin-bottom: 5px; font-weight: 600;">Ninguna cuenta conectada</h5>
          <p style="color: #64748b; font-size: 0.9rem; margin: 0;">Conecta tu cuenta de Google o Microsoft para habilitar el envío de correos.</p>
        </div>

        <!-- Botón de Acción OAuth -->
        <a v-if="!conectado" href="/auth/google/redirect" class="btn" style="display: inline-flex; align-items: center; justify-content: center; gap: 10px; background: #ffffff; color: #334155; border: 1px solid #cbd5e1; padding: 12px 24px; border-radius: 8px; font-weight: 600; text-decoration: none; box-shadow: 0 1px 2px rgba(0,0,0,0.05); transition: background 0.2s;">
          <!-- Icono de Google opcional -->
          <svg width="18" height="18" viewBox="0 0 24 24"><path fill="#4285F4" d="M23.745 12.27c0-.7-.06-1.4-.19-2.07H12v4.51h6.6c-.29 1.52-1.14 2.82-2.4 3.68v3.05h3.88c2.27-2.09 3.66-5.17 3.66-9.17z"/><path fill="#34A853" d="M12 24c3.24 0 5.95-1.08 7.93-2.91l-3.88-3.05c-1.08.72-2.45 1.16-4.05 1.16-3.13 0-5.78-2.11-6.73-4.96H1.18v3.14C3.15 21.32 7.23 24 12 24z"/><path fill="#FBBC05" d="M5.27 14.24c-.25-.72-.38-1.49-.38-2.24s.13-1.52.38-2.24V6.6H1.18C.43 8.12 0 9.82 0 11.6s.43 3.48 1.18 5.01l4.09-3.37z"/><path fill="#EA4335" d="M12 4.75c1.77 0 3.35.61 4.6 1.8l3.42-3.42C17.95 1.19 15.24 0 12 0 7.23 0 3.15 2.68 1.18 6.6l4.09 3.14c.95-2.85 3.6-4.99 6.73-4.99z"/></svg>
          Vincular cuenta con Google
        </a>

        <!-- Botón para desconectar si ya está vinculado -->
        <button v-else @click="desconectarCuenta" class="btn" style="background: #fee2e2; color: #991b1b; border: none; padding: 10px 20px; border-radius: 8px; font-weight: 600; cursor: pointer;">
          Desconectar cuenta
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
            conectado: false,
            emailConectado: '',
            cargando: false,
            form: {
                    mail_host: 'smtp.gmail.com',
                    mail_port: 587,
                    mail_username: '',
                    mail_password: '',
                    mail_encryption: 'tls'
                }
            };
        },
        created() {
            this.verificarEstatusCorreo();
        },
        methods: {
            async verificarEstatusCorreo() {
            // Aquí puedes hacer un GET rápido a tu API para saber si la empresa ya tiene token guardado
            try {
                const response = await ApiService.get('estatus-correo');
                this.conectado = response.data.conectado;
                this.emailConectado = response.data.email;
            } catch (error) {
                console.error("Error al verificar estatus del correo", error);
            }
            },
            // FUNCIÓN DE ACTUALIZACIÓN (Para rellenar los campos vacíos si omitió el onboarding)
            async actualizarCorreo() {
                this.cargando = true;
                try {
                    // Usamos ApiService con patch para actualizar la fila existente de la empresa
                    const response = await ApiService.patch('actualizar-correo', this.form);
                    
                    Swal.fire({
                        icon: 'success',
                        title: 'Listo',
                        text: response.data.message || 'Configuración actualizada correctamente.',
                        confirmButtonColor: '#0f4c5c'
                    });
                    this.verificarEstatusCorreo(); // Refrescamos el estatus
                } catch (error) {
                    console.error("Error al actualizar el correo:", error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Ocurrió un error al actualizar los datos.',
                        confirmButtonColor: '#0f4c5c'
                    });
                } finally {
                    this.cargando = false;
                }
            },
            async desconectarCuenta() {
                const confirmacion = await Swal.fire({
                    icon: 'warning',
                    title: '¿Desconectar cuenta?',
                    text: '¿Estás seguro de que deseas desconectar el correo?',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, desconectar',
                    cancelButtonText: 'Cancelar',
                    confirmButtonColor: '#991b1b',
                    cancelButtonColor: '#64748b',
                    reverseButtons: true
                });

                if (!confirmacion.isConfirmed) return;

                this.cargando = true;
                try {
                    await ApiService.post('desconectar-correo');
                    this.conectado = false;
                    this.emailConectado = '';

                    Swal.fire({
                        icon: 'success',
                        title: 'Desconectado',
                        text: 'Cuenta desconectada correctamente.',
                        confirmButtonColor: '#0f4c5c'
                    });
                } catch (error) {
                    console.error("Error al desconectar:", error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Ocurrió un error al desconectar la cuenta.',
                        confirmButtonColor: '#0f4c5c'
                    });
                } finally {
                    this.cargando = false;
                }
            }
        }
    };
</script>