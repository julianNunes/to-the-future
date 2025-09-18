# 🎯 Guia Laravel - To The Future

## 📋 Sobre o Framework

### Laravel 10.x - Características

- **Versão**: Laravel 10.x
- **PHP**: 8.2 (mínimo 8.1)
- **Eloquent ORM**: Sistema de banco de dados elegante
- **Blade**: Template engine + Inertia.js integration
- **Artisan**: CLI para desenvolvimento
- **Middleware**: Sistema de filtros HTTP
- **Migrations**: Controle de versão do banco
- **Queue System**: Processamento em background
- **Inertia.js**: Bridge para SPA com Vue.js

### Por que Laravel?

✅ **Produtividade**: Sintaxe elegante e expressiva  
✅ **Ecossistema**: Vasto conjunto de ferramentas  
✅ **Documentação**: Excelente documentação oficial  
✅ **Comunidade**: Grande comunidade ativa  
✅ **Segurança**: Recursos de segurança built-in  
✅ **Testing**: Framework de testes integrado  

## 🏗️ Estrutura do Projeto Laravel

### Diretórios Principais

```
to-the-future/
├── app/                    # Lógica da aplicação
│   ├── Models/            # Models Eloquent
│   ├── Http/
│   │   ├── Controllers/   # Controllers
│   │   ├── Middleware/    # Middleware customizado
│   │   └── Requests/      # Form Requests
│   ├── Services/          # Lógica de negócio
│   ├── Policies/          # Autorização
│   └── Providers/         # Service Providers
├── resources/             # Views e assets
│   ├── views/            # Templates Blade
│   ├── js/               # JavaScript
│   └── sass/             # Styles
├── routes/               # Definição de rotas
│   ├── web.php          # Rotas web
│   ├── api.php          # Rotas API
│   └── console.php      # Comandos Artisan
├── database/            # Banco de dados
│   ├── migrations/      # Migrations
│   ├── seeders/         # Seeders
│   └── factories/       # Factories
├── config/              # Configurações
├── storage/             # Arquivos e logs
└── vendor/              # Dependências Composer
```

### Arquivos de Configuração Importantes

```
.env                     # Variáveis de ambiente
composer.json           # Dependências PHP
package.json           # Dependências JavaScript
webpack.mix.js         # Build de assets
phpunit.xml            # Configuração de testes
```

## 🎨 Desenvolvimento com Blade Templates

### Sintaxe Básica do Blade

```php
{{-- Comentários Blade --}}

{{-- Exibir dados (escapados) --}}
{{ $user->name }}

{{-- Exibir dados não escapados (CUIDADO!) --}}
{!! $html !!}

{{-- Condicionais --}}
@if($user->isAdmin())
    <p>Admin</p>
@elseif($user->isModerator())
    <p>Moderador</p>
@else
    <p>Usuário</p>
@endif

{{-- Loops --}}
@foreach($users as $user)
    <p>{{ $user->name }}</p>
@endforeach

@forelse($posts as $post)
    <p>{{ $post->title }}</p>
@empty
    <p>Nenhum post encontrado</p>
@endforelse

{{-- Include de templates --}}
@include('components.header')

{{-- Extends e sections --}}
@extends('layouts.app')

@section('content')
    <h1>Conteúdo da página</h1>
@endsection
```

### Layout Base (resources/views/layouts/app.blade.php)

```php
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Nova Central V4.1')</title>
    
    {{-- CSS --}}
    <link href="{{ mix('css/app.css') }}" rel="stylesheet">
    @stack('styles')
</head>
<body>
    <div id="app">
        @include('components.navbar')
        
        <main class="container">
            @yield('content')
        </main>
        
        @include('components.footer')
    </div>
    
    {{-- JavaScript --}}
    <script src="{{ mix('js/app.js') }}"></script>
    @stack('scripts')
</body>
</html>
```

## 📊 Trabalhando com Models e Eloquent

### Criando Models

```bash
# Criar model simples
./scripts/artisan.sh make:model User

# Criar model com migration
./scripts/artisan.sh make:model Post -m

# Criar model completo (migration, factory, seeder, controller)
./scripts/artisan.sh make:model Product -mfsc
```

### Model Básico

```php
<?php
// app/Models/User.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Model
{
    use HasFactory;
    
    // Tabela (opcional se seguir convenção)
    protected $table = 'users';
    
    // Campos que podem ser mass assigned
    protected $fillable = [
        'name',
        'email',
        'password',
    ];
    
    // Campos ocultos (não retornados em JSON)
    protected $hidden = [
        'password',
        'remember_token',
    ];
    
    // Cast de tipos
    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_active' => 'boolean',
    ];
    
    // Relacionamentos
    public function posts()
    {
        return $this->hasMany(Post::class);
    }
    
    public function profile()
    {
        return $this->hasOne(Profile::class);
    }
    
    // Mutators (modificar dados ao salvar)
    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = bcrypt($value);
    }
    
    // Accessors (modificar dados ao recuperar)
    public function getFullNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }
    
    // Scopes (consultas reutilizáveis)
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
```

### Operações com Eloquent

```php
// Criar registros
$user = User::create([
    'name' => 'João Silva',
    'email' => 'joao@email.com',
    'password' => 'senha123'
]);

// Buscar registros
$users = User::all();
$user = User::find(1);
$user = User::where('email', 'joao@email.com')->first();
$activeUsers = User::active()->get();

// Atualizar registros
$user = User::find(1);
$user->update(['name' => 'João Santos']);

// Ou em uma linha
User::where('id', 1)->update(['name' => 'João Santos']);

// Deletar registros
$user = User::find(1);
$user->delete();

// Ou soft delete (se configurado)
$user->delete(); // Marca como deletado
$user->restore(); // Restaura
$user->forceDelete(); // Deleta permanentemente
```

## 🛣️ Sistema de Rotas

### Rotas Web (routes/web.php)

```php
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PostController;

// Rota simples
Route::get('/', function () {
    return view('welcome');
});

// Rota com parâmetro
Route::get('/user/{id}', function ($id) {
    return "Usuário: " . $id;
});

// Rota com parâmetro opcional
Route::get('/posts/{id?}', function ($id = null) {
    return $id ? "Post: " . $id : "Todos os posts";
});

// Rotas para controllers
Route::get('/users', [UserController::class, 'index']);
Route::post('/users', [UserController::class, 'store']);

// Resource routes (CRUD completo)
Route::resource('posts', PostController::class);
// Gera: index, create, store, show, edit, update, destroy

// Agrupamento de rotas
Route::prefix('admin')->group(function () {
    Route::get('/users', [UserController::class, 'index']);
    Route::get('/posts', [PostController::class, 'index']);
});

// Middleware em rotas
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', 'DashboardController@index');
});

// Nomeação de rotas
Route::get('/profile', 'ProfileController@show')->name('profile.show');
// Uso: route('profile.show')
```

### Rotas API (routes/api.php)

```php
<?php

use App\Http\Controllers\Api\UserController;

// Prefixo automático: /api/
Route::apiResource('users', UserController::class);
// Gera: index, store, show, update, destroy (sem create/edit)

// Middleware de API (rate limiting, etc.)
Route::middleware('throttle:60,1')->group(function () {
    Route::get('/data', 'ApiController@getData');
});
```

## 🎮 Controllers

### Controller Básico

```php
<?php
// app/Http/Controllers/UserController.php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        $users = User::paginate(15);
        return view('users.index', compact('users'));
    }
    
    public function show(User $user)
    {
        // Route Model Binding automático
        return view('users.show', compact('user'));
    }
    
    public function create()
    {
        return view('users.create');
    }
    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8|confirmed',
        ]);
        
        $user = User::create($validated);
        
        return redirect()
            ->route('users.show', $user)
            ->with('success', 'Usuário criado com sucesso!');
    }
    
    public function edit(User $user)
    {
        return view('users.edit', compact('user'));
    }
    
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
        ]);
        
        $user->update($validated);
        
        return redirect()
            ->route('users.show', $user)
            ->with('success', 'Usuário atualizado!');
    }
    
    public function destroy(User $user)
    {
        $user->delete();
        
        return redirect()
            ->route('users.index')
            ->with('success', 'Usuário removido!');
    }
}
```

### Controller API

```php
<?php
// app/Http/Controllers/Api/UserController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        return User::paginate(15);
    }
    
    public function show(User $user)
    {
        return $user;
    }
    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
        ]);
        
        $user = User::create($validated);
        
        return response()->json($user, 201);
    }
    
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|unique:users,email,' . $user->id,
        ]);
        
        $user->update($validated);
        
        return $user;
    }
    
    public function destroy(User $user)
    {
        $user->delete();
        
        return response()->json(null, 204);
    }
}
```

## 📋 Validação e Form Requests

### Validação Básica

```php
// No controller
public function store(Request $request)
{
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users',
        'password' => 'required|min:8|confirmed',
        'age' => 'nullable|integer|min:18',
        'avatar' => 'nullable|image|max:2048',
    ]);
    
    // Dados validados em $validated
}
```

### Form Request (Recomendado)

```bash
# Criar Form Request
./scripts/artisan.sh make:request StoreUserRequest
```

```php
<?php
// app/Http/Requests/StoreUserRequest.php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    public function authorize()
    {
        // Autorização (pode usar Gates/Policies)
        return true;
    }
    
    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8|confirmed',
        ];
    }
    
    public function messages()
    {
        return [
            'name.required' => 'O nome é obrigatório.',
            'email.unique' => 'Este email já está em uso.',
        ];
    }
    
    public function attributes()
    {
        return [
            'email' => 'endereço de email',
            'password' => 'senha',
        ];
    }
}
```

### Usar Form Request no Controller

```php
public function store(StoreUserRequest $request)
{
    // Dados já validados automaticamente
    $user = User::create($request->validated());
    
    return redirect()->route('users.show', $user);
}
```

## 💾 Migrations e Schema

### Criar Migration

```bash
# Migration para criar tabela
./scripts/artisan.sh make:migration create_posts_table

# Migration para modificar tabela
./scripts/artisan.sh make:migration add_status_to_posts_table --table=posts
```

### Migration Básica

```php
<?php
// database/migrations/xxxx_create_posts_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePostsTable extends Migration
{
    public function up()
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('content');
            $table->string('slug')->unique();
            $table->boolean('is_published')->default(false);
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            
            // Índices
            $table->index('is_published');
            $table->index(['user_id', 'is_published']);
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('posts');
    }
}
```

### Tipos de Campo Comuns

```php
$table->id();                          // BIGINT AUTO_INCREMENT PRIMARY KEY
$table->string('name', 100);           // VARCHAR(100)
$table->text('description');           // TEXT
$table->integer('count');              // INT
$table->bigInteger('views');           // BIGINT
$table->decimal('price', 8, 2);        // DECIMAL(8,2)
$table->boolean('is_active');          // BOOLEAN
$table->date('birth_date');            // DATE
$table->datetime('created_at');        // DATETIME
$table->timestamp('updated_at');       // TIMESTAMP
$table->json('metadata');              // JSON
$table->enum('status', ['active', 'inactive']);  // ENUM

// Chaves estrangeiras
$table->foreignId('user_id')->constrained();
$table->foreignId('category_id')->constrained('categories')->onDelete('cascade');

// Campos nullable
$table->string('middle_name')->nullable();

// Valores default
$table->boolean('is_verified')->default(false);
$table->timestamp('created_at')->useCurrent();
```

## 🌱 Seeders e Factories

### Factory

```bash
# Criar factory
./scripts/artisan.sh make:factory PostFactory
```

```php
<?php
// database/factories/PostFactory.php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PostFactory extends Factory
{
    public function definition()
    {
        return [
            'title' => $this->faker->sentence(),
            'content' => $this->faker->paragraphs(3, true),
            'slug' => $this->faker->slug(),
            'is_published' => $this->faker->boolean(70), // 70% chance de true
            'user_id' => User::factory(),
        ];
    }
    
    // States (variações)
    public function published()
    {
        return $this->state(function (array $attributes) {
            return [
                'is_published' => true,
            ];
        });
    }
    
    public function unpublished()
    {
        return $this->state(function (array $attributes) {
            return [
                'is_published' => false,
            ];
        });
    }
}
```

### Seeder

```bash
# Criar seeder
./scripts/artisan.sh make:seeder PostSeeder
```

```php
<?php
// database/seeders/PostSeeder.php

namespace Database\Seeders;

use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Seeder;

class PostSeeder extends Seeder
{
    public function run()
    {
        // Criar usuários e posts
        User::factory(10)
            ->has(Post::factory(5)->published())
            ->create();
        
        // Criar posts para usuário específico
        $admin = User::find(1);
        Post::factory(3)->for($admin)->create();
    }
}
```

### Database Seeder Principal

```php
<?php
// database/seeders/DatabaseSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call([
            UserSeeder::class,
            PostSeeder::class,
            CategorySeeder::class,
        ]);
    }
}
```

### Executar Seeders

```bash
# Executar todos os seeders
./scripts/artisan.sh db:seed

# Executar seeder específico
./scripts/artisan.sh db:seed --class=PostSeeder

# Fresh migration + seed
./scripts/artisan.sh migrate:fresh --seed
```

## 🔐 Autenticação e Middleware

### Middleware Básico

```bash
# Criar middleware
./scripts/artisan.sh make:middleware CheckAge
```

```php
<?php
// app/Http/Middleware/CheckAge.php

namespace App\Http\Middleware;

use Closure;

class CheckAge
{
    public function handle($request, Closure $next, $minAge = 18)
    {
        if ($request->user()->age < $minAge) {
            return redirect('home')->with('error', 'Acesso negado');
        }
        
        return $next($request);
    }
}
```

### Registrar Middleware

```php
// app/Http/Kernel.php

protected $routeMiddleware = [
    'auth' => \App\Http\Middleware\Authenticate::class,
    'check.age' => \App\Http\Middleware\CheckAge::class,
];
```

### Usar Middleware

```php
// Em rotas
Route::get('admin', 'AdminController@index')->middleware('auth');
Route::get('adults-only', 'ContentController@show')->middleware('check.age:21');

// Em controllers
class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('check.age:18')->only(['sensitive']);
    }
}
```

## 🎯 Comandos Artisan Úteis

### Desenvolvimento

```bash
# Informações da aplicação
./scripts/artisan.sh about

# Servir aplicação (em ambiente Docker não necessário)
./scripts/artisan.sh serve

# REPL interativo
./scripts/artisan.sh tinker

# Listar rotas
./scripts/artisan.sh route:list
./scripts/artisan.sh route:list --name=user

# Cache
./scripts/artisan.sh cache:clear
./scripts/artisan.sh config:clear
./scripts/artisan.sh view:clear
./scripts/artisan.sh route:clear

# Otimização (produção)
./scripts/artisan.sh optimize
./scripts/artisan.sh config:cache
./scripts/artisan.sh route:cache
./scripts/artisan.sh view:cache
```

### Geradores

```bash
# Models
./scripts/artisan.sh make:model Post -mfsc  # com migration, factory, seeder, controller

# Controllers
./scripts/artisan.sh make:controller PostController --resource
./scripts/artisan.sh make:controller Api/PostController --api

# Middleware
./scripts/artisan.sh make:middleware CheckRole

# Requests
./scripts/artisan.sh make:request StorePostRequest

# Migrations
./scripts/artisan.sh make:migration create_posts_table
./scripts/artisan.sh make:migration add_status_to_posts --table=posts

# Seeders e Factories
./scripts/artisan.sh make:seeder PostSeeder
./scripts/artisan.sh make:factory PostFactory

# Jobs (filas)
./scripts/artisan.sh make:job ProcessEmail

# Notifications
./scripts/artisan.sh make:notification UserRegistered

# Commands personalizados
./scripts/artisan.sh make:command ImportUsers
```

---

**📚 Este guia cobre os conceitos essenciais do Laravel. Para informações mais detalhadas, consulte a [documentação oficial](https://laravel.com/docs/8.x).**
