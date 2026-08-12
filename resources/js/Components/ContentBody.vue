<template>
  <div class="content-body text-gray-700 text-sm leading-relaxed">
    <template v-for="(block, i) in blocks" :key="i">
      <div v-if="block.type === 'html'" v-html="block.content"></div>
      <ImageCarousel v-else-if="block.type === 'carousel'" :images="block.images" />
      <DocumentLinks v-else :documents="block.documents" />
    </template>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import ImageCarousel from './ImageCarousel.vue';
import DocumentLinks from './DocumentLinks.vue';

const props = defineProps({
  html: String,
});

const DOC_EXTENSIONS = ['pdf', 'xlsx', 'xls', 'doc', 'docx', 'zip'];

// Igual espíritu que el carrusel de imágenes: si el editor pone 2 o más links a
// documentos seguidos (cada uno solo en su <p>/<li>, o dentro de una tabla como
// migró el legacy), los mostramos como tarjetas con ícono en vez de una lista de
// links pelados. Corre en el navegador, no requiere ninguna marca especial del editor.
const docExtOf = (href) => {
  const match = (href || '').split('?')[0].match(/\.([a-z0-9]+)$/i);
  return match ? match[1].toLowerCase() : null;
};

// Solo cuenta como "bloque de un solo link" si ese <a> es el único hijo de peso del
// nodo (igual criterio que isImageOnlyBlock) -- así no confunde un contenedor con
// varios links adentro (ej. la tabla de abajo) con un párrafo de un solo link.
const isDocumentLinkBlock = (node) => {
  if (node.nodeType !== 1) return null;
  let link = null;
  if (node.tagName === 'A') {
    link = node;
  } else if (['P', 'DIV', 'LI'].includes(node.tagName)) {
    const children = Array.from(node.childNodes).filter(
      (n) => !(n.nodeType === 3 && !n.textContent.trim())
    );
    if (children.length === 1 && children[0].nodeType === 1 && children[0].tagName === 'A') {
      link = children[0];
    }
  }
  if (!link) return null;
  const ext = docExtOf(link.getAttribute('href'));
  if (!ext || !DOC_EXTENSIONS.includes(ext)) return null;
  return { href: link.getAttribute('href'), label: link.textContent.trim().replace(/\s+/g, ' '), ext };
};

// El legacy migró estas listas como <div class="table-responsive"><table><tr><td>
// <a>...</a></td></tr></table></div> -- si todos los links de la tabla son
// documentos, la tabla entera (venga sola o envuelta en un div) se reemplaza.
const documentsFromTable = (node) => {
  if (node.nodeType !== 1) return null;
  const table = node.tagName === 'TABLE' ? node : node.querySelector?.('table');
  if (!table) return null;
  const links = Array.from(table.querySelectorAll('a'));
  if (!links.length) return null;
  const docs = links.map((a) => {
    const ext = docExtOf(a.getAttribute('href'));
    return ext && DOC_EXTENSIONS.includes(ext)
      ? { href: a.getAttribute('href'), label: a.textContent.trim().replace(/\s+/g, ' '), ext }
      : null;
  });
  return docs.every(Boolean) ? docs : null;
};

// Si el editor puso 2 o más imágenes seguidas (cada una sola en su <p>/<div>, como
// hace el RichEditor de Filament), las agrupamos en un carrusel en vez de mostrarlas
// apiladas una debajo de la otra. Una imagen sola entre texto queda igual que siempre.
// Esto corre en el navegador (DOMParser), así que el HTML que viene del CMS no necesita
// ninguna marca especial -- el editor de contenidos sigue trabajando como siempre.
const isImageOnlyBlock = (node) => {
  if (node.nodeType !== 1) return false;
  if (node.tagName === 'IMG') return true;
  if (['P', 'DIV'].includes(node.tagName)) {
    const children = Array.from(node.childNodes).filter(
      (n) => !(n.nodeType === 3 && !n.textContent.trim())
    );
    return children.length === 1 && children[0].nodeType === 1 && children[0].tagName === 'IMG';
  }
  return false;
};

const extractImg = (node) => {
  const img = node.tagName === 'IMG' ? node : node.querySelector('img');
  return { src: img?.getAttribute('src') ?? '', alt: img?.getAttribute('alt') ?? '', html: node.outerHTML };
};

const blocks = computed(() => {
  if (!props.html || typeof window === 'undefined') {
    return [{ type: 'html', content: props.html ?? '' }];
  }

  const doc = new DOMParser().parseFromString(props.html, 'text/html');
  const nodes = Array.from(doc.body.childNodes);

  const result = [];
  let htmlBuffer = '';
  let imgBuffer = [];
  let docBuffer = [];

  const flushHtml = () => {
    if (htmlBuffer.trim()) result.push({ type: 'html', content: htmlBuffer });
    htmlBuffer = '';
  };

  const flushImgs = () => {
    if (imgBuffer.length >= 2) {
      result.push({ type: 'carousel', images: imgBuffer.map(({ src, alt }) => ({ src, alt })) });
    } else if (imgBuffer.length === 1) {
      htmlBuffer += imgBuffer[0].html;
    }
    imgBuffer = [];
  };

  const flushDocs = () => {
    if (docBuffer.length >= 2) {
      result.push({ type: 'documentos', documents: docBuffer });
    } else if (docBuffer.length === 1) {
      const d = docBuffer[0];
      htmlBuffer += `<p><a href="${d.href}">${d.label}</a></p>`;
    }
    docBuffer = [];
  };

  for (const node of nodes) {
    // Los saltos de línea entre bloques quedan como nodos de texto en blanco -- si no
    // los saltamos, cortan la racha de imágenes/documentos seguidos antes de agruparlos.
    if (node.nodeType === 3 && !node.textContent.trim()) continue;

    const tableDocs = documentsFromTable(node);
    const docLink = isDocumentLinkBlock(node);

    if (tableDocs) {
      flushHtml();
      flushImgs();
      flushDocs();
      result.push({ type: 'documentos', documents: tableDocs });
    } else if (docLink) {
      flushHtml();
      flushImgs();
      docBuffer.push(docLink);
    } else if (isImageOnlyBlock(node)) {
      flushHtml();
      flushDocs();
      imgBuffer.push(extractImg(node));
    } else {
      flushImgs();
      flushDocs();
      htmlBuffer += node.nodeType === 1 ? node.outerHTML : (node.textContent ?? '');
    }
  }
  flushImgs();
  flushDocs();
  flushHtml();

  return result;
});
</script>

<style scoped>
.content-body :deep(h1),
.content-body :deep(h2),
.content-body :deep(h3),
.content-body :deep(h4) {
  font-weight: 700;
  color: #00335f;
  margin-top: 1.25rem;
  margin-bottom: 0.5rem;
}
.content-body :deep(p) {
  margin-bottom: 0.85rem;
}
.content-body :deep(ul),
.content-body :deep(ol) {
  margin: 0.5rem 0 1rem 1.25rem;
  list-style: disc;
}
.content-body :deep(ol) {
  list-style: decimal;
}
.content-body :deep(li) {
  margin-bottom: 0.35rem;
}
.content-body :deep(a) {
  color: #004c98;
  font-weight: 600;
  text-decoration: underline;
}
/* Las tarjetas de documentos (DocumentLinks) traen su propio color/tipografía por
   ítem -- se anula el subrayado/color genérico de arriba, que si no se filtra a
   través de los <a> que ellas mismas renderizan dentro de .content-body. */
.content-body :deep(a.doc-card),
.content-body :deep(a.doc-card *) {
  text-decoration: none !important;
}
.content-body :deep(strong) {
  color: #111827;
}
.content-body :deep(img) {
  display: block;
  max-width: 100%;
  height: auto;
  border-radius: 0.75rem;
  margin: 1rem auto;
}
.content-body :deep(table) {
  width: 100%;
  border-collapse: collapse;
  margin: 1rem 0;
  font-size: 0.8rem;
}
.content-body :deep(td),
.content-body :deep(th) {
  border: 1px solid #e5e7eb;
  padding: 0.5rem;
  text-align: left;
}
</style>
