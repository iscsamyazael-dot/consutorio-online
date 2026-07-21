
<template>

<div class="header-recetas d-flex justify-content-between align-items-center mb-4">

    <div>

        <h1 class="header-title fw-bold text-primary">

            <i class="fas fa-prescription-bottle-alt me-2"></i>

            Recetas Médicas Inteligentes

        </h1>

        <p class="header-subtitle text-muted mb-0">

            Gestión de prescripciones médicas y validación clínica

        </p>

    </div>

    

</div>

</template>

<script>

export default {

    name: 'HeaderRecetas'

}

</script>

<style scoped>

.header-recetas{

    background: white;

    padding: 25px;

    border-radius: 20px;

    box-shadow: 0 2px 10px rgba(0,0,0,.05);

}

.header-title{

    font-size: 32px;

}

.header-subtitle{

    font-size: 15px;

}

.btn-receta{

    padding-left: 20px;
    padding-right: 20px;

}

</style>

