<template>
  <div class="container-fluid px-0">
    <!-- Header con 2 botones -->
    <HeaderEmpresasCliente
      v-if="vistaActual === 'tabla'"
      :filtros="filtros"
      @ir-a-registro="vistaActual = 'registro'"
      @abrir-importar="abrirModalImportar"
      @filtro-cambiado="aplicarFiltros"
      @actualizar-buscar="actualizarBuscar"
      @actualizar-activo="actualizarActivo"
    ></HeaderEmpresasCliente>

    <!-- Tabla de empresas -->
    <TablaEmpresasCliente
      v-if="vistaActual === 'tabla'"
      :empresas="lista.data"
      :cargando="cargando"
      :lista="lista"
      @editar="abrirEdicion"
      @eliminar="confirmarEliminacion"
      @filtrar-por-estado="filtrarPorEstadoTabla"
      @buscar-empresas="buscarEmpresasTabla"
      @pagina-cambiada="cambiarPagina"
    ></TablaEmpresasCliente>

    <!-- Formulario de empresa -->
    <FormEmpresaCliente
      v-else-if="vistaActual === 'registro'"
      :empresa="empresaSeleccionada"
      @guardado="empresaGuardada"
      @cancelar="vistaActual = 'tabla'"
    ></FormEmpresaCliente>

    <!-- ✅ Modal único de Importación -->
    <ImportarExcelModal 
      :show="mostrarModalImportar" 
      tipo="empresa" 
      @close="mostrarModalImportar = false"
      @success="onImportacionExitosa"
    />
  </div>
</template>

<script>
import HeaderEmpresasCliente from './HeaderEmpresasCliente.vue';
import TablaEmpresasCliente from './TablaEmpresasCliente.vue';
import FormEmpresaCliente from './FormEmpresaCliente.vue';
import ImportarExcelModal from '../Pacientes/ImportarExcelModal.vue';
import ApiServiceClinicaTrabajo from '@/services/ApiServiceClinicaTrabajo.js';
import Swal from 'sweetalert2';

export default {
  components: {
    HeaderEmpresasCliente,
    TablaEmpresasCliente,
    FormEmpresaCliente,
    ImportarExcelModal
  },
  data() {
    return {
      vistaActual: 'tabla',
      lista: {
        data: [],
        current_page: 1,
        last_page: 1,
        total: 0
      },
      filtros: {
        buscar: '',
        activo: ''
      },
      pagina: 1,
      cargando: false,
      empresaSeleccionada: null,
      mostrarModalImportar: false
    }
  },
  methods: {
    async cargarLista() {
      this.cargando = true;
      try {
        const response = await ApiServiceClinicaTrabajo.get('/empresa-cliente', {
          params: {
            buscar: this.filtros.buscar,
            activo: this.filtros.activo,
            page: this.pagina
          }
        });
        
        if (response.data) {
          this.lista = response.data;
        } else if (response) {
          this.lista = response;
        }
      } catch (error) {
        console.error('Error al cargar la lista de empresas:', error);
        Swal.fire({
          title: 'Error',
          text: 'No se pudo cargar la lista de empresas',
          icon: 'error',
          confirmButtonText: 'Aceptar'
        });
      } finally {
        this.cargando = false;
      }
    },
    aplicarFiltros(filtros) {
      this.pagina = 1;
      this.filtros = filtros;
      this.cargarLista();
    },
    actualizarBuscar(buscar) {
      this.filtros.buscar = buscar;
    },
    actualizarActivo(activo) {
      this.filtros.activo = activo;
    },
    filtrarPorEstadoTabla(estado) {
      this.filtros.activo = estado === '' ? null : estado;
      this.pagina = 1;
      this.cargarLista();
    },
    buscarEmpresasTabla(busqueda) {
      this.filtros.buscar = busqueda;
      this.pagina = 1;
      this.cargarLista();
    },
    cambiarPagina(pagina) {
      this.pagina = pagina;
      this.cargarLista();
    },
    async abrirEdicion(id) {
      this.cargando = true;
      try {
        const response = await ApiServiceClinicaTrabajo.get('/empresa-cliente/' + id);
        this.empresaSeleccionada = response.data || response;
        this.vistaActual = 'registro';
      } catch (error) {
        console.error('Error al obtener la empresa:', error);
        Swal.fire({
          title: 'Error',
          text: 'No se pudo obtener los datos de la empresa',
          icon: 'error',
          confirmButtonText: 'Aceptar'
        });
      } finally {
        this.cargando = false;
      }
    },
    async empresaGuardada() {
      this.vistaActual = 'tabla';
      this.empresaSeleccionada = null;
      await this.cargarLista();
      Swal.fire({
        title: 'Éxito',
        text: 'Empresa guardada correctamente',
        icon: 'success',
        confirmButtonText: 'Aceptar'
      });
    },
    async confirmarEliminacion(id) {
      const result = await Swal.fire({
        title: '¿Estás seguro?',
        text: 'Esta acción dará de baja la empresa y no se podrá deshacer',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Dar de baja',
        cancelButtonText: 'Cancelar'
      });

      if (result.isConfirmed) {
        await this.eliminarEmpresa(id);
      }
    },
    async eliminarEmpresa(id) {
      this.cargando = true;
      try {
        await ApiServiceClinicaTrabajo.delete('/empresa-cliente/' + id);
        await this.cargarLista();
        Swal.fire({
          title: 'Éxito',
          text: 'Empresa dada de baja correctamente',
          icon: 'success',
          confirmButtonText: 'Aceptar'
        });
      } catch (error) {
        console.error('Error al dar de baja la empresa:', error);
        Swal.fire({
          title: 'Error',
          text: 'No se pudo dar de baja la empresa',
          icon: 'error',
          confirmButtonText: 'Aceptar'
        });
      } finally {
        this.cargando = false;
      }
    },
    abrirModalImportar() {
      this.mostrarModalImportar = true;
    },
    onImportacionExitosa() {
      this.cargarLista();
    }
  },
  mounted() {
    this.cargarLista();
  }
}
</script>