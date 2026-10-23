# Value Objects

Value objects expose their state as `public private(set)` properties, configured through fluent setters, and stay immutable from the outside.

## State

- Readable state is a `public private(set)` property, not a getter: callers read `$item->label`, not `$item->getLabel()`.
- Only the object changes its own state; nothing outside can assign to it.
- Closures, caches and other internals stay `private`. Only meaningful state is publicly readable.
- Value objects live in the domain's `Data/`; see [[domain-modules]].

```php
final class {Entity}Definition
{
    public private(set) string $label = '';

    public private(set) {Entity}Style $style = {Entity}Style::Default;

    private ?Closure $resolver = null;

    public function label(string $label): static
    {
        $this->label = $label;

        return $this;
    }
}
```

## Configuration

- Fluent setters share the property's name: `->label('Reports')` sets `$label` and returns `$this`.
- A fixed set of choices is a string-backed enum whose backing value is the config token, so config maps in with `::from()` and needs no lookup table.
- Behavior that varies by choice is a method on the enum, not a `switch` elsewhere.

## Options objects

- Tuning for a service is a `readonly` options class with a named-argument constructor and defaults, not a growing list of method parameters. Methods take the data they work on; configuration comes from the options passed to the constructor.
- New settings go on the options constructor, so method signatures stay stable. Common combinations are named presets: `{Service}Options::compact()`.
- Immutable options are safe to share and reuse without mutation bugs.

## Stateless helpers

- Split classes by state: a helper with no configuration and no state is static-only and takes everything it needs as arguments; a class that holds options or progress is an instance.
- Static helpers never store configuration, and configuration is never a singleton or global; see [[configuration]].
