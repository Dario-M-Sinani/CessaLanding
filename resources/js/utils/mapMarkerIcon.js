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
