<template>
  <div class="hoja4-audiometria-delli">
    <!-- ALERT INFO -->
    <div class="alert alert-info">
      <i class="fas fa-info-circle mr-2"></i>
      <strong>Instrucciones:</strong> Ingrese los umbrales de audición en dB HL. El sistema redondeará automáticamente a múltiplos de 5 dB (estándar AD226) y calculará PTA, clasificación y gráficas.
    </div>

    <!-- SECTION: DATOS DE TRAZABILIDAD Y CONDICIONES (NOM-011) -->
    <div class="card mb-3">
      <div class="card-header" style="background: linear-gradient(135deg, #5F6E7E 0%, #4A5568 100%); color: white;">
        <h5 class="mb-0">
          <i class="fas fa-clipboard-check mr-2"></i> DATOS DE LA PRUEBA Y CONDICIONES
        </h5>
      </div>
      <div class="card-body">
        <div class="row">
          <!-- TIPO DE AUDIOMETRÍA -->
          <div class="col-md-4 mb-3">
            <label class="font-weight-bold">TIPO DE AUDIOMETRÍA <span class="text-danger">*</span></label>
            <select class="form-control" v-model="localData.tipo_audiometria" @change="emitChange" style="height: 38px;">
              <option value="">-- Seleccionar --</option>
              <option value="Inicial">Inicial (Ingreso)</option>
              <option value="Periodica">Periódica (Anual)</option>
              <option value="Egreso">De Salida (Egreso)</option>
              <option value="Post-exposicion">Post-exposición / Control</option>
            </select>
            <small class="text-muted">Requerido para trazabilidad NOM-011</small>
          </div>

          <!-- CALIBRACIÓN DEL EQUIPO -->
          <div class="col-md-4 mb-3">
            <label class="font-weight-bold">ÚLTIMA CALIBRACIÓN (AD226)</label>
            <input type="date" class="form-control" v-model="localData.fecha_ultima_calibracion" @change="emitChange" style="height: 38px;">
            <small class="text-muted">Req. NOM-011 Art. 7.1</small>
          </div>

          <!-- EXPOSICIÓN RECIENTE -->
          <div class="col-md-4 mb-3">
            <label class="font-weight-bold">¿ÚLTIMAS 14 HORAS EXPUESTO A RUIDO ≥80dB?</label>
            <label class="custom-control custom-radio d-block">
              <input type="radio" class="custom-control-input" name="exposicion_14h" value="true" v-model="localData.exposicion_14horas" @change="emitChange">
              <span class="custom-control-label text-danger font-weight-bold">SÍ (Reprogramar)</span>
            </label>
            <label class="custom-control custom-radio d-block">
              <input type="radio" class="custom-control-input" name="exposicion_14h" value="false" v-model="localData.exposicion_14horas" @change="emitChange">
              <span class="custom-control-label text-success font-weight-bold">NO (Válida)</span>
            </label>
          </div>
        </div>
      </div>
    </div>

    <!-- SECTION: AUDIOMETRÍA TONAL PURA - VÍA AÉREA -->
    <div class="card mb-3">
      <div class="card-header" style="background: linear-gradient(135deg, #5F6E7E 0%, #4A5568 100%); color: white;">
        <h5 class="mb-0">
          <i class="fas fa-wave-square mr-2"></i> AUDIOMETRÍA TONAL PURA - VÍA AÉREA (dB HL)
        </h5>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-sm table-bordered">
            <thead style="background: #ecf0f1; text-align: center;">
              <tr>
                <th colspan="10">FRECUENCIAS (Hz)</th>
              </tr>
              <tr>
                <th style="width: 15%;">OÍDO</th>
                <th>125</th>
                <th>250</th>
                <th>500</th>
                <th>1000</th>
                <th>2000</th>
                <th>3000</th>
                <th>4000</th>
                <th>6000</th>
                <th>8000</th>
              </tr>
            </thead>
            <tbody>
              <!-- OÍDO DERECHO -->
              <tr style="background: #e8f4f8;">
                <td class="font-weight-bold">DERECHO</td>
                <td><input type="number" class="form-control form-control-sm" v-model.number="localData.audiometria_125_der" @change="onAudiometriaChange" min="-10" max="120" style="text-align: center;"></td>
                <td><input type="number" class="form-control form-control-sm" v-model.number="localData.audiometria_250_der" @change="onAudiometriaChange" min="-10" max="120" style="text-align: center;"></td>
                <td><input type="number" class="form-control form-control-sm" v-model.number="localData.audiometria_500_der" @change="onAudiometriaChange" min="-10" max="120" style="text-align: center;"></td>
                <td><input type="number" class="form-control form-control-sm" v-model.number="localData.audiometria_1000_der" @change="onAudiometriaChange" min="-10" max="120" style="text-align: center;"></td>
                <td><input type="number" class="form-control form-control-sm" v-model.number="localData.audiometria_2000_der" @change="onAudiometriaChange" min="-10" max="120" style="text-align: center;"></td>
                <td><input type="number" class="form-control form-control-sm" v-model.number="localData.audiometria_3000_der" @change="onAudiometriaChange" min="-10" max="120" style="text-align: center;"></td>
                <td><input type="number" class="form-control form-control-sm" v-model.number="localData.audiometria_4000_der" @change="onAudiometriaChange" min="-10" max="120" style="text-align: center;"></td>
                <td><input type="number" class="form-control form-control-sm" v-model.number="localData.audiometria_6000_der" @change="onAudiometriaChange" min="-10" max="120" style="text-align: center;"></td>
                <td><input type="number" class="form-control form-control-sm" v-model.number="localData.audiometria_8000_der" @change="onAudiometriaChange" min="-10" max="120" style="text-align: center;"></td>
              </tr>

              <!-- OÍDO IZQUIERDO -->
              <tr style="background: #fff3e0;">
                <td class="font-weight-bold">IZQUIERDO</td>
                <td><input type="number" class="form-control form-control-sm" v-model.number="localData.audiometria_125_izq" @change="onAudiometriaChange" min="-10" max="120" style="text-align: center;"></td>
                <td><input type="number" class="form-control form-control-sm" v-model.number="localData.audiometria_250_izq" @change="onAudiometriaChange" min="-10" max="120" style="text-align: center;"></td>
                <td><input type="number" class="form-control form-control-sm" v-model.number="localData.audiometria_500_izq" @change="onAudiometriaChange" min="-10" max="120" style="text-align: center;"></td>
                <td><input type="number" class="form-control form-control-sm" v-model.number="localData.audiometria_1000_izq" @change="onAudiometriaChange" min="-10" max="120" style="text-align: center;"></td>
                <td><input type="number" class="form-control form-control-sm" v-model.number="localData.audiometria_2000_izq" @change="onAudiometriaChange" min="-10" max="120" style="text-align: center;"></td>
                <td><input type="number" class="form-control form-control-sm" v-model.number="localData.audiometria_3000_izq" @change="onAudiometriaChange" min="-10" max="120" style="text-align: center;"></td>
                <td><input type="number" class="form-control form-control-sm" v-model.number="localData.audiometria_4000_izq" @change="onAudiometriaChange" min="-10" max="120" style="text-align: center;"></td>
                <td><input type="number" class="form-control form-control-sm" v-model.number="localData.audiometria_6000_izq" @change="onAudiometriaChange" min="-10" max="120" style="text-align: center;"></td>
                <td><input type="number" class="form-control form-control-sm" v-model.number="localData.audiometria_8000_izq" @change="onAudiometriaChange" min="-10" max="120" style="text-align: center;"></td>
              </tr>
            </tbody>
          </table>
        </div>
        <small class="text-muted d-block mt-2">Ingrese valores entre -10 y 120 dB HL. Los valores vacíos no se consideran en el cálculo.</small>
      </div>
    </div>

    <!-- SECTION: VÍA ÓSEA -->
    <div class="card mb-3">
      <div class="card-header" style="background: linear-gradient(135deg, #5F6E7E 0%, #4A5568 100%); color: white;">
        <h5 class="mb-0">
          <i class="fas fa-bone mr-2"></i> VÍA ÓSEA (dB HL) - OPCIONAL
        </h5>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-sm table-bordered">
            <thead style="background: #ecf0f1; text-align: center;">
              <tr>
                <th colspan="7">FRECUENCIAS (Hz)</th>
              </tr>
              <tr>
                <th style="width: 15%;">OÍDO</th>
                <th>250</th>
                <th>500</th>
                <th>1000</th>
                <th>2000</th>
                <th>3000</th>
                <th>4000</th>
              </tr>
            </thead>
            <tbody>
              <!-- VÍA ÓSEA DERECHA -->
              <tr style="background: #ffe6e6;">
                <td class="font-weight-bold">DERECHA (◁)</td>
                <td><input type="number" class="form-control form-control-sm" v-model.number="localData.via_osea_250_der" @change="onAudiometriaChange" min="-10" max="120" style="text-align: center;"></td>
                <td><input type="number" class="form-control form-control-sm" v-model.number="localData.via_osea_500_der" @change="onAudiometriaChange" min="-10" max="120" style="text-align: center;"></td>
                <td><input type="number" class="form-control form-control-sm" v-model.number="localData.via_osea_1000_der" @change="onAudiometriaChange" min="-10" max="120" style="text-align: center;"></td>
                <td><input type="number" class="form-control form-control-sm" v-model.number="localData.via_osea_2000_der" @change="onAudiometriaChange" min="-10" max="120" style="text-align: center;"></td>
                <td><input type="number" class="form-control form-control-sm" v-model.number="localData.via_osea_3000_der" @change="onAudiometriaChange" min="-10" max="120" style="text-align: center;"></td>
                <td><input type="number" class="form-control form-control-sm" v-model.number="localData.via_osea_4000_der" @change="onAudiometriaChange" min="-10" max="120" style="text-align: center;"></td>
              </tr>

              <!-- VÍA ÓSEA IZQUIERDA -->
              <tr style="background: #e6f0ff;">
                <td class="font-weight-bold">IZQUIERDA (▷)</td>
                <td><input type="number" class="form-control form-control-sm" v-model.number="localData.via_osea_250_izq" @change="onAudiometriaChange" min="-10" max="120" style="text-align: center;"></td>
                <td><input type="number" class="form-control form-control-sm" v-model.number="localData.via_osea_500_izq" @change="onAudiometriaChange" min="-10" max="120" style="text-align: center;"></td>
                <td><input type="number" class="form-control form-control-sm" v-model.number="localData.via_osea_1000_izq" @change="onAudiometriaChange" min="-10" max="120" style="text-align: center;"></td>
                <td><input type="number" class="form-control form-control-sm" v-model.number="localData.via_osea_2000_izq" @change="onAudiometriaChange" min="-10" max="120" style="text-align: center;"></td>
                <td><input type="number" class="form-control form-control-sm" v-model.number="localData.via_osea_3000_izq" @change="onAudiometriaChange" min="-10" max="120" style="text-align: center;"></td>
                <td><input type="number" class="form-control form-control-sm" v-model.number="localData.via_osea_4000_izq" @change="onAudiometriaChange" min="-10" max="120" style="text-align: center;"></td>
              </tr>
            </tbody>
          </table>
        </div>
        <small class="text-muted d-block mt-2">La vía ósea solo se evalúa en frecuencias de 250 a 4000 Hz. Deje vacío si no aplica.</small>
      </div>
    </div>

    <!-- SECTION: ENMASCARAMIENTO -->
    <div class="card mb-3">
      <div class="card-header" style="background: linear-gradient(135deg, #5F6E7E 0%, #4A5568 100%); color: white;">
        <h5 class="mb-0">
          <i class="fas fa-mask mr-2"></i> ENMASCARAMIENTO (dB HL) - OPCIONAL
        </h5>
      </div>
      <div class="card-body">
        <div class="alert alert-warning mb-3">
          <small>
            <i class="fas fa-exclamation-triangle"></i> 
            <strong>¿Cuándo aplicar enmascaramiento?</strong> Cuando la diferencia entre vía aérea y vía ósea sea ≥10 dB, 
            o cuando la diferencia entre oídos sea ≥40 dB en vía aérea o ≥15 dB en vía ósea.
          </small>
        </div>
        <div class="table-responsive">
          <table class="table table-sm table-bordered">
            <thead style="background: #ecf0f1; text-align: center;">
              <tr>
                <th colspan="7">FRECUENCIAS (Hz)</th>
              </tr>
              <tr>
                <th style="width: 15%;">OÍDO</th>
                <th>250</th>
                <th>500</th>
                <th>1000</th>
                <th>2000</th>
                <th>3000</th>
                <th>4000</th>
              </tr>
            </thead>
            <tbody>
              <!-- ENMASCARAMIENTO DERECHO -->
              <tr style="background: #fff5f5;">
                <td class="font-weight-bold">DERECHO (△)</td>
                <td><input type="number" class="form-control form-control-sm" v-model.number="localData.enmascarado_250_der" @change="onAudiometriaChange" min="-10" max="120" style="text-align: center;"></td>
                <td><input type="number" class="form-control form-control-sm" v-model.number="localData.enmascarado_500_der" @change="onAudiometriaChange" min="-10" max="120" style="text-align: center;"></td>
                <td><input type="number" class="form-control form-control-sm" v-model.number="localData.enmascarado_1000_der" @change="onAudiometriaChange" min="-10" max="120" style="text-align: center;"></td>
                <td><input type="number" class="form-control form-control-sm" v-model.number="localData.enmascarado_2000_der" @change="onAudiometriaChange" min="-10" max="120" style="text-align: center;"></td>
                <td><input type="number" class="form-control form-control-sm" v-model.number="localData.enmascarado_3000_der" @change="onAudiometriaChange" min="-10" max="120" style="text-align: center;"></td>
                <td><input type="number" class="form-control form-control-sm" v-model.number="localData.enmascarado_4000_der" @change="onAudiometriaChange" min="-10" max="120" style="text-align: center;"></td>
              </tr>

              <!-- ENMASCARAMIENTO IZQUIERDO -->
              <tr style="background: #f0f7ff;">
                <td class="font-weight-bold">IZQUIERDO (□)</td>
                <td><input type="number" class="form-control form-control-sm" v-model.number="localData.enmascarado_250_izq" @change="onAudiometriaChange" min="-10" max="120" style="text-align: center;"></td>
                <td><input type="number" class="form-control form-control-sm" v-model.number="localData.enmascarado_500_izq" @change="onAudiometriaChange" min="-10" max="120" style="text-align: center;"></td>
                <td><input type="number" class="form-control form-control-sm" v-model.number="localData.enmascarado_1000_izq" @change="onAudiometriaChange" min="-10" max="120" style="text-align: center;"></td>
                <td><input type="number" class="form-control form-control-sm" v-model.number="localData.enmascarado_2000_izq" @change="onAudiometriaChange" min="-10" max="120" style="text-align: center;"></td>
                <td><input type="number" class="form-control form-control-sm" v-model.number="localData.enmascarado_3000_izq" @change="onAudiometriaChange" min="-10" max="120" style="text-align: center;"></td>
                <td><input type="number" class="form-control form-control-sm" v-model.number="localData.enmascarado_4000_izq" @change="onAudiometriaChange" min="-10" max="120" style="text-align: center;"></td>
              </tr>
            </tbody>
          </table>
        </div>
        <small class="text-muted d-block mt-2">
          <strong>Símbolos:</strong> △ = Enmascarado derecho (rojo) | □ = Enmascarado izquierdo (azul)
        </small>
      </div>
    </div>

    <!-- SECTION: GRÁFICAS AUDIOMÉTRICAS -->
    <div class="card mb-3">
      <div class="card-header" style="background: linear-gradient(135deg, #5F6E7E 0%, #4A5568 100%); color: white;">
        <h5 class="mb-0">
          <i class="fas fa-chart-line mr-2"></i> GRÁFICAS AUDIOMÉTRICAS
        </h5>
      </div>
      <div class="card-body">
        <div class="row">
          <!-- OÍDO DERECHO -->
          <div class="col-md-6 mb-3">
            <h6 class="font-weight-bold text-center mb-3" style="color: #dc3545;">
              <i class="fas fa-ear-listen"></i> OÍDO DERECHO (Vía Aérea)
            </h6>
            <div style="position: relative; height: 350px;">
              <canvas id="audiometriaChartDerecho"></canvas>
            </div>
          </div>

          <!-- OÍDO IZQUIERDO -->
          <div class="col-md-6 mb-3">
            <h6 class="font-weight-bold text-center mb-3" style="color: #0d6efd;">
              <i class="fas fa-ear-listen"></i> OÍDO IZQUIERDO (Vía Aérea)
            </h6>
            <div style="position: relative; height: 350px;">
              <canvas id="audiometriaChartIzquierdo"></canvas>
            </div>
          </div>

          <!-- LEYENDA ESTÁNDAR ISO 8253-1 -->
          <div class="col-12 mt-3">
            <div class="alert alert-light border p-2 mb-0">
              <small class="text-muted d-block mb-2"><strong>Leyenda Estándar ISO 8253-1:</strong></small>
              <div class="row" style="font-size: 11px;">
                <div class="col-md-6">
                  <div class="d-flex align-items-center mb-1">
                    <span style="color: #dc3545; font-size: 16px; margin-right: 8px;">○</span>
                    <span>Oído Derecho Vía Aérea</span>
                  </div>
                  <div class="d-flex align-items-center mb-1">
                    <span style="color: #dc3545; font-size: 16px; margin-right: 8px;">△</span>
                    <span>Oído Derecho Enmascarado</span>
                  </div>
                  <div class="d-flex align-items-center mb-1">
                    <span style="color: #28a745; font-size: 16px; margin-right: 8px;">◁</span>
                    <span>Vía Ósea Derecha</span>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="d-flex align-items-center mb-1">
                    <span style="color: #0d6efd; font-size: 16px; margin-right: 8px;">✕</span>
                    <span>Oído Izquierdo Vía Aérea</span>
                  </div>
                  <div class="d-flex align-items-center mb-1">
                    <span style="color: #0d6efd; font-size: 16px; margin-right: 8px;">□</span>
                    <span>Oído Izquierdo Enmascarado</span>
                  </div>
                  <div class="d-flex align-items-center mb-1">
                    <span style="color: #198754; font-size: 16px; margin-right: 8px;">▷</span>
                    <span>Vía Ósea Izquierda</span>
                  </div>
                </div>
              </div>
              <div class="mt-2 pt-2 border-top">
                <small class="text-muted">
                  <span style="color: #28a745; font-weight: bold;">- - -</span> Línea de referencia: 20 dB HL (Límite de normalidad)
                </small>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- SECTION: CÁLCULOS AUTOMÁTICOS -->
    <div class="card mb-3">
      <div class="card-header" style="background: linear-gradient(135deg, #5F6E7E 0%, #4A5568 100%); color: white;">
        <h5 class="mb-0">
          <i class="fas fa-calculator mr-2"></i> CÁLCULOS AUTOMÁTICOS
        </h5>
      </div>
      <div class="card-body">
        <div class="row">
          <!-- PTA DERECHO -->
          <div class="col-md-3 text-center">
            <div class="alert alert-primary">
              <small class="d-block text-muted">PTA DERECHO</small>
              <h4 class="mb-0">{{ localData.pta_der }} dB</h4>
              <small class="text-muted">Promedio 500,1k,2k,3k Hz</small>
            </div>
          </div>

          <!-- PTA IZQUIERDO -->
          <div class="col-md-3 text-center">
            <div class="alert alert-warning">
              <small class="d-block text-muted">PTA IZQUIERDO</small>
              <h4 class="mb-0">{{ localData.pta_izq }} dB</h4>
              <small class="text-muted">Promedio 500,1k,2k,3k Hz</small>
            </div>
          </div>

          <!-- PTA PROMEDIO -->
          <div class="col-md-3 text-center">
            <div class="alert alert-info">
              <small class="d-block text-muted">PTA PROMEDIO</small>
              <h4 class="mb-0">{{ localData.pta_promedio }} dB</h4>
              <small class="text-muted">(Der + Izq) / 2</small>
            </div>
          </div>

          <!-- CLASIFICACIÓN -->
          <div class="col-md-3 text-center">
            <div :class="'alert ' + getAlertClass(localData.pta_promedio)">
              <small class="d-block text-muted">CLASIFICACIÓN</small>
              <h4 class="mb-0" style="font-size: 16px;">{{ localData.clasificacion || 'PENDIENTE' }}</h4>
              <small class="text-muted">Según PTA Promedio</small>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- SECTION: VALORACIÓN -->
    <div class="card mb-3">
      <div class="card-header" style="background: linear-gradient(135deg, #5F6E7E 0%, #4A5568 100%); color: white;">
        <h5 class="mb-0">
          <i class="fas fa-comments mr-2"></i> VALORACIÓN
        </h5>
      </div>
      <div class="card-body">
        <div class="row">
          <div class="col-md-12 mb-3">
            <label class="font-weight-bold">VALORACIÓN</label>
            <textarea
              class="form-control"
              v-model="localData.valoracion"
              @change="emitChange"
              placeholder="Describa su valoración clínica y conclusiones"
              rows="3"
              style="border-radius: 4px; padding: 10px 12px;"
            ></textarea>
          </div>
        </div>
      </div>
    </div>

    <!-- SECTION: INTERPRETACIÓN Y DIAGNÓSTICO -->
    <div class="card mb-3">
      <div class="card-header" style="background: linear-gradient(135deg, #5F6E7E 0%, #4A5568 100%); color: white;">
        <h5 class="mb-0">
          <i class="fas fa-clipboard-list mr-2"></i> INTERPRETACIÓN AUDIOLÓGICA
        </h5>
      </div>
      <div class="card-body">
        <div class="row">
          <div class="col-md-12 mb-3">
            <label class="font-weight-bold">INTERPRETACIÓN (Notas del Audiólogo)</label>
            <textarea
              class="form-control"
              v-model="localData.interpretacion"
              @change="emitChange"
              placeholder="Describa hallazgos, patrones audiométricos, simetría, etc."
              rows="3"
              style="border-radius: 4px; padding: 10px 12px;"
            ></textarea>
          </div>

          <div class="col-md-6 mb-3">
            <label class="font-weight-bold">TIPO DE HIPOACUSIA <span class="text-danger">*</span></label>
            <select class="form-control" v-model="localData.tipo_hipoacusia" @change="emitChange" style="height: 38px;">
              <option value="">-- Seleccionar --</option>
              <option value="Normal">Normal</option>
              <option value="Neurosensorial">Neurosensorial</option>
              <option value="Conductiva">Conductiva</option>
              <option value="Mixta">Mixta</option>
            </select>
          </div>

          <div class="col-md-6 mb-3">
            <label class="font-weight-bold">RESTRICCIONES LABORALES</label>
            <textarea
              class="form-control"
              v-model="localData.restricciones"
              @change="emitChange"
              placeholder="Si aplica (ej: evitar exposición a ruido >80dB)"
              rows="2"
              style="border-radius: 4px; padding: 10px 12px;"
            ></textarea>
          </div>
        </div>
      </div>
    </div>

    <!-- SECTION: APTITUD Y RECOMENDACIONES -->
    <div class="card">
      <div class="card-header" style="background: linear-gradient(135deg, #5F6E7E 0%, #4A5568 100%); color: white;">
        <h5 class="mb-0">
          <i class="fas fa-check-square mr-2"></i> APTITUD Y RECOMENDACIONES
        </h5>
      </div>
      <div class="card-body">
        <div class="row">
          <div class="col-md-4 mb-3">
            <label class="font-weight-bold">APTITUD <span class="text-danger">* OBLIGATORIA</span></label>
            <select class="form-control" v-model="localData.aptitud" @change="emitChange" style="height: 38px; border: 2px solid #dc3545;">
              <option value="">-- DEBE SELECCIONAR --</option>
              <option value="Apto">✓ APTO</option>
              <option value="Apto con restricciones">⚠️ APTO CON RESTRICCIONES</option>
              <option value="No apto">✗ NO APTO</option>
            </select>
            <small class="text-danger d-block mt-1">Este campo es REQUERIDO para guardar.</small>
          </div>

          <div class="col-md-8">
            <label class="font-weight-bold">RECOMENDACIONES FIJAS</label>
            <div class="alert alert-success">
              <ul class="mb-0 pl-3">
                <li>✓ Usar protección auditiva personal cuando exposición ≥85 dB(A)</li>
                <li>✓ Realizar audiometría de control anualmente (mínimo)</li>
                <li>✓ Revisar medidas técnicas y organizacionales de control</li>
                <li>✓ Informar al trabajador de resultados y recomendaciones</li>
              </ul>
            </div>
          </div>
        </div>

        <!-- RESUMEN FINAL -->
        <div class="alert alert-secondary mt-3">
          <h6 class="font-weight-bold mb-2">📋 RESUMEN DE EVALUACIÓN</h6>
          <small>
            <strong>Tipo:</strong> {{ localData.tipo_audiometria || 'No especificado' }} |
            <strong>PTA Promedio:</strong> {{ localData.pta_promedio }} dB |
            <strong>Clasificación:</strong> {{ localData.clasificacion || 'Pendiente' }} |
            <strong>Aptitud:</strong> <span class="font-weight-bold" :class="localData.aptitud === 'No apto' ? 'text-danger' : 'text-success'">{{ localData.aptitud || 'NO SELECCIONADA' }}</span>
          </small>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import Chart from 'chart.js/auto'

export default {
  name: 'Hoja4AudiometriaDelli',
  props: {
    modelValue: {
      type: Object,
      default: () => ({})
    }
  },
  data() {
    return {
      localData: {
        // NUEVOS CAMPOS DE TRAZABILIDAD NOM-011
        tipo_audiometria: '',
        fecha_ultima_calibracion: '',
        
        exposicion_14horas: 'false',
        // VÍA AÉREA
        audiometria_125_der: null,
        audiometria_250_der: null,
        audiometria_500_der: null,
        audiometria_1000_der: null,
        audiometria_2000_der: null,
        audiometria_3000_der: null,
        audiometria_4000_der: null,
        audiometria_6000_der: null,
        audiometria_8000_der: null,
        audiometria_125_izq: null,
        audiometria_250_izq: null,
        audiometria_500_izq: null,
        audiometria_1000_izq: null,
        audiometria_2000_izq: null,
        audiometria_3000_izq: null,
        audiometria_4000_izq: null,
        audiometria_6000_izq: null,
        audiometria_8000_izq: null,
        // VÍA ÓSEA
        via_osea_250_der: null,
        via_osea_500_der: null,
        via_osea_1000_der: null,
        via_osea_2000_der: null,
        via_osea_3000_der: null,
        via_osea_4000_der: null,
        via_osea_250_izq: null,
        via_osea_500_izq: null,
        via_osea_1000_izq: null,
        via_osea_2000_izq: null,
        via_osea_3000_izq: null,
        via_osea_4000_izq: null,
        // ENMASCARAMIENTO
        enmascarado_250_der: null,
        enmascarado_500_der: null,
        enmascarado_1000_der: null,
        enmascarado_2000_der: null,
        enmascarado_3000_der: null,
        enmascarado_4000_der: null,
        enmascarado_250_izq: null,
        enmascarado_500_izq: null,
        enmascarado_1000_izq: null,
        enmascarado_2000_izq: null,
        enmascarado_3000_izq: null,
        enmascarado_4000_izq: null,
        // CÁLCULOS
        pta_der: 0,
        pta_izq: 0,
        pta_promedio: 0,
        clasificacion: '',
        interpretacion: '',
        tipo_hipoacusia: '',
        restricciones: '',
        aptitud: '',
        valoracion: ''
      },
      chartDerechoInstance: null,
      chartIzquierdoInstance: null,
      frecuencias: [125, 250, 500, 1000, 2000, 3000, 4000, 6000, 8000],
      frecuenciasOsea: [250, 500, 1000, 2000, 3000, 4000]
    }
  },
  watch: {
    modelValue: {
      handler(newVal) {
        if (newVal && Object.keys(newVal).length > 0) {
          this.localData = { ...this.localData, ...newVal }
          this.$nextTick(() => {
            this.calcularPTA()
            this.actualizarGrafica()
          })
        }
      },
      deep: true,
      immediate: true
    }
  },
  methods: {
    onAudiometriaChange() {
      // 1. Primero validamos y corregimos a múltiplos de 5 dB (estándar AD226)
      this.validarYRedondear5dB();
      
      // 2. Luego calculamos y actualizamos
      this.calcularPTA()
      this.actualizarGrafica()
      this.emitChange()
    },
    
    // NUEVO MÉTODO: Validación y redondeo a pasos de 5 dB
    validarYRedondear5dB() {
      const campos = Object.keys(this.localData).filter(k => 
        k.includes('audiometria_') || k.includes('via_osea_') || k.includes('enmascarado_')
      )
      campos.forEach(campo => {
        let val = this.localData[campo]
        if (val !== null && val !== undefined && val !== '') {
          const valorRedondeado = Math.round(val / 5) * 5
          if (val !== valorRedondeado) {
            console.warn(`[AD226 Validator] Campo ${campo}: Valor ${val} dB ajustado automáticamente a ${valorRedondeado} dB (múltiplo de 5)`)
            this.localData[campo] = valorRedondeado
          }
        }
      })
    },

    calcularPTA() {
      const calcularPromedio = (valores) => {
        const validos = valores.filter(v => v !== null && v !== undefined && v !== '')
        return validos.length > 0 ? Math.round(validos.reduce((a, b) => a + b, 0) / validos.length) : 0
      }

      this.localData.pta_der = calcularPromedio([
        this.localData.audiometria_500_der,
        this.localData.audiometria_1000_der,
        this.localData.audiometria_2000_der,
        this.localData.audiometria_3000_der
      ])

      this.localData.pta_izq = calcularPromedio([
        this.localData.audiometria_500_izq,
        this.localData.audiometria_1000_izq,
        this.localData.audiometria_2000_izq,
        this.localData.audiometria_3000_izq
      ])

      if (this.localData.pta_der > 0 && this.localData.pta_izq > 0) {
        this.localData.pta_promedio = Math.round((this.localData.pta_der + this.localData.pta_izq) / 2)
      } else {
        this.localData.pta_promedio = this.localData.pta_der > 0 ? this.localData.pta_der : this.localData.pta_izq
      }

      this.clasificarHipoacusia()
    },
    clasificarHipoacusia() {
      const pta = this.localData.pta_promedio
      if (pta === 0 && !this.localData.audiometria_500_der) {
        this.localData.clasificacion = ''
      } else if (pta <= 20) {
        this.localData.clasificacion = 'NORMAL'
      } else if (pta <= 40) {
        this.localData.clasificacion = 'HIPOACUSIA LEVE'
      } else if (pta <= 60) {
        this.localData.clasificacion = 'HIPOACUSIA MODERADA'
      } else if (pta <= 90) {
        this.localData.clasificacion = 'HIPOACUSIA SEVERA'
      } else {
        this.localData.clasificacion = 'HIPOACUSIA PROFUNDA'
      }
    },
    getAlertClass(pta) {
      if (!pta) return 'alert-secondary'
      if (pta <= 20) return 'alert-success'
      if (pta <= 60) return 'alert-warning'
      return 'alert-danger'
    },
    obtenerDatosOido(oido) {
      const sufijo = oido === 'derecho' ? '_der' : '_izq'
      return this.frecuencias.map(f => this.localData[`audiometria_${f}${sufijo}`])
    },
    obtenerDatosOsea(oido) {
      const sufijo = oido === 'derecho' ? '_der' : '_izq'
      return this.frecuenciasOsea.map(f => this.localData[`via_osea_${f}${sufijo}`])
    },
    obtenerDatosEnmascarado(oido) {
      const sufijo = oido === 'derecho' ? '_der' : '_izq'
      return this.frecuenciasOsea.map(f => this.localData[`enmascarado_${f}${sufijo}`])
    },
    mapearDatosAuxiliares(datosAuxiliares) {
      const resultado = Array(this.frecuencias.length).fill(null)
      const indices = [1, 2, 3, 4, 5, 6]
      
      indices.forEach((indice, i) => {
        if (datosAuxiliares[i] !== null && datosAuxiliares[i] !== undefined) {
          resultado[indice] = datosAuxiliares[i]
        }
      })
      
      return resultado
    },
    actualizarGrafica() {
      this.$nextTick(() => {
        const datosAireDerechos = this.obtenerDatosOido('derecho')
        const datosAireIzquierdos = this.obtenerDatosOido('izquierdo')
        const datosOseaDerechos = this.obtenerDatosOsea('derecho')
        const datosOseaIzquierdos = this.obtenerDatosOsea('izquierdo')
        const datosEnmascaradoDerechos = this.obtenerDatosEnmascarado('derecho')
        const datosEnmascaradoIzquierdos = this.obtenerDatosEnmascarado('izquierdo')

        this.crearGraficaOido(
          'audiometriaChartDerecho',
          datosAireDerechos,
          datosOseaDerechos,
          datosEnmascaradoDerechos,
          'derecho',
          'chartDerechoInstance'
        )
        this.crearGraficaOido(
          'audiometriaChartIzquierdo',
          datosAireIzquierdos,
          datosOseaIzquierdos,
          datosEnmascaradoIzquierdos,
          'izquierdo',
          'chartIzquierdoInstance'
        )
      })
    },
    crearGraficaOido(elementId, datosAire, datosOsea, datosEnmascarado, oido, instanceProperty) {
      const canvas = document.getElementById(elementId)
      if (!canvas) return

      if (this[instanceProperty]) {
        this[instanceProperty].destroy()
      }

      const esDerecho = oido === 'derecho'
      const colorAire = esDerecho ? '#dc3545' : '#0d6efd'
      const colorOsea = esDerecho ? '#28a745' : '#198754'
      const colorEnmascarado = esDerecho ? '#dc3545' : '#0d6efd'
      
      const simboloAire = esDerecho ? 'circle' : 'cross'
      const simboloOsea = 'triangle'
      const simboloEnmascarado = esDerecho ? 'triangle' : 'rect'
      
      const lineaReferencia = Array(this.frecuencias.length).fill(20)
      const datosOseaMapeados = this.mapearDatosAuxiliares(datosOsea)
      const datosEnmascaradoMapeados = this.mapearDatosAuxiliares(datosEnmascarado)
      
      const hayDatosOsea = datosOsea.some(v => v !== null && v !== undefined)
      const hayDatosEnmascarado = datosEnmascarado.some(v => v !== null && v !== undefined)

      this[instanceProperty] = new Chart(canvas, {
        type: 'line',
        data: {
          labels: this.frecuencias.map(f => f >= 1000 ? (f/1000) + 'k' : f),
          datasets: [
            // VÍA AÉREA
            {
              label: 'Vía Aérea',
              data: datosAire,
              borderColor: colorAire,
              backgroundColor: colorAire,
              borderWidth: 2,
              pointRadius: 7,
              pointStyle: simboloAire,
              pointBackgroundColor: esDerecho ? '#ffffff' : colorAire,
              pointBorderColor: colorAire,
              pointBorderWidth: 2,
              pointHoverRadius: 9,
              tension: 0,
              spanGaps: true,
              order: 1
            },
            // VÍA ÓSEA
            {
              label: 'Vía Ósea',
              data: datosOseaMapeados,
              borderColor: colorOsea,
              backgroundColor: colorOsea,
              borderWidth: 2,
              borderDash: [5, 5],
              pointRadius: 6,
              pointStyle: simboloOsea,
              pointBackgroundColor: 'transparent',
              pointBorderColor: colorOsea,
              pointBorderWidth: 2,
              pointHoverRadius: 8,
              tension: 0,
              spanGaps: true,
              hidden: !hayDatosOsea,
              order: 1
            },
            // ENMASCARAMIENTO
            {
              label: esDerecho ? 'Enmascarado (△)' : 'Enmascarado (□)',
              data: datosEnmascaradoMapeados,
              borderColor: colorEnmascarado,
              backgroundColor: colorEnmascarado,
              borderWidth: 2,
              borderDash: [8, 4],
              pointRadius: 7,
              pointStyle: simboloEnmascarado,
              pointBackgroundColor: 'transparent',
              pointBorderColor: colorEnmascarado,
              pointBorderWidth: 2.5,
              pointHoverRadius: 9,
              tension: 0,
              spanGaps: true,
              hidden: !hayDatosEnmascarado,
              order: 1
            },
            // LÍNEA DE REFERENCIA (20 dB)
            {
              label: 'Límite Normal (20 dB)',
              data: lineaReferencia,
              borderColor: '#28a745',
              borderWidth: 2,
              borderDash: [6, 4],
              pointRadius: 0,
              fill: false,
              order: 2
            }
          ]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          interaction: { mode: 'index', intersect: false },
          plugins: {
            legend: {
              display: true,
              position: 'bottom',
              labels: {
                usePointStyle: true,
                font: { size: 11 }
              }
            },
            tooltip: {
              backgroundColor: 'rgba(0, 0, 0, 0.85)',
              padding: 10,
              callbacks: {
                title: (ctx) => `Frecuencia: ${ctx[0].label} Hz`,
                label: (ctx) => {
                  if (ctx.datasetIndex === 3) return 'Límite de normalidad: 20 dB HL'
                  const tipos = ['Vía Aérea', 'Vía Ósea', esDerecho ? 'Enmascarado (△)' : 'Enmascarado (□)']
                  const tipo = tipos[ctx.datasetIndex]
                  return ctx.raw === null || ctx.raw === undefined ? `${tipo}: Sin dato` : `${tipo}: ${ctx.raw} dB HL`
                }
              }
            }
          },
          scales: {
            y: {
              min: -10,
              max: 120,
              reverse: true,
              title: { display: true, text: 'Nivel (dB HL)', font: { weight: 'bold' } },
              grid: {
                color: (ctx) => ctx.tick.value === 20 ? 'rgba(40, 167, 69, 0.4)' : 'rgba(0, 0, 0, 0.1)',
                lineWidth: (ctx) => ctx.tick.value === 20 ? 2 : 1
              },
              ticks: { stepSize: 10, font: { size: 10 } }
            },
            x: {
              title: { display: true, text: 'Frecuencia (Hz)', font: { weight: 'bold' } },
              grid: { color: 'rgba(0, 0, 0, 0.08)' },
              ticks: { font: { size: 10, weight: 'bold' } }
            }
          }
        }
      })
    },
    emitChange() {
      this.$emit('update:modelValue', { ...this.localData })
    }
  },
  mounted() {
    this.$nextTick(() => {
      this.calcularPTA()
      this.actualizarGrafica()
    })
  },
  beforeUnmount() {
    if (this.chartDerechoInstance) this.chartDerechoInstance.destroy()
    if (this.chartIzquierdoInstance) this.chartIzquierdoInstance.destroy()
  }
}
</script>

<style scoped>
.hoja4-audiometria-delli {
  background: #f8f9fa;
}

.card {
  border: 1px solid #dee2e6;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08);
}

.form-control {
  border-radius: 4px;
  border: 1px solid #ced4da;
  font-size: 14px;
  padding: 10px 12px;
}

.form-control:focus {
  border-color: #5F6E7E;
  box-shadow: 0 0 0 0.2rem rgba(95, 110, 126, 0.25);
}

.form-control-sm {
  font-size: 13px;
  padding: 6px 8px;
  height: 32px !important;
}

.table {
  margin-bottom: 0;
  font-size: 12px;
}

.table thead th {
  font-weight: 600;
  color: #2c3e50;
  border-bottom: 2px solid #dee2e6;
  padding: 8px 4px;
}

.table td {
  padding: 6px 4px;
  text-align: center;
}

.alert {
  margin-bottom: 15px;
  border-radius: 4px;
  font-size: 12px;
}

.alert h4 {
  margin-top: 5px;
  font-weight: bold;
}

label {
  font-size: 13px;
  margin-bottom: 6px;
  color: #2c3e50;
}

.alert-success {
  background-color: #d4edda;
  color: #155724;
  border: 1px solid #c3e6cb;
}

.alert-success ul li {
  font-size: 12px;
  margin-bottom: 4px;
}

canvas {
  max-width: 100%;
}
</style>