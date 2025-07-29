#!/bin/bash

echo "🐳 TESTE DE FORMATADORES NO DEV CONTAINER"
echo "=========================================="
echo ""

# Verificar se estamos no container
echo "=== 1. VERIFICANDO AMBIENTE ==="
echo "📍 Diretório atual: $(pwd)"
echo "🐳 Container ID: $(hostname)"
echo "🔧 Node.js: $(node --version 2>/dev/null || echo 'Não encontrado')"
echo "🔧 npm: $(npm --version 2>/dev/null || echo 'Não encontrado')"
echo "🔧 PHP: $(php --version | head -1 2>/dev/null || echo 'Não encontrado')"
echo ""

# Verificar dependências
echo "=== 2. VERIFICANDO DEPENDÊNCIAS ==="
echo "📦 Prettier: $(npm list prettier --depth=0 2>/dev/null | grep prettier || echo 'Não encontrado')"
echo "📦 Laravel Pint: $([ -f ./vendor/bin/pint ] && echo 'Instalado' || echo 'Não encontrado')"
echo ""

# Criar arquivos de teste
echo "=== 3. CRIANDO ARQUIVOS DE TESTE ==="

# Arquivo Vue desformatado
cat > test-vue-format.vue << 'EOF'
<template>
<div class="container">
<h1 >{{ title }}</h1>
<v-btn @click="handleClick"   color="primary"  >
Click Me
</v-btn>
<p>This is a test component with bad formatting</p>
</div>
</template>

<script>
export default{
name:'TestComponent',
data(){
return{
title:'Hello World'
}
},
methods:{
handleClick(){
console.log('Button clicked')
this.$emit('click',{message:'clicked'})
}
}
}
</script>

<style scoped>
.container{
padding:20px;
margin:10px;
background-color:#f5f5f5;
}
</style>
EOF

# Arquivo PHP desformatado
cat > test-php-format.php << 'EOF'
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class TestController extends Controller
{
public function index(Request $request)
{
$data=['title'=>'Hello World','items'=>[1,2,3,4,5]];
return Inertia::render('TestPage',$data);
}

public function store(Request $request){
$validated=$request->validate(['name'=>'required|string','email'=>'required|email']);
return redirect()->back()->with('success','Created successfully');
}
}
EOF

# Arquivo JS desformatado
cat > test-js-format.js << 'EOF'
const testFunction=(param1,param2)=>{
if(param1&&param2){
return{result:param1+param2,status:'success'};
}
return{result:null,status:'error'};
};

export default testFunction;
EOF

echo "✅ Arquivos de teste criados:"
echo "   - test-vue-format.vue"
echo "   - test-php-format.php"
echo "   - test-js-format.js"
echo ""

# Testar formatadores
echo "=== 4. TESTANDO FORMATADORES ==="

# Testar Prettier
echo "🎨 Testando Prettier..."
if command -v npx >/dev/null 2>&1; then
    echo "📝 Formatando Vue:"
    npx prettier --write test-vue-format.vue
    echo "📝 Formatando JS:"
    npx prettier --write test-js-format.js
    echo "✅ Prettier executado com sucesso!"
else
    echo "❌ npx não encontrado"
fi
echo ""

# Testar Laravel Pint
echo "🎨 Testando Laravel Pint..."
if [ -f ./vendor/bin/pint ]; then
    echo "📝 Formatando PHP:"
    ./vendor/bin/pint test-php-format.php --quiet
    echo "✅ Laravel Pint executado com sucesso!"
else
    echo "❌ Laravel Pint não encontrado"
fi
echo ""

# Mostrar resultados
echo "=== 5. RESULTADOS DA FORMATAÇÃO ==="
echo ""
echo "🔍 Vue formatado (primeiras 10 linhas):"
head -10 test-vue-format.vue
echo ""
echo "🔍 PHP formatado (primeiras 10 linhas):"
head -10 test-php-format.php
echo ""
echo "🔍 JS formatado:"
cat test-js-format.js
echo ""

echo "🎉 TESTE CONCLUÍDO!"
echo ""
echo "📋 PRÓXIMOS PASSOS:"
echo "1. Verificar se os arquivos têm indentação de 4 espaços"
echo "2. Testar formatação automática ao salvar (Ctrl+S)"
echo "3. Testar formatação manual (Ctrl+Shift+P → 'Format Document')"
echo ""
echo "🧹 Para limpar os arquivos de teste:"
echo "rm test-*-format.*"
