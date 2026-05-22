import './bootstrap';
// import './medicamentosJs/receta.js';

import 'bootstrap';
import 'admin-lte';

import { createApp } from 'vue';

import ConsultaInteligente from './components/ConsultaInteligente.vue'

import TRIAGE from './components/atencion-medica/AtencionMedica.vue'
import EvaluacionIA from './components/atencion-medica/EvaluacionesIA.vue'


const app = createApp({});


app.component(
    'consulta-inteligente',
    ConsultaInteligente
)


app.component(
    'atencion-medica',
    TRIAGE
);

app.component(
    'atencion-medica-evaluacionia',
    EvaluacionIA
);


app.mount('#app')

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

import axios from 'axios';

window.axios = axios;