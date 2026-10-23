# Data Objects

An action's input is a `final readonly` `{Action}Data` object in the domain's `Data/`, never an array, and it carries its own validation rules.

## Shape

- One Data class per action input: `Create{Entity}Data` for `Create{Entity}Action`, in `src/Domains/{Domain}/Data/`; see [[domain-modules]].
- Fields are promoted constructor properties, named in camelCase of the input keys: the `per_page` key fills `$perPage`.
- Partial updates type optional fields `T|Missing = new Missing`, so a field that was left out stays distinct from one sent as `null`, and the action changes only what was sent.
- `rules()` is a static method on the Data, the single source of validation for that input. It may take context, such as the record being updated.
- Nested input is nested Data: a single child is a Data-typed field; a list is an array field marked `#[ListOf({Item}Data::class)]`. The parent's rules check nested items under their key (`items.*.quantity`), so children are not validated again.

```php
final readonly class Update{Entity}Data extends Data
{
    public function __construct(
        public string|Missing $name = new Missing,
        public string|Missing|null $description = new Missing,
        #[ListOf({Item}Data::class)]
        public array|Missing $items = new Missing,
    ) {}

    public static function rules(?{Entity}Model ${entity} = null): array { ... }
}
```

## Building one

- **In a request:** `rules()` returns `XData::rules()`, and `persist()` builds with `XData::fromValidated($this->validated())`. Never `XData::from()` in a request — input is validated once and every error is reported together; see [[request-persist]].
- **Untrusted input elsewhere** (a tool call, a CSV row, a queued payload): `XData::from($input)`, which validates against `rules()` and throws a `ValidationException`.
- **Trusted code** that vouches for its values: `new XData(...)` with named arguments.

## Why

- Typed, immutable input makes an action's contract visible in its signature, and the starting event carries the same object; see [[actions-events]].
- Rules on the Data keep every surface that feeds an action — HTTP, tools, imports — validating identically.

## Reference implementation (Refactor Circus)

- Data classes extend `RefactorCircus\Keystone\Data\Data`, which provides `from()`, `fromValidated()`, `has()`, `toArray()` and a `prepare()` hook for fields that need a lookup. `Keystone\Data\Missing` marks an omitted field; `Keystone\Data\Attributes\ListOf` types list items.
- `tests/ArchTest.php` checks that every Data is `final readonly` with `rules()` (`Arch::dataWithoutRules()`), that `handle()` takes no array, and that no request calls `Data::from(`; see [[arch-tests]].
