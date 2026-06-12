import './bootstrap';
//import './medicamentosJs/receta.js';

import 'bootstrap';
import 'admin-lte';

import { createApp } from 'vue';

// import vSelect from 'vue-select';
// import 'vue-select/dist/vue-select.css';

// 1. IMPORTACIONES (Verifica que las rutas y mayúsculas coincidan con tus archivos reales)
import ConsultaInteligente from './components/ConsultaInteligente.vue';
import PacientesIndex from './components/pasientes/pacientesIndex.vue';
import CreateConsulta from './components/consultas/CreateConsulta.vue'; // <-- Corregida 'C' mayúscula
import IndexConsultas from './components/consultas/IndexConsultas.vue';
import NuevaConsulta from './components/NuevaConsulta.vue'; // <-- Importación agregada (ajusta la ruta si está en otra carpeta)
import InformacionPaciente    from './components/pasientes/masterregistroN.vue'; // ✅ variable correcta
import expedientepaciente from './components/pasientes/maesterexpediente.vue';
import consultapaciente from './components/consultas/masterindividual.vue';// historial de las consultas del pacinte //


const app = createApp({});
// Registrar Vue Select globalmente
//app.component('v-select', vSelect);

app.component(
    'consultapaciente',
    consultapaciente 
)

app.component(
    'expedientepaciente',
    expedientepaciente
 
)

app.component('master-registro-paciente', InformacionPaciente);
// 2. REGISTRO DE COMPONENTES
app.component(
    'index-consultas',
    IndexConsultas
);

app.component(
    'crear-consulta',
    CreateConsulta // <-- Corregido para que coincida exactamente con la importación de arriba
);

app.component(
    'nueva-consulta',
    NuevaConsulta // <-- Corregido para usar la variable importada
);

app.component(
    'pacientes-index',
    PacientesIndex
);

app.component(
    'consulta-inteligente',
    ConsultaInteligente
);

// 3. MONTAJE DE LA APP
app.mount('#app');

// 4. OTROS COMPLEMENTOS
import Alpine from 'alpinejs';
window.Alpine = Alpine;
Alpine.start();

import axios from 'axios';
window.axios = axios;
import Swal from 'sweetalert2';
import Maesterexpediente from './components/pasientes/maesterexpediente.vue';
window.Swal = Swal;