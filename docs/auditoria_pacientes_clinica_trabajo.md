# Auditoría de Datos de Paciente para Módulo Clínica de Trabajo

## Resumen ejecutivo

Se realizó una auditoría exhaustiva de los componentes Vue en `resources/js/components/ClinicaTrabajo/secciones/` y archivos relacionados para identificar los campos de paciente utilizados en el módulo Clínica de Trabajo y compararlos con la estructura actual de la tabla `pacientes`. Se encontraron varios problemas de consistencia, principalmente relacionados con:

1. Campos de paciente que se acceden pero no existen en la tabla (apellido_paterno, apellido_materno, genero)
2. Uso de campos calculados o duplicados (edad vs fecha_nacimiento)
3. Campos ambiguos que requieren confirmación (cedula)
4. Campos que se copian innecesariamente a las tablas de valoraciones cuando solo se necesita el paciente_id

Se propone crear una tabla `pacientes_clinica_trabajo` que conserve únicamente los campos esenciales para el módulo clínico, eliminando redundancias y agregando campos de trazabilidad necesarios.

**Archivo generado:** docs/auditoria_pacientes_clinica_trabajo.md

## Detalle por sección

### audiologia/

| Archivo | Campo | Tipo | Cotejo con pacientes | Observación |
|---------|-------|------|----------------------|-------------|
| AntecedentesLaborales.vue | empresa_anterior | String | No existe en pacientes | Pertenece a historial laboral, no es dato de paciente |
| AntecedentesLaborales.vue | puesto_anterior | String | No existe en pacientes | Historial laboral |
| AntecedentesLaborales.vue | anos_puesto_anterior | Number | No existe en pacientes | Historial laboral |
| AntecedentesLaborales.vue | nivel_ruido_anterior | Number | No existe en pacientes | Dato de exposición laboral |
| AntecedentesLaborales.vue | antibioticos_consume | Boolean | No existe en pacientes | Antecedente médico/ocupacional |
| AntecedentesLaborales.vue | diureticos_consume | Boolean | No existe en pacientes | Antecedente médico/ocupacional |
| AntecedentesLaborales.vue | salicilatos_consume | Boolean | No existe en pacientes | Antecedente médico/ocupacional |
| AntecedentesLaborales.vue | antimalarcicos_consume | Boolean | No existe en pacientes | Antecedente médico/ocupacional |
| AntecedentesLaborales.vue | armas_fuego_caza | Boolean | No existe en pacientes | Antecedente personal/no laboral |
| AntecedentesLaborales.vue | niveles_altos_musica | Boolean | No existe en pacientes | Antecedente personal/no laboral |
| AntecedentesLaborales.vue | lugares_ruidosos_discoteca | Boolean | No existe en pacientes | Antecedente personal/no laboral |
| AntecedentesLaborales.vue | automovilismo_motociclismo | Boolean | No existe en pacientes | Antecedente personal/no laboral |
| AntecedentesLaborales.vue | otros_pasatiempos_detalle | String | No existe en pacientes | Dato de antecedentes personales |
| AptitudDictamen.vue | aptitud | String | No existe en pacientes | Campo de valoración (dictamen) |
| AptitudDictamen.vue | restricciones | String | No existe en pacientes | Campo de valoración |
| AptitudDictamen.vue | recomendaciones | String | No existe en pacientes | Campo de valoración |
| AptitudDictamen.vue | tipo_hipoacusia | String/Array | No existe en pacientes | Campo de diagnóstico audiológico |
| Audiometria.vue | (campos de frecuencias) | Number | No existen en pacientes | Parte del examen audiométrico específico |
| Otros componentes audiología | (todos) | - | No existen en pacientes | Todos pertenecen a tablas de valoraciones o examen específico |

### ergonomia/

| Archivo | Campo | Tipo | Cotejo con pacientes | Observación |
|---------|-------|------|----------------------|-------------|
| AntecedentesHeredofamiliares.vue | diabetes | Boolean | No existe en pacientes | Antecedente familiar |
| AntecedentesHeredofamiliares.vue | obesidad | Boolean | No existe en pacientes | Antecedente familiar |
| AntecedentesHeredofamiliares.vue | dislipidemia | Boolean | No existe en pacientes | Antecedente familiar |
| AntecedentesHeredofamiliares.vue | cardiopatia | Boolean | No existe en pacientes | Antecedente familiar |
| AntecedentesHeredofamiliares.vue | hipertension | Boolean | No existe en pacientes | Antecedente familiar |
| AntecedentesHeredofamiliares.vue | enfermedad_psiquiatrica | Boolean | No existe en pacientes | Antecedente familiar |
| AntecedentesHeredofamiliares.vue | enfermedad_neurologica | Boolean | No existe en pacientes | Antecedente familiar |
| AntecedentesHeredofamiliares.vue | enfermedad_congenita | Boolean | No existe en pacientes | Antecedente familiar |
| AntecedentesHeredofamiliares.vue | cancer | Boolean | No existe en pacientes | Antecedente familiar |
| AntecedentesHeredofamiliares.vue | sordera | Boolean | No existe en pacientes | Antecedente familiar |
| AntecedentesHeredofamiliares.vue | tuberculosis | Boolean | No existe en pacientes | Antecedente familiar |
| AntecedentesHeredofamiliares.vue | hepatitis | Boolean | No existe en pacientes | Antecedente familiar |
| AntecedentesHeredofamiliares.vue | epilepsia | Boolean | No existe en pacientes | Antecedente familiar |
| AntecedentesHeredofamiliares.vue | artrosis | Boolean | No existe en pacientes | Antecedente familiar |
| AntecedentesHeredofamiliares.vue | otras | String | No existe en pacientes | Antecedente familiar |
| AntecedentesMedicos.vue | (campos de antecedentes personales) | - | No existen en pacientes | Parte de historial médico |
| DatosBasico.vue | (datos de identificación básica) | - | Parcialmente existen | Algunos campos como nombre, teléfono sí existen |
| ExamenMedico.vue | (signos vitales, exploración) | - | No existen en pacientes | Parte del examen físico |
| FactoresLaborales.vue | (factores de riesgo laboral) | - | No existen en pacientes | Pertenece a condiciones de trabajo |
| HabitosConsumo.vue | (hábitos de consumo) | - | No existen en pacientes | Antecedentes personales/no patológicos |
| Hallazgos.vue | (hallazgos físicos) | - | No existen en pacientes | Resultado del examen |
| InstrumentoEspecifico/* | (puntuaciones RULA/REBA/NIOSH) | - | No existen en pacientes | Resultados de instrumentos específicos |

### espirometria/

| Archivo | Campo | Tipo | Cotejo con pacientes | Observación |
|---------|-------|------|----------------------|-------------|
| DatosGeneralesEspirometria.vue | (datos de identificación y antropometría básica) | - | Parcialmente existen | Algunos campos como nombre, edad existen en pacientes |
| InterpretacionEspirometria.vue | (interpretación de espirometría) | - | No existen en pacientes | Resultado específico del examen |
| ParametrosFvlEspirometria.vue | (FVC, FEV1, ratios, etc.) | - | No existen en pacientes | Parámetros específicos de espirometría |

### medicina/

| Archivo | Campo | Tipo | Cotejo con pacientes | Observación |
|---------|-------|------|----------------------|-------------|
| AptitudDictamen.vue | aptitud | String | No existe en pacientes | Campo de valoración general |
| ClinicoExamen.vue | (examen físico completo por sistemas) | - | No existen en pacientes | Parte del examen clínico detallado |
| DatosPuesto.vue | (datos de puesto y laboral) | - | No existen en pacientes | Pertenece a datos_laborales |
| ExposicionRiesgos.vue | (agentes, condiciones, otras) | - | No existen en pacientes | Pertenece a exposición ocupacional |

### medicina-enfermeria/ (sección crítica - MasterFichaOcupacional)

| Archivo | Campo | Tipo | Cotejo con pacientes | Observación |
|---------|-------|------|----------------------|-------------|
| datospuesto.vue | folio | String | No existe en pacientes | Pertenece a la valoración (datos_puesto) |
| datospuesto.vue | tipo_evaluacion | String | No existe en pacientes | Tipo de valoración ocupacional |
| datospuesto.vue | fecha_evaluacion | Date | No existe en pacientes | Fecha de la valoración |
| datospuesto.vue | hora_evaluacion | Time | No existe en pacientes | Hora de la valoración |
| datospuesto.vue | medico_evaluador | String | No existe en pacientes | ID del médico evaluador |
| datospuesto.vue | apellido_paterno | String | **Existe como apellido_paterno** | ✅ Campo válido |
| datospuesto.vue | apellido_materno | String | **Existe como apellido_materno** | ✅ Campo válido |
| datospuesto.vue | nombre | String | **Existe como nombre** | ✅ Campo válido |
| datospuesto.vue | edad | String | **Existe como edad** | ⚠️ Campo redundantemente almacenado (debería calcularse) |
| datospuesto.vue | fecha_nacimiento | String | **Existe como fecha_nacimiento** | ✅ Campo válido |
| datospuesto.vue | estado_civil | String | **Existe como estado_civil** | ✅ Campo válido |
| datospuesto.vue | escolaridad | String | **Existe como escolaridad** | ✅ Campo válido |
| datospuesto.vue | edad_inicio_vida_laboral | String | No existe en pacientes | Dato laboral |
| datospuesto.vue | cedula | String | **No existe explícitamente** | ⚠️ Posible mapeo a documento de identidad |
| datospuesto.vue | genero | String | **No existe** | ❌ Usa 'sexo' en la tabla |
| datospuesto.vue | lugar_nacimiento | String | No existe en pacientes | Dato biográfico |
| datospuesto.vue | tipo_sanguineo | String | **Existe como tipo_sangre** | ✅ Campo válido (diferente nombre) |
| datospuesto.vue | telefono | String | **Existe como telefono** | ✅ Campo válido |
| datospuesto.vue | celular | String | **No existe** | ⚠️ Posible duplicado de telefono |
| datospuesto.vue | email | String | **Existe como email** | ✅ Campo válido |
| datospuesto.vue | direccion | String | **Existe como direccion** | ✅ Campo válido |
| datospuesto.vue | emergencia_nombre | String | No existe en pacientes | Parte de contacto_emergencia |
| datospuesto.vue | emergencia_telefono | String | No existe en pacientes | Parte de telefono_emergencia |
| datospuesto.vue | emergencia_relacion | String | No existe en pacientes | Relación de contacto de emergencia |
| datospuesto.vue | ultima_consulta_fecha | String | **Existe como fecha_ultima_consulta_medica** | ✅ Campo válido (diferente nombre) |
| datospuesto.vue | ultima_consulta_motivo | String | **Existe como motivo_ultima_consulta_medica** | ✅ Campo válido (diferente nombre) |
| datospuesto.vue | numero_empleado | String | No existe en pacientes | Dato laboral |
| datospuesto.vue | empresa | String | No existe en pacientes | Pertenece a datos_laborales (empresa_cliente_id) |
| datospuesto.vue | departamento | String | No existe en pacientes | Dato laboral |
| datospuesto.vue | puesto | String | No existe en pacientes | Pertenece a datos_laborales (puesto_trabajo_id) |
| datospuesto.vue | antiguedad | String | No existe en pacientes | Dato laboral |
| datospuesto.vue | antiguedad_laboral | String | No existe en pacientes | Dato laboral |
| datospuesto.vue | antiguedad_empresa | String | No existe en pacientes | Dato laboral |
| datospuesto.vue | tipo_contrato | String | No existe en pacientes | Dato laboral |
| datospuesto.vue | jornada | String | No existe en pacientes | Dato laboral |
| datospuesto.vue | modalidad_trabajo | String | No existe en pacientes | Dato laboral |
| datospuesto.vue | categoria_laboral | String | No existe en pacientes | Dato laboral |
| datospuesto.vue | descripcion_puesto | String | No existe en pacientes | Descripción del puesto |
| datospuesto.vue | riesgos_identificados | String | No existe en pacientes | Resumen de riesgos ocupacionales |
| datospuesto.vue | signos_vitales.frecuencia_cardiaca | String | No existe en pacientes | Parte del examen físico |
| datospuesto.vue | signos_vitales.frecuencia_respiratoria | String | No existe en pacientes | Parte del examen físico |
| datospuesto.vue | signos_vitales.presion_arterial | String | No existe en pacientes | Parte del examen físico |
| datospuesto.vue | signos_vitales.temperatura | String | No existe en pacientes | Parte del examen físico |
| datospuesto.vue | signos_vitales.saturacion_oxigeno | String | No existe en pacientes | Parte del examen físico |
| datospuesto.vue | signos_vitales.glucosa | String | No existe en pacientes | Parte del examen físico |
| exposicionriesgos.vue | (todos los campos de agentes, condiciones, otras) | - | No existen en pacientes | Pertenece a tablas de exposición ocupacional |
| antecedentes.vue | (todos los campos de antecedentes) | - | No existen en pacientes | Pertenece a tablas específicas de antecedentes |
| clinicoexamen.vue | (examen físico completo por sistemas) | - | No existen en pacientes | Parte del examen clínico detallado |
| aptituddictamen.vue | aptitud | String | No existe en pacientes | Campo de valoración final |

### nutricion/

| Archivo | Campo | Tipo | Cotejo con pacientes | Observación |
|---------|-------|------|----------------------|-------------|
| AntecedentesHeredofamiliares.vue | (misma estructura que ergonomia) | - | No existen en pacientes | Antecedentes familiares |
| AntecedentesMedicos.vue | (antecedentes personales médicos) | - | No existen en pacientes | Historial médico |
| Antropometria.vue | peso | String | No existe en pacientes | Dato antropométrico (temporal) |
| Antropometria.vue | talla | String | No existe en pacientes | Dato antropométrico (temporal) |
| Antropometria.vue | imc | String | No existe en pacientes | Dato calculado (peso/talla²) |
| Antropometria.vue | perimetro_cintura | String | No existe en pacientes | Dato antropométrico |
| Antropometria.vue | perimetro_cadera | String | No existe en pacientes | Dato antropométrico |
| Antropometria.vue | complexion_fisica | String | No existe en pacientes | Evaluación subjetiva |
| Antropometria.vue | estado_nutricional | String | No existe en pacientes | Clasificación nutricional |
| Bioquimica.vue | glucosa | String | No existe en pacientes | Resultado de laboratorio |
| Bioquimica.vue | colesterol_total | String | No existe en pacientes | Resultado de laboratorio |
| Bioquimica.vue | hdl | String | No existe en pacientes | Resultado de laboratorio |
| Bioquimica.vue | ldl | String | No existe en pacientes | Resultado de laboratorio |
| Bioquimica.vue | trigliceridos | String | No existe en pacientes | Resultado de laboratorio |
| Bioquimica.vue | acido_urico | String | No existe en pacientes | Resultado de laboratorio |
| Bioquimica.vue | hemoglobina | String | No existe en pacientes | Resultado de laboratorio |
| Bioquimica.vue | vitamina_d | String | No existe en pacientes | Resultado de laboratorio |
| Diagnostico.vue | diagnostico | String | No existe en pacientes | Campo de valoración nutricional |
| Diagnostico.vue | recomendaciones | String | No existe en pacientes | Campo de valoración |
| FactoresLaborales.vue | (factores de riesgo laboral nutricional) | - | No existen en pacientes | Pertenece a condiciones de trabajo |
| HabitosConsumoNutricion.vue | (hábitos alimenticios específicos) | - | No existen en pacientes | Antecedentes personales |

### psicologia/

| Archivo | Campo | Tipo | Cotejo con pacientes | Observación |
|---------|-------|------|----------------------|-------------|
| DatosBasico.vue | (datos de identificación básica) | - | Parcialmente existen | Algunos campos básicos coinciden |
| Diagnostico.vue | diagnostico | String | No existe en pacientes | Campo de valoración psicológica |
| Diagnostico.vue | recomendaciones | String | No existe en pacientes | Campo de valoración |
| FactoresPsicosociales.vue | (factores de NOM-035) | - | No existen en pacientes | Pertenece a valoracion_factores_psicosociales |
| GuiaIEvento.vue | guia_ref_i_aplicada | Boolean | No existe en pacientes | Campo de valoración psicológica |
| GuiaIEvento.vue | ha_presenciado_evento_traumatico | Boolean | No existe en pacientes | Campo de valoración psicológica |
| GuiaIEvento.vue | descripcion_evento_traumatico | String | No existe en pacientes | Campo de valoración psicológica |
| GuiaIEvento.vue | fecha_evento_traumatico | Date | No existe en pacientes | Campo de valoración psicológica |
| GuiaIEvento.vue | requiere_canalizacion_imss | Boolean | No existe en pacientes | Campo de valoración psicológica |
| GuiaIEvento.vue | ambiente_laboral_descripcion | String | No existe en pacientes | Campo de valoración psicológica |
| GuiaIEvento.vue | puntuacion_riesgo_texto | String | No existe en pacientes | Campo de valoración psicológica |
| GuiaIEvento.vue | codigo_cie11 | String | No existe en pacientes | Campo de valoración psicológica |
| GuiaIEvento.vue | relacionado_con_trabajo | Boolean | No existe en pacientes | Campo de valoración psicológica |
| GuiaIEvento.vue | aptitud | String | No existe en pacientes | Campo de valoración psicológica |
| GuiaIEvento.vue | restricciones | String | No existe en pacientes | Campo de valoración psicológica |
| GuiaIEvento.vue | recomendaciones | String | No existe en pacientes | Campo de valoración psicológica |
| GuiaIEvento.vue | requiere_seguimiento | Boolean | No existe en pacientes | Campo de valoración psicológica |
| GuiaIEvento.vue | plazo_proximo_seguimiento | Date | No existe en pacientes | Campo de valoración psicológica |
| GuiaIEvento.vue | canalizado_a | String | No existe en pacientes | Campo de valoración psicológica |
| GuiaIEvento.vue | lugar_canalizacion | String | No existe en pacientes | Campo de valoración psicológica |
| GuiaIEvento.vue | notas_seguimiento | String | No existe en pacientes | Campo de valoración psicológica |

## Campos que NO son del paciente y a qué tabla pertenecen

### Tablas de Valoraciones Ocupacionales
- **valoraciones_ocupacionales**: folio, tipo, fecha_valoracion, hora_valoracion, ta_sistolica, ta_diastolica, fc, fr, spo2, temperatura_c, estatura_cm, peso_kg, talla_abdomen_cm, imc, imc_clasificacion, complexion_fisica, estado_nutricional, glicemia, grasa_visceral, grasa_corporal_pct, musculo_pct, edad_biologica, antecedentes_heredofamiliares, antecedentes_patologicos_activos, estilo_vida_habitos, tabaco_tiempo, tabaco_cantidad, tabaco_frecuencia, alcohol_tiempo, alcohol_cantidad, alcohol_frecuencia, drogas_tipo, drogas_frecuencia, exploracion_fisica_funcional, agudeza_visual, ishihara, estudios_paraclinicos_resumen, analisis_correlacion_riesgos, dictamen, restricciones, dictamen_justificacion, diagnostico_clinico, plan_intervencion, vigencia_hasta, firmado_medico_en, firmado_trabajador_en, submódulo
- **datos_puesto**: lugar_nacimiento, celular, antiguedad_empresa, riesgos_identificados
- **agentes_exposicion**: polvo_mineral, asbesto, silice, berilio, cadmio, plomo, mercurio, cromo, arsenico, niquel, dioxinas, vibraciones, radiacion_ionizante, radiacion_no_ionizante, ruido, temperatura_extrema, estres_termico, exposicion_solar, otros
- **condiciones_riesgo**: trabajo_altura_2m, espacio_confinado, maquinaria_pesada, cargas_25kg, sustancias_quimicas, agentes_biologicos, radiaciones_ionizantes, radiaciones_no_ionizantes, turnos_rotativos_nocturnos, estres_ocupacional, trabajo_repetitivo, posturas_forzadas, vibraciones, temperatura_extrema, exposicion_solar_prolongada
- **otras_condiciones_trabajo**: realiza_generalmente, carga_mental, decisiones_criticas, responsabilidad_personas, comunicacion_constante, precision_extrema, ritmo_acelerado, monotonia, falta_autonomia, conflictos, acoso, discriminacion
- **clinico_examen**: comorbilidades (json), cabeza_interrogatorio, cabeza_exploracion, cabeza_notas, ojos_interrogatorio, ojos_exploracion, ojos_notas, ojos_agudeza_od, ojos_agudeza_oi, ojos_ishihara, ojos_presion_intraocular, oidos_interrogatorio, oidos_exploracion, oidos_notas, oidos_acufenos, oidos_mareos, oidos_audiometria, nariz_interrogatorio, nariz_exploracion, nariz_notas, nariz_epistaxis, nariz_obstruccion, nariz_desviacion_septal, cardiovascular_interrogatorio, cardiovascular_exploracion, cardiovascular_notas, cardiovascular_auscultacion, cardiovascular_pulsos, cardiovascular_edemas, cardiovascular_varices, pulmonar_interrogatorio, pulmonar_exploracion, pulmonar_notas, pulmonar_auscultacion, pulmonar_tos_esputo, pulmonar_espirometria, gastrointestinal_interrogatorio, gastrointestinal_exploracion, gastrointestinal_notas, gastrointestinal_palpacion, gastrointestinal_ruidos, gastrointestinal_higado, gastrointestinal_bazo, abdomen_interrogatorio, abdomen_exploracion, abdomen_notas, miembros_sup_interrogatorio, miembros_sup_exploracion, miembros_sup_notas, miembros_inf_interrogatorio, miembros_inf_exploracion, miembros_inf_notas, genitourinario_interrogatorio, genitourinario_exploracion, genitourinario_notas, genitourinario_hallazgos, genitourinario_puño_percusion, medicamentos, alergias, pruebas_complementarias (json)
- **valoracion_alimentos**: alimento_id, frecuencia_texto
- **valoracion_condiciones_trabajo**: de_pie, de_pie_caminando, sentado, posicion_forzada, esfuerzo_fisico, movimientos_repetitivos, manipulando_cargas, con_herramientas, esfuerzo_mental, tension_emocional_estres, monotonia_repetitividad, trabajo_bajo_presion, concentracion, trato_clientes, responsabilidad_laboral, toma_decisiones
- **valoracion_estudios_paraclinicos**: tipo_estudio, laboratorio, fecha_estudio, resultados (json), interpretacion, archivo_url
- **valoracion_exploracion_sistemas**: sistema, sintomas_referidos, hallazgos_exploracion
- **valoracion_factores_psicosociales**: factor_id, presente, nivel_severidad
- **valoracion_norma_aplicada**: norma_id, aplicacion_especifica
- **valoracion_riesgos_ergonomicos**: riesgo_id, presente, medida_control
- **valoracion_seguimiento**: tipo_revaloracion, plazo, objetivo, completado, fecha_completado

### Tablas Especializadas por Módulo
- **valoraciones_audiologia**: Todos los campos específicos de audiología (frecuencias, PTA, tipos de hipoacusia, etc.)
- **valoraciones_ergonomicas**: Todos los campos específicos de ergonomía (instrumentos, hallazgos, restricciones, etc.)
- **valoraciones_espirometria**: Todos los campos específicos de espirometría (volúmenes, flujos, interpretación, etc.)
- **valoraciones_nutricion**: Todos los campos específicos de nutrición (antropometría, bioquímica, estado nutricional, etc.)
- **valoraciones_psicologicas**: Todos los campos específicos de psicología (factores psicosociales, guías NOM, diagnóstico, etc.)
- **historial_puestos**: paciente_id, puesto_trabajo_id, fecha_inicio, fecha_fin, motivo_cambio
- **empresas_cliente**: razon_social, rfc, giro_actividad, direccion, contacto_nombre, contacto_telefono, contacto_email, activo
- **puestos_trabajo**: empresa_cliente_id, nombre, departamento, descripcion_actividades, riesgos_* (booleanos), requiere_epp, epp_detalle, riesgos_adicionales (json), activo
- **medicos**: user_id, folio, nombre, cedula_profesional, especialidad_id, activo
- **users**: name, email, password, role, remember_token, rol, activo, onboarding_completado, password_changed_at

## Accesos a propiedades inexistentes

1. **resources/js/components/ClinicaTrabajo/secciones/medicina-enfermeria/datospuesto.vue:283-284**
   - `apellido_paterno` y `apellido_materno` - **EXISTEN** en la tabla pacientes (correcto)

2. **resources/js/components/ClinicaTrabajo/secciones/medicina-enfermeria/datospuesto.vue:286**
   - `edad` - **EXISTE** pero es redundantemente almacenado (debería calcularse de fecha_nacimiento)

3. **resources/js/components/ClinicaTrabajo/secciones/medicina-enfermeria/datospuesto.vue:292**
   - `genero` - **NO EXISTE** en la tabla pacientes (la tabla usa `sexo`)

4. **resources/js/components/ClinicaTrabajo/secciones/medicina-enfermeria/datospuesto.vue:296**
   - `cedula` - **NO EXISTE explícitamente** en la tabla pacientes (posible mapeo ambiguo)

5. **resources/js/components/ClinicaTrabajo/secciones/medicina-enfermeria/datospuesto.vue:300**
   - `celular` - **NO EXISTE** en la tabla pacientes (posible duplicado de telefono)

6. **resources/js/components/ClinicaTrabajo/secciones/medicina-enfermeria/datospuesto.vue:302**
   - `email` - **EXISTE** en la tabla pacientes (correcto)

7. **resources/js/components/ClinicaTrabajo/secciones/medicina-enfermeria/datospuesto.vue:304**
   - `direccion` - **EXISTE** en la tabla pacientes (correcto)

8. **resources/js/components/ClinicaTrabajo/secciones/medicina-enfermeria/datospuesto.vue:306-308**
   - `emergencia_nombre`, `emergencia_telefono`, `emergencia_relacion` - **NO EXISTEN** como campos separados (deberían estar en contacto_emergencia y telefono_emergencia)

9. **resources/js/components/ClinicaTrabajo/secciones/medicina-enfermeria/datospuesto.vue:310-312**
   - `ultima_consulta_fecha`, `ultima_consulta_motivo` - **EXISTEN** como fecha_ultima_consulta_medica y motivo_ultima_consulta_medica (correcto, diferente nombre)

10. **resources/js/components/ClinicaTrabajo/secciones/medicina-enfermeria/datospuesto.vue:314**
    - `numero_empleado` - **NO EXISTE** en la tabla pacientes (pertenece a datos_laborales)

## Preguntas abiertas para el equipo

1. **¿Cuál es el mapeo correcto del campo `cedula` en el frontend?** ¿Existe en alguna otra tabla como documento de identidad alternativo o debería agregarse a la tabla pacientes?

2. **¿Debería eliminarse el campo `edad` de la tabla pacientes y calcularse únicamente a partir de `fecha_nacimiento`?** Actualmente se almacena ambos, lo que genera inconsistencias potenciales.

3. **¿Cómo debería modelarse el contacto de emergencia?** Actualmente se tiene `contacto_emergencia` y `telefono_emergencia` en la tabla, pero el frontend los separa en nombre, teléfono y relación.

4. **¿El campo `genero` en el frontend debería mapearse a `sexo` en la base de datos o se necesita agregar un campo `genero` separado?** Hay una semántica diferente entre ambos conceptos.

5. **¿Qué información de contacto debe conservarse en pacientes_clinica_trabajo?** ¿Solo lo esencial o todos los medios disponibles (teléfono, celular, email, whatsapp_id)?

6. **¿Se necesita mantener el `qr_token` y `whatsapp_id` en la tabla clínica de trabajo o son relevantes solo para otros módulos?**

7. **¿Cómo se debería manejar la trazabilidad de cambios en datos de paciente entre módulos?** ¿Se necesita un campo `paciente_origen_id` o similar?

## Propuesta de columnas para pacientes_clinica_trabajo

### Conservar de pacientes (esenciales para módulo clínico)
- `id` (PK)
- `nombre`
- `apellido_paterno`
- `apellido_materno`
- `fecha_nacimiento` (en lugar de edad redundante)
- `sexo` (mapear de genero del frontend)
- `tipo_sangre`
- `estado_civil`
- `escolaridad`
- `direccion`
- `email`
- `telefono` (teléfono principal/fijo)
- `celular` (teléfono móvil - agregar como nuevo campo)
- `contacto_emergencia` (nombre del contacto)
- `telefono_emergencia` (teléfono del contacto)
- `curp`
- `alergias`
- `alergia_medicamentos`
- `antecedentes_medicos`
- `antecedentes_quirurgicos`
- `enfermedades_cronicas`
- `medicamentos_actuales`
- `fecha_ultima_consulta_medica`
- `motivo_ultima_consulta_medica`

### Eliminar de pacientes (no relevantes para módulo clínico o redundantes)
- `edad` (redundante, calcular de fecha_nacimiento)
- `edad_unidad` (inherente al cálculo de edad)
- `whatsapp_id` (relevante solo para módulo de comunicaciones)
- `consentimiento_datos` (debería manejarse a nivel de sistema, no por paciente)
- `ultima_interaccion` (puede derivarse de consultas o módulos específicos)
- `foto` (almacenamiento separado recomendado)
- `notas_generales` (mejor manejado en módulos específicos)
- `qr_token` (relevante solo para módulo de citas/check-in)
- `paciente_id` (el folio debería generarse en la tabla clínica)

### Agregar (para trazabilidad y funcionalidad clínica)
- `paciente_origen_id` (FK a pacientes.id para trazabilidad)
- `fecha_registro_clinico` (cuando se creó el registro en este contexto)
- `fecha_actualizacion_clinica` (última actualización desde este módulo)
- `activo` (estado específico del registro clínico)
- `origen_registro` (manual, importado, sincronizado, etc.)

### Notas importantes:
1. **No se debe duplicar datos** que ya existen en la tabla pacientes principales - solo crear referencias
2. **Los campos de examen, valoraciones y antecedentes específicos** deben permanecer en sus tablas especializadas (valoraciones_*, datos_puesto, agentes_exposicion, etc.)
3. **La tabla pacientes_clinica_trabajo debería ser una vista materializada o tabla de sincronización** que se actualice desde el módulo clínico de trabajo, no una copia estática
4. **Se recomienda usar triggers o eventos** para mantener sincronización entre pacientes y pacientes_clinica_trabajo cuando corresponda