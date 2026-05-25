console.log('APP JS CARGADO');

import './bootstrap';
// import './medicamentosJs/receta.js';

import 'bootstrap';
import 'admin-lte';

import { createApp } from 'vue';
import medicamentos from './components/Medicamentos/PanelMedicamento.vue'
import ConsultaInteligente from './components/ConsultaInteligente.vue'
import TRIAGE from './components/atencion-medica/AtencionMedica.vue'
import EvaluacionIA from './components/atencion-medica/EvaluacionesIA.vue'
import ArchivosClinico from './components/atencion-medica/ArchivosClinicos.vue'
import Derivacion from './components/atencion-medica/Derivaciones.vue'

const app = createApp({});

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





app.mount('#app')

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

import axios from 'axios';

window.axios = axios;