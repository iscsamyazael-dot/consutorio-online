<template>
    <div class="list-view">

        <!-- ==========================================
             CABECERA DE LA LISTA
        =========================================== -->
        <div class="card-head">

            <div class="card-title">

                <div class="card-title-icon">
                    <svg
                        width="18"
                        height="18"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <path d="M3 21h18" />
                        <path d="M6 21V7l6-4 6 4v14" />
                        <path d="M9 21v-6h6v6" />
                        <path d="M9 10h.01" />
                        <path d="M15 10h.01" />
                    </svg>
                </div>

                <div>
                    <h2>Clientes registrados</h2>

                    <p>
                        Consultorios registrados en la plataforma
                    </p>
                </div>

            </div>

            <!-- ======================================
                 HERRAMIENTAS
            ======================================= -->
            <div class="list-tools">

                <div class="search">

                    <svg
                        width="16"
                        height="16"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <circle cx="11" cy="11" r="7" />
                        <path d="M21 21l-4.3-4.3" />
                    </svg>

                    <input
                        type="text"
                        v-model="search"
                        placeholder="Buscar cliente..."
                    />

                </div>

            </div>

        </div>


        <!-- ==========================================
             TABLA
        =========================================== -->
        <div class="table-wrapper">

            <table>

                <thead>
                    <tr>
                        <th>Folio</th>
                        <th>Consultorio</th>
                        <th>Base de datos</th>
                        <th>Dominio / correo</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>


                <tbody>

                    <!-- ==================================
                         CLIENTES
                    =================================== -->
                    <tr
                        v-for="cliente in filteredClientes" :key="cliente.id">

                        <!-- FOLIO -->
                        <td>
                            <span class="folio">
                                {{ cliente.folio }}
                            </span>
                        </td>


                        <!-- CONSULTORIO -->
                        <td>
                            <div class="cliente-name">
                                {{ cliente.nombre_consultorio }}
                            </div>

                            <div class="cliente-sub">
                                Consultorio médico
                            </div>
                        </td>


                        <!-- BASE DE DATOS -->
                        <td>
                            <span class="db-chip">
                                {{ cliente.db_name }}
                            </span>
                        </td>


                        <!-- DOMINIO -->
                        <td>
                            <span class="domain-text">
                                {{ cliente.dominio_correo }}
                            </span>
                        </td>


                        <!-- ESTADO -->
                        <td>

                            <span
                                class="pill"
                                :class="
                                    cliente.estatus === 'activo'
                                        ? 'pill-active'
                                        : 'pill-inactive'
                                "
                            >
                                {{
                                    cliente.estatus === 'activo'
                                        ? 'Activo'
                                        : 'Inactivo'
                                }}
                            </span>
                        </td>

                        <!-- ACCIONES -->
                        <td>
                            <div class="row-actions">
                                <!-- EDITAR -->
                                <button
                                    type="button"
                                    aria-label="Editar cliente"
                                    title="Editar cliente"
                                    @click="$emit('editar-cliente', cliente.id)"
                                >
                                    <svg
                                        width="14"
                                        height="14"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                    >
                                        <path d="M12 20h9" />
                                        <path
                                            d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"
                                        />
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>


                    <!-- ==================================
                         SIN RESULTADOS
                    =================================== -->
                    <tr v-if="filteredClientes.length === 0">
                        <td
                            colspan="6"
                            class="empty-state"
                        >
                            <div class="empty-state-icon">
                                <svg
                                    width="22"
                                    height="22"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                >
                                    <circle
                                        cx="11"
                                        cy="11"
                                        r="7"
                                    />

                                    <path
                                        d="M21 21l-4.3-4.3"
                                    />
                                </svg>
                            </div>
                            <div>
                                <strong>
                                    No se encontraron clientes
                                </strong>
                                <span>
                                    Intenta modificar el término de búsqueda.
                                </span>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>


<script>
import ApiService from '../../services/ApiService.js'
export default {

    name: 'TenantList',

    // NOTA: por ahora este componente sigue haciendo su propio fetch (ver
    // obtenerClientes) y por eso la prop 'tenants' quedó sin usar. La dejo
    // comentada como referencia hasta que decidamos una sola fuente de
    // verdad (ver mi pregunta al final de la respuesta).
    // props: {
    //     tenants: {
    //         type: Array,
    //         required: true
    //     }
    // },

    data() {

        return {
            clientes: [],
            search: ''
        }

    },

    computed: {

        filteredClientes() {

            const term = this.search
                .toLowerCase()
                .trim()

            if (!term) {
                return this.clientes
            }

            return this.clientes.filter(cliente => {

                return (

                    cliente.folio
                        ?.toLowerCase()
                        .includes(term)

                    ||

                    cliente.nombre_consultorio
                        ?.toLowerCase()
                        .includes(term)

                    ||

                    cliente.db_name
                        ?.toLowerCase()
                        .includes(term)

                    ||

                    cliente.dominio_correo
                        ?.toLowerCase()
                        .includes(term)

                    ||

                    cliente.estatus
                        ?.toLowerCase()
                        .includes(term)

                )

            })

        }

    },

    mounted(){
        this.obtenerClientes();
    },

    methods:{
        async obtenerClientes() {
            try {
                const response = await ApiService.get('inquilinos');
                // Asignas directamente la lista que te da el servidor
                this.clientes = response.data;
                console.log('Clientes encontrodos:', this.clientes);
            } catch (error) {
                console.error('Error al obtener lista de clientes:', error);
            }
        },
    }


}
</script>