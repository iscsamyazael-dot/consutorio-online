<template>
  <div class="antecedentes-container">
    <!-- HEADER -->
    <div class="section-header">
      <i class="fas fa-history" aria-hidden="true"></i>
      <h2>Antecedentes Médicos (WORLDSTRIDE - HOJA 2)</h2>
    </div>

    <!-- 2.1 ACCIDENTES DE TRABAJO -->
    <div class="card-section">
      <div class="card-header">
        <h3>2.1 Accidentes de Trabajo</h3>
      </div>
      <div class="card-body">
        <p class="text-muted small">Registre todos los accidentes laborales previos.</p>
        
        <div v-for="(acc, idx) in form.accidentes" :key="idx" class="form-row mb-3 pb-3 border-bottom">
          <div class="form-group col-md-2">
            <label>Fecha</label>
            <input type="date" v-model="acc.fecha" class="form-control" />
          </div>
          <div class="form-group col-md-3">
            <label>Empresa</label>
            <input type="text" v-model="acc.empresa" class="form-control" />
          </div>
          <div class="form-group col-md-2">
            <label>Tipo Accidente</label>
            <input type="text" v-model="acc.tipo" class="form-control" placeholder="Caída, golpe..." />
          </div>
          <div class="form-group col-md-2">
            <label>Parte Cuerpo Afectada</label>
            <input type="text" v-model="acc.parte_cuerpo" class="form-control" />
          </div>
          <div class="form-group col-md-1">
            <label>Días Incapacidad</label>
            <input type="number" v-model="acc.dias_incapacidad" class="form-control" />
          </div>
          <div class="form-group col-md-2">
            <label>Secuelas</label>
            <input type="text" v-model="acc.secuelas" class="form-control" placeholder="Ninguna, cicatriz..." />
          </div>
        </div>

        <button @click="agregarAccidente" class="btn btn-sm btn-outline-primary mt-2">
          <i class="fas fa-plus mr-1"></i> Agregar accidente
        </button>
      </div>
    </div>

    <!-- 2.2 ANTECEDENTES HEREDO-FAMILIARES -->
    <div class="card-section">
      <div class="card-header">
        <h3>2.2 Antecedentes Heredo-Familiares (Marque con X si aplica)</h3>
      </div>
      <div class="card-body">
        <p class="text-muted small">Indique si hay enfermedades en la familia y el parentesco.</p>
        
        <div class="form-row">
          <div class="col-md-6">
            <div class="mb-3">
              <label>Cáncer</label>
              <div class="custom-control custom-checkbox">
                <input type="checkbox" id="hf-cancer" v-model="form.heredo_familiares.cancer.presente" class="custom-control-input" />
                <label class="custom-control-label" for="hf-cancer">Sí</label>
              </div>
              <input v-if="form.heredo_familiares.cancer.presente" type="text" v-model="form.heredo_familiares.cancer.parentesco" class="form-control mt-1 form-control-sm" placeholder="Padre, hermano..." />
            </div>

            <div class="mb-3">
              <label>Diabetes</label>
              <div class="custom-control custom-checkbox">
                <input type="checkbox" id="hf-diabetes" v-model="form.heredo_familiares.diabetes.presente" class="custom-control-input" />
                <label class="custom-control-label" for="hf-diabetes">Sí</label>
              </div>
              <input v-if="form.heredo_familiares.diabetes.presente" type="text" v-model="form.heredo_familiares.diabetes.parentesco" class="form-control mt-1 form-control-sm" placeholder="Padre, hermano..." />
            </div>

            <div class="mb-3">
              <label>Hipertensión arterial</label>
              <div class="custom-control custom-checkbox">
                <input type="checkbox" id="hf-hipertension" v-model="form.heredo_familiares.hipertension.presente" class="custom-control-input" />
                <label class="custom-control-label" for="hf-hipertension">Sí</label>
              </div>
              <input v-if="form.heredo_familiares.hipertension.presente" type="text" v-model="form.heredo_familiares.hipertension.parentesco" class="form-control mt-1 form-control-sm" placeholder="Padre, hermano..." />
            </div>

            <div class="mb-3">
              <label>Cardiopatías</label>
              <div class="custom-control custom-checkbox">
                <input type="checkbox" id="hf-cardio" v-model="form.heredo_familiares.cardiopatias.presente" class="custom-control-input" />
                <label class="custom-control-label" for="hf-cardio">Sí</label>
              </div>
              <input v-if="form.heredo_familiares.cardiopatias.presente" type="text" v-model="form.heredo_familiares.cardiopatias.parentesco" class="form-control mt-1 form-control-sm" placeholder="Padre, hermano..." />
            </div>

            <div class="mb-3">
              <label>Infarto del miocardio</label>
              <div class="custom-control custom-checkbox">
                <input type="checkbox" id="hf-infarto" v-model="form.heredo_familiares.infarto.presente" class="custom-control-input" />
                <label class="custom-control-label" for="hf-infarto">Sí</label>
              </div>
              <input v-if="form.heredo_familiares.infarto.presente" type="text" v-model="form.heredo_familiares.infarto.parentesco" class="form-control mt-1 form-control-sm" placeholder="Padre, hermano..." />
            </div>

            <div class="mb-3">
              <label>Accidente cerebrovascular</label>
              <div class="custom-control custom-checkbox">
                <input type="checkbox" id="hf-acv" v-model="form.heredo_familiares.acv.presente" class="custom-control-input" />
                <label class="custom-control-label" for="hf-acv">Sí</label>
              </div>
              <input v-if="form.heredo_familiares.acv.presente" type="text" v-model="form.heredo_familiares.acv.parentesco" class="form-control mt-1 form-control-sm" placeholder="Padre, hermano..." />
            </div>

            <div class="mb-3">
              <label>Asma/EPOC/Problemas pulmonares</label>
              <div class="custom-control custom-checkbox">
                <input type="checkbox" id="hf-pulmonar" v-model="form.heredo_familiares.pulmonar.presente" class="custom-control-input" />
                <label class="custom-control-label" for="hf-pulmonar">Sí</label>
              </div>
              <input v-if="form.heredo_familiares.pulmonar.presente" type="text" v-model="form.heredo_familiares.pulmonar.parentesco" class="form-control mt-1 form-control-sm" placeholder="Padre, hermano..." />
            </div>

            <div class="mb-3">
              <label>Tuberculosis</label>
              <div class="custom-control custom-checkbox">
                <input type="checkbox" id="hf-tb" v-model="form.heredo_familiares.tuberculosis.presente" class="custom-control-input" />
                <label class="custom-control-label" for="hf-tb">Sí</label>
              </div>
              <input v-if="form.heredo_familiares.tuberculosis.presente" type="text" v-model="form.heredo_familiares.tuberculosis.parentesco" class="form-control mt-1 form-control-sm" placeholder="Padre, hermano..." />
            </div>
          </div>

          <div class="col-md-6">
            <div class="mb-3">
              <label>Enfermedades hepáticas</label>
              <div class="custom-control custom-checkbox">
                <input type="checkbox" id="hf-hepatica" v-model="form.heredo_familiares.hepatica.presente" class="custom-control-input" />
                <label class="custom-control-label" for="hf-hepatica">Sí</label>
              </div>
              <input v-if="form.heredo_familiares.hepatica.presente" type="text" v-model="form.heredo_familiares.hepatica.parentesco" class="form-control mt-1 form-control-sm" placeholder="Padre, hermano..." />
            </div>

            <div class="mb-3">
              <label>Enfermedades renales</label>
              <div class="custom-control custom-checkbox">
                <input type="checkbox" id="hf-renal" v-model="form.heredo_familiares.renal.presente" class="custom-control-input" />
                <label class="custom-control-label" for="hf-renal">Sí</label>
              </div>
              <input v-if="form.heredo_familiares.renal.presente" type="text" v-model="form.heredo_familiares.renal.parentesco" class="form-control mt-1 form-control-sm" placeholder="Padre, hermano..." />
            </div>

            <div class="mb-3">
              <label>Artritis/Enfermedades reumáticas</label>
              <div class="custom-control custom-checkbox">
                <input type="checkbox" id="hf-artritis" v-model="form.heredo_familiares.artritis.presente" class="custom-control-input" />
                <label class="custom-control-label" for="hf-artritis">Sí</label>
              </div>
              <input v-if="form.heredo_familiares.artritis.presente" type="text" v-model="form.heredo_familiares.artritis.parentesco" class="form-control mt-1 form-control-sm" placeholder="Padre, hermano..." />
            </div>

            <div class="mb-3">
              <label>Osteoporosis</label>
              <div class="custom-control custom-checkbox">
                <input type="checkbox" id="hf-osteo" v-model="form.heredo_familiares.osteoporosis.presente" class="custom-control-input" />
                <label class="custom-control-label" for="hf-osteo">Sí</label>
              </div>
              <input v-if="form.heredo_familiares.osteoporosis.presente" type="text" v-model="form.heredo_familiares.osteoporosis.parentesco" class="form-control mt-1 form-control-sm" placeholder="Padre, hermano..." />
            </div>

            <div class="mb-3">
              <label>Depresión/Problemas mentales</label>
              <div class="custom-control custom-checkbox">
                <input type="checkbox" id="hf-mental" v-model="form.heredo_familiares.mental.presente" class="custom-control-input" />
                <label class="custom-control-label" for="hf-mental">Sí</label>
              </div>
              <input v-if="form.heredo_familiares.mental.presente" type="text" v-model="form.heredo_familiares.mental.parentesco" class="form-control mt-1 form-control-sm" placeholder="Padre, hermano..." />
            </div>

            <div class="mb-3">
              <label>Alcoholismo/Adicciones</label>
              <div class="custom-control custom-checkbox">
                <input type="checkbox" id="hf-adiccion" v-model="form.heredo_familiares.adiccion.presente" class="custom-control-input" />
                <label class="custom-control-label" for="hf-adiccion">Sí</label>
              </div>
              <input v-if="form.heredo_familiares.adiccion.presente" type="text" v-model="form.heredo_familiares.adiccion.parentesco" class="form-control mt-1 form-control-sm" placeholder="Padre, hermano..." />
            </div>

            <div class="mb-3">
              <label>Otros antecedentes</label>
              <textarea v-model="form.heredo_familiares.otros" class="form-control" rows="2" placeholder="Especificar..."></textarea>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- 2.3 PERSONALES NO PATOLÓGICOS -->
    <div class="card-section">
      <div class="card-header">
        <h3>2.3 Antecedentes Personales No Patológicos</h3>
      </div>
      <div class="card-body">
        <div class="form-row">
          <div class="form-group col-md-3">
            <label>Tabaquismo</label>
            <select v-model="form.no_patologicos.tabaquismo" class="form-control">
              <option value="">Seleccionar</option>
              <option value="nunca">Nunca fumó</option>
              <option value="exfumador">Ex-fumador</option>
              <option value="activo">Fumador activo</option>
            </select>
            <input v-if="form.no_patologicos.tabaquismo" type="text" v-model="form.no_patologicos.tabaquismo_detalles" class="form-control mt-2 form-control-sm" placeholder="Cigarrillos/día, años..." />
          </div>
          
          <div class="form-group col-md-3">
            <label>Alcoholismo</label>
            <select v-model="form.no_patologicos.alcoholismo" class="form-control">
              <option value="">Seleccionar</option>
              <option value="no">No consume</option>
              <option value="ocasional">Ocasional</option>
              <option value="moderado">Moderado</option>
              <option value="frecuente">Frecuente</option>
            </select>
            <input v-if="form.no_patologicos.alcoholismo" type="text" v-model="form.no_patologicos.alcoholismo_detalles" class="form-control mt-2 form-control-sm" placeholder="Tipo, frecuencia..." />
          </div>

          <div class="form-group col-md-3">
            <label>Drogas</label>
            <div class="custom-control custom-checkbox mb-2">
              <input type="checkbox" id="drogas-no" v-model="form.no_patologicos.drogas_no" class="custom-control-input" />
              <label class="custom-control-label" for="drogas-no">No consume</label>
            </div>
            <div class="custom-control custom-checkbox">
              <input type="checkbox" id="drogas-si" v-model="form.no_patologicos.drogas_si" class="custom-control-input" />
              <label class="custom-control-label" for="drogas-si">Consume (especificar)</label>
            </div>
            <input v-if="form.no_patologicos.drogas_si" type="text" v-model="form.no_patologicos.drogas_detalles" class="form-control mt-2 form-control-sm" placeholder="Tipo, frecuencia..." />
          </div>

          <div class="form-group col-md-3">
            <label>Actividad Física</label>
            <select v-model="form.no_patologicos.actividad_fisica" class="form-control">
              <option value="">Seleccionar</option>
              <option value="sedentario">Sedentario</option>
              <option value="leve">Leve (1-2x/semana)</option>
              <option value="moderada">Moderada (3-4x/semana)</option>
              <option value="intensa">Intensa (5+ veces/semana)</option>
            </select>
          </div>
        </div>

        <div class="form-group mt-3">
          <label>Estrés/Problemas Emocionales</label>
          <select v-model="form.no_patologicos.estres" class="form-control">
            <option value="">Seleccionar</option>
            <option value="bajo">Bajo</option>
            <option value="moderado">Moderado</option>
            <option value="alto">Alto</option>
            <option value="muy_alto">Muy alto</option>
          </select>
          <textarea v-if="form.no_patologicos.estres" v-model="form.no_patologicos.estres_detalles" class="form-control mt-2" rows="2" placeholder="Causas, síntomas..."></textarea>
        </div>
      </div>
    </div>

    <!-- 2.4 GINECO/UROLÓGICO (DINÁMICO POR GÉNERO) -->
    <div class="card-section">
      <div class="card-header">
        <h3>2.4 Antecedentes Gineco/Urológico</h3>
      </div>
      <div class="card-body">
        <div class="form-group mb-3">
          <label>Género Biológico:</label>
          <select v-model="form.genero" class="form-control">
            <option value="">Seleccionar</option>
            <option value="M">Masculino</option>
            <option value="F">Femenino</option>
            <option value="Otro">Prefiero no especificar</option>
          </select>
        </div>

        <!-- FEMENINO -->
        <div v-if="form.genero === 'F'" class="row">
          <div class="col-md-6">
            <div class="mb-3">
              <label>Edad de menarquía (primera menstruación)</label>
              <input type="number" v-model="form.gineco.menarquia" class="form-control" />
            </div>
            <div class="mb-3">
              <label>Ciclo menstrual (días)</label>
              <input type="number" v-model="form.gineco.ciclo_dias" class="form-control" placeholder="Ej: 28" />
            </div>
            <div class="mb-3">
              <label>Duración menstruación (días)</label>
              <input type="number" v-model="form.gineco.duracion_dias" class="form-control" placeholder="Ej: 5" />
            </div>
            <div class="mb-3">
              <div class="custom-control custom-checkbox">
                <input type="checkbox" id="gineco-desorden" v-model="form.gineco.desorden_menstrual" class="custom-control-input" />
                <label class="custom-control-label" for="gineco-desorden">Desorden menstrual</label>
              </div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="mb-3">
              <div class="custom-control custom-checkbox">
                <input type="checkbox" id="gineco-dismenorrea" v-model="form.gineco.dismenorrea" class="custom-control-input" />
                <label class="custom-control-label" for="gineco-dismenorrea">Dismenorrea (cólicos intensos)</label>
              </div>
            </div>
            <div class="mb-3">
              <label>Gestaciones (embarazos)</label>
              <input type="number" v-model="form.gineco.gestaciones" class="form-control" />
            </div>
            <div class="mb-3">
              <label>Partos</label>
              <input type="number" v-model="form.gineco.partos" class="form-control" />
            </div>
            <div class="mb-3">
              <label>Abortos</label>
              <input type="number" v-model="form.gineco.abortos" class="form-control" />
            </div>
            <div class="mb-3">
              <div class="custom-control custom-checkbox">
                <input type="checkbox" id="gineco-anticoncept" v-model="form.gineco.anticonceptivos" class="custom-control-input" />
                <label class="custom-control-label" for="gineco-anticoncept">Usa anticonceptivos</label>
              </div>
            </div>
          </div>
        </div>

        <!-- MASCULINO -->
        <div v-if="form.genero === 'M'" class="row">
          <div class="col-md-6">
            <div class="mb-3">
              <div class="custom-control custom-checkbox">
                <input type="checkbox" id="uro-disfuncion" v-model="form.urologo.disfuncion_erectil" class="custom-control-input" />
                <label class="custom-control-label" for="uro-disfuncion">Disfunción eréctil</label>
              </div>
            </div>
            <div class="mb-3">
              <div class="custom-control custom-checkbox">
                <input type="checkbox" id="uro-infertilidad" v-model="form.urologo.infertilidad" class="custom-control-input" />
                <label class="custom-control-label" for="uro-infertilidad">Infertilidad</label>
              </div>
            </div>
            <div class="mb-3">
              <div class="custom-control custom-checkbox">
                <input type="checkbox" id="uro-prostata" v-model="form.urologo.problemas_prostaticos" class="custom-control-input" />
                <label class="custom-control-label" for="uro-prostata">Problemas prostáticos</label>
              </div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="mb-3">
              <div class="custom-control custom-checkbox">
                <input type="checkbox" id="uro-iti" v-model="form.urologo.infecciones_urinarias" class="custom-control-input" />
                <label class="custom-control-label" for="uro-iti">Infecciones urinarias recurrentes</label>
              </div>
            </div>
            <div class="mb-3">
              <label>Otras alteraciones urológicas</label>
              <textarea v-model="form.urologo.otros" class="form-control" rows="2" placeholder="Especificar..."></textarea>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- 2.5 PERSONALES PATOLÓGICOS (40+ ENFERMEDADES) -->
    <div class="card-section">
      <div class="card-header">
        <h3>2.5 Antecedentes Personales Patológicos (Marque las enfermedades que ha padecido)</h3>
      </div>
      <div class="card-body">
        <p class="text-muted small mb-3">Registre todas las enfermedades diagnosticadas, año aproximado y estado actual.</p>

        <div class="form-row">
          <!-- COLUMNA 1: SISTEMA CARDIOVASCULAR -->
          <div class="col-md-6">
            <h5 class="border-bottom pb-2 mb-3">Sistema Cardiovascular</h5>
            <div class="mb-2">
              <div class="custom-control custom-checkbox">
                <input type="checkbox" id="pat-hipertension" v-model="form.patologicos.hipertension.presente" class="custom-control-input" />
                <label class="custom-control-label" for="pat-hipertension">Hipertensión arterial</label>
              </div>
              <input v-if="form.patologicos.hipertension.presente" type="text" v-model="form.patologicos.hipertension.observaciones" class="form-control mt-1 form-control-sm" placeholder="Año, estado actual..." />
            </div>
            <div class="mb-2">
              <div class="custom-control custom-checkbox">
                <input type="checkbox" id="pat-cardiopatia" v-model="form.patologicos.cardiopatia.presente" class="custom-control-input" />
                <label class="custom-control-label" for="pat-cardiopatia">Cardiopatía</label>
              </div>
              <input v-if="form.patologicos.cardiopatia.presente" type="text" v-model="form.patologicos.cardiopatia.observaciones" class="form-control mt-1 form-control-sm" placeholder="Año, tipo..." />
            </div>
            <div class="mb-2">
              <div class="custom-control custom-checkbox">
                <input type="checkbox" id="pat-infarto" v-model="form.patologicos.infarto.presente" class="custom-control-input" />
                <label class="custom-control-label" for="pat-infarto">Infarto del miocardio</label>
              </div>
              <input v-if="form.patologicos.infarto.presente" type="text" v-model="form.patologicos.infarto.observaciones" class="form-control mt-1 form-control-sm" placeholder="Año..." />
            </div>
            <div class="mb-2">
              <div class="custom-control custom-checkbox">
                <input type="checkbox" id="pat-arritmia" v-model="form.patologicos.arritmia.presente" class="custom-control-input" />
                <label class="custom-control-label" for="pat-arritmia">Arritmias cardíacas</label>
              </div>
            </div>

            <h5 class="border-bottom pb-2 mb-3 mt-4">Sistema Respiratorio</h5>
            <div class="mb-2">
              <div class="custom-control custom-checkbox">
                <input type="checkbox" id="pat-asma" v-model="form.patologicos.asma.presente" class="custom-control-input" />
                <label class="custom-control-label" for="pat-asma">Asma</label>
              </div>
            </div>
            <div class="mb-2">
              <div class="custom-control custom-checkbox">
                <input type="checkbox" id="pat-epoc" v-model="form.patologicos.epoc.presente" class="custom-control-input" />
                <label class="custom-control-label" for="pat-epoc">EPOC/Enfisema</label>
              </div>
            </div>
            <div class="mb-2">
              <div class="custom-control custom-checkbox">
                <input type="checkbox" id="pat-tuberculosis" v-model="form.patologicos.tuberculosis.presente" class="custom-control-input" />
                <label class="custom-control-label" for="pat-tuberculosis">Tuberculosis</label>
              </div>
            </div>
            <div class="mb-2">
              <div class="custom-control custom-checkbox">
                <input type="checkbox" id="pat-neumonía" v-model="form.patologicos.neumonia.presente" class="custom-control-input" />
                <label class="custom-control-label" for="pat-neumonía">Neumonía</label>
              </div>
            </div>
            <div class="mb-2">
              <div class="custom-control custom-checkbox">
                <input type="checkbox" id="pat-silicosis" v-model="form.patologicos.silicosis.presente" class="custom-control-input" />
                <label class="custom-control-label" for="pat-silicosis">Silicosis/Neumoconiosis</label>
              </div>
            </div>

            <h5 class="border-bottom pb-2 mb-3 mt-4">Sistema Digestivo</h5>
            <div class="mb-2">
              <div class="custom-control custom-checkbox">
                <input type="checkbox" id="pat-ulcera" v-model="form.patologicos.ulcera.presente" class="custom-control-input" />
                <label class="custom-control-label" for="pat-ulcera">Úlcera gástrica/duodenal</label>
              </div>
            </div>
            <div class="mb-2">
              <div class="custom-control custom-checkbox">
                <input type="checkbox" id="pat-gastritis" v-model="form.patologicos.gastritis.presente" class="custom-control-input" />
                <label class="custom-control-label" for="pat-gastritis">Gastritis crónica</label>
              </div>
            </div>
            <div class="mb-2">
              <div class="custom-control custom-checkbox">
                <input type="checkbox" id="pat-hepatitis" v-model="form.patologicos.hepatitis.presente" class="custom-control-input" />
                <label class="custom-control-label" for="pat-hepatitis">Hepatitis</label>
              </div>
            </div>
            <div class="mb-2">
              <div class="custom-control custom-checkbox">
                <input type="checkbox" id="pat-cirrosis" v-model="form.patologicos.cirrosis.presente" class="custom-control-input" />
                <label class="custom-control-label" for="pat-cirrosis">Cirrosis hepática</label>
              </div>
            </div>
          </div>

          <!-- COLUMNA 2: OTROS SISTEMAS -->
          <div class="col-md-6">
            <h5 class="border-bottom pb-2 mb-3">Sistema Endocrino</h5>
            <div class="mb-2">
              <div class="custom-control custom-checkbox">
                <input type="checkbox" id="pat-diabetes" v-model="form.patologicos.diabetes.presente" class="custom-control-input" />
                <label class="custom-control-label" for="pat-diabetes">Diabetes</label>
              </div>
              <input v-if="form.patologicos.diabetes.presente" type="text" v-model="form.patologicos.diabetes.observaciones" class="form-control mt-1 form-control-sm" placeholder="Tipo, año..." />
            </div>
            <div class="mb-2">
              <div class="custom-control custom-checkbox">
                <input type="checkbox" id="pat-hipertiroidismo" v-model="form.patologicos.hipertiroidismo.presente" class="custom-control-input" />
                <label class="custom-control-label" for="pat-hipertiroidismo">Hipertiroidismo</label>
              </div>
            </div>
            <div class="mb-2">
              <div class="custom-control custom-checkbox">
                <input type="checkbox" id="pat-hipotiroidismo" v-model="form.patologicos.hipotiroidismo.presente" class="custom-control-input" />
                <label class="custom-control-label" for="pat-hipotiroidismo">Hipotiroidismo</label>
              </div>
            </div>

            <h5 class="border-bottom pb-2 mb-3 mt-4">Sistema Renal/Urológico</h5>
            <div class="mb-2">
              <div class="custom-control custom-checkbox">
                <input type="checkbox" id="pat-nefritis" v-model="form.patologicos.nefritis.presente" class="custom-control-input" />
                <label class="custom-control-label" for="pat-nefritis">Nefritis/Problemas renales</label>
              </div>
            </div>
            <div class="mb-2">
              <div class="custom-control custom-checkbox">
                <input type="checkbox" id="pat-litasis" v-model="form.patologicos.litiasis.presente" class="custom-control-input" />
                <label class="custom-control-label" for="pat-litasis">Litiasis renal (piedras)</label>
              </div>
            </div>

            <h5 class="border-bottom pb-2 mb-3 mt-4">Sistema Osteomuscular</h5>
            <div class="mb-2">
              <div class="custom-control custom-checkbox">
                <input type="checkbox" id="pat-artrosis" v-model="form.patologicos.artrosis.presente" class="custom-control-input" />
                <label class="custom-control-label" for="pat-artrosis">Artrosis</label>
              </div>
            </div>
            <div class="mb-2">
              <div class="custom-control custom-checkbox">
                <input type="checkbox" id="pat-artritis" v-model="form.patologicos.artritis.presente" class="custom-control-input" />
                <label class="custom-control-label" for="pat-artritis">Artritis reumatoide</label>
              </div>
            </div>
            <div class="mb-2">
              <div class="custom-control custom-checkbox">
                <input type="checkbox" id="pat-osteoporosis" v-model="form.patologicos.osteoporosis.presente" class="custom-control-input" />
                <label class="custom-control-label" for="pat-osteoporosis">Osteoporosis</label>
              </div>
            </div>
            <div class="mb-2">
              <div class="custom-control custom-checkbox">
                <input type="checkbox" id="pat-hernia" v-model="form.patologicos.hernia_discal.presente" class="custom-control-input" />
                <label class="custom-control-label" for="pat-hernia">Hernia discal</label>
              </div>
            </div>
            <div class="mb-2">
              <div class="custom-control custom-checkbox">
                <input type="checkbox" id="pat-sindrome-tunel" v-model="form.patologicos.sindrome_tunel_carpal.presente" class="custom-control-input" />
                <label class="custom-control-label" for="pat-sindrome-tunel">Síndrome del túnel carpiano</label>
              </div>
            </div>

            <h5 class="border-bottom pb-2 mb-3 mt-4">Sistema Nervioso</h5>
            <div class="mb-2">
              <div class="custom-control custom-checkbox">
                <input type="checkbox" id="pat-epilepsia" v-model="form.patologicos.epilepsia.presente" class="custom-control-input" />
                <label class="custom-control-label" for="pat-epilepsia">Epilepsia</label>
              </div>
            </div>
            <div class="mb-2">
              <div class="custom-control custom-checkbox">
                <input type="checkbox" id="pat-migraña" v-model="form.patologicos.migraña.presente" class="custom-control-input" />
                <label class="custom-control-label" for="pat-migraña">Migraña/Cefaleas crónicas</label>
              </div>
            </div>
            <div class="mb-2">
              <div class="custom-control custom-checkbox">
                <input type="checkbox" id="pat-depresion" v-model="form.patologicos.depresion.presente" class="custom-control-input" />
                <label class="custom-control-label" for="pat-depresion">Depresión</label>
              </div>
            </div>
            <div class="mb-2">
              <div class="custom-control custom-checkbox">
                <input type="checkbox" id="pat-ansiedad" v-model="form.patologicos.ansiedad.presente" class="custom-control-input" />
                <label class="custom-control-label" for="pat-ansiedad">Trastorno de ansiedad</label>
              </div>
            </div>
          </div>
        </div>

        <div class="form-group mt-4">
          <label>Otras enfermedades o condiciones relevantes</label>
          <textarea v-model="form.patologicos.otros" class="form-control" rows="3" placeholder="Especificar diagnósticos, años, estado actual..."></textarea>
        </div>
      </div>
    </div>

  </div>
</template>

<script>
export default {
  name: 'AntecedentesCompletos',
  props: {
    modelValue: {
      type: Object,
      required: true
    }
  },
  emits: ['update:modelValue'],
  data() {
    return {
      form: this.modelValue || {
        accidentes: [{ fecha: '', empresa: '', tipo: '', parte_cuerpo: '', dias_incapacidad: '', secuelas: '' }],
        heredo_familiares: {
          cancer: { presente: false, parentesco: '' },
          diabetes: { presente: false, parentesco: '' },
          hipertension: { presente: false, parentesco: '' },
          cardiopatias: { presente: false, parentesco: '' },
          infarto: { presente: false, parentesco: '' },
          acv: { presente: false, parentesco: '' },
          pulmonar: { presente: false, parentesco: '' },
          tuberculosis: { presente: false, parentesco: '' },
          hepatica: { presente: false, parentesco: '' },
          renal: { presente: false, parentesco: '' },
          artritis: { presente: false, parentesco: '' },
          osteoporosis: { presente: false, parentesco: '' },
          mental: { presente: false, parentesco: '' },
          adiccion: { presente: false, parentesco: '' },
          otros: ''
        },
        no_patologicos: {
          tabaquismo: '',
          tabaquismo_detalles: '',
          alcoholismo: '',
          alcoholismo_detalles: '',
          drogas_no: true,
          drogas_si: false,
          drogas_detalles: '',
          actividad_fisica: '',
          estres: '',
          estres_detalles: ''
        },
        genero: '',
        gineco: {
          menarquia: '',
          ciclo_dias: '',
          duracion_dias: '',
          desorden_menstrual: false,
          dismenorrea: false,
          gestaciones: '',
          partos: '',
          abortos: '',
          anticonceptivos: false
        },
        urologo: {
          disfuncion_erectil: false,
          infertilidad: false,
          problemas_prostaticos: false,
          infecciones_urinarias: false,
          otros: ''
        },
        patologicos: {
          hipertension: { presente: false, observaciones: '' },
          cardiopatia: { presente: false, observaciones: '' },
          infarto: { presente: false, observaciones: '' },
          arritmia: { presente: false, observaciones: '' },
          asma: { presente: false, observaciones: '' },
          epoc: { presente: false, observaciones: '' },
          tuberculosis: { presente: false, observaciones: '' },
          neumonia: { presente: false, observaciones: '' },
          silicosis: { presente: false, observaciones: '' },
          ulcera: { presente: false, observaciones: '' },
          gastritis: { presente: false, observaciones: '' },
          hepatitis: { presente: false, observaciones: '' },
          cirrosis: { presente: false, observaciones: '' },
          diabetes: { presente: false, observaciones: '' },
          hipertiroidismo: { presente: false, observaciones: '' },
          hipotiroidismo: { presente: false, observaciones: '' },
          nefritis: { presente: false, observaciones: '' },
          litiasis: { presente: false, observaciones: '' },
          artrosis: { presente: false, observaciones: '' },
          artritis: { presente: false, observaciones: '' },
          osteoporosis: { presente: false, observaciones: '' },
          hernia_discal: { presente: false, observaciones: '' },
          sindrome_tunel_carpal: { presente: false, observaciones: '' },
          epilepsia: { presente: false, observaciones: '' },
          migraña: { presente: false, observaciones: '' },
          depresion: { presente: false, observaciones: '' },
          ansiedad: { presente: false, observaciones: '' },
          otros: ''
        }
      }
    }
  },
  methods: {
    agregarAccidente() {
      this.form.accidentes.push({
        fecha: '',
        empresa: '',
        tipo: '',
        parte_cuerpo: '',
        dias_incapacidad: '',
        secuelas: ''
      })
    }
  },
  watch: {
    modelValue(newVal) {
      this.form = newVal
    },
    form: {
      handler(newVal) {
        this.$emit('update:modelValue', newVal)
      },
      deep: true
    }
  }
}
</script>

<style scoped>
.section-header {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 1rem;
}

.section-header i {
  font-size: 24px;
  color: #5F6E7E;
}

.section-header h2 {
  font-size: 18px;
  font-weight: 500;
  color: #1F2937;
  margin: 0;
}

.card-section {
  background: #FFFFFF;
  border: 0.5px solid #E5E7EB;
  border-radius: 6px;
  margin-bottom: 2rem;
}

.card-header {
  background: linear-gradient(135deg, #5F6E7E 0%, #4A5568 100%);
  padding: 12px 20px;
}

.card-header h3 {
  font-size: 14px;
  font-weight: 500;
  color: #FFFFFF;
  margin: 0;
}

.card-body {
  padding: 20px;
}

.form-control {
  padding: 10px 12px;
  border: 0.5px solid #D1D5DB;
  border-radius: 6px;
  font-size: 14px;
}

h5 {
  font-size: 13px;
  font-weight: 600;
  color: #1F2937;
}
</style>
