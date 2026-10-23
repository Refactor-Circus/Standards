# Models

A package model is `{Entity}Model` in `Domains/{Domain}/Models`, resolves its table from the package's configurable tables map, and has a policy.

## Location and naming

- `src/Domains/{Domain}/Models/{Entity}Model.php`, namespace `{Package}\Domains\{Domain}\Models`. See [[domain-modules]] and [[naming]].
- Every model maps its Eloquent hooks to `{Entity}{Hook}Event` classes; see [[lifecycle-events]].

## Tables

- Override `getTable()` to return the table's configured name by key, from the package's tables class (backed by the `{package}.tables` config). Never set `$table` to a literal or write a prefixed table name anywhere else — package tables live in a host app, which may need to rename them.
- Default names carry the package prefix (`{package}_{entities}`), so they don't collide with host tables.
- Migrations, validation rules (`Rule::exists(...)`) and joins ask the same tables class with the same keys; see [[migrations]] and [[configuration]].

## Authorization

- Every model has a policy, registered from the package's `{package}.policies` config map (model class → policy class), so a host can swap one. Policies sit in `Policies/` beside `Models/`.
- Requests authorize through those policies: `viewAny`/`create` on the model class, `view`/`update`/`delete` on the instance. See [[authorization]].

## Attributes and relations

- A `@property` docblock lists every column with its PHP type; relations declare generics (`@return HasMany<{Entity}ItemModel, $this>`) so static analysis knows the related model.
- Cast columns whose PHP type differs from the stored one (booleans, integers, JSON, dates) in the model's casts.
- Name foreign keys explicitly in relations rather than relying on inferred names.
- `newFactory()` points at the package's factory namespace; factory discovery assumes the host app's namespace.

```php
/**
 * @property int $id
 * @property string $slug
 * @property bool $is_shared
 */
class {Entity}Model extends Model
{
    use DispatchesModelEvents;

    protected $casts = ['is_shared' => 'boolean'];

    public function getTable(): string
    {
        return Tables::name('{entities}');
    }
}
```

## Moving or renaming a model

A moved or renamed model keeps its previous class name as its morph alias, so stored polymorphic values still resolve. See [[stored-identifiers]].

## Reference implementation (Refactor Circus)

- A package's `Support\Tables` extends `RefactorCircus\Keystone\Database\Tables`, setting `CONFIG` (`'{package}.tables'`) and `DEFAULTS`; `Tables::name($key)` falls back to the default when the config omits a key.
- `tests/ArchTest.php` checks for literal table names with `Arch::literalTableNames()` and for models missing a policy with `Arch::modelsWithoutPolicies()`.
