---
description: "Pest it() with a behavior phrase and fluent expect() chains; ->and() for related assertions; ->toBe() for exact values; ->throws() modifier for exceptions; no describe() or class methods"
tags: [testing, pest]
kind: convention
---
# Pest Style

Tests use Pest `it()` with a behavior phrase and fluent `expect()` chains. No class methods, no `describe()` blocks; `test()` only rarely.

```php
it('wraps query in body when executing', function () {
    expect($query['query']['multi_match']['query'])->toBe('laptop for work')
        ->and($query['query']['multi_match']['fields'])->toBe(['title^3']);
});
```

- `it('<behavior phrase>', fn)` — phrasing is free (describe the behavior); no PHPUnit test-method style.
- Chain related assertions with `->and(...)` off a single `expect()`; separate `expect()` calls are fine for unrelated ones.
- Assert exact values with `->toBe()` (strict identity). Use `->toEqual()` only when loose equality is genuinely intended.
- Use `->toHaveKey()` / `->not->toHaveKey()` for presence and absence.
- Expected-exception tests use the trailing modifier: `it('throws when ...', fn () => ...)->throws(RuntimeException::class);`
