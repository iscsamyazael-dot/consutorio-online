console.log('APP JS CARGADO');

import './bootstrap';
// import './medicamentosJs/receta.js';
//import './medicamentosJs/receta.js';

import 'bootstrap';
import 'admin-lte';

import { createApp } from 'vue';
import medicamentos from './components/Medicamentos/PanelMedicamento.vue'
import ConsultaInteligente from './components/ConsultaInteligente.vue'
import TRIAGE from './components/atencion-medica/AtencionMedica.vue'
import EvaluacionIA from './components/atencion-medica/EvaluacionesIA.vue'
import ArchivosClinico from './components/atencion-medica/ArchivosClinicos.vue'
import Derivacion from './components/atencion-medica/Derivaciones.vue'
import PacientesIndex from './components/pasientes/pacientesindex.vue'

// 1. IMPORTACIONES (Verifica que las rutas y mayúsculas coincidan con tus archivos reales)
import ConsultaInteligente from './components/ConsultaInteligente.vue';
import PacientesIndex from './components/pasientes/pacientesIndex.vue';
import CreateConsulta from './components/consultas/CreateConsulta.vue'; // <-- Corregida 'C' mayúscula
import IndexConsultas from './components/consultas/IndexConsultas.vue';
import NuevaConsulta from './components/NuevaConsulta.vue'; // <-- Importación agregada (ajusta la ruta si está en otra carpeta)

//Modulo de inventario de medicamentos
import AlertaFarmacia from './components/Medicamentos/alertasMedicamentos.vue'



const app = createApp({})

app.component(
     'consulta-inteligente',
     ConsultaInteligente
 );

app.component(
    'medicamentos-inventario',
    medicamentos
);

app.component(
    'atencion-medica-evaluacionia',
    EvaluacionIA
);
app.component(
    'alerta-farmacia',
    AlertaFarmacia
);

app.mount('#app')

app.component(
    'atencion-medica-archivosclinicos',
    ArchivosClinico
);

app.component(
    'atencion-medica-derivaciones',
    Derivacion
);

app.component(
    'atencion-medica',
     TRIAGE
);

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
import axios from  'axios';
window.axios = axios;

window.axios = axios;
