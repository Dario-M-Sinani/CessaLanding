// Pin personalizado con los colores de marca de CESSA (azul #004c98 / amarillo #ffe71e)
// en vez del pin rojo genérico de Google. Requiere que window.google.maps ya esté
// cargado (google.maps.Size/Point no existen antes de eso).
export function buildCessaMarkerIcon(scale = 1) {
  const width = Math.round(40 * scale);
  const height = Math.round(52 * scale);

  return {
    url: 'data:image/svg+xml;charset=UTF-8,' + encodeURIComponent(`
      <svg xmlns="http://www.w3.org/2000/svg" width="40" height="52" viewBox="0 0 40 52">
        <path d="M20 0C8.954 0 0 8.954 0 20c0 14 20 32 20 32s20-18 20-32C40 8.954 31.046 0 20 0z" fill="#004c98"/>
        <circle cx="20" cy="20" r="10" fill="#ffe71e"/>
      </svg>
    `),
    scaledSize: new window.google.maps.Size(width, height),
    anchor: new window.google.maps.Point(width / 2, height),
  };
}

// Logo de CESSA en base64 (no se puede referenciar por URL: los íconos de
// google.maps.Marker se renderizan en un contexto de "imagen" que bloquea
// la carga de recursos externos, incluso dentro de un <image> de SVG).
import { CESSA_LOGO_BASE64 } from './cessaLogoBase64';

// Pin con el isotipo de CESSA para marcar la oficina en los mapas de referencia
// (Inicio, Contacto). No usar para el pin de "selecciona tu dirección" en los
// formularios de trámites -- ese es la ubicación del cliente, no de CESSA.
export function buildCessaLogoMarkerIcon(scale = 1) {
  const width = Math.round(52 * scale);
  const height = Math.round(66 * scale);
  const logoDataUri = `data:image/png;base64,${CESSA_LOGO_BASE64}`;

  return {
    url: 'data:image/svg+xml;charset=UTF-8,' + encodeURIComponent(`
      <svg xmlns="http://www.w3.org/2000/svg" width="52" height="66" viewBox="0 0 52 66">
        <path d="M26 0C11.64 0 0 11.64 0 26c0 18.2 26 40 26 40s26-21.8 26-40C52 11.64 40.36 0 26 0z" fill="#004c98"/>
        <circle cx="26" cy="26" r="20" fill="#ffffff"/>
        <clipPath id="cessaLogoClip"><circle cx="26" cy="26" r="18"/></clipPath>
        <image href="${logoDataUri}" x="8" y="8" width="36" height="36" clip-path="url(#cessaLogoClip)" preserveAspectRatio="xMidYMid slice"/>
      </svg>
    `),
    scaledSize: new window.google.maps.Size(width, height),
    anchor: new window.google.maps.Point(width / 2, height),
  };
}
