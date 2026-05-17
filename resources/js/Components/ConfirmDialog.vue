<template>
    <v-dialog
        v-model="dialog"
        :max-width="options.width"
        :style="{ zIndex: options.zIndex }"
        role="alertdialog"
        aria-modal="true"
        :aria-labelledby="titleId"
        :aria-describedby="message ? messageId : undefined"
        @keydown.esc="cancel"
    >
        <v-card>
            <v-toolbar :color="options.color" dense flat>
                <v-toolbar-title :id="titleId" class="text-body-3 grey--text">
                    {{ title }}
                </v-toolbar-title>
            </v-toolbar>
            <v-card-text v-if="message" :id="messageId" class="pa-4 black--text">{{ message }}</v-card-text>
            <v-card-actions class="pt-3">
                <v-spacer></v-spacer>
                <v-btn
                    v-if="!options.noconfirm"
                    ref="cancelButton"
                    color="error"
                    elevated
                    text
                    class="body-2 font-weight-bold"
                    :aria-label="$t('default.cancel')"
                    @click="cancel"
                >
                    {{ $t('default.cancel') }}
                </v-btn>
                <v-btn ref="confirmButton" color="primary" elevated text class="body-2 font-weight-bold" @click="agree">
                    OK
                </v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>

<script setup>
    import { nextTick, ref } from 'vue'

    defineOptions({ name: 'ConfirmDialog' })

    const DEFAULT_OPTIONS = {
        color: 'primary',
        width: 400,
        zIndex: 200,
        noconfirm: false,
    }

    let dialogCount = 0

    const dialog = ref(false)
    const resolve = ref(null)
    const message = ref(null)
    const title = ref(null)
    const options = ref({ ...DEFAULT_OPTIONS })
    const cancelButton = ref(null)
    const confirmButton = ref(null)
    const dialogId = `confirm-dialog-${++dialogCount}`
    const titleId = `${dialogId}-title`
    const messageId = `${dialogId}-message`

    async function open(t, m, opts) {
        dialog.value = true
        title.value = t
        message.value = m
        options.value = { ...DEFAULT_OPTIONS, ...(opts ?? {}) }

        await nextTick()
        focusPrimaryAction()

        return new Promise((res) => {
            resolve.value = res
        })
    }

    function focusPrimaryAction() {
        const target = cancelButton.value?.$el ?? cancelButton.value ?? confirmButton.value?.$el ?? confirmButton.value

        target?.focus?.()
    }

    function agree() {
        closeDialog(true)
    }

    function cancel() {
        closeDialog(false)
    }

    function closeDialog(result) {
        if (resolve.value) resolve.value(result)

        resolve.value = null
        dialog.value = false
    }

    defineExpose({
        open,
    })
</script>
