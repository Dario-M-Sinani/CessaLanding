<template>
  <div class="pointer-events-none fixed inset-0 overflow-hidden z-0" aria-hidden="true">
    <span
      v-for="leaf in leaves"
      :key="leaf.id"
      class="leaf"
      :style="{
        left: leaf.left + '%',
        width: leaf.size + 'px',
        height: leaf.size + 'px',
        color: leaf.color,
        opacity: leaf.opacity,
        animationDuration: leaf.duration + 's',
        animationDelay: leaf.delay + 's',
        '--drift': leaf.drift + 'px',
      }"
    >
      <svg viewBox="0 0 24 24" fill="currentColor" class="w-full h-full">
        <path d="M17 8C8 10 5.9 16.17 3.82 21.34l1.89.66.95-2.3c.48.17.98.3 1.34.3C19 20 22 3 22 3c-1 2-8 2.25-13 3.25S2 11.5 2 13.5s1.75 3.75 1.75 3.75C7 8 17 8 17 8z" />
      </svg>
    </span>
  </div>
</template>

<script setup>
import { ref } from 'vue';

// Fondo decorativo minimalista -- pocas hojas (no "lluvia" densa), tonos verdes,
// generadas una sola vez al montar para que no salten de posición en cada
// re-render de Vue.
const COLORS = ['#16a34a', '#22c55e', '#15803d', '#4ade80'];
const LEAF_COUNT = 18;

const leaves = ref(
  Array.from({ length: LEAF_COUNT }, (_, id) => ({
    id,
    left: Math.random() * 100,
    size: 14 + Math.random() * 12,
    color: COLORS[id % COLORS.length],
    opacity: 0.18 + Math.random() * 0.18,
    duration: 16 + Math.random() * 10,
    delay: Math.random() * -20,
    drift: (Math.random() - 0.5) * 120,
  }))
);
</script>

<style scoped>
.leaf {
  position: absolute;
  top: -10%;
  animation-name: leaf-fall;
  animation-timing-function: linear;
  animation-iteration-count: infinite;
}

@keyframes leaf-fall {
  0% {
    transform: translateY(-10vh) translateX(0) rotate(0deg);
  }
  100% {
    transform: translateY(110vh) translateX(var(--drift)) rotate(360deg);
  }
}

@media (prefers-reduced-motion: reduce) {
  .leaf {
    animation: none;
    display: none;
  }
}
</style>
