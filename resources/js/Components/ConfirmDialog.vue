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

<script>
export default {
    name: 'ConfirmDialog',

    data: function () {
        return {
            dialog: false,
            resolve: null,
            reject: null,
            message: null,
            title: null,
            options: {
                color: 'primary',
                width: 400,
                zIndex: 200,
                noconfirm: false,
            },
        }
    },
    methods: {
        open(title, message, options) {
            this.dialog = true
            this.title = title
            this.message = message
            this.options = Object.assign(this.options, options)
            return new Promise((resolve, reject) => {
                this.resolve = resolve
                this.reject = reject
            })
        },
        agree() {
            this.resolve(true)
            this.dialog = false
        },
        cancel() {
            this.resolve(false)
            this.dialog = false
        },
    },
}
</script>
