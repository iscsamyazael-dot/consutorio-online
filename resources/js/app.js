console.log('APP JS CARGADO');

import './bootstrap';
// import './medicamentosJs/receta.js';

import 'bootstrap';
import 'admin-lte';

import { createApp } from 'vue';
import ConsultaInteligente from './components/ConsultaInteligente.vue'
import medicamentos from './components/Medicamentos/PanelMedicamento.vue'
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
    'alerta-farmacia',
    AlertaFarmacia
);

app.mount('#app')

import Alpine from 'alpinejs';
window.Alpine = Alpine;
Alpine.start();

import axios from  'axios';
window.axios = axios;