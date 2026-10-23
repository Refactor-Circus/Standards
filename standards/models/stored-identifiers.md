# Stored Identifiers

Inputs and external references identify package records by slug (plus version number where the record is versioned), never by internal id, and stored class names keep resolving after a model moves.

## Slugs, not ids

- Action inputs, request fields and anything a caller stores or sends (API, MCP, exports) reference a record by its slug: `'{entity}' => ['string', Rule::exists(<configured table>, 'slug')]`.
- Why: slugs are stable, human- and agent-facing identifiers that survive export/import across environments; internal ids don't.
- Internal ids stay internal. The action, or a shared concern beside it, resolves the slug to the foreign key column; cast the resolved key to string (`(string) $model->getKey()`).
- A versioned record is referenced by slug plus an integer version number (`min:1`), never by the version row's id.
- Records the package doesn't own, such as the host's users, have no slug; reference them by the model's route key (`getRouteKeyName()`), validated against its table.

```php
'{entity}' => ['sometimes', 'nullable', 'string', Rule::exists(Tables::name('{entities}'), 'slug')],
'{entity}_version' => ['sometimes', 'nullable', 'integer', 'min:1'],
```

## Adding a cross-record input

1. Accept the slug (and version number, if versioned).
2. Validate it with `Rule::exists()` against the configured table, never a literal name.
3. Resolve it to the foreign key in the action or a shared concern, not in the request.

## Morph aliases

- A model that moves or is renamed keeps its previous class name as its morph alias (`Relation::morphMap()` in the domain service provider), so polymorphic `*_type` columns and audit records written under the old name still resolve.
- A test stores the old class name in a polymorphic column and proves it resolves to the moved model.

See [[models]], [[domain-modules]] and [[data-objects]].
