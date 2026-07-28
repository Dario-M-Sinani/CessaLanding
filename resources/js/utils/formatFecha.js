const MESES = [
  'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio',
  'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre',
];

const DIAS = [
  'domingo', 'lunes', 'martes', 'miércoles', 'jueves', 'viernes', 'sábado',
];

/**
 * Formats a pure "date" value (e.g. execution_date) as "Lunes 28 de julio, 2026".
 * Parses the Y-M-D parts directly instead of going through Date+timezone
 * conversion, since a UTC-serialized date-only value can otherwise shift
 * to the previous day when the browser is west of UTC (e.g. Bolivia, UTC-4).
 */
export function formatFechaLarga(value) {
  if (!value) return '';
  const [y, m, d] = value.slice(0, 10).split('-').map(Number);
  if (!y || !m || !d) return value;
  const date = new Date(y, m - 1, d);
  const dia = DIAS[date.getDay()];
  const diaCapitalizado = dia.charAt(0).toUpperCase() + dia.slice(1);
  return `${diaCapitalizado} ${d} de ${MESES[m - 1]}, ${y}`;
}

/**
 * Formats a real timestamp (created_at) as "23 de julio de 2026".
 */
export function formatFechaPublicacion(value) {
  if (!value) return '';
  const date = new Date(value);
  if (isNaN(date)) return value;
  return date.toLocaleDateString('es-BO', { day: 'numeric', month: 'long', year: 'numeric' });
}

/**
 * Strips seconds from a "HH:MM:SS" time string, e.g. "06:00:00" -> "06:00".
 */
export function formatHora(value) {
  if (!value) return '';
  return value.slice(0, 5);
}
