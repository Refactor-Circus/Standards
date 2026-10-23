# Authorization

Every model has a policy registered from the package config, and every request authorizes in `authorize()` by asking that policy, never in the controller.

## Policies

- Each model has a policy in its domain's `Policies/`, beside `Models/` ([[domain-modules]]). Policies extend the package's shared policy base.
- Policies are registered from the `policies` key of `config/{package}.php` (model class => policy class) by the package provider ([[service-providers]], [[configuration]]). A host application swaps a policy by pointing the model at another class there, without touching the package.
- A child model's policy defers to its parent's policy through the Gate rather than repeating the parent's rules, so it follows whichever policy the application has registered for the parent. Guests are asked about too, so a parent that lets guests in decides for its children as well.

```php
final class ProjectTaskPolicy extends Policy
{
    public function update(?Authenticatable $user, ProjectTaskModel $task): bool
    {
        return Gate::forUser($user)->allows('update', $task->project);
    }
}
```

## Requests

- `authorize()` calls `$this->allows($ability, $subject)`, or `$this->allowsEach($ability, $models)` for a batch (an empty list passes) ([[request-persist]]).
- Pass the model class for `viewAny` and `create`, and the instance for `view`, `update` and `delete`:

```php
return $this->allows('create', ProjectModel::class);
return $this->allows('update', $this->project());
return $this->allowsEach('delete', $this->tasks());
```

- The route middleware's gate still runs first; the request's policy check is the per-model decision on top of it.
- A refused guest gets 401, a refused signed-in user 403.

## Enforcement

`tests/ArchTest.php` asserts every model in the package has a policy in the config ([[arch-tests]]).

## Reference implementation (Refactor Circus)

`RefactorCircus\Keystone\Policies\Policy` is the base; its `allowsOn($user, $ability, $parent)` asks the Gate about the parent for child-model policies. `allows()`/`allowsEach()` and the 401/403 split come from `Keystone\Http\Requests\Request`, and `Keystone\Testing\Arch::modelsWithoutPolicies()` backs the arch rule.
