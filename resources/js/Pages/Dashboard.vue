<template>
    <Head title="Dashboard" />
    <AuthenticatedLayout>
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
    </AuthenticatedLayout>
</template>

<script setup>
    import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
    import Breadcrumbs from '@/Components/Breadcrumbs.vue'
    import { Head } from '@inertiajs/vue3'
    // import GroupResumes from '../Components/Dashboard/GroupResumes.vue'
</script>

<script>
    export default {
        name: 'DashboardPage',
        data() {
            return {
                breadcrumbs: [
                    {
                        title: 'Dashboard',
                        disabled: true,
                    },
                ],
            }
        },
    }
</script>
