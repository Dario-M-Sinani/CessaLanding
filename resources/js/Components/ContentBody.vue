<template>
  <div class="content-body text-gray-700 text-sm leading-relaxed">
    <template v-for="(block, i) in blocks" :key="i">
      <div v-if="block.type === 'html'" v-html="block.content"></div>
      <ImageCarousel v-else :images="block.images" />
    </template>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import ImageCarousel from './ImageCarousel.vue';

const props = defineProps({
  html: String,
});

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

  for (const node of nodes) {
    // Los saltos de línea entre bloques quedan como nodos de texto en blanco -- si no
    // los saltamos, cortan la racha de imágenes seguidas antes de llegar a 2.
    if (node.nodeType === 3 && !node.textContent.trim()) continue;

    if (isImageOnlyBlock(node)) {
      flushHtml();
      imgBuffer.push(extractImg(node));
    } else {
      flushImgs();
      htmlBuffer += node.nodeType === 1 ? node.outerHTML : (node.textContent ?? '');
    }
  }
  flushImgs();
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
