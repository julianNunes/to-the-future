import { router } from '@inertiajs/vue3'
import { ref } from 'vue'

export function useCrudOperations(baseUrl) {
    const isLoading = ref(false)
    const editDialog = ref(false)
    const deleteId = ref(null)
    const titleModal = ref('')

    function create(data, options = {}) {
        router.post(baseUrl, data, {
            preserveScroll: true,
            onStart: () => {
                isLoading.value = true
            },
            onSuccess: () => {
                editDialog.value = false
                options.onSuccess?.()
            },
            onFinish: () => {
                isLoading.value = false
            },
        })
    }

    function update(id, data, options = {}) {
        router.put(`${baseUrl}/${id}`, data, {
            preserveScroll: true,
            onStart: () => {
                isLoading.value = true
            },
            onSuccess: () => {
                editDialog.value = false
                options.onSuccess?.()
            },
            onFinish: () => {
                isLoading.value = false
            },
        })
    }

    function remove() {
        router.delete(`${baseUrl}/${deleteId.value}`, {
            preserveScroll: true,
            onStart: () => {
                isLoading.value = true
            },
            onFinish: () => {
                isLoading.value = false
            },
        })
    }

    async function confirmRemove(item, confirmRef, title, message) {
        deleteId.value = item.id
        if (await confirmRef.open(title, message)) {
            remove()
        }
    }

    async function save(formRef, entity, options = {}) {
        const validate = await formRef.validate()
        if (validate.valid) {
            if (entity.id) {
                update(entity.id, entity, options)
            } else {
                create(entity, options)
            }
        }
    }

    return {
        isLoading,
        editDialog,
        deleteId,
        titleModal,
        create,
        update,
        remove,
        confirmRemove,
        save,
    }
}
