import '../css/app.css'
import { createApp } from 'vue'
import Kioscoautoregistro from './components/Kiosco/Kioscoautoregistro.vue'
import KioscoPantallaTV from './components/Kiosco/listaEsperaTV.vue'

const app = createApp({});

app.component('pantalla-kiosco',Kioscoautoregistro);
app.component('pantalla-kiosco-tv',KioscoPantallaTV);

app.mount('#app');