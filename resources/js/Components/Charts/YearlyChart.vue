<template>
  <div ref="root" class="w-full">
    <div class="flex flex-wrap items-center gap-4 mb-3 text-xs">
      <div class="flex items-center gap-1.5">
        <span class="inline-block w-2.5 h-2.5 rounded-sm" :style="{ backgroundColor: barSeries.color }"></span>
        <span class="text-gray-600 font-medium">{{ barSeries.name }}</span>
      </div>
      <div v-for="s in lineSeries" :key="s.name" class="flex items-center gap-1.5">
        <span class="inline-block w-3 h-0.5 rounded-full" :style="{ backgroundColor: s.color }"></span>
        <span class="text-gray-600 font-medium">{{ s.name }}</span>
      </div>
    </div>

    <div class="relative">
      <svg :viewBox="`0 0 ${width} ${height}`" class="w-full h-auto select-none" role="img" :aria-label="ariaLabel">
        <line
          v-for="(gy, i) in gridLines"
          :key="'grid-' + i"
          :x1="padding.left"
          :x2="width - padding.right"
          :y1="gy.y"
          :y2="gy.y"
          stroke="#e1e0d9"
          stroke-width="1"
        />
        <text
          v-for="(gy, i) in gridLines"
          :key="'ytick-left-' + i"
          :x="padding.left - 8"
          :y="gy.y + 3"
          text-anchor="end"
          font-size="10"
          fill="#898781"
        >{{ formatValue(gy.leftValue) }}</text>
        <text
          v-for="(gy, i) in gridLines"
          :key="'ytick-right-' + i"
          :x="width - padding.right + 8"
          :y="gy.y + 3"
          text-anchor="start"
          font-size="10"
          fill="#898781"
        >{{ formatValue(gy.rightValue) }}</text>

        <text :x="padding.left" :y="14" font-size="10" fill="#898781">{{ barSeries.name }}</text>
        <text :x="width - padding.right" :y="14" text-anchor="end" font-size="10" fill="#898781">Personal</text>

        <rect
          v-for="bar in bars"
          :key="bar.key"
          :x="bar.x"
          :y="bar.y"
          :width="barWidth"
          :height="bar.height"
          :fill="barSeries.color"
          rx="4"
          class="cursor-pointer transition-opacity hover:opacity-80 focus:opacity-80 outline-none"
          tabindex="0"
          @pointermove="showTooltip($event, bar.yearIndex)"
          @pointerleave="hideTooltip"
          @focus="showTooltip($event, bar.yearIndex)"
          @blur="hideTooltip"
        />

        <polyline
          v-for="s in lineSeries"
          :key="'line-' + s.name"
          :points="linePoints(s)"
          fill="none"
          :stroke="s.color"
          stroke-width="2"
          stroke-linecap="round"
          stroke-linejoin="round"
        />
        <circle
          v-for="dot in dots"
          :key="dot.key"
          :cx="dot.cx"
          :cy="dot.cy"
          r="4"
          :fill="dot.color"
          stroke="#fcfcfb"
          stroke-width="2"
          class="cursor-pointer outline-none"
          tabindex="0"
          @pointermove="showTooltip($event, dot.yearIndex)"
          @pointerleave="hideTooltip"
          @focus="showTooltip($event, dot.yearIndex)"
          @blur="hideTooltip"
        />
        <text
          v-for="s in lineSeries"
          :key="'endlabel-' + s.name"
          :x="xScale(years.length - 1) + 8"
          :y="yScaleRight(s.values[years.length - 1]) + 4"
          font-size="11"
          font-weight="700"
          fill="#0b0b0b"
        >{{ formatValue(s.values[years.length - 1]) }}</text>

        <line :x1="padding.left" :x2="width - padding.right" :y1="baselineY" :y2="baselineY" stroke="#c3c2b7" stroke-width="1" />

        <text
          v-for="(year, yi) in years"
          :key="'xlabel-' + yi"
          :x="xScale(yi)"
          :y="height - 6"
          text-anchor="middle"
          font-size="10"
          fill="#52514e"
        >{{ year }}</text>
      </svg>

      <div
        v-if="tooltip.visible"
        class="absolute pointer-events-none bg-blue-950 text-white text-xs rounded-lg px-3 py-2 shadow-lg z-10 -translate-x-1/2 -translate-y-full"
        :style="{ left: tooltip.x + 'px', top: tooltip.y + 'px' }"
      >
        <div class="font-bold mb-1">{{ years[tooltip.index] }}</div>
        <div class="flex items-center gap-1.5">
          <span class="inline-block w-2 h-2 rounded-sm shrink-0" :style="{ backgroundColor: barSeries.color }"></span>
          <span>{{ barSeries.name }}: <strong>{{ formatValue(barSeries.values[tooltip.index]) }}</strong></span>
        </div>
        <div v-for="s in lineSeries" :key="s.name" class="flex items-center gap-1.5">
          <span class="inline-block w-2 h-2 rounded-full shrink-0" :style="{ backgroundColor: s.color }"></span>
          <span>{{ s.name }}: <strong>{{ formatValue(s.values[tooltip.index]) }}</strong></span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue';

const props = defineProps({
  years: { type: Array, required: true },
  barSeries: { type: Object, required: true }, // { name, color, values: number[] } -- own (left) axis
  lineSeries: { type: Array, required: true }, // [{ name, color, values: number[] }] -- share the right axis
  ariaLabel: { type: String, default: '' },
  formatValue: { type: Function, default: (v) => new Intl.NumberFormat('es-BO').format(v) },
});

const root = ref(null);
const width = 640;
const height = 300;
const padding = { top: 28, right: 40, bottom: 28, left: 48 };

const niceMaxOf = (raw) => {
  const padded = Math.max(1, raw) * 1.15;
  const magnitude = Math.pow(10, Math.floor(Math.log10(padded)));
  return Math.ceil(padded / magnitude) * magnitude;
};

const leftNiceMax = computed(() => niceMaxOf(Math.max(1, ...props.barSeries.values)));
const rightNiceMax = computed(() => niceMaxOf(Math.max(1, ...props.lineSeries.flatMap((s) => s.values))));

const baselineY = computed(() => height - padding.bottom);
const plotHeight = computed(() => baselineY.value - padding.top);

const yScaleLeft = (value) => baselineY.value - (value / leftNiceMax.value) * plotHeight.value;
const yScaleRight = (value) => baselineY.value - (value / rightNiceMax.value) * plotHeight.value;

const gridLines = computed(() => {
  const steps = 4;
  return Array.from({ length: steps + 1 }, (_, i) => {
    const fraction = i / steps;
    return {
      y: baselineY.value - fraction * plotHeight.value,
      leftValue: fraction * leftNiceMax.value,
      rightValue: fraction * rightNiceMax.value,
    };
  });
});

const plotWidth = computed(() => width - padding.left - padding.right);
const xScale = (index) => {
  if (props.years.length <= 1) return padding.left + plotWidth.value / 2;
  return padding.left + (plotWidth.value / (props.years.length - 1)) * index;
};

const barWidth = computed(() => Math.min(28, (plotWidth.value / props.years.length) * 0.5));

const bars = computed(() =>
  props.years.map((year, yi) => {
    const value = props.barSeries.values[yi] ?? 0;
    const y = yScaleLeft(value);
    return {
      key: `bar-${yi}`,
      x: xScale(yi) - barWidth.value / 2,
      y,
      height: Math.max(0, baselineY.value - y),
      yearIndex: yi,
    };
  })
);

const linePoints = (s) => s.values.map((v, i) => `${xScale(i)},${yScaleRight(v)}`).join(' ');

const dots = computed(() => {
  const items = [];
  props.lineSeries.forEach((s) => {
    s.values.forEach((v, yi) => {
      items.push({ key: `${s.name}-${yi}`, cx: xScale(yi), cy: yScaleRight(v), color: s.color, yearIndex: yi });
    });
  });
  return items;
});

const tooltip = ref({ visible: false, index: 0, x: 0, y: 0 });

const showTooltip = (event, index) => {
  const containerRect = root.value.getBoundingClientRect();
  const targetRect = event.target.getBoundingClientRect();
  tooltip.value = {
    visible: true,
    index,
    x: targetRect.left - containerRect.left + targetRect.width / 2,
    y: targetRect.top - containerRect.top - 8,
  };
};

const hideTooltip = () => {
  tooltip.value.visible = false;
};
</script>
