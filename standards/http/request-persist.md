# Requests & Controllers

Each HTTP operation is a FormRequest that owns validation, authorization and the call into an action through `persist()`; controllers only `return $request->persist();`.

## Base

- Requests extend the package's `Http\Requests\Request` and live in their domain's `Http/Requests/`; controllers live in `Http/Controllers/`.
- The base declares `abstract public function persist(): Response`, so every request must own its work and every controller method collapses to the same one line.
- `authorize()` is overridden by every concrete request with a policy check ([[authorization]]). `rules()` defaults to `[]`, so read-only requests omit it.

## Controller

```php
public function store(StoreProjectRequest $request): JsonResponse
{
    return $request->persist();
}
```

- No validation, action calls, response building or database calls in a controller. Controllers stay identical in shape across models.

## Request

```php
final class StoreProjectRequest extends Request
{
    public function authorize(): bool
    {
        return $this->allows('create', ProjectModel::class);
    }

    public function rules(): array
    {
        return CreateProjectData::rules();
    }

    public function persist(): JsonResponse
    {
        $project = app(CreateProjectAction::class)->execute(CreateProjectData::fromValidated($this->validated()));

        return (new ProjectResource($project))->response()->setStatusCode(201);
    }
}
```

## Rules

- One request class per operation, named `{Verb}{Entity}Request` with `Index`, `Store`, `Show`, `Update` or `Delete` ([[naming]]).
- `rules()` delegates to the action input's rules (`{Action}Data::rules()`), never inline, so input is validated once. `persist()` builds the input with `fromValidated($this->validated())`; never `Data::from()` in a request ([[data-objects]]).
- `persist()` calls one action ([[actions-events]]) and returns the response, serialized through a resource ([[resources]]). The action does the writing.
- JSON status codes: 201 for create, 204 (empty response) for delete, 200 otherwise.
- Route-bound models resolve in a `protected` helper that aborts 404 when the binding is missing, so `authorize()` and `persist()` share one resolution path.

## Enforcement

Tests assert that every request extends the package base and declares its own public `persist()`, and that no controller contains database calls (`DB::`, `->create(`, `->delete(`).

## Reference implementation (Refactor Circus)

`RefactorCircus\Keystone\Http\Requests\Request` is the shared base: abstract `persist()`, `allows()`/`allowsEach()` against the package's configured policies, and a 401 for guests versus 403 for signed-in users. Each package's `Http\Requests\Request` extends it.
