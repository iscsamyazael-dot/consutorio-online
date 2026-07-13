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
import CreateConsulta from './components/consultas/CreateConsulta.vue' // <-- Corregida 'C' mayúscula
import IndexConsultas from './components/consultas/IndexConsultas.vue'
import NuevaConsulta from './components/NuevaConsulta.vue' // <-- Importación agregada (ajusta la ruta si está en otra carpeta)
import InformacionPaciente    from './components/pasientes/masterregistroN.vue' // ✅ variable correcta
import expedientepaciente from './components/pasientes/maesterexpediente.vue'
import consultapaciente from './components/consultas/masterindividual.vue'// historial de las consultas del pacinte //
import nuevaconsultamedica from'./components/consultas/maesternuevaconsulta.vue'
import centroconsultas from './components/consultas/mastercentro.vue'


import Especialidades from './components/Especialidades/Especialidades.vue'// importa especialidades 
//Modulo de inventario de medicamentos
import AlertaFarmacia from './components/Medicamentos/alertasMedicamentos.vue'

//MODULO DE ALTA MEDICOS
import AltaMedicos from './components/medicos-alta/PanelMedicos.vue'
import RegistroMedico from './components/medicos-alta/PanelRegistro.vue'
 
//MODULO AGENDA 
import masteragenda from './components/citasmedicas/mastercitas.vue'
import masterprocita from './components/citasmedicas/masterprograma.vue'

//Modulo de Perfil
import ConfiSistema from './components/configuracion-sistema/ConfiSistema.vue'
import PanelContraseña from './components/configuracion-sistema/PanelContraseña.vue'

//Modulo de Historial de recetas 
import Historialreceta from './components/recetass/PanelRecetas.vue'
//Modulo de registro de usuario 
import RegistrarUsuario from './components/configuracion-sistema/PanelUsuario.vue'

import Ubicacionesmaster from './components/Ubicaciones/Ubicacionesmaster.vue';

const app = createApp({})

app.component(
    'Especialidades',
    Especialidades
);

app.component(
    'Ubicacionesmaster',
     Ubicacionesmaster
    );

app.component(
    'masterprocita',
    masterprocita
);

app.component(
    'masteragenda',
    masteragenda
);

app.component(
     'alta-medicos',
     AltaMedicos
);

app.component(
     'registro-medicos',
     RegistroMedico
);

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


app.component(
    'centroconsultas',
    centroconsultas
);

app.component(
    'nuevaconsultamedica',
    nuevaconsultamedica
);

app.component(
    'consultapaciente',
    consultapaciente 
);

app.component(
    'expedientepaciente',
    expedientepaciente
);

app.component(
    'master-registro-paciente', 
    InformacionPaciente
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
    'recetass-historial',
    Historialreceta
);
app.component(
    'configuracion-sistema',
    ConfiSistema
);

app.component(
    'configuracion-sistema-panelcontrasena',
    PanelContraseña
);


app.component(
    'recetass-historial',
    Historialreceta
);

app.component(
     'registro-usuario',
     RegistrarUsuario
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
window.Swal = Swal;

