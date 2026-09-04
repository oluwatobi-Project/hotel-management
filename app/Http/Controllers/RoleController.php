<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::withCount('users')->orderBy('name')->get();
        $modules = collect(config('rbac.modules', []))
            ->reject(fn ($definition) => $definition['always'] ?? false);

        return view('roles.index', compact('roles', 'modules'));
    }

    protected function validateData(Request $request, ?Role $role = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:120', Rule::unique('roles', 'name')->ignore($role?->id)],
            'description' => ['nullable', 'string', 'max:255'],
            'modules' => ['required', 'array', 'min:1'],
            'modules.*' => ['required', 'string', Rule::in(User::assignableModules())],
        ]);
    }

    protected function respond(Request $request, string $message, bool $success = true)
    {
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => $success, 'message' => $message]);
        }

        return back()->with($success ? 'success' : 'error', $message);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);

        Role::create([
            'name' => $data['name'],
            'slug' => Role::uniqueSlug($data['name']),
            'description' => $data['description'] ?? null,
            'modules' => $data['modules'],
        ]);

        return $this->respond($request, 'Role created.');
    }

    public function update(Request $request, Role $role)
    {
        $data = $this->validateData($request, $role);

        $role->update([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'modules' => $data['modules'],
        ]);

        return $this->respond($request, 'Role updated.');
    }

    public function destroy(Request $request, Role $role)
    {
        if ($role->users()->count() > 0) {
            return $this->respond($request, 'Cannot delete a role that is assigned to staff members.', false);
        }

        $role->delete();

        return $this->respond($request, 'Role deleted.');
    }
}
