<template>
  <div class="seccion-parametros-fvl-espirometria">
    <div class="card mb-3">
      <div class="card-header" style="background: linear-gradient(135deg, #43A047 0%, #2E7D32 100%); color: white;">
        <h5 class="mb-0">
          <i class="fas fa-chart-line mr-2"></i> PARÁMETROS FVL - MÚLTIPLES MANIOBRAS
        </h5>
      </div>
      <div class="card-body">

        <!-- SECCIÓN DE IMPORTACIÓN CON IA -->
        <div class="card mb-4 border-primary">
          <div class="card-header bg-primary text-white">
            <h6 class="mb-0"><i class="fas fa-robot mr-2"></i>Importar Informe de Espirometría (IA)</h6>
          </div>
          <div class="card-body">
            <div class="row align-items-end">
              <div class="col-md-8 mb-3">
                <label class="font-weight-bold">Seleccionar informe (PDF o Excel)</label>
                <div class="custom-file">
                  <input type="file" class="custom-file-input" id="archivoEspirometria" accept=".pdf,.xls,.xlsx" @change="seleccionarArchivo">
                  <label class="custom-file-label" for="archivoEspirometria">{{ nombreArchivo || 'Elegir archivo...' }}</label>
                </div>
                <small class="text-muted d-block mt-1">
                  <i class="fas fa-info-circle"></i> Sube el PDF generado por el equipo (ndd, Vitalograph, etc.)
                </small>
              </div>
              <div class="col-md-4 mb-3">
                <button type="button" class="btn btn-primary btn-block" @click="importarConIA" :disabled="!archivoSeleccionado || procesandoIA">
                  <i :class="procesandoIA ? 'fas fa-spinner fa-spin' : 'fas fa-magic'" class="mr-2"></i>
                  {{ procesandoIA ? 'Analizando...' : 'Extraer Datos con IA' }}
                </button>
              </div>
            </div>

            <div v-if="mensajeIA" class="alert mt-3" :class="exitoIA ? 'alert-success' : 'alert-danger'">
              <i :class="exitoIA ? 'fas fa-check-circle' : 'fas fa-exclamation-triangle'" class="mr-2"></i>
              {{ mensajeIA }}
            </div>
            <div v-if="advertencias.length" class="alert alert-warning mt-3">
              <strong><i class="fas fa-exclamation-circle mr-1"></i>Revisa antes de guardar:</strong>
              <ul class="mb-0 mt-2 pl-3">
                <li v-for="(adv, i) in advertencias" :key="i">{{ adv }}</li>
              </ul>
            </div>
          </div>
        </div>

        <!-- CONFIGURACIÓN DE REFERENCIA -->
        <div class="row mb-4">
          <div class="col-md-4 mb-3">
            <label class="font-weight-bold">Ecuación de Referencia <span class="text-danger">*</span></label>
            <select v-model="localForm.referencia" @change="emitChange" class="form-control" style="height: 38px;">
              <option value="NHANES III">NHANES III (EE.UU.)</option>
              <option value="GLI-2012">GLI-2012 (Global)</option>
              <option value="Hankinson">Hankinson (EE.UU.)</option>
              <option value="Lloveras">Lloveras (Latinoamérica)</option>
              <option value="Pellegrino">Pellegrino (Europa)</option>
            </select>
          </div>
          <div class="col-md-4 mb-3">
            <label class="font-weight-bold">Interpretación Predicha *</label>
            <select v-model="localForm.interpretacion_predicha" @change="emitChange" class="form-control" style="height: 38px;">
              <option value="GOLD(2008)/Hardie">GOLD (2008) / Hardie</option>
              <option value="ATS/ERS 2021">ATS/ERS 2021</option>
              <option value="GOLD-2023">GOLD 2023 (Actualizada)</option>
            </select>
          </div>
          <div class="col-md-4 mb-3">
            <label class="font-weight-bold">Número de Maniobras</label>
            <input type="number" :value="maniobrasOrdenadas.length" class="form-control" style="height: 38px;" readonly />
            <small class="text-muted d-block mt-1">ATS/ERS recomienda ≥3 maniobras aceptables</small>
          </div>
        </div>

        <!-- BOTONES DE GESTIÓN DE MANIOBRAS -->
        <div class="d-flex justify-content-between mb-3">
          <button type="button" @click="agregarManiobra" class="btn btn-success btn-sm" :disabled="maniobrasOrdenadas.length >= 8">
            <i class="fas fa-plus mr-1"></i> Agregar Maniobra
          </button>
          <button type="button" @click="eliminarUltimaManiobra" class="btn btn-danger btn-sm" :disabled="maniobrasOrdenadas.length <= 1">
            <i class="fas fa-minus mr-1"></i> Eliminar Última
          </button>
        </div>

        <!-- TABLA COMPARATIVA DE MANIOBRAS -->
        <div class="table-responsive mb-2">
          <table class="table table-sm table-bordered table-hover align-middle">
            <thead class="bg-gray-100">
              <tr>
                <th class="text-center" rowspan="2" style="vertical-align: middle;">Parámetro</th>
                <th class="text-center" rowspan="2" style="vertical-align: middle;">Unidad</th>
                <th class="text-center" rowspan="2" style="vertical-align: middle;">Pred</th>
                <th class="text-center" rowspan="2" style="vertical-align: middle;">LLN</th>
                <th class="text-center" rowspan="2" style="vertical-align: middle;">Puntuación Z</th>
                <th class="text-center bg-blue-50" rowspan="2" style="vertical-align: middle;">Mejor</th>
                <th class="text-center" :colspan="maniobrasOrdenadas.length">Maniobras</th>
                <th class="text-center" rowspan="2" style="vertical-align: middle;">%Pred</th>
              </tr>
              <tr>
                <!-- MUESTRA LAS PRUEBAS EN ORDEN DESCENDENTE (6, 4, 3) como en el PDF -->
                <th class="text-center" v-for="(maniobra, index) in maniobrasOrdenadasDesc" :key="'header-'+index">
                  Prueba {{ maniobra.numero }}
                  <button type="button" @click="eliminarManiobraPorNumero(maniobra.numero)" class="btn btn-xs btn-danger ml-1"
                    v-if="maniobrasOrdenadas.length > 1" style="padding: 2px 6px; font-size: 10px;">
                    ✕
                  </button>
                </th>
              </tr>
            </thead>
            <tbody>
              <!-- FVC -->
              <tr>
                <td class="font-weight-bold">FVC</td>
                <td class="text-center text-muted small">L</td>
                <td class="text-center">
                  <input type="text" inputmode="decimal" v-dec="{ valor: localForm.fvc_pred, dec: 2 }" @input="setDec(localForm, 'fvc_pred', $event.target.value)" class="form-control form-control-sm text-center" style="height: 34px;" />
                </td>
                <td class="text-center">
                  <input type="text" inputmode="decimal" v-dec="{ valor: localForm.fvc_lln, dec: 2 }" @input="setDec(localForm, 'fvc_lln', $event.target.value)" class="form-control form-control-sm text-center" style="height: 34px;" />
                </td>
                <td class="text-center">{{ fmt(localForm.fvc_z, 2) || '—' }}</td>
                <td class="text-center bg-blue-50 font-weight-bold text-blue-700">{{ fmt(localForm.mejor_fvc, 2) || '—' }}</td>
                <td class="text-center" v-for="(maniobra, index) in maniobrasOrdenadasDesc" :key="'fvc-'+index" :class="{ 'celda-marcada': esMarcado(maniobra, 'fvc_l') }">
                  <input type="text" inputmode="decimal" v-dec="{ valor: maniobra.fvc_l, dec: 2 }" @input="setDec(maniobra, 'fvc_l', $event.target.value)" class="form-control form-control-sm text-center" style="height: 34px;" />
                </td>
                <td class="text-center font-weight-bold" :class="getColorPorcentaje('fvc')">{{ localForm.fvc_porcentaje_predicho || '—' }}%</td>
              </tr>

              <!-- FEV1 -->
              <tr>
                <td class="font-weight-bold">FEV1</td>
                <td class="text-center text-muted small">L</td>
                <td class="text-center">
                  <input type="text" inputmode="decimal" v-dec="{ valor: localForm.fev1_pred, dec: 2 }" @input="setDec(localForm, 'fev1_pred', $event.target.value)" class="form-control form-control-sm text-center" style="height: 34px;" />
                </td>
                <td class="text-center">
                  <input type="text" inputmode="decimal" v-dec="{ valor: localForm.fev1_lln, dec: 2 }" @input="setDec(localForm, 'fev1_lln', $event.target.value)" class="form-control form-control-sm text-center" style="height: 34px;" />
                </td>
                <td class="text-center">{{ fmt(localForm.fev1_z, 2) || '—' }}</td>
                <td class="text-center bg-blue-50 font-weight-bold text-blue-700">{{ fmt(localForm.mejor_fev1, 2) || '—' }}</td>
                <td class="text-center" v-for="(maniobra, index) in maniobrasOrdenadasDesc" :key="'fev1-'+index" :class="{ 'celda-marcada': esMarcado(maniobra, 'fev1_l') }">
                  <input type="text" inputmode="decimal" v-dec="{ valor: maniobra.fev1_l, dec: 2 }" @input="setDec(maniobra, 'fev1_l', $event.target.value)" class="form-control form-control-sm text-center" style="height: 34px;" />
                </td>
                <td class="text-center font-weight-bold" :class="getColorPorcentaje('fev1')">{{ localForm.fev1_porcentaje_predicho || '—' }}%</td>
              </tr>

              <!-- FEV1/FVC -->
              <tr>
                <td class="font-weight-bold">FEV1/FVC</td>
                <td class="text-center text-muted small">—</td>
                <td class="text-center">
                  <input type="text" inputmode="decimal" v-dec="{ valor: localForm.fev1_fvc_pred, dec: 1 }" @input="setDec(localForm, 'fev1_fvc_pred', $event.target.value)" class="form-control form-control-sm text-center" style="height: 34px;" />
                </td>
                <td class="text-center">
                  <input type="text" inputmode="decimal" v-dec="{ valor: localForm.fev1_fvc_lln, dec: 1 }" @input="setDec(localForm, 'fev1_fvc_lln', $event.target.value)" class="form-control form-control-sm text-center" style="height: 34px;" />
                </td>
                <td class="text-center">{{ fmt(localForm.fev1_fvc_z, 2) || '—' }}</td>
                <td class="text-center bg-blue-50 font-weight-bold text-blue-700">{{ fmt(localForm.mejor_fev1_fvc, 1) || '—' }}</td>
                <td class="text-center" v-for="(maniobra, index) in maniobrasOrdenadasDesc" :key="'ratio-'+index" :class="{ 'celda-marcada': esMarcado(maniobra, 'fev1_fvc_ratio') }">
                  <input type="text" inputmode="decimal" v-dec="{ valor: maniobra.fev1_fvc_ratio, dec: 1 }" @input="setDec(maniobra, 'fev1_fvc_ratio', $event.target.value)" class="form-control form-control-sm text-center" style="height: 34px;" />
                </td>
                <td class="text-center font-weight-bold" :class="getColorPorcentaje('fev1_fvc')">{{ localForm.fev1_fvc_porcentaje_predicho || '—' }}%</td>
              </tr>

              <!-- FEF25-75% -->
              <tr>
                <td class="font-weight-bold">FEF25-75%</td>
                <td class="text-center text-muted small">L/s</td>
                <td class="text-center">
                  <input type="text" inputmode="decimal" v-dec="{ valor: localForm.fef25_75_pred, dec: 2 }" @input="setDec(localForm, 'fef25_75_pred', $event.target.value)" class="form-control form-control-sm text-center" style="height: 34px;" />
                </td>
                <td class="text-center">
                  <input type="text" inputmode="decimal" v-dec="{ valor: localForm.fef25_75_lln, dec: 2 }" @input="setDec(localForm, 'fef25_75_lln', $event.target.value)" class="form-control form-control-sm text-center" style="height: 34px;" />
                </td>
                <td class="text-center">{{ fmt(localForm.fef25_75_z, 2) || '—' }}</td>
                <td class="text-center bg-blue-50 font-weight-bold text-blue-700">{{ fmt(localForm.mejor_fef25_75, 2) || '—' }}</td>
                <td class="text-center" v-for="(maniobra, index) in maniobrasOrdenadasDesc" :key="'fef-'+index" :class="{ 'celda-marcada': esMarcado(maniobra, 'fef25_75') }">
                  <input type="text" inputmode="decimal" v-dec="{ valor: maniobra.fef25_75, dec: 2 }" @input="setDec(maniobra, 'fef25_75', $event.target.value)" class="form-control form-control-sm text-center" style="height: 34px;" />
                </td>
                <td class="text-center font-weight-bold" :class="getColorPorcentaje('fef25_75')">{{ localForm.fef25_75_porcentaje_predicho || '—' }}%</td>
              </tr>

              <!-- PEF -->
              <tr>
                <td class="font-weight-bold">PEF</td>
                <td class="text-center text-muted small">L/s</td>
                <td class="text-center">
                  <input type="text" inputmode="decimal" v-dec="{ valor: localForm.pef_pred, dec: 2 }" @input="setDec(localForm, 'pef_pred', $event.target.value)" class="form-control form-control-sm text-center" style="height: 34px;" />
                </td>
                <td class="text-center">
                  <input type="text" inputmode="decimal" v-dec="{ valor: localForm.pef_lln, dec: 2 }" @input="setDec(localForm, 'pef_lln', $event.target.value)" class="form-control form-control-sm text-center" style="height: 34px;" />
                </td>
                <td class="text-center">{{ fmt(localForm.pef_z, 2) || '—' }}</td>
                <td class="text-center bg-blue-50 font-weight-bold text-blue-700">{{ fmt(localForm.mejor_pef, 2) || '—' }}</td>
                <td class="text-center" v-for="(maniobra, index) in maniobrasOrdenadasDesc" :key="'pef-'+index" :class="{ 'celda-marcada': esMarcado(maniobra, 'pef_l_s') }">
                  <input type="text" inputmode="decimal" v-dec="{ valor: maniobra.pef_l_s, dec: 2 }" @input="setDec(maniobra, 'pef_l_s', $event.target.value)" class="form-control form-control-sm text-center" style="height: 34px;" />
                </td>
                <td class="text-center font-weight-bold" :class="getColorPorcentaje('pef')">{{ localForm.pef_porcentaje_predicho || '—' }}%</td>
              </tr>

              <!-- FET -->
              <tr>
                <td class="font-weight-bold">FET</td>
                <td class="text-center text-muted small">s</td>
                <td class="text-center">—</td>
                <td class="text-center">—</td>
                <td class="text-center text-muted">—</td>
                <td class="text-center bg-blue-50 font-weight-bold text-blue-700">{{ fmt(localForm.mejor_fet, 1) || '—' }}</td>
                <td class="text-center" v-for="(maniobra, index) in maniobrasOrdenadasDesc" :key="'fet-'+index" :class="{ 'celda-marcada': esMarcado(maniobra, 'fet_s') }">
                  <input type="text" inputmode="decimal" v-dec="{ valor: maniobra.fet_s, dec: 1 }" @input="setDec(maniobra, 'fet_s', $event.target.value)" class="form-control form-control-sm text-center" style="height: 34px;" />
                  <small :class="maniobra.fet_s >= 6 ? 'text-success' : 'text-warning'">
                    {{ maniobra.fet_s >= 6 ? '✓' : '⚠' }}
                  </small>
                </td>
                <td class="text-center text-muted">
                  <small class="text-muted">≥ 6 seg en adultos</small>
                </td>
              </tr>

              <!-- FIVC -->
              <tr>
                <td class="font-weight-bold">FIVC</td>
                <td class="text-center text-muted small">L</td>
                <td class="text-center">
                  <input type="text" inputmode="decimal" v-dec="{ valor: localForm.fvc_pred, dec: 2 }" class="form-control form-control-sm text-center" style="height: 34px;" readonly />
                </td>
                <td class="text-center">
                  <input type="text" inputmode="decimal" v-dec="{ valor: localForm.fvc_lln, dec: 2 }" class="form-control form-control-sm text-center" style="height: 34px;" readonly />
                </td>
                <td class="text-center">{{ fmt(localForm.fivc_z, 2) || '—' }}</td>
                <td class="text-center bg-blue-50 font-weight-bold text-blue-700">{{ fmt(localForm.mejor_fivc, 2) || '—' }}</td>
                <td class="text-center" v-for="(maniobra, index) in maniobrasOrdenadasDesc" :key="'fivc-'+index" :class="{ 'celda-marcada': esMarcado(maniobra, 'fivc_l') }">
                  <input type="text" inputmode="decimal" v-dec="{ valor: maniobra.fivc_l, dec: 2 }" @input="setDec(maniobra, 'fivc_l', $event.target.value)" class="form-control form-control-sm text-center" style="height: 34px;" />
                </td>
                <td class="text-center font-weight-bold" :class="getColorPorcentaje('fivc')">{{ localForm.fivc_porcentaje_predicho || '—' }}%</td>
              </tr>

              <!-- PIF -->
              <tr>
                <td class="font-weight-bold">PIF</td>
                <td class="text-center text-muted small">L/s</td>
                <td class="text-center">
                  <input type="text" inputmode="decimal" v-dec="{ valor: localForm.pif_pred, dec: 2 }" @input="setDec(localForm, 'pif_pred', $event.target.value)" class="form-control form-control-sm text-center" style="height: 34px;" />
                </td>
                <td class="text-center">
                  <input type="text" inputmode="decimal" v-dec="{ valor: localForm.pif_lln, dec: 2 }" @input="setDec(localForm, 'pif_lln', $event.target.value)" class="form-control form-control-sm text-center" style="height: 34px;" />
                </td>
                <td class="text-center text-muted">—</td>
                <td class="text-center bg-blue-50 font-weight-bold text-blue-700">{{ fmt(localForm.mejor_pif, 2) || '—' }}</td>
                <td class="text-center" v-for="(maniobra, index) in maniobrasOrdenadasDesc" :key="'pif-'+index" :class="{ 'celda-marcada': esMarcado(maniobra, 'pif_l_s') }">
                  <input type="text" inputmode="decimal" v-dec="{ valor: maniobra.pif_l_s, dec: 2 }" @input="setDec(maniobra, 'pif_l_s', $event.target.value)" class="form-control form-control-sm text-center" style="height: 34px;" />
                </td>
                <td class="text-center font-weight-bold" :class="getColorPorcentaje('pif')">{{ localForm.pif_porcentaje_predicho ? localForm.pif_porcentaje_predicho + '%' : '—' }}</td>
              </tr>

              <!-- EOTV -->
              <tr>
                <td class="font-weight-bold">EOTV</td>
                <td class="text-center text-muted small">L</td>
                <td class="text-center">
                  <input type="text" inputmode="decimal" v-dec="{ valor: localForm.eotv_pred, dec: 2 }" @input="setDec(localForm, 'eotv_pred', $event.target.value)" class="form-control form-control-sm text-center" style="height: 34px;" />
                </td>
                <td class="text-center">
                  <input type="text" inputmode="decimal" v-dec="{ valor: localForm.eotv_lln, dec: 2 }" @input="setDec(localForm, 'eotv_lln', $event.target.value)" class="form-control form-control-sm text-center" style="height: 34px;" />
                </td>
                <td class="text-center text-muted">—</td>
                <td class="text-center bg-blue-50 font-weight-bold text-blue-700">{{ fmt(localForm.mejor_eotv, 2) || '—' }}</td>
                <td class="text-center" v-for="(maniobra, index) in maniobrasOrdenadasDesc" :key="'eotv-'+index" :class="{ 'celda-marcada': esMarcado(maniobra, 'eotv_l') }">
                  <input type="text" inputmode="decimal" v-dec="{ valor: maniobra.eotv_l, dec: 2 }" @input="setDec(maniobra, 'eotv_l', $event.target.value)" class="form-control form-control-sm text-center" style="height: 34px;" />
                </td>
                <td class="text-center text-muted">Info</td>
              </tr>

              <!-- BEV -->
              <tr>
                <td class="font-weight-bold">BEV</td>
                <td class="text-center text-muted small">L</td>
                <td class="text-center">
                  <input type="text" inputmode="decimal" v-dec="{ valor: localForm.bev_pred, dec: 2 }" @input="setDec(localForm, 'bev_pred', $event.target.value)" class="form-control form-control-sm text-center" style="height: 34px;" />
                </td>
                <td class="text-center">
                  <input type="text" inputmode="decimal" v-dec="{ valor: localForm.bev_lln, dec: 2 }" @input="setDec(localForm, 'bev_lln', $event.target.value)" class="form-control form-control-sm text-center" style="height: 34px;" />
                </td>
                <td class="text-center text-muted">—</td>
                <td class="text-center bg-blue-50 font-weight-bold text-blue-700">{{ fmt(localForm.mejor_bev, 2) || '—' }}</td>
                <td class="text-center" v-for="(maniobra, index) in maniobrasOrdenadasDesc" :key="'bev-'+index" :class="{ 'celda-marcada': esMarcado(maniobra, 'bev_l') }">
                  <input type="text" inputmode="decimal" v-dec="{ valor: maniobra.bev_l, dec: 2 }" @input="setDec(maniobra, 'bev_l', $event.target.value)" class="form-control form-control-sm text-center" style="height: 34px;" />
                </td>
                <td class="text-center text-muted">Info</td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- LEYENDA DE VALORES MARCADOS EN EL INFORME -->
        <div v-if="hayMarcados" class="small text-muted mb-4">
          <span class="leyenda-marcada-swatch"></span>
          Valor marcado en el informe con *: fuera del rango normal o cambio posterior significativo.
        </div>

        <!-- GRÁFICAS ESPIROMÉTRICAS: debajo de la tabla, una fila con dos columnas -->
        <div class="card mb-4 card-graficas">
          <div class="card-header">
            <h6 class="mb-0"><i class="fas fa-chart-line mr-2"></i>GRÁFICAS ESPIROMÉTRICAS</h6>
          </div>
          <div class="card-body">
            <div class="row">
              <div class="col-md-6 mb-3">
                <h6 class="titulo-grafica">Curva Flujo / Volumen [L/s vs L]</h6>
                <p v-if="!hayDatosGrafica" class="text-muted small text-center mb-2">
                  Importa el informe o captura las maniobras para ver la gráfica.
                </p>
                <div class="grafica-contenedor">
                  <canvas ref="canvasFV"></canvas>
                </div>
                <div class="pie-grafica">
                  <span v-for="item in itemsLeyenda" :key="'fv-' + item.texto" class="item-leyenda">
                    <span class="caja-leyenda" :class="item.clase"></span>{{ item.texto }}
                  </span>
                  <span class="eje-x-titulo">Volumen [L]</span>
                </div>
                <div class="text-center mt-2 text-nowrap">
                  <button type="button" class="btn btn-sm btn-outline-primary" @click="construirGraficas">
                    <i class="fas fa-redo mr-1"></i>Regenerar
                  </button>
                  <button type="button" class="btn btn-sm btn-outline-success ml-2" @click="descargarGrafica('fv')">
                    <i class="fas fa-download mr-1"></i>PNG
                  </button>
                </div>
              </div>

              <div class="col-md-6 mb-3">
                <h6 class="titulo-grafica">Curva Volumen / Tiempo [L vs s]</h6>
                <p v-if="!hayDatosGrafica" class="text-muted small text-center mb-2">
                  Importa el informe o captura las maniobras para ver la gráfica.
                </p>
                <div class="grafica-contenedor">
                  <canvas ref="canvasVT"></canvas>
                </div>
                <div class="pie-grafica">
                  <span v-for="item in itemsLeyenda" :key="'vt-' + item.texto" class="item-leyenda">
                    <span class="caja-leyenda" :class="item.clase"></span>{{ item.texto }}
                  </span>
                  <span class="eje-x-titulo">Tiempo [s]</span>
                </div>
                <div class="text-center mt-2 text-nowrap">
                  <button type="button" class="btn btn-sm btn-outline-primary" @click="construirGraficas">
                    <i class="fas fa-redo mr-1"></i>Regenerar
                  </button>
                  <button type="button" class="btn btn-sm btn-outline-success ml-2" @click="descargarGrafica('vt')">
                    <i class="fas fa-download mr-1"></i>PNG
                  </button>
                </div>
              </div>
            </div>

            <!-- Puntuación Z por parámetro, con LLN, predicho y ULN (como el informe ndd) -->
            <div class="mb-3">
              <h6 class="titulo-grafica">Puntuación Z por parámetro</h6>
              <p v-if="!hayZ" class="text-muted small text-center mb-2">
                El informe no trae puntuación Z, por eso no se dibuja esta gráfica.
              </p>
              <div v-show="hayZ" style="position: relative; height: 260px;">
                <canvas ref="canvasZ"></canvas>
              </div>
              <div v-show="hayZ" class="text-center mt-2 text-nowrap">
                <button type="button" class="btn btn-sm btn-outline-primary" @click="construirGraficas">
                  <i class="fas fa-redo mr-1"></i>Regenerar
                </button>
                <button type="button" class="btn btn-sm btn-outline-success ml-2" @click="descargarGrafica('z')">
                  <i class="fas fa-download mr-1"></i>PNG
                </button>
              </div>
            </div>

            <!-- Leyenda y nota, en el mismo estilo que la leyenda ISO del audiómetro -->
            <div class="leyenda-graficas">
              <div class="leyenda-graficas-titulo">Leyenda de las gráficas</div>
              <div class="row">
                <div class="col-md-6">
                  <div><span class="sw sw-mejor"></span> Prueba Mejor (línea azul continua)</div>
                  <div><span class="sw sw-prueba"></span> Otras pruebas (línea azul punteada)</div>
                </div>
                <div class="col-md-6">
                  <div><span class="sw sw-predicho"></span> Predicho (línea gris punteada)</div>
                  <div><span class="pt-fev1"></span> FEV1 a 1 s (volumen-tiempo)</div>
                </div>
              </div>
              <div class="nota-graficas">
                Curvas generadas a partir de la tabla; pueden diferir de la escala del equipo. La forma es aproximada; los valores son los del informe.
              </div>
            </div>
          </div>
        </div>

        <!-- CRITERIOS DE CALIDAD Y REPETIBILIDAD (ATS/ERS) -->
        <div class="border-top pt-4">
          <h5 class="font-weight-bold text-dark mb-3">
            <i class="fas fa-clipboard-check mr-2 text-primary"></i> CRITERIOS DE CALIDAD Y REPETIBILIDAD (ATS/ERS)
          </h5>

          <div class="row mb-4">
            <div class="col-md-4 mb-3">
              <label class="font-weight-bold">Varianza FEV1 (L)</label>
              <input type="text" inputmode="decimal" v-dec="{ valor: localForm.fev1_var_l, dec: 2 }" class="form-control" style="height: 38px;" readonly />
              <div class="mt-1">
                <span class="badge" :class="(localForm.fev1_var_l || 999) <= 0.150 ? 'badge-success' : 'badge-danger'">
                  {{ (localForm.fev1_var_l || 999) <= 0.150 ? '✓ Cumple' : '✗ Excede límite' }}
                </span>
                <small class="text-muted d-block mt-1">Debe ser ≤ 0.150 L para Grado A</small>
              </div>
            </div>
            <div class="col-md-4 mb-3">
              <label class="font-weight-bold">Varianza FVC (L)</label>
              <input type="text" inputmode="decimal" v-dec="{ valor: localForm.fvc_var_l, dec: 2 }" class="form-control" style="height: 38px;" readonly />
              <div class="mt-1">
                <span class="badge" :class="(localForm.fvc_var_l || 999) <= 0.150 ? 'badge-success' : 'badge-danger'">
                  {{ (localForm.fvc_var_l || 999) <= 0.150 ? '✓ Cumple' : '✗ Excede límite' }}
                </span>
                <small class="text-muted d-block mt-1">Debe ser ≤ 0.150 L para Grado A</small>
              </div>
            </div>
            <div class="col-md-4 mb-3">
              <label class="font-weight-bold">Calidad de Sesión</label>
              <div class="form-control" style="height: 38px; background: #f8f9fa; display: flex; align-items: center;">
                <span :class="getCalidadClass()" class="font-weight-bold">
                  {{ calidadSesion || '—' }}
                </span>
                <small class="text-muted ml-3">FEV1 {{ localForm.calidad_fev1 || '—' }} · FVC {{ localForm.calidad_fvc || '—' }}</small>
              </div>
              <small class="text-muted d-block mt-1">Grado A a F (U = usable), según ATS/ERS 2019. General = peor de FEV1 y FVC.</small>
            </div>
          </div>

          <div class="d-flex justify-content-end">
            <button type="button" @click="validarCalidadSesion" class="btn text-white font-weight-bold px-4 py-2"
              style="background: linear-gradient(135deg, #43A047 0%, #2E7D32 100%); border: none; border-radius: 0.375rem;">
              <i class="fas fa-check-circle mr-2"></i> VALIDAR CALIDAD DE SESIÓN
            </button>
          </div>

          <div v-if="validacionCalidad" class="mt-4 p-3 border rounded"
            :class="validacionCalidad.ok ? 'bg-success bg-opacity-10 border-success' : 'bg-danger bg-opacity-10 border-danger'">
            <h6 class="font-weight-bold mb-2 d-flex align-items-center"
              :class="validacionCalidad.ok ? 'text-success' : 'text-danger'">
              <i :class="validacionCalidad.ok ? 'fas fa-check-circle mr-2' : 'fas fa-times-circle mr-2'"></i>
              {{ validacionCalidad.ok ? '✓ SESIÓN VÁLIDA' : '✗ SESIÓN REQUIERE REVISIÓN' }}
            </h6>
            <ul class="text-sm mb-0">
              <li v-for="(item, idx) in validacionCalidad.detalles" :key="idx" :class="validacionCalidad.ok ? 'text-success' : 'text-danger'">
                {{ item }}
              </li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import Chart from 'chart.js/auto'
import { markRaw } from 'vue'
import apiClient from '@/services/ApiServiceClinicaTrabajo'

// ---------- Utilidades ----------

// Formatea un número con los decimales indicados; devuelve '' si no hay valor
function formatearNumero(valor, dec = 2) {
  if (valor === null || valor === undefined || valor === '' || isNaN(Number(valor))) return ''
  return Number(valor).toFixed(dec)
}

// Devuelve el número si es válido, o null
function numValido(valor) {
  if (valor === null || valor === undefined || valor === '') return null
  const n = Number(valor)
  return Number.isFinite(n) ? n : null
}

// FEV1/FVC: el PDF lo trae como fracción (0.779) y el Excel como porcentaje (73.8).
// Si el valor es ≤ 1 se pasa a porcentaje; si ya es porcentaje, se deja igual.
function aPorcentaje(valor) {
  if (valor === null || valor === undefined || valor === '') return null
  const n = Number(valor)
  if (!Number.isFinite(n)) return null
  return n <= 1 ? parseFloat((n * 100).toFixed(1)) : n
}

// ---------- Gráficas ----------

const COLOR_MEJOR = '#1F3FA0'
const COLOR_PRUEBA = '#5B8DEF'
const COLOR_PREDICHO = '#9AA5B1'

// Puntuación Z: parámetros en orden de aparición en el informe (de arriba hacia abajo)
const ZONAS_Z = [
  { etiqueta: 'FVC', campo: 'fvc_z' },
  { etiqueta: 'FEV1', campo: 'fev1_z' },
  { etiqueta: 'FEV1/FVC', campo: 'fev1_fvc_z' },
  { etiqueta: 'FEF25-75', campo: 'fef25_75_z' },
  { etiqueta: 'PEF', campo: 'pef_z' },
  { etiqueta: 'FIVC', campo: 'fivc_z' }
]
const LLN_Z = -1.645
const ULN_Z = 1.645

// Dibuja las zonas de color, las líneas de LLN, predicho (0) y ULN, y las etiquetas de cada parámetro
const plantillaZ = {
  id: 'plantillaZ',
  beforeDatasetsDraw(chart) {
    const { ctx, chartArea: a, scales: { x, y } } = chart
    if (!a) return
    const px = v => x.getPixelForValue(v)
    const py = v => y.getPixelForValue(v)

    ctx.save()

    // Zona anormal (izquierda del LLN): degradado rojo → amarillo; zona normal: verde claro
    const degradado = ctx.createLinearGradient(px(x.min), 0, px(LLN_Z), 0)
    degradado.addColorStop(0, '#E07A5F')
    degradado.addColorStop(1, '#FFD98A')
    ctx.fillStyle = degradado
    ctx.fillRect(px(x.min), a.top, px(LLN_Z) - px(x.min), a.bottom - a.top)
    ctx.fillStyle = '#E2F2D5'
    ctx.fillRect(px(LLN_Z), a.top, px(x.max) - px(LLN_Z), a.bottom - a.top)

    // Separadores entre parámetros
    ctx.strokeStyle = 'rgba(0,0,0,0.35)'
    ctx.lineWidth = 1
    for (let k = 0; k <= ZONAS_Z.length; k++) {
      const yLinea = py(ZONAS_Z.length - 0.5 - k)
      ctx.beginPath()
      ctx.moveTo(a.left, yLinea)
      ctx.lineTo(a.right, yLinea)
      ctx.stroke()
    }

    // Líneas verticales negras: LLN, predicho (0) y ULN
    ctx.strokeStyle = '#000'
    ctx.lineWidth = 3
    for (const v of [LLN_Z, 0, ULN_Z]) {
      ctx.beginPath()
      ctx.moveTo(px(v), a.top)
      ctx.lineTo(px(v), a.bottom)
      ctx.stroke()
    }

    // Etiquetas: encabezados arriba y nombre de cada parámetro a la izquierda
    ctx.fillStyle = '#000'
    ctx.font = '12px sans-serif'
    ctx.textAlign = 'center'
    ctx.fillText('LLN', px(LLN_Z), a.top - 6)
    ctx.fillText('predicho', px(0), a.top - 6)
    ctx.textAlign = 'right'
    ZONAS_Z.forEach((zona, i) => {
      ctx.fillText(zona.etiqueta, a.left - 8, py(ZONAS_Z.length - 1 - i) + 4)
    })

    ctx.restore()
  }
}

function configuracionZ() {
  return {
    type: 'scatter',
    data: {
      datasets: [{
        label: 'Puntuación Z',
        data: [],
        showLine: false,
        pointStyle: 'star',
        pointRadius: 9,
        pointBorderWidth: 3,
        borderColor: COLOR_MEJOR,
        backgroundColor: COLOR_MEJOR
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      animation: false,
      layout: { padding: { left: 70, top: 14 } },
      plugins: {
        legend: { display: false },
        tooltip: { callbacks: { label: ctx => `Z = ${ctx.parsed.x.toFixed(2)}` } }
      },
      scales: {
        x: {
          type: 'linear',
          min: -5,
          max: 3,
          ticks: { stepSize: 1 },
          title: { display: true, text: 'puntuación z' },
          grid: { display: false }
        },
        y: {
          min: -0.5,
          max: ZONAS_Z.length - 0.5,
          display: false,
          grid: { display: false }
        }
      }
    },
    plugins: [plantillaZ]
  }
}

// Forma de la espiración: u = fracción exhalada desde TLC (0) hasta FVC (1).
// Sube rápido hasta el PEF y baja a 0 al final de la espiración.
function formaEspiracion(u) {
  return Math.pow(1 - Math.exp(-u / 0.05), 0.5) * Math.pow(1 - u, 1.2)
}

// Máximo de la forma, para escalar la curva de modo que su pico sea exactamente el PEF
const MAXIMO_FORMA = (() => {
  let m = 0
  for (let i = 0; i <= 1000; i++) m = Math.max(m, formaEspiracion(i / 1000))
  return m
})()

// Curva flujo-volumen: espiración de 0 a FVC y luego inspiración de FIVC a 0
function puntosFlujoVolumen({ fvc, pef, fivc, pif }) {
  const pts = []
  for (let i = 0; i <= 100; i++) {
    const u = i / 100
    pts.push({ x: +(u * fvc).toFixed(3), y: +(pef * formaEspiracion(u) / MAXIMO_FORMA).toFixed(2) })
  }
  // Inspiración: arranca en FVC − FIVC, termina en FVC y tiene su mínimo a la mitad, como en el informe.
  for (let i = 0; i <= 100; i++) {
    const u = i / 100
    pts.push({ x: +(fvc - fivc + u * fivc).toFixed(3), y: +(-pif * Math.pow(Math.sin(Math.PI * u), 0.3)).toFixed(2) })
  }
  return pts
}

// Volumen a un tiempo t: V(t) = FVC · (1 − e^(−t/τ)), con τ tal que el FEV1 se alcanza en t = 1 s
function volumenEnTiempo(t, fvc, fev1) {
  const ratio = Math.min(Math.max(fev1 / fvc, 0.05), 0.99)
  const tau = -1 / Math.log(1 - ratio)
  return fvc * (1 - Math.exp(-t / tau))
}

// Curva volumen-tiempo de 0 a 14 s
function puntosVolumenTiempo({ fvc, fev1 }) {
  const pts = []
  for (let i = 0; i <= 140; i++) {
    const t = i / 10
    pts.push({ x: t, y: +volumenEnTiempo(t, fvc, fev1).toFixed(3) })
  }
  return pts
}

// Punto del PEF en la curva flujo-volumen: el máximo de la forma escalado al PEF
function puntoPEF({ fvc, pef }) {
  let mejorU = 0
  let mejorY = -1
  for (let i = 0; i <= 100; i++) {
    const y = formaEspiracion(i / 100)
    if (y > mejorY) {
      mejorY = y
      mejorU = i / 100
    }
  }
  return { x: +(mejorU * fvc).toFixed(3), y: pef }
}

function estiloCurva(esMejor) {
  return esMejor
    ? { borderColor: COLOR_MEJOR, borderWidth: 3, borderDash: [] }
    : { borderColor: COLOR_PRUEBA, borderWidth: 2, borderDash: [2, 3] }
}

function configuracionGrafica(datos, ejes) {
  return {
    type: 'line',
    data: { datasets: datos.datasets },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      animation: false,
      interaction: { mode: 'nearest', intersect: false },
      // La leyenda y el título del eje X se muestran en HTML, debajo de la gráfica
      plugins: {
        legend: { display: false }
      },
      scales: {
        x: {
          type: 'linear',
          min: datos.x.min,
          max: datos.x.max,
          ticks: { stepSize: 1 },
          // Línea vertical negra en x = 0, como en el reporte del equipo
          grid: {
            color: ctx => (ctx.tick && ctx.tick.value === 0 ? '#000' : 'rgba(0,0,0,0.25)'),
            borderDash: [2, 3]
          }
        },
        y: {
          min: datos.y.min,
          max: datos.y.max,
          title: { display: true, text: ejes.tituloY },
          ticks: { stepSize: ejes.stepY },
          grid: { color: 'rgba(0,0,0,0.25)', borderDash: [2, 3] }
        }
      }
    }
  }
}

export default {
  name: 'ParametrosFvlEspirometria',
  props: {
    modelValue: { type: Object, required: true }
  },
  emits: ['update:modelValue', 'importar-interpretacion'],
  directives: {
    // Muestra el valor con los decimales del informe (ej. 3.90) sin perder el número.
    // No reescribe el campo mientras el usuario lo está escribiendo.
    dec: {
      mounted(el, binding) {
        el._dec = binding.value
        el._decBlur = () => {
          el.value = formatearNumero(el._dec.valor, el._dec.dec)
        }
        el.addEventListener('blur', el._decBlur)
        el.value = formatearNumero(binding.value.valor, binding.value.dec)
      },
      updated(el, binding) {
        el._dec = binding.value
        if (document.activeElement !== el) {
          el.value = formatearNumero(binding.value.valor, binding.value.dec)
        }
      },
      unmounted(el) {
        el.removeEventListener('blur', el._decBlur)
      }
    }
  },
  data() {
    return {
      localForm: {
        referencia: 'NHANES III',
        interpretacion_predicha: 'GOLD(2008)/Hardie',
        fvc_pred: null,
        fvc_lln: null,
        fev1_pred: null,
        fev1_lln: null,
        fev1_fvc_pred: null,
        fev1_fvc_lln: null,
        fef25_75_pred: null,
        fef25_75_lln: null,
        pef_pred: null,
        pef_lln: null,
        mejor_fvc: null,
        mejor_fev1: null,
        mejor_fev1_fvc: null,
        mejor_fef25_75: null,
        mejor_pef: null,
        mejor_fivc: null,
        mejor_pif: null,
        mejor_eotv: null,
        mejor_fet: null,
        mejor_bev: null,
        pif_pred: null,
        pif_lln: null,
        pif_porcentaje_predicho: null,
        eotv_pred: null,
        eotv_lln: null,
        bev_pred: null,
        bev_lln: null,
        fvc_porcentaje_predicho: null,
        fev1_porcentaje_predicho: null,
        fev1_fvc_porcentaje_predicho: null,
        fef25_75_porcentaje_predicho: null,
        pef_porcentaje_predicho: null,
        fivc_porcentaje_predicho: null,
        fev1_var_l: null,
        fvc_var_l: null,
        calidad_sesion: '',
        calidad_fev1: '',
        calidad_fvc: '',
        fvc_z: null,
        fev1_z: null,
        fev1_fvc_z: null,
        fef25_75_z: null,
        pef_z: null,
        fivc_z: null,
        interpretacion_sistema: '',
        maniobras: []
      },
      plantillaManiobra: {
        numero: null,
        fvc_l: null,
        fev1_l: null,
        fev1_fvc_ratio: null,
        fef25_75: null,
        pef_l_s: null,
        fet_s: null,
        fivc_l: null,
        pif_l_s: null,
        bev_l: null,
        eotv_l: null,
        marcados: {}
      },
      validacionCalidad: null,
      graficaFV: null,
      graficaVT: null,
      graficaZ: null,
      archivoSeleccionado: null,
      nombreArchivo: '',
      procesandoIA: false,
      mensajeIA: '',
      exitoIA: false,
      advertencias: []
    }
  },
  computed: {
    // ORDENA LAS MANIOBRAS ASCENDENTE (3, 4, 6)
    maniobrasOrdenadas() {
      if (!Array.isArray(this.localForm.maniobras)) return []
      return [...this.localForm.maniobras].sort((a, b) => {
        const numA = a.numero || 999
        const numB = b.numero || 999
        return numA - numB
      })
    },
    // ORDENA LAS MANIOBRAS DESCENDENTE (6, 4, 3) como en el PDF
    maniobrasOrdenadasDesc() {
      if (!Array.isArray(this.localForm.maniobras)) return []
      return [...this.localForm.maniobras].sort((a, b) => {
        const numA = a.numero || 999
        const numB = b.numero || 999
        return numB - numA
      })
    },
    // Indica si alguna celda del informe trae asterisco (para mostrar la leyenda)
    hayMarcados() {
      return (this.localForm.maniobras || []).some(m =>
        m.marcados && Object.values(m.marcados).some(Boolean)
      )
    },
    // Elementos de la leyenda debajo de las gráficas (mismo orden que las curvas)
    itemsLeyenda() {
      const mejor = this.maniobraMejor
      const items = this.maniobrasOrdenadas
        .filter(m => numValido(m.fvc_l) !== null && numValido(m.pef_l_s) !== null)
        .sort((a, b) => (a === mejor) - (b === mejor))
        .map(m => ({
          texto: `Previo Prueba ${m.numero}${m === mejor ? ' (Mejor)' : ''}`,
          clase: m === mejor ? 'sw-mejor' : 'sw-prueba'
        }))
      if (numValido(this.localForm.fvc_pred) !== null && numValido(this.localForm.pef_pred) !== null) {
        items.push({ texto: 'Predicho', clase: 'sw-predicho' })
      }
      if (numValido(this.localForm.fvc_lln) !== null && numValido(this.localForm.fev1_lln) !== null) {
        items.push({ texto: 'LLN', clase: 'sw-predicho' })
      }
      return items
    },
    // Maniobra Mejor: la que coincide con el Mejor FEV1 del informe; si no, la de mayor FEV1
    maniobraMejor() {
      const conFev1 = this.maniobrasOrdenadas.filter(m => numValido(m.fev1_l) !== null)
      if (conFev1.length === 0) return null
      const mejorFev1 = numValido(this.localForm.mejor_fev1)
      const coincide = mejorFev1 !== null
        ? conFev1.find(m => Math.abs(numValido(m.fev1_l) - mejorFev1) < 0.005)
        : null
      return coincide || conFev1.reduce((a, b) => (numValido(b.fev1_l) > numValido(a.fev1_l) ? b : a))
    },
    // Hay al menos una puntuación Z en el informe
    hayZ() {
      return ZONAS_Z.some(z => numValido(this.localForm[z.campo]) !== null)
    },

    // Hay datos suficientes para dibujar las curvas
    hayDatosGrafica() {
      return this.maniobrasOrdenadas.some(m =>
        numValido(m.fvc_l) !== null && numValido(m.pef_l_s) !== null
      )
    },
    calidadSesion() {
      const fev1Var = this.localForm.fev1_var_l
      const fvcVar = this.localForm.fvc_var_l
      if (fev1Var == null || fvcVar == null) return null
      if (fev1Var <= 0.150 && fvcVar <= 0.150) return 'A'
      if (fev1Var <= 0.200 && fvcVar <= 0.200) return 'B'
      if (fev1Var <= 0.250 && fvcVar <= 0.250) return 'C'
      return 'D'
    },
  },
  created() {
    this.inicializarDesdeProps()
  },
  mounted() {
    this.$nextTick(() => this.construirGraficas())
  },
  beforeUnmount() {
    clearTimeout(this._timerGraficas)
    this.destruirGraficas()
  },
  watch: {
    modelValue: {
      handler(newVal) {
        if (newVal && typeof newVal === 'object') {
          Object.keys(newVal).forEach(key => {
            if (key !== 'maniobras' && this.localForm[key] !== newVal[key]) {
              this.localForm[key] = newVal[key]
            }
          })
          if (newVal.maniobras && Array.isArray(newVal.maniobras)) {
            this.localForm.maniobras = JSON.parse(JSON.stringify(newVal.maniobras))
          }
          this.recalcularTodo({ respetarImportado: true })
        }
      },
      deep: true,
      immediate: true
    },
    // Solo redibuja las gráficas; NO emite al padre, para no provocar un bucle
    // con el watcher de modelValue.
    localForm: {
      handler() {
        this.programarGraficas()
      },
      deep: true
    }
  },
  methods: {
    fmt(valor, dec = 2) {
      return formatearNumero(valor, dec)
    },

    // Convierte lo escrito en el input a número (acepta coma o punto)
    setDec(objeto, campo, texto) {
      const limpio = String(texto ?? '').trim().replace(',', '.')
      objeto[campo] = (limpio === '' || isNaN(Number(limpio))) ? null : Number(limpio)
      this.onEdicion()
    },

    esMarcado(maniobra, campo) {
      return !!(maniobra && maniobra.marcados && maniobra.marcados[campo])
    },

    seleccionarArchivo(event) {
      const file = event.target.files[0]
      if (file) {
        this.archivoSeleccionado = file
        this.nombreArchivo = file.name
        this.mensajeIA = ''
        this.exitoIA = false
        this.advertencias = []
      }
    },

    async importarConIA() {
      if (!this.archivoSeleccionado) return

      this.procesandoIA = true
      this.mensajeIA = 'Enviando archivo a la IA para análisis...'
      this.exitoIA = false
      this.advertencias = []

      const formData = new FormData()
      formData.append('archivo', this.archivoSeleccionado)

      try {
        const { data: respuesta } = await apiClient.post('/espirometria-importar', formData)

        if (!respuesta.success) {
          throw new Error(respuesta.message || 'Error desconocido al extraer datos')
        }

        // El backend devuelve las secciones del formulario; los valores ya vienen en porcentaje
        const datos = respuesta.data.parametrosFVL || {}
        const interpretacion = respuesta.data.interpretacion || {}
        const numManiobras = Array.isArray(datos.maniobras) ? datos.maniobras.length : 0

        this.exitoIA = true
        this.mensajeIA = `¡Datos extraídos exitosamente! Se encontraron ${numManiobras} maniobras.`
        this.advertencias = respuesta.advertencias ?? []

        // Un import nuevo reemplaza los valores anteriores; lo que el informe no trae queda en null
        const camposDelInforme = [
          'fvc_pred', 'fvc_lln', 'fev1_pred', 'fev1_lln', 'fev1_fvc_pred', 'fev1_fvc_lln',
          'fef25_75_pred', 'fef25_75_lln', 'pef_pred', 'pef_lln', 'fivc_pred', 'fivc_lln',
          'pif_pred', 'pif_lln', 'eotv_pred', 'eotv_lln', 'bev_pred', 'bev_lln',
          'mejor_fvc', 'mejor_fev1', 'mejor_fev1_fvc', 'mejor_fef25_75', 'mejor_pef',
          'mejor_fivc', 'mejor_pif', 'mejor_eotv', 'mejor_bev', 'mejor_fet',
          'fev1_var_l', 'fvc_var_l', 'calidad_fev1', 'calidad_fvc',
          'fvc_porcentaje_predicho', 'fev1_porcentaje_predicho', 'fev1_fvc_porcentaje_predicho',
          'fef25_75_porcentaje_predicho', 'pef_porcentaje_predicho', 'fivc_porcentaje_predicho',
          'pif_porcentaje_predicho', 'fvc_z', 'fev1_z', 'fev1_fvc_z', 'fef25_75_z', 'pef_z', 'fivc_z'
        ]
        camposDelInforme.forEach(campo => {
          this.localForm[campo] = datos[campo] ?? null
        })

        if (numManiobras > 0) {
          this.localForm.maniobras = datos.maniobras.map((m, idx) => ({
            ...this.plantillaManiobra,
            ...m,
            numero: m.numero ?? (idx + 1),
            marcados: { ...(m.marcados || {}) }
          }))
        }

        this.recalcularTodo({ respetarImportado: true })
        this.programarGraficas()
        this.emitChange()

        // La sección Interpretación vive en el padre
        this.$emit('importar-interpretacion', interpretacion)
      } catch (error) {
        console.error('Error al importar con IA:', error)
        this.exitoIA = false
        this.mensajeIA = error.response?.data?.message
          || error.message
          || 'Ocurrió un error al procesar el archivo. Verifica que sea un informe legible.'
        this.advertencias = error.response?.data?.advertencias ?? []
      } finally {
        this.procesandoIA = false
      }
    },

    inicializarDesdeProps() {
      if (this.modelValue && typeof this.modelValue === 'object') {
        Object.keys(this.modelValue).forEach(key => {
          if (key in this.localForm) {
            this.localForm[key] = this.modelValue[key]
          }
        })
      }
      if (!Array.isArray(this.localForm.maniobras) || this.localForm.maniobras.length === 0) {
        this.localForm.maniobras = [
          { ...this.plantillaManiobra, numero: 1, marcados: {} },
          { ...this.plantillaManiobra, numero: 2, marcados: {} },
          { ...this.plantillaManiobra, numero: 3, marcados: {} }
        ]
      }
      this.$nextTick(() => {
        this.recalcularTodo({ respetarImportado: true })
        this.emitChange()
      })
    },
    emitChange() {
      this.$emit('update:modelValue', { ...this.localForm })
    },
    // Edición manual: recalcula todo y avisa al padre
    onEdicion() {
      this.recalcularTodo()
      this.emitChange()
    },
    agregarManiobra() {
      if (this.localForm.maniobras.length < 8) {
        const nuevoNumero = this.localForm.maniobras.length + 1
        this.localForm.maniobras.push({ ...this.plantillaManiobra, numero: nuevoNumero, marcados: {} })
        this.recalcularTodo()
        this.emitChange()
      }
    },
    eliminarManiobraPorNumero(numero) {
      const index = this.localForm.maniobras.findIndex(m => m.numero === numero)
      if (index !== -1 && this.localForm.maniobras.length > 1) {
        this.localForm.maniobras.splice(index, 1)
        this.localForm.maniobras.forEach((m, idx) => {
          m.numero = idx + 1
        })
        this.recalcularTodo()
        this.emitChange()
      }
    },
    eliminarUltimaManiobra() {
      if (this.localForm.maniobras.length > 1) {
        this.localForm.maniobras.pop()
        this.recalcularTodo()
        this.emitChange()
      }
    },
    recalcularTodo(opciones = {}) {
      const respetar = opciones.respetarImportado === true

      this.calcularMejoresValores(respetar)
      this.calcularPorcentajes(respetar)
      this.calcularVarianzas(respetar)
    },

    calcularMejoresValores(respetarImportado = false) {
      const maniobras = this.localForm.maniobras || []
      const max = (campo) => {
        const valores = maniobras
          .map(m => m[campo])
          .filter(v => v !== null && v !== undefined && !isNaN(v))
        return valores.length > 0 ? Math.max(...valores) : null
      }

      const reglas = {
        mejor_fvc: () => max('fvc_l'),
        mejor_fev1: () => max('fev1_l'),
        mejor_fef25_75: () => max('fef25_75'),
        mejor_pef: () => max('pef_l_s'),
        mejor_fivc: () => max('fivc_l'),
        mejor_pif: () => max('pif_l_s'),
        // mejor_eotv: () => max('eotv_l'),
      }

      Object.entries(reglas).forEach(([campo, calcular]) => {
        // Si el informe ya trae el Mejor y no es una edición manual, lo conserva
        if (respetarImportado && this.localForm[campo] !== null && this.localForm[campo] !== undefined) return
        this.localForm[campo] = calcular()
      })

      // FEV1/FVC mejor: solo como respaldo si el informe no lo trae
      if (!(respetarImportado && this.localForm.mejor_fev1_fvc != null)) {
        const fev1 = this.localForm.mejor_fev1
        const fvc = this.localForm.mejor_fvc
        this.localForm.mejor_fev1_fvc = (fev1 && fvc && fvc > 0)
          ? parseFloat((fev1 / fvc * 100).toFixed(1))
          : null
      }

      // FET del Mejor: el FET de la maniobra con mayor FEV1
      if (!(respetarImportado && this.localForm.mejor_fet != null)) {
        const mejorManiobra = maniobras
          .filter(m => m.fev1_l != null && m.fet_s != null)
          .sort((a, b) => b.fev1_l - a.fev1_l)[0]
        this.localForm.mejor_fet = mejorManiobra ? mejorManiobra.fet_s : null
      }
    },

    calcularPorcentajes(respetarImportado = false) {
      const f = this.localForm

      const pct = (mejor, pred) => (pred > 0 && mejor)
        ? Math.round((mejor / pred) * 100)
        : null

      const reglas = [
        ['fvc_porcentaje_predicho', 'mejor_fvc', 'fvc_pred'],
        ['fev1_porcentaje_predicho', 'mejor_fev1', 'fev1_pred'],
        ['fev1_fvc_porcentaje_predicho', 'mejor_fev1_fvc', 'fev1_fvc_pred'],
        ['fef25_75_porcentaje_predicho', 'mejor_fef25_75', 'fef25_75_pred'],
        ['pef_porcentaje_predicho', 'mejor_pef', 'pef_pred'],
        ['fivc_porcentaje_predicho', 'mejor_fivc', 'fvc_pred'],
      ]

      reglas.forEach(([destino, mejor, referencia]) => {
        if (respetarImportado && f[destino] != null) return
        f[destino] = pct(f[mejor], f[referencia])
      })
    },

    calcularVarianzas(respetarImportado = false) {
      const f = this.localForm

      const calcularDiferencia = (campo) => {
        const valores = (this.localForm.maniobras || [])
          .map(m => m[campo])
          .filter(v => v !== null && v !== undefined && !isNaN(v))
          .sort((a, b) => b - a)
        return valores.length >= 2
          ? parseFloat(Math.abs(valores[0] - valores[1]).toFixed(3))
          : null
      }

      if (!(respetarImportado && f.fev1_var_l != null)) {
        f.fev1_var_l = calcularDiferencia('fev1_l')
      }
      if (!(respetarImportado && f.fvc_var_l != null)) {
        f.fvc_var_l = calcularDiferencia('fvc_l')
      }
    },

    // ---------- Gráficas ----------

    // Arma los datos de ambas gráficas a partir de las maniobras y los predichos
    construirDatosGraficas() {
      const f = this.localForm
      const mejor = this.maniobraMejor

      const validas = this.maniobrasOrdenadas.filter(m =>
        numValido(m.fvc_l) !== null && numValido(m.pef_l_s) !== null
      )

      // Las no-Mejor primero y la Mejor al final, para que quede encima
      const ordenadas = [...validas].sort((a, b) => (a === mejor) - (b === mejor))

      const datasetsFV = []
      const datasetsVT = []
      let maxVolumen = 0
      let maxFlujo = 0
      let maxPif = 0

      ordenadas.forEach(m => {
        const fvc = numValido(m.fvc_l)
        const pef = numValido(m.pef_l_s)
        const fev1 = numValido(m.fev1_l) ?? fvc * 0.8
        const pif = numValido(m.pif_l_s) ?? pef * 0.6
        const fivc = numValido(m.fivc_l) ?? fvc
        const esMejor = m === mejor

        maxVolumen = Math.max(maxVolumen, fvc)
        maxFlujo = Math.max(maxFlujo, pef)
        maxPif = Math.max(maxPif, pif)

        datasetsFV.push({
          label: `Previo Prueba ${m.numero}${esMejor ? ' (Mejor)' : ''}`,
          data: puntosFlujoVolumen({ fvc, pef, fivc, pif }),
          ...estiloCurva(esMejor),
          fill: false,
          pointRadius: 0,
          tension: 0.35
        })

        datasetsVT.push({
          label: `Previo Prueba ${m.numero}${esMejor ? ' (Mejor)' : ''}`,
          data: puntosVolumenTiempo({ fvc, fev1 }),
          ...estiloCurva(esMejor),
          fill: false,
          pointRadius: 0,
          tension: 0.3
        })
      })

      // Predicho
      const fvcPred = numValido(f.fvc_pred)
      const pefPred = numValido(f.pef_pred)
      const fev1Pred = numValido(f.fev1_pred)
      const pifPred = numValido(f.pif_pred) ?? (pefPred !== null ? pefPred * 0.6 : null)

      if (fvcPred !== null && pefPred !== null) {
        maxVolumen = Math.max(maxVolumen, fvcPred)
        maxFlujo = Math.max(maxFlujo, pefPred)
        datasetsFV.push({
          label: 'Predicho',
          data: [{ x: 0.6, y: pefPred }, { x: fvcPred, y: 0 }],
          borderColor: COLOR_PREDICHO,
          borderWidth: 4,
          borderDash: [8, 5],
          pointRadius: 0,
          fill: false
        })
      }
      if (fvcPred !== null && pefPred !== null) {
        datasetsFV.push({
          label: '',
          data: [puntoPEF({ fvc: fvcPred, pef: pefPred })],
          showLine: false,
          pointStyle: 'rectRot',
          pointRadius: 6,
          pointBackgroundColor: COLOR_PREDICHO,
          pointBorderColor: COLOR_PREDICHO
        })
      }
      if (fvcPred !== null && fev1Pred !== null) {
        datasetsVT.push({
          label: 'Predicho',
          data: puntosVolumenTiempo({ fvc: fvcPred, fev1: fev1Pred }),
          borderColor: COLOR_PREDICHO,
          borderWidth: 2,
          borderDash: [8, 5],
          pointRadius: 0,
          fill: false,
          tension: 0.3
        })
        // Marcador del FEV1 predicho en t = 1 s
        datasetsVT.push({
          label: '',
          data: [{ x: 1, y: fev1Pred }],
          showLine: false,
          pointRadius: 5,
          pointBackgroundColor: COLOR_PREDICHO,
          pointBorderColor: COLOR_PREDICHO
        })
      }

      // LLN de FVC y FEV1 (volumen-tiempo), punteada y más fina que el predicho
      const fvcLln = numValido(f.fvc_lln)
      const fev1Lln = numValido(f.fev1_lln)
      if (fvcLln !== null && fev1Lln !== null) {
        datasetsVT.push({
          label: 'LLN',
          data: puntosVolumenTiempo({ fvc: fvcLln, fev1: fev1Lln }),
          borderColor: COLOR_PREDICHO,
          borderWidth: 2,
          borderDash: [3, 4],
          pointRadius: 0,
          fill: false,
          tension: 0.3
        })
      }

      // Mejor: rombo del PEF en la flujo-volumen y marcador de FVC a 6 s en la volumen-tiempo
      if (mejor && numValido(mejor.fvc_l) !== null && numValido(mejor.pef_l_s) !== null) {
        datasetsFV.push({
          label: '',
          data: [puntoPEF({ fvc: numValido(mejor.fvc_l), pef: numValido(mejor.pef_l_s) })],
          showLine: false,
          pointStyle: 'rectRot',
          pointRadius: 6,
          pointBackgroundColor: COLOR_MEJOR,
          pointBorderColor: COLOR_MEJOR
        })
      }
      if (mejor && numValido(mejor.fvc_l) !== null && numValido(mejor.fev1_l) !== null) {
        const fvcM = numValido(mejor.fvc_l)
        const fev1M = numValido(mejor.fev1_l)
        datasetsVT.push({
          label: '',
          data: [
            { x: 6, y: +volumenEnTiempo(6, fvcM, fev1M).toFixed(3) },
            { x: 12, y: +volumenEnTiempo(12, fvcM, fev1M).toFixed(3) }
          ],
          showLine: false,
          pointRadius: 5,
          pointBackgroundColor: COLOR_MEJOR,
          pointBorderColor: COLOR_MEJOR
        })
      }

      // Marcador del FEV1 del Mejor en t = 1 s
      if (mejor && numValido(mejor.fev1_l) !== null) {
        datasetsVT.push({
          label: '',
          data: [{ x: 1, y: numValido(mejor.fev1_l) }],
          showLine: false,
          pointRadius: 5,
          pointBackgroundColor: COLOR_MEJOR,
          pointBorderColor: COLOR_MEJOR
        })
      }

      // Ejes: enteros y redondeados para que las etiquetas sean limpias
      const xMaxFV = Math.max(8, Math.ceil(maxVolumen + 0.5))
      const mitadFlujo = Math.max(4, Math.ceil(maxFlujo / 2) * 2 + 2)
      const mitadPif = Math.max(4, Math.ceil(maxPif / 2) * 2 + 2)
      const yMaxVT = Math.max(8, Math.ceil(maxVolumen) + 1)
      const limiteFlujo = Math.max(14, mitadFlujo)
      const limitePif = Math.max(14, mitadPif)

      // Línea horizontal en cero (no aparece en la leyenda)
      datasetsFV.push({
        label: '',
        data: [{ x: 0, y: 0 }, { x: xMaxFV, y: 0 }],
        borderColor: '#000',
        borderWidth: 1,
        pointRadius: 0,
        fill: false
      })

      return {
        fv: {
          datasets: datasetsFV,
          x: { min: -2, max: xMaxFV },
          y: { min: -limitePif, max: limiteFlujo }
        },
        vt: {
          datasets: datasetsVT,
          x: { min: -1, max: 14 },
          y: { min: 0, max: yMaxVT }
        }
      }
    },

    // Puntos (z, fila) de la gráfica Z; solo los parámetros que traen valor
    puntosZ() {
      return ZONAS_Z
        .map((zona, i) => ({ x: numValido(this.localForm[zona.campo]), y: ZONAS_Z.length - 1 - i }))
        .filter(p => p.x !== null)
    },

    // Crea o actualiza la gráfica Z; si el informe no trae Z, la quita
    actualizarZ() {
      const canvas = this.$refs.canvasZ
      if (!canvas) return
      const puntos = this.puntosZ()
      if (puntos.length === 0) {
        if (this.graficaZ) {
          this.graficaZ.destroy()
          this.graficaZ = null
        }
        return
      }
      if (!this.graficaZ) {
        this.graficaZ = markRaw(new Chart(canvas, configuracionZ()))
      }
      this.graficaZ.data.datasets[0].data = puntos
      this.graficaZ.update('none')
    },

    // Crea o recrea ambas gráficas (botón "Regenerar")
    construirGraficas() {
      const canvasFV = this.$refs.canvasFV
      const canvasVT = this.$refs.canvasVT
      if (!canvasFV || !canvasVT) return

      this.destruirGraficas()
      const datos = this.construirDatosGraficas()

      // markRaw: evita que Vue vuelva reactiva la instancia de Chart.js (causaba
      // el desbordamiento de pila y que la primera importación no dibujara)
      this.graficaFV = markRaw(new Chart(canvasFV, configuracionGrafica(datos.fv, {
        tituloX: 'Volumen [L]', tituloY: 'Flujo [L/s]', stepY: 2
      })))
      this.graficaVT = markRaw(new Chart(canvasVT, configuracionGrafica(datos.vt, {
        tituloX: 'Tiempo [s]', tituloY: 'Volumen [L]', stepY: 1
      })))
      this.actualizarZ()
    },

    // Actualiza las gráficas existentes sin recrearlas (más ligero al editar)
    actualizarGraficas() {
      if (!this.graficaFV || !this.graficaVT) {
        this.construirGraficas()
        return
      }
      const datos = this.construirDatosGraficas()
      this.aplicarDatos(this.graficaFV, datos.fv)
      this.aplicarDatos(this.graficaVT, datos.vt)
      this.actualizarZ()
    },

    aplicarDatos(grafica, datos) {
      grafica.data.datasets = datos.datasets
      grafica.options.scales.x.min = datos.x.min
      grafica.options.scales.x.max = datos.x.max
      grafica.options.scales.y.min = datos.y.min
      grafica.options.scales.y.max = datos.y.max
      grafica.update('none')
    },

    // Agrupa los cambios seguidos (por ejemplo, al escribir) en una sola actualización
    programarGraficas() {
      clearTimeout(this._timerGraficas)
      this._timerGraficas = setTimeout(() => this.actualizarGraficas(), 150)
    },

    destruirGraficas() {
      if (this.graficaZ) {
        this.graficaZ.destroy()
        this.graficaZ = null
      }
      if (this.graficaFV) {
        this.graficaFV.destroy()
        this.graficaFV = null
      }
      if (this.graficaVT) {
        this.graficaVT.destroy()
        this.graficaVT = null
      }
    },

    descargarGrafica(tipo) {
      const grafica = { fv: this.graficaFV, vt: this.graficaVT, z: this.graficaZ }[tipo]
      if (!grafica) return
      const nombres = { fv: 'curva-flujo-volumen.png', vt: 'curva-volumen-tiempo.png', z: 'puntuacion-z.png' }
      const link = document.createElement('a')
      link.download = nombres[tipo]
      link.href = grafica.toBase64Image()
      link.click()
    },

    // ---------- Colores y validación ----------

    getColorPorcentaje(key) {
      const pct = this.localForm[key + '_porcentaje_predicho']
      if (!pct) return 'text-muted'
      if (pct >= 80) return 'text-success'
      if (pct >= 70) return 'text-warning'
      return 'text-danger'
    },
    getBadgeClass(key) {
      const pct = this.localForm[key + '_porcentaje_predicho']
      if (!pct) return 'bg-light text-muted'
      if (pct >= 80) return 'badge-success'
      if (pct >= 70) return 'badge-warning'
      return 'badge-danger'
    },
    getInterpretacion(key) {
      const pct = this.localForm[key + '_porcentaje_predicho']
      if (!pct) return '—'
      if (pct >= 80) return 'Normal'
      if (pct >= 70) return 'Límite bajo'
      return 'Anormal'
    },
    getCalidadClass() {
      const c = this.calidadSesion
      if (c === 'A') return 'text-success'
      if (c === 'B' || c === 'C') return 'text-warning'
      if (c === 'D') return 'text-danger'
      return 'text-muted'
    },
    validarCalidadSesion() {
      const detalles = []
      let ok = true
      const f = this.localForm

      if (f.fev1_var_l !== null && f.fev1_var_l !== undefined) {
        if (f.fev1_var_l <= 0.150) detalles.push(`✓ Varianza FEV1 = ${f.fev1_var_l.toFixed(3)} L (≤ 0.150 L)`)
        else { detalles.push(`✗ Varianza FEV1 = ${f.fev1_var_l.toFixed(3)} L (> 0.150 L)`); ok = false }
      } else { detalles.push('⚠ Varianza FEV1 no calculada (se necesitan ≥2 maniobras)'); ok = false }

      if (f.fvc_var_l !== null && f.fvc_var_l !== undefined) {
        if (f.fvc_var_l <= 0.150) detalles.push(`✓ Varianza FVC = ${f.fvc_var_l.toFixed(3)} L (≤ 0.150 L)`)
        else { detalles.push(`✗ Varianza FVC = ${f.fvc_var_l.toFixed(3)} L (> 0.150 L)`); ok = false }
      } else { detalles.push('⚠ Varianza FVC no calculada (se necesitan ≥2 maniobras)'); ok = false }

      const numManiobras = this.localForm.maniobras.length
      if (numManiobras >= 3) detalles.push(`✓ ${numManiobras} maniobras realizadas (≥ 3 requeridas)`)
      else { detalles.push(`✗ Solo ${numManiobras} maniobras (< 3 requeridas)`); ok = false }

      const maniobrasConFETValido = this.localForm.maniobras.filter(m => m.fet_s >= 6).length
      if (maniobrasConFETValido >= 2) detalles.push(`✓ ${maniobrasConFETValido} maniobras con FET ≥ 6 seg`)
      else detalles.push(`⚠ Solo ${maniobrasConFETValido} maniobras con FET ≥ 6 seg`)

      this.validacionCalidad = { ok, detalles }
    }
  }
}
</script>

<style scoped>
.seccion-parametros-fvl-espirometria { background: #f8f9fa; }
.card { border: 1px solid #dee2e6; box-shadow: 0 2px 4px rgba(0,0,0,0.08); }
.card-header { background: linear-gradient(135deg, #43A047 0%, #2E7D32 100%) !important; color: white !important; border-bottom: none; padding: 0.75rem 1.25rem; }
.card-body { padding: 1.25rem; }
.form-control { border-radius: 4px; border: 1px solid #ced4da; font-size: 14px; padding: 10px 12px; }
.form-control:focus { border-color: #43A047; box-shadow: 0 0 0 0.2rem rgba(67, 160, 71, 0.25); }
.form-control-sm { font-size: 0.8125rem; padding: 4px 8px; }
.badge { font-size: 0.75rem; padding: 0.375rem 0.75rem; border-radius: 2rem; }
.badge-success { background-color: #28a745; color: white; }
.badge-warning { background-color: #ffc107; color: #212529; }
.badge-danger { background-color: #dc3545; color: white; }
.text-success { color: #28a745 !important; }
.text-warning { color: #ffc107 !important; }
.text-danger { color: #dc3545 !important; }
.text-muted { color: #6c757d !important; }
.text-blue-700 { color: #1e40af !important; }
.font-weight-bold { font-weight: 600 !important; }
.bg-light { background-color: #f8f9fa !important; }
.bg-white { background-color: #fff !important; }
.bg-blue-50 { background-color: #eff6ff !important; }
.border { border: 1px solid #dee2e6 !important; }
.border-top { border-top: 1px solid #dee2e6 !important; }
.border-success { border-color: #28a745 !important; }
.border-danger { border-color: #dc3545 !important; }
.rounded { border-radius: 0.25rem !important; }
.p-3 { padding: 1rem !important; }
.mt-1 { margin-top: 0.25rem !important; }
.mt-2 { margin-top: 0.5rem !important; }
.mt-3 { margin-top: 1rem !important; }
.mt-4 { margin-top: 1.5rem !important; }
.mb-2 { margin-bottom: 0.5rem !important; }
.mb-3 { margin-bottom: 1rem !important; }
.mb-4 { margin-bottom: 1.5rem !important; }
.px-4 { padding-left: 1.5rem !important; padding-right: 1.5rem !important; }
.py-2 { padding-top: 0.5rem !important; padding-bottom: 0.5rem !important; }
.d-flex { display: flex !important; }
.justify-content-between { justify-content: space-between !important; }
.justify-content-end { justify-content: flex-end !important; }
.align-items-center { align-items: center !important; }
.text-center { text-align: center !important; }
.text-nowrap { white-space: nowrap !important; }
.text-sm { font-size: 0.875rem !important; }
.small { font-size: 0.8125rem !important; }
.table { width: 100%; margin-bottom: 1rem; color: #212529; background-color: #fff; border-collapse: collapse; }
.table th, .table td { padding: 0.5rem; vertical-align: middle; border-top: 1px solid #dee2e6; }
.table thead th { vertical-align: bottom; border-bottom: 2px solid #dee2e6; background-color: #f8f9fa; }
.table-hover tbody tr:hover { background-color: rgba(0, 0, 0, 0.03); }
.table-bordered { border: 1px solid #dee2e6; }
.table-bordered th, .table-bordered td { border: 1px solid #dee2e6; }
.align-middle { vertical-align: middle !important; }
.bg-opacity-10 { background-color: rgba(40, 167, 69, 0.1) !important; }
.btn-xs { padding: 2px 6px; font-size: 10px; line-height: 1.5; border-radius: 0.2rem; }

/* Tarjeta de gráficas (mismo estilo que la sección del audiómetro) */
.titulo-grafica { text-align: center; font-weight: 600; color: #37474f; margin-bottom: 8px; }

/* Contenedor de las gráficas: alto fijo para que Chart.js no crezca al redibujar */
.grafica-contenedor { position: relative; height: 340px; width: 100%; }

/* Leyenda inferior de las gráficas */
.leyenda-graficas { background: #fafcfb; border: 1px solid #dfe6e9; border-radius: 4px; padding: 12px 16px; margin-top: 8px; font-size: 13px; color: #37474f; }
.leyenda-graficas-titulo { font-size: 12px; font-weight: 600; margin-bottom: 8px; }
.leyenda-graficas .sw { display: inline-block; width: 28px; border-top: 3px solid; vertical-align: middle; margin-right: 8px; }
.sw-mejor { border-color: #1F3FA0; }
.sw-prueba { border-top-style: dotted !important; border-color: #5B8DEF; }
.sw-predicho { border-top-style: dashed !important; border-color: #9AA5B1; }
.pt-fev1 { display: inline-block; width: 9px; height: 9px; border-radius: 50%; background: #1F3FA0; vertical-align: middle; margin: 0 12px 0 10px; }
.nota-graficas { margin-top: 8px; font-size: 12px; color: #607d8b; }

/* Pie de cada gráfica: leyenda centrada debajo de la gráfica, con el título del eje X en la misma línea */
.pie-grafica { display: flex; flex-wrap: wrap; align-items: center; justify-content: center; gap: 8px 18px; margin-top: 6px; font-size: 12px; color: #37474f; }
.item-leyenda { white-space: nowrap; }
.eje-x-titulo { font-weight: 600; white-space: nowrap; }

/* Símbolos rectangulares de la leyenda */
.caja-leyenda { display: inline-block; width: 24px; height: 12px; border: 2px solid; margin-right: 6px; vertical-align: middle; }
.caja-leyenda.sw-mejor { border: 3px solid #1F3FA0; background: rgba(31, 63, 160, 0.08); }
.caja-leyenda.sw-prueba { border-style: dotted; border-color: #5B8DEF; background: rgba(91, 141, 239, 0.08); }
.caja-leyenda.sw-predicho { border-style: dashed; border-color: #9AA5B1; background: rgba(154, 165, 177, 0.2); }

/* Celdas con valor marcado en el informe (asterisco) */
.celda-marcada { background-color: #FFE8A3 !important; box-shadow: inset 0 0 0 2px #F5B400; }
.leyenda-marcada-swatch { display: inline-block; width: 14px; height: 14px; background: #FFE8A3; border: 2px solid #F5B400; vertical-align: middle; margin-right: 6px; }

@media (max-width: 768px) {
  .col-md-4 { flex: 0 0 100%; max-width: 100%; }
  .table-responsive { overflow-x: auto; }
  .grafica-contenedor { height: 280px; }
}
</style>