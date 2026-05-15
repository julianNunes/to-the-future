<template>

    <Head title="Dashboard" />
    <div class="mb-5">
        <h5 class="text-h5 font-weight-bold">Dashboard</h5>
        <Breadcrumbs :items="breadcrumbs" class="pa-0 mt-1" />
    </div>
    <!-- <v-card>
        <v-card-text>
            <div class="text-h6 text-medium-emphasis">Welcome back, {{ $page.props.auth.user.name }}!</div>
        </v-card-text>
    </v-card> -->

    <!-- Componente do Resumo para o Dashboard -->
    <!-- <GroupResumes :itens="[]" /> -->
</template>

<script setup>
import { computed } from 'vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Breadcrumbs from '@/Components/Breadcrumbs.vue'
import { Head } from '@inertiajs/vue3'
// import GroupResumes from '../Components/Dashboard/GroupResumes.vue'

defineOptions({ name: 'DashboardPage', layout: AuthenticatedLayout })

const breadcrumbs = computed(() => [{ title: 'Dashboard', disabled: true }])
</script>
