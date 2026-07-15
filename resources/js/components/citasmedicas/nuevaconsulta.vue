<template>
    <div class="modern-wrapper">
        <div class="page-header">
            <div>
                <h1>Nueva cita médica</h1>
                <p>Registra y organiza una nueva cita para el paciente.</p>
            </div>
            <div class="header-badge">
                <i class="fas fa-stethoscope"></i>
                Agenda médica 
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'PageHeader'
}
</script>

<style scoped>
.modern-wrapper {
    padding: 1rem 0;
}

.page-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    padding: 1.5rem 1.75rem;
    background: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
}

.page-header h1 {
    margin: 0;
    font-size: 22px;
    font-weight: 600;
    color: #111827;
}

.page-header p {
    margin: 6px 0 0;
    font-size: 14px;
    color: #6b7280;
}

.header-badge {
    display: flex;
    align-items: center;
    gap: 8px;
    background: #E6F1FB;
    color: #185FA5;
    font-size: 13px;
    font-weight: 500;
    padding: 8px 16px;
    border-radius: 999px;
    white-space: nowrap;
    flex-shrink: 0;
}

.header-badge i {
    font-size: 15px;
}
</style>