<template>
    <div class="container">
        <h1>{{ title }}</h1>
        <v-btn @click="handleClick" color="primary"> Click Me </v-btn>
        <p>This is a test component with bad formatting</p>
    </div>
</template>

<script>
export default {
    name: "TestComponent",
    data() {
        return {
            title: "Hello World",
        };
    },
    methods: {
        handleClick() {
            console.log("Button clicked");
            this.$emit("click", { message: "clicked" });
        },
    },
};
</script>

<style scoped>
.container {
    padding: 20px;
    margin: 10px;
    background-color: #f5f5f5;
}
</style>
