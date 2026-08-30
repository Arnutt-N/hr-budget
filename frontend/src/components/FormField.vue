<script setup lang="ts">
withDefaults(
  defineProps<{
    /**
     * Base field id — the label points at it (`for`, or `${id}-label` for the
     * aria-labelledby idiom) and the error element becomes `${id}-error`.
     * Bind `:aria-describedby="errors.x ? `${id}-error` : undefined"` on the
     * control inside the slot.
     */
    id: string
    /** PrimeVue Select idiom: label carries `${id}-label` for `aria-labelledby`. */
    labelledBy?: boolean
    label: string
    error?: string
  }>(),
  { labelledBy: false, error: undefined },
)
</script>

<template>
  <div class="flex flex-col gap-1">
    <label
      :id="labelledBy ? `${id}-label` : undefined"
      :for="labelledBy ? undefined : id"
      class="text-sm font-medium text-dark-muted"
    >{{ label }}</label>
    <slot />
    <small v-if="error" :id="`${id}-error`" class="text-red-400" role="alert">{{ error }}</small>
  </div>
</template>
