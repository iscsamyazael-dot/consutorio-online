<template>
    <div class="search-box">
        <i class="fas fa-search"></i>
        <input
            type="text"
            class="form-control"
            placeholder="Buscar paciente..."
            :value="modelValue"
            @input="$emit('update:modelValue', $event.target.value)"
        >
    </div>
</template>

<script setup>
defineProps({ modelValue: { type: String, default: '' } })
defineEmits(['update:modelValue'])
</script>