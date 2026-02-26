<template>
    <v-dialog v-model="dialog" :max-width="options.width" :style="{ zIndex: options.zIndex }" @keydown.esc="cancel">
        <v-card>
            <v-toolbar :color="options.color" dense flat>
                <v-toolbar-title class="text-body-3 grey--text">
                    {{ title }}
                </v-toolbar-title>
            </v-toolbar>
            <v-card-text v-show="!!message" class="pa-4 black--text">{{ message }}</v-card-text>
            <v-card-actions class="pt-3">
                <v-spacer></v-spacer>
                <v-btn
                    v-if="!options.noconfirm"
                    color="error"
                    elevated
                    text
                    class="body-2 font-weight-bold"
                    @click="cancel"
                >
                    {{ $t('default.cancel') }}
                </v-btn>
                <v-btn color="primary" elevated text class="body-2 font-weight-bold" @click="agree">OK</v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>

<script setup>
    import { ref } from 'vue'

    defineOptions({ name: 'ConfirmDialog' })

    const dialog = ref(false)
    const resolve = ref(null)
    const reject = ref(null)
    const message = ref(null)
    const title = ref(null)
    const options = ref({
        color: 'primary',
        width: 400,
        zIndex: 200,
        noconfirm: false,
    })

    function open(t, m, opts) {
        dialog.value = true
        title.value = t
        message.value = m
        options.value = Object.assign(options.value, opts)
        return new Promise((res, rej) => {
            resolve.value = res
            reject.value = rej
        })
    }

    function agree() {
        if (resolve.value) resolve.value(true)
        dialog.value = false
    }

    function cancel() {
        if (resolve.value) resolve.value(false)
        dialog.value = false
    }

    defineExpose({
        open,
    })
</script>
