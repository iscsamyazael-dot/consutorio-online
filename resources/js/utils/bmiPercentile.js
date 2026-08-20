/**
 * Cálculo de percentil de IMC pediátrico (método LMS del CDC).
 * Fuente: CDC Extended BMI-for-age Growth Charts
 * https://www.cdc.gov/growthcharts/extended-bmi-data-files.htm
 *
 * Requiere una fila de la tabla LMS para el sexo y edad (en meses) del paciente:
 *   { sex, agemos, L, M, S, P95, sigma }
 */

// CDF de la normal estándar (aproximación Abramowitz-Stegun, error < 1.5e-7)
function normalCDF(z) {
  const b1 = 0.319381530;
  const b2 = -0.356563782;
  const b3 = 1.781477937;
  const b4 = -1.821255978;
  const b5 = 1.330274429;
  const p = 0.2316419;
  const c = 0.39894228;

  if (z >= 0) {
    const t = 1 / (1 + p * z);
    return 1 - c * Math.exp(-z * z / 2) * t *
      (t * (t * (t * (t * b5 + b4) + b3) + b2) + b1);
  } else {
    return 1 - normalCDF(-z);
  }
}

// Inversa de la CDF normal estándar (aproximación de Beasley-Springer-Moro)
function normalInverseCDF(p) {
  if (p <= 0) p = 1e-15;
  if (p >= 1) p = 1 - 1e-15;
  // if (p <= 0 || p >= 1) throw new Error("p debe estar entre 0 y 1");
  const a = [-3.969683028665376e+01, 2.209460984245205e+02, -2.759285104469687e+02,
    1.383577518672690e+02, -3.066479806614716e+01, 2.506628277459239e+00];
  const b = [-5.447609879822406e+01, 1.615858368580409e+02, -1.556989798598866e+02,
    6.680131188771972e+01, -1.328068155288572e+01];
  const c = [-7.784894002430293e-03, -3.223964580411365e-01, -2.400758277161838e+00,
    -2.549732539343734e+00, 4.374664141464968e+00, 2.938163982698783e+00];
  const d = [7.784695709041462e-03, 3.224671290700398e-01, 2.445134137142996e+00,
    3.754408661907416e+00];

  const pLow = 0.02425;
  const pHigh = 1 - pLow;
  let q, r;

  if (p < pLow) {
    q = Math.sqrt(-2 * Math.log(p));
    return (((((c[0] * q + c[1]) * q + c[2]) * q + c[3]) * q + c[4]) * q + c[5]) /
      ((((d[0] * q + d[1]) * q + d[2]) * q + d[3]) * q + 1);
  } else if (p <= pHigh) {
    q = p - 0.5;
    r = q * q;
    return (((((a[0] * r + a[1]) * r + a[2]) * r + a[3]) * r + a[4]) * r + a[5]) * q /
      (((((b[0] * r + b[1]) * r + b[2]) * r + b[3]) * r + b[4]) * r + 1);
  } else {
    q = Math.sqrt(-2 * Math.log(1 - p));
    return -(((((c[0] * q + c[1]) * q + c[2]) * q + c[3]) * q + c[4]) * q + c[5]) /
      ((((d[0] * q + d[1]) * q + d[2]) * q + d[3]) * q + 1);
  }
}

/**
 * Calcula IMC = peso(kg) / talla(m)^2
 */
export function calcularIMC(pesoKg, tallaCm) {
  const tallaM = tallaCm / 100;
  return pesoKg / (tallaM * tallaM);
}

/**
 * Calcula z-score y percentil de IMC pediátrico según el método LMS/extendido del CDC.
 * @param {number} bmi - IMC calculado del paciente
 * @param {object} lmsRow - fila de la tabla CDC para el sexo/edad: { L, M, S, P95, sigma }
 * @returns {{ zScore: number, percentile: number, sobreP95: boolean }}
 */
export function calcularPercentilIMC(bmi, lmsRow) {
  const { L, M, S, P95, sigma } = lmsRow;

  if (bmi <= P95) {
    // Fórmula estándar LMS (CDC 2000)
    const zScore = (Math.pow(bmi / M, L) - 1) / (L * S);
    const percentile = normalCDF(zScore) * 100;
    return { zScore, percentile, sobreP95: false };
  } else {
    // Fórmula extendida (CDC 2022) para IMC por encima del percentil 95
    const percentile = 90 + 10 * normalCDF((bmi - P95) / sigma);
    const zScore = normalInverseCDF(percentile / 100);
    return { zScore, percentile, sobreP95: true };
  }
}

/**
 * Clasifica el percentil de IMC pediátrico según criterio CDC.
 */
export function clasificarPercentilPediatrico(percentile) {
  if (percentile < 5) return "Bajo peso";
  if (percentile < 85) return "Peso normal";
  if (percentile < 95) return "Sobrepeso";
  return "Obesidad";
}

/**
 * Clasificación de IMC en adultos (OMS, rangos fijos).
 */
export function clasificarIMCAdulto(bmi) {
  if (bmi < 18.5) return "Bajo peso";
  if (bmi < 25) return "Peso normal";
  if (bmi < 30) return "Sobrepeso";
  if (bmi < 35) return "Obesidad grado I";
  if (bmi < 40) return "Obesidad grado II";
  return "Obesidad grado III";
}

/**
 * Busca la fila LMS más cercana en la tabla del CDC para un sexo/edad en meses dados.
 * @param {Array} lmsTable - array de filas { Sex, Agemos, L, M, S, P95, sigma }
 * @param {number} sex - 1 = niño/hombre, 2 = niña/mujer
 * @param {number} agemos - edad en meses
 */
export function buscarFilaLMS(lmsTable, sex, agemos) {
  const filas = lmsTable.filter(r => Number(r.sex) === Number(sex));
  if (!filas.length) return null;
  // La tabla trae edad al punto medio del mes (ej. 48.5), buscamos la más cercana
  let mejor = filas[0];
  let mejorDist = Math.abs(filas[0].agemos - agemos);
  for (const fila of filas) {
    const dist = Math.abs(fila.agemos - agemos);
    if (dist < mejorDist) {
      mejor = fila;
      mejorDist = dist;
    }
  }
  return mejor;
}

/**
 * Convierte edad en años (y meses opcionales) a meses totales.
 */
export function edadAMeses(anios, meses = 0) {
  return anios * 12 + meses;
}

/**
 * Función de conveniencia todo-en-uno: dado peso, talla, edad y sexo,
 * calcula el IMC y su clasificación (pediátrica vía percentil CDC, o adulto vía OMS).
 * @param {object} datos - { pesoKg, tallaCm, edadAnios, edadMeses, sexo: 'M'|'F' }
 * @param {Array} lmsTable - tabla LMS del CDC (bmi-lms-cdc.json)
 */
export function evaluarIMC({ pesoKg, tallaCm, edadAnios, edadMeses = 0, sexo }, lmsTable) {
  const bmi = calcularIMC(pesoKg, tallaCm);
  const esAdulto = edadAnios >= 20; // la tabla CDC pediátrica cubre 2-20 años

  if (esAdulto) {
    return {
      bmi: Number(bmi.toFixed(2)),
      tipo: 'adulto',
      clasificacion: clasificarIMCAdulto(bmi)
    };
  }

  if (edadAnios < 2) {
    return {
      bmi: Number(bmi.toFixed(2)),
      tipo: 'no_aplica',
      clasificacion: 'La tabla CDC de percentiles aplica de 2 a 20 años. Para menores de 2 años se deben usar las tablas OMS de peso/talla.'
    };
  }

  const agemosNum = edadAMeses(edadAnios, edadMeses);
  const sexNum = sexo === 'M' || sexo === 'm' || Number(sexo) === 1 ? 1 : 2;
  const fila = buscarFilaLMS(lmsTable, sexNum, agemosNum);

  if (!fila) {
    return {
      bmi: Number(bmi.toFixed(2)),
      tipo: 'error',
      clasificacion: 'No se encontró una fila LMS correspondiente a esta edad/sexo.'
    };
  }

  const { zScore, percentile } = calcularPercentilIMC(bmi, fila);

  return {
    bmi: Number(bmi.toFixed(2)),
    tipo: 'pediatrico',
    zScore: Number(zScore.toFixed(2)),
    percentil: Number(percentile.toFixed(1)),
    clasificacion: clasificarPercentilPediatrico(percentile)
  };
}