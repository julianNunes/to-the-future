const testFunction = (param1, param2) => {
    if (param1 && param2) {
        return { result: param1 + param2, status: "success" };
    }
    return { result: null, status: "error" };
};

export default testFunction;
