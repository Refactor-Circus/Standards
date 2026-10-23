# Resources

Every payload a package returns serializes through a JSON resource, and every surface uses the same resource, so a payload is identical wherever it is served.

## Placement

- A model's resource sits beside the model, in `src/Domains/{Domain}/Resources/` next to `Models/` ([[domain-modules]]).
- A payload with no model behind it lives in `src/Domains/{Domain}/Http/Resources/`, or `src/Http/Resources/` when several domains share it.
- Named `{Entity}Resource` ([[naming]]).

## Shape

```php
/**
 * @mixin ProjectModel
 */
final class ProjectResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'name' => $this->name,
            'slug' => $this->slug,
            'owner' => new OwnerResource($this->whenLoaded('owner')),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
```

- `final`, with a `@mixin` docblock naming the model for static analysis.
- The public identity (slug or route key) is exposed; internal ids are left out unless there is a concrete need.
- Nested relations only through `whenLoaded()`; the action or request eager-loads what the resource needs, so a resource never triggers queries.
- Timestamps as `?->toIso8601String()`.
- No conditionals on request context: the same model always yields the same payload.

## One resource, every surface

- HTTP responses, console output and any other surface (a tool server, a UI's data endpoint) return the same resource, never a hand-built array. A second serializer for the same model is drift waiting to happen.
- Lists use the resource's collection, with paging metadata beside the data.

## Reference implementation (Refactor Circus)

Resources extend `RefactorCircus\Keystone\Http\Resources\JsonResource`, whose collections are `Keystone\Http\Resources\ResourceCollection`. The base answers HTTP through Laravel's `toResponse()` and other surfaces through `toStructuredResponse()` with the same payload, and declares the payload's fields through `schema()`.
