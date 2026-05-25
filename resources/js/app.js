
import './bootstrap';
//import './medicamentosJs/receta.js';

import 'bootstrap';
import 'admin-lte';

import { createApp } from 'vue';
import ConsultaInteligente from './components/ConsultaInteligente.vue'
import PacientesIndex from './components/pasientes/pacientesindex.vue'

const app = createApp({})

app.component(
    'pacientes-index',
    PacientesIndex
)


app.component(
    'consulta-inteligente',
    ConsultaInteligente
)
app.mount('#app')

import Alpine from 'alpinejs';
window.Alpine = Alpine;
Alpine.start();

import axios from  'axios';
window.axios = axios;

