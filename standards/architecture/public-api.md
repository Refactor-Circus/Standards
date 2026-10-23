# Public API

A package's static entry point is thin: it wires config and delegates to instances that hold the logic and work without it.

## Entry point and facade

- The package class `{Package}` is the public API and the facade target. Its methods build the right instance from config and call it; no per-value logic lives there.
- Cross-cutting orchestration that spans instances (choosing a strategy, comparing results) may live on the entry point. Work on a single value never does.
- The logic lives in instances (`{Entity}Encoder`, `{Entity}Builder`) that are testable and usable directly, without the static wrapper.
- Caller-passed options win; config only fills in what the caller left out; see [[configuration]].

```php
public static function encode(mixed $data, ?EncoderOptions $options = null): string
{
    return (new Encoder($options ?? self::defaultEncoderOptions()))->encode($data);
}
```

## Facade docblock

- The facade proxies through `getFacadeAccessor()`, so its `@method static` lines are the only static API IDEs and PHPStan see. Every public method on `{Package}` has a matching line with the exact signature and return type.
- Adding, renaming or removing a public method updates the facade docblock in the same change. Keep `@see \{Vendor}\{Package}\{Package}` on the facade.

## Extension points

- Classes are `final` by default. Anything left open can be subclassed, and then its internals are API you have to keep stable.
- The supported extension points are the ones the package documents: contracts bound in the container ([[container-bindings]]), config keys, events ([[actions-events]]) and the registration methods on its services. Prefer plain package code over an abstraction unless the extension point is real.
- An override that plugs the package into a host framework (a trait, middleware, response wrapper, stream filter) adapts that host's hook and calls `{Package}::*`. It holds no package logic of its own.

## State and internals

- Value objects expose meaningful state as `public private(set)` properties, read as `$item->label` and immutable from outside; see [[value-objects]].
- Closures, caches and other internals stay `private`. Only state a caller has a reason to read is public.
