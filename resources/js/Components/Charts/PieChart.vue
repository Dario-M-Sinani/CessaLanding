<template>
  <div ref="root" class="w-full">
    <div class="flex flex-col sm:flex-row items-center gap-6">
      <div class="relative shrink-0">
        <svg :viewBox="`0 0 ${size} ${size}`" :width="size" :height="size" role="img" :aria-label="ariaLabel">
          <path
            v-for="slice in slicePaths"
            :key="slice.name"
            :d="slice.path"
            :fill="slice.color"
            stroke="#fcfcfb"
            stroke-width="2"
            class="cursor-pointer transition-opacity hover:opacity-85 focus:opacity-85 outline-none"
            tabindex="0"
            @pointermove="showTooltip($event, slice)"
            @pointerleave="hideTooltip"
            @focus="showTooltip($event, slice)"
            @blur="hideTooltip"
          />
          <circle :cx="size / 2" :cy="size / 2" :r="size * 0.28" fill="#fcfcfb" />
          <text :x="size / 2" :y="size / 2 - 6" text-anchor="middle" font-size="12" fill="#898781">Total</text>
          <text :x="size / 2" :y="size / 2 + 14" text-anchor="middle" font-size="18" font-weight="800" fill="#0b0b0b">{{ formatValue(total) }}</text>
        </svg>

        <div
          v-if="tooltip.visible"
          class="absolute pointer-events-none bg-blue-950 text-white text-xs rounded-lg px-3 py-2 shadow-lg z-10 -translate-x-1/2 -translate-y-full"
          :style="{ left: tooltip.x + 'px', top: tooltip.y + 'px' }"
        >
          <div class="flex items-center gap-1.5">
            <span class="inline-block w-2 h-2 rounded-full shrink-0" :style="{ backgroundColor: tooltip.slice?.color }"></span>
            <span>{{ tooltip.slice?.name }}: <strong>{{ formatValue(tooltip.slice?.value) }}</strong> ({{ tooltip.slice?.pct.toFixed(2) }}%)</span>
          </div>
        </div>
      </div>

      <div class="space-y-2 text-sm">
        <div v-for="slice in slicePaths" :key="'legend-' + slice.name" class="flex items-center gap-2">
          <span class="inline-block w-2.5 h-2.5 rounded-sm shrink-0" :style="{ backgroundColor: slice.color }"></span>
          <span class="text-gray-700">{{ slice.name }}</span>
          <span class="font-bold text-blue-950">{{ slice.pct.toFixed(2) }}%</span>
          <span class="text-gray-400">({{ formatValue(slice.value) }})</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue';

const props = defineProps({
  slices: { type: Array, required: true }, // [{ name, value, color }]
  ariaLabel: { type: String, default: '' },
  size: { type: Number, default: 200 },
  formatValue: { type: Function, default: (v) => new Intl.NumberFormat('es-BO').format(v) },
});

const root = ref(null);
const total = computed(() => props.slices.reduce((sum, s) => sum + s.value, 0));

const slicePaths = computed(() => {
  const cx = props.size / 2;
  const cy = props.size / 2;
  const r = props.size * 0.48;
  let angle = -Math.PI / 2;

  return props.slices.map((s) => {
    const pct = total.value ? (s.value / total.value) * 100 : 0;
    const sweep = total.value ? (s.value / total.value) * Math.PI * 2 : 0;
    const start = angle;
    const end = angle + sweep;
    angle = end;

    const x1 = cx + r * Math.cos(start);
    const y1 = cy + r * Math.sin(start);
    const x2 = cx + r * Math.cos(end);
    const y2 = cy + r * Math.sin(end);
    const largeArc = sweep > Math.PI ? 1 : 0;

    return {
      name: s.name,
      value: s.value,
      color: s.color,
      pct,
      path: `M ${cx} ${cy} L ${x1} ${y1} A ${r} ${r} 0 ${largeArc} 1 ${x2} ${y2} Z`,
    };
  });
});

const tooltip = ref({ visible: false, slice: null, x: 0, y: 0 });

const showTooltip = (event, slice) => {
  const containerRect = root.value.getBoundingClientRect();
  const targetRect = event.target.getBoundingClientRect();
  tooltip.value = {
    visible: true,
    slice,
    x: targetRect.left - containerRect.left + targetRect.width / 2,
    y: targetRect.top - containerRect.top,
  };
};

const hideTooltip = () => {
  tooltip.value.visible = false;
};
</script>
