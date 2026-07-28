<template>
  <div class="content-body text-gray-700 text-sm leading-relaxed" v-html="html"></div>
</template>

<script setup>
defineProps({
  html: String,
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
