console.log('APP JS CARGADO');
import './bootstrap';
import 'bootstrap';
import 'admin-lte';

import { createApp } from 'vue';
import { createPinia } from 'pinia'; // 👈 NUEVO
import NotificationBell from './components/NotificationBell.vue'; // 👈 NUEVO

// Componentes - Medicamentos
import medicamentos from './components/Medicamentos/PanelMedicamento.vue'
// Componentes - Consulta Inteligente
import ConsultaInteligente from './components/consultaIA/ConsultaInteligente.vue'
// Componentes - Atención Médica
import TRIAGE from './components/atencion-medica/AtencionMedica.vue'
import EvaluacionIA from './components/atencion-medica/EvaluacionesIA.vue'
import ArchivosClinico from './components/atencion-medica/ArchivosClinicos.vue'
import Derivacion from './components/atencion-medica/Derivaciones.vue'
// Componentes - Pacientes
import PacientesIndex from './components/pasientes/pacientesindex.vue'
import InformacionPaciente from './components/pasientes/masterregistroN.vue'
import expedientepaciente from './components/pasientes/maesterexpediente.vue'
// Componentes - Consultas
import CreateConsulta from './components/consultas/CreateConsulta.vue'
import IndexConsultas from './components/consultas/IndexConsultas.vue'
import NuevaConsulta from './components/consultaIA/NuevaConsulta.vue'
import consultapaciente from './components/consultas/masterindividual.vue'
import nuevaconsultamedica from './components/consultas/maesternuevaconsulta.vue'
import centroconsultas from './components/consultas/mastercentro.vue'
// Módulo de recetas
import Historialreceta from './components/recetass/PanelRecetas.vue'
// Módulo de especialidades
import Especialidades from './components/Especialidades/Especialidades.vue'
//Modulo de inventario de medicamentos
import AlertaFarmacia from './components/Medicamentos/alertasMedicamentos.vue'

//MODULO DE ALTA MEDICOS
import AltaMedicos from './components/medicos-alta/PanelMedicos.vue'
import RegistroMedico from './components/medicos-alta/PanelRegistro.vue'
// Módulo de agenda
import masteragenda from './components/citasmedicas/mastercitas.vue'
import masterprocita from './components/citasmedicas/masterprograma.vue'
// Módulo de perfil / configuración
import ConfiSistema from './components/configuracion-sistema/ConfiSistema.vue'
import PanelContraseña from './components/configuracion-sistema/PanelContraseña.vue'
// Módulo de ubicaciones
import PanelTabla from './components/configuracion-sistema/PanelTabla.vue'
//Modulo de registro de usuario 
import RegistrarUsuario from './components/configuracion-sistema/PanelUsuario.vue'
import Ubicacionesmaster from './components/Ubicaciones/Ubicacionesmaster.vue';
import DashboardHome from './components/Dashboard/Home.vue'

// ------------------------------------------------------
// Instancia única de la app
// ------------------------------------------------------
const app = createApp({});

app.use(createPinia()); // 👈 NUEVO

app.component('Especialidades', Especialidades);
app.component('Ubicacionesmaster', Ubicacionesmaster);
app.component('masterprocita', masterprocita);
app.component('masteragenda', masteragenda);
app.component('alta-medicos', AltaMedicos);
app.component('registro-medicos', RegistroMedico);
app.component('consulta-inteligente', ConsultaInteligente);
app.component('medicamentos-inventario', medicamentos);
app.component('atencion-medica-evaluacionia', EvaluacionIA);
app.component('alerta-farmacia', AlertaFarmacia);
app.component('atencion-medica-archivosclinicos', ArchivosClinico);
app.component('atencion-medica-derivaciones', Derivacion);
app.component('atencion-medica', TRIAGE);
app.component('centroconsultas', centroconsultas);
app.component('nuevaconsultamedica', nuevaconsultamedica);
app.component('consultapaciente', consultapaciente);
app.component('expedientepaciente', expedientepaciente);
app.component('master-registro-paciente', InformacionPaciente);
app.component('index-consultas', IndexConsultas);
app.component('crear-consulta', CreateConsulta);
app.component('nueva-consulta', NuevaConsulta);
app.component('pacientes-index', PacientesIndex);
app.component('recetass-historial', Historialreceta);
app.component('configuracion-sistema', ConfiSistema);
app.component('configuracion-sistema-panelcontrasena', PanelContraseña);
app.component('registro-usuario', RegistrarUsuario);
app.component('panel-tabla', PanelTabla);
app.component('dashboard-home', DashboardHome);

// 3. MONTAJE DE LA APP
app.mount('#app');

// ------------------------------------------------------
// 👈 NUEVO: Campanita de notificaciones (vive en la barra de
// navegación, fuera del #app de cada página, así que se monta aparte)
// ------------------------------------------------------
const pinia = createPinia();

function mountBell(selector) {
    const el = document.querySelector(selector);
    if (!el) return; // el contenedor no existe en esta página/breakpoint, se omite
    const bellApp = createApp(NotificationBell);
    bellApp.use(pinia);
    bellApp.mount(el);
}

document.addEventListener('DOMContentLoaded', () => {
    mountBell('#notification-bell-app');
    mountBell('#notification-bell-app-mobile');
});

// ------------------------------------------------------
// Otros complementos
// ------------------------------------------------------
import Alpine from 'alpinejs';
window.Alpine = Alpine;
Alpine.start();
import axios from 'axios';
window.axios = axios;
import Swal from 'sweetalert2';
window.Swal = Swal;