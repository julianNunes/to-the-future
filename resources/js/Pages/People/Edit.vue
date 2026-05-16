<template>
    <Head :title="$t('people.title-edit')" />

    <div class="mb-5">
        <h5 class="text-h5 font-weight-bold">{{ $t('people.title-edit') }}</h5>
    </div>

    <v-card>
        <v-card-text>
            <v-form ref="formRef" @submit.prevent>
                <v-row dense>
                    <v-col cols="12" md="6">
                        <v-text-field
                            v-model="form.name"
                            :label="$t('default.name')"
                            :rules="requiredRules"
                            :error-messages="form.errors.name"
                            density="comfortable"
                            required
                        />
                    </v-col>
                    <v-col cols="12" md="6">
                        <v-select
                            v-model="form.gender"
                            :items="genderOptions"
                            item-title="title"
                            item-value="value"
                            :label="$t('default.gender')"
                            :rules="requiredRules"
                            :error-messages="form.errors.gender"
                            density="comfortable"
                            required
                        />
                    </v-col>
                    <v-col cols="12" md="6">
                        <v-text-field
                            v-model="form.email"
                            :label="$t('default.email')"
                            :error-messages="form.errors.email"
                            density="comfortable"
                        />
                    </v-col>
                    <v-col cols="12" md="6">
                        <v-text-field
                            v-model="form.phone"
                            :label="$t('default.phone')"
                            :error-messages="form.errors.phone"
                            density="comfortable"
                        />
                    </v-col>
                    <v-col cols="12">
                        <v-text-field
                            v-model="form.address"
                            :label="$t('default.address')"
                            :error-messages="form.errors.address"
                            density="comfortable"
                        />
                    </v-col>
                </v-row>
            </v-form>
        </v-card-text>
        <v-card-actions>
            <v-spacer />
            <Link href="/people" as="div">
                <v-btn color="error" flat>{{ $t('default.cancel') }}</v-btn>
            </Link>
            <v-btn color="primary" flat :loading="form.processing" @click="submit">
                {{ $t('default.save') }}
            </v-btn>
        </v-card-actions>
    </v-card>
</template>

<script setup>
    import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import { computed, ref } from 'vue'
import { useI18n } from 'vue-i18n'

    defineOptions({ name: 'PeopleEdit', layout: AuthenticatedLayout })

    const componentProps = defineProps({
        person: {
            type: Object,
            required: true,
        },
    })

    const { t } = useI18n()

    const formRef = ref(null)
    const form = useForm({
        name: componentProps.person.name,
        gender: componentProps.person.gender,
        email: componentProps.person.email,
        phone: componentProps.person.phone,
        address: componentProps.person.address,
    })

    const requiredRules = computed(() => [(value) => !!value || t('rules.required-text-field')])

    const genderOptions = computed(() => [
        { title: t('people.female'), value: 'F' },
        { title: t('people.male'), value: 'M' },
    ])

    async function submit() {
        const validate = await formRef.value.validate()

        if (!validate.valid) {
            return
        }

        form.put(`/people/${componentProps.person.id}`)
    }
</script>
