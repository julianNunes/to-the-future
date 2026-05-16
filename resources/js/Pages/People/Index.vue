<template>
    <Head :title="$t('people.title-index')" />

    <div class="mb-5">
        <h5 class="text-h5 font-weight-bold">{{ $t('people.title-index') }}</h5>
    </div>

    <v-card>
        <v-card-text>
            <v-row dense>
                <v-col cols="12">
                    <Link href="/people/create" as="div">
                        <v-btn color="primary">{{ $t('default.new') }}</v-btn>
                    </Link>
                </v-col>
                <v-col cols="12">
                    <v-text-field
                        v-model="search"
                        :label="$t('default.search')"
                        append-icon="mdi-magnify"
                        clearable
                        hide-details
                        @keyup.enter="applyFilters"
                        @click:clear="clearFilters"
                    />
                </v-col>
                <v-col cols="12">
                    <v-data-table
                        :headers="headers"
                        :items="componentProps.data.data"
                        :loading="isLoading"
                        :loading-text="$t('default.loading-text-table')"
                        :items-per-page="componentProps.data.per_page"
                        class="elevation-3"
                        density="compact"
                        :no-data-text="$t('default.no-data-text')"
                        :no-results-text="$t('default.no-data-text')"
                    >
                        <template #[`item.gender`]="{ item }">
                            {{ formatGender(item.gender) }}
                        </template>

                        <template #[`item.action`]="{ item }">
                            <div class="d-flex ga-2 justify-center">
                                <Link :href="`/people/${item.id}/edit`" as="div">
                                    <v-btn color="warning" icon="mdi-pencil" size="small" variant="text" />
                                </Link>
                                <v-btn
                                    color="error"
                                    icon="mdi-delete"
                                    size="small"
                                    variant="text"
                                    @click="confirmRemove(item)"
                                />
                            </div>
                        </template>
                    </v-data-table>
                </v-col>
            </v-row>
        </v-card-text>
    </v-card>

    <ConfirmDialog ref="confirm" />
</template>

<script setup>
    import ConfirmDialog from '@/Components/ConfirmDialog.vue'
import { useCrudOperations } from '@/composables/useCrudOperations.js'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { computed, ref } from 'vue'
import { useI18n } from 'vue-i18n'

    defineOptions({ name: 'PeopleIndex', layout: AuthenticatedLayout })

    const componentProps = defineProps({
        data: {
            type: Object,
            required: true,
        },
    })

    const { t } = useI18n()
    const { isLoading, confirmRemove: crudConfirmRemove } = useCrudOperations('/people')

    const search = ref('')
    const confirm = ref(null)

    const headers = computed(() => [
        { title: t('default.name'), key: 'name', sortable: false },
        { title: t('default.email'), key: 'email', sortable: false },
        { title: t('default.phone'), key: 'phone', sortable: false },
        { title: t('default.gender'), key: 'gender', sortable: false },
        { title: t('default.action'), key: 'action', align: 'center', sortable: false, width: 120 },
    ])

    function applyFilters() {
        router.get('/people', search.value ? { search: search.value } : {}, {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        })
    }

    function clearFilters() {
        search.value = ''
        applyFilters()
    }

    function formatGender(gender) {
        if (gender === 'F') {
            return t('people.female')
        }

        if (gender === 'M') {
            return t('people.male')
        }

        return gender
    }

    function confirmRemove(item) {
        crudConfirmRemove(item, confirm.value, item.name, t('default.confirm-delete-item'))
    }
</script>
