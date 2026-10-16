# Campos de Triage para Módulo Clínica de Trabajo

## Resumen
Se identificaron 6 campos de triage en la sección medicina-enfermeria, específicamente en el componente datospuesto.vue dentro del objeto signos_vitales. Estos campos corresponden a mediciones iniciales de signos vitales y glucosa capturadas al inicio de la evaluación ocupacional. No se encontró un componente de triage explícito; los campos están integrados en el primer paso del formulario de evaluación ocupacional (Hoja 1: Datos Laborales). Se validan en frontend con rangos clínicos típicos.

## Tabla de campos de triage

| Sección | Archivo | Línea | Campo (ruta completa) | Tipo | Validación en frontend | Observación |
|---------|---------|-------|-----------------------|------|------------------------|-------------|
| medicina-enfermeria | resources/js/components/ClinicaTrabajo/secciones/medicina-enfermeria/datospuesto.vue | 317-323 | signos_vitales.frecuencia_cardiaca | number | min="30" max="200" | Latidos por minuto, captura inicial |
| medicina-enfermeria | resources/js/components/ClinicaTrabajo/secciones/medicina-enfermeria/datospuesto.vue | 318-324 | signos_vitales.frecuencia_respiratoria | number | min="10" max="40" | Respiraciones por minuto, captura inicial |
| medicina-enfermeria | resources/js/components/ClinicaTrabajo/secciones/medicina-enfermeria/datospuesto.vue | 319-325 | signos_vitales.presion_arterial | string (formato "SISTOLICA/DIASTOLICA") | validación implícita en backend | Capturada como texto pero representa dos valores |
| medicina-enfermeria | resources/js/components/ClinicaTrabajo/secciones/medicina-enfermeria/datospuesto.vue | 320-326 | signos_vitales.temperatura | number | min="30" max="45" | Grados Celsius, captura inicial |
| medicina-enfermeria | resources/js/components/ClinicaTrabajo/secciones/medicina-enfermeria/datospuesto.vue | 321-327 | signos_vitales.saturacion_oxigeno | number | min="70" max="100" | Porcentaje, captura inicial |
| medicina-enfermeria | resources/js/components/ClinicaTrabajo/secciones/medicina-enfermeria/datospuesto.vue | 322-328 | signos_vitales.glucosa | number | min="40" max="500" | mg/dL, tamizaje rápido de llegada |

## Campos ambiguos
- **peso, talla**: Aunque son medidas básicas, aparecen en componentes de valoración específica (Antropometria.vue en nutricion, DatosGeneralesEspirometria.vue en espirometria) y no en el flujo inicial de llegada. Se consideran parte de la valoración antropométrica, no triage.
- **motivo_consulta, sintomas**: Se captulan mediante el chat no estructurado o en la consulta previa, no como campos de formulario dedicados en el módulo de clínica de trabajo. No aparecen como v-model en los componentes de secciones.
- **urgencia**: No se registra explícitamente en el módulo clínico de trabajo; se deriva de la consulta o se maneja a nivel de sistema.

## Campos excluidos
- **frecuencia_cardiaca (oidos)**: En audiologia/Audiometria.vue se mide pero como parte del examen audiométrico específico, no como signo vital inicial.
- **temperatura (oidos)**: No aplicable.
- **glucosa (bioquimica)**: En nutricion/Bioquimica.vue es resultado de laboratorio, no tamizaje de llegada.
- **peso/estatura (espirometria)**: En espirometria/DatosGeneralesEspirometria.vue son parte de la valoración espirométrica específica.
- **signos vitales en exploracionfisica.vue**: Corresponden al examen físico detallado post-triage.

## Cálculos en frontend
- **IMC**: Se calcula en múltiples componentes:
  - nutricion/Antropometria.vue: `imc` como campo editable (no calculado automáticamente en frontend)
  - espirometria/DatosGeneralesEspirometria.vue: `imc` como campo editable
  - MedicinaService.php: No se encontró cálculo automático de IMC en servicios
- **Edad**: Se calcula a partir de `fecha_nacimiento` en Paciente.php mediante el accessor `getEdadFormateadaAttribute()`, pero en el frontend de clínica de trabajo se captura `edad` directamente como campo (lo cual es problemático - debería calcularse).
- **Presión arterial**: No se calcula; se captura directamente como texto en formato "SISTOLICA/DIASTOLICA".

## Propuesta de columnas para triage_clinica_trabajo

Comparación con la estructura esperada de una tabla de triage clínica de trabajo:

### Campos que ya estarían en la tabla propuesta
- frecuencia_cardiaca (integer)
- frecuencia_respiratoria (integer) 
- temperatura (decimal)
- saturacion_oxigeno (integer)
- glucosa (integer)
- presion_sistolica (integer) - requiere separación del campo presion_arterial
- presion_diastolica (integer) - requiere separación del campo presion_arterial

### Campos encontrados que faltan en la tabla propuesta
- Ninguno - todos los campos de triage identificados tienen equivalente propuesto (con la nota de que presión arterial necesita separación)

### Campos de la tabla propuesta que no se encontraron en el frontend
- peso (decimal) - Aunque se captura en otros módulos, no aparece como campo de triage inicial en clínica de trabajo
- talla (decimal) - Igual que peso, no en flujo inicial de llegada
- circunferencia_cintura (integer) - No encontrado
- circunferencia_cadera (integer) - No encontrado
- motivo_consulta (text) - Se maneja mediante chat no estructurado o consulta previa
- sintomas_referidos (text) - No hay campo dedicado en formularios de secciones
- nivel_urgencia (varchar) - No registrado explícitamente en el módulo
- observaciones_llegada (text) - Se integra en el chat o notas generales
- fecha_triage (timestamp) - Se inferiría de created_at de la consulta o sesión
- hora_triage (time) - Igual que fecha

### Recomendaciones para la tabla triage_clinica_trabajo
1. Separar presion_arterial en dos campos enteros: presion_sistolica y presion_diastolica
2. Considerar si incluir peso y talla aunque no sean parte del flujo inicial - podrían copiarse desde la última valoración antropométrica disponible
3. Añadir campos de trazabilidad: paciente_id (FK), consulta_id (FK opcional), creado_en, actualizado_en
4. Los campos de motivo, sintomas y urgencia se manejan mejor a nivel de consulta general que en este módulo específico