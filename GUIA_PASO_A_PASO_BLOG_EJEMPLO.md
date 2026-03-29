# 🖋️ Guía Paso a Paso: Creación de un Módulo (Ejemplo: Blog)

Esta guía detalla cómo crear un nuevo módulo en el proyecto utilizando el módulo de **Blog** como un ejemplo real y práctico. Sigue la **Arquitectura Hexagonal**, dividiendo la lógica en Dominio, Aplicación e Infraestructura.

---

## 🏗️ 1. Capa de Dominio (`Domain/`)

Es el corazón del módulo. No depende de ningún framework (Laravel) ni base de datos.

### 1.1 Definir las Entidades
En el caso de Blog, tenemos una relación de "Uno a Muchos" con las traducciones.

**Archivo:** `src/Admin/Blog/Domain/Entity/Blog.php`
```php
final class Blog implements \JsonSerializable {
    private ?int $id;
    private string $categoryCode;
    private string $status;
    private array $translations; // Array de BlogTranslation

    public function __construct(
        string $categoryCode,
        string $status = 'draft',
        array $translations = [],
        ?int $id = null
    ) {
        $this->categoryCode = $categoryCode;
        $this->status = $status;
        $this->translations = $translations;
        $this->id = $id;
    }

    // Getters...
    public function translations(): array { return $this->translations; }
}
```

**Archivo:** `src/Admin/Blog/Domain/Entity/BlogTranslation.php`
```php
final class BlogTranslation implements \JsonSerializable {
    private string $lang;
    private string $title;
    private ?string $content;

    public function __construct(string $lang, string $title, ?string $content) {
        $this->lang = $lang;
        $this->title = $title;
        $this->content = $content;
    }
    // Getters...
}
```

### 1.2 Definir el Contrato del Repositorio (Interface)
Define **qué** se puede hacer con los datos, pero no **cómo**.

**Archivo:** `src/Admin/Blog/Domain/Contracts/BlogRepositoryContract.php`
```php
interface BlogRepositoryContract {
    public function save(Blog $blog): int;
    public function findById(int $id): ?Blog;
    public function update(Blog $blog): void;
    public function delete(int $id): void;
}
```

---

## 📦 2. Capa de Aplicación (`Application/`)

Aquí reside la lógica de orquestación (Casos de Uso).

### 2.1 Caso de Uso: Crear Blog
Este caso de uso recibe los datos, genera las traducciones automáticas usando un servicio compartido y luego le pide al repositorio que guarde la entidad.

**Archivo:** `src/Admin/Blog/Application/CreateBlogUseCase.php`
```php
final class CreateBlogUseCase {
    private BlogRepositoryContract $repository;
    private TranslatorServiceContract $translator;

    public function __construct(BlogRepositoryContract $repo, TranslatorServiceContract $trans) {
        $this->repository = $repo;
        $this->translator = $trans;
    }

    public function execute(string $category, string $baseLang, string $title, ?string $content, array $targets): int {
        $translations = [];
        $translations[] = new BlogTranslation($baseLang, $title, $content);

        foreach ($targets as $lang) {
            $tTitle = $this->translator->translate($title, $lang, $baseLang);
            $tContent = $content ? $this->translator->translate($content, $lang, $baseLang) : null;
            $translations[] = new BlogTranslation($lang, $tTitle, $tContent);
        }

        $blog = new Blog($category, 'draft', $translations);
        return $this->repository->save($blog);
    }
}
```

---

## 🔧 3. Capa de Infraestructura (`Infrastructure/`)

Aquí es donde conectamos con Laravel, la base de datos y servicios externos.

### 3.1 Modelos Eloquent
Son las clases de Laravel que representan las tablas.

**Archivo:** `src/Admin/Blog/Infrastructure/Models/BlogEloquentModel.php`
```php
class BlogEloquentModel extends Model {
    protected $table = 'blogs';
    protected $fillable = ['category_code', 'status', ...];

    public function translations() {
        return $this->hasMany(BlogTranslationEloquentModel::class, 'blog_id');
    }
}
```

### 3.2 Implementación del Repositorio
Usa los modelos Eloquent para persistir la Entidad de Dominio.

**Archivo:** `src/Admin/Blog/Infrastructure/Repositories/EloquentBlogRepository.php`
```php
final class EloquentBlogRepository implements BlogRepositoryContract {
    public function save(Blog $blog): int {
        return DB::transaction(function () use ($blog) {
            $eloquentBlog = BlogEloquentModel::create([
                'category_code' => $blog->categoryCode(),
                'status' => $blog->status(),
            ]);

            foreach ($blog->translations() as $t) {
                $eloquentBlog->translations()->create([
                    'lang' => $t->lang(),
                    'title' => $t->title(),
                    'content' => $t->content(),
                ]);
            }
            return $eloquentBlog->id;
        });
    }
}
```

### 3.3 Controlador (Single Action)
Recibe el Request HTTP, valida y llama al Caso de Uso.

**Archivo:** `src/Admin/Blog/Infrastructure/Controllers/CreateBlogController.php`
```php
final class CreateBlogController {
    public function __invoke(Request $request): JsonResponse {
        $request->validate(['title' => 'required', 'categoryCode' => 'required', ...]);

        $id = $this->useCase->execute(...);

        return response()->json(['blogId' => $id], 201);
    }
}
```

### 3.4 Definición de Rutas
**Archivo:** `src/Admin/Blog/Infrastructure/routes/api.php`
```php
Route::prefix('blogs')->group(function () {
    Route::post('/', CreateBlogController::class);
});
```

---

## 🔗 4. Registro de Dependencias

Para que Laravel sepa qué implementación usar cuando se pide una interfaz, debes registrarla.

**Archivo:** `app/Providers/RepositoryServiceProvider.php` (o similar)
```php
$this->app->bind(
    \Src\Admin\Blog\Domain\Contracts\BlogRepositoryContract::class,
    \Src\Admin\Blog\Infrastructure\Repositories\EloquentBlogRepository::class
);
```

---

## ✅ Resumen del Flujo de Trabajo
1. 🛠️ **Dominio**: Crea la Entidad y el Contrato (Interface).
2. 📦 **Aplicación**: Crea el Caso de Uso (Lógica de negocio).
3. 💾 **Infraestructura**: Crea el Modelo Eloquent y la implementación del Repositorio.
4. 🎮 **Infraestructura**: Crea el Controlador y define la Ruta.
5. 🔗 **Provider**: Registra el bind en el ServiceProvider.
