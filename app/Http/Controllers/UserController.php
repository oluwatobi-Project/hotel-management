<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('accessRole')->orderBy('name')->get();
        $roles = Role::orderBy('name')->get();
        $modules = collect(config('rbac.modules', []))
            ->reject(fn ($definition) => $definition['always'] ?? false);

        return view('users.index', compact('users', 'roles', 'modules'));
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);

        $request->validate([
            'password' => ['required', 'string', 'min:8'],
        ]);

        User::create(array_merge($data, [
            'password' => Hash::make($request->password),
        ]));

        return $this->respond($request, 'User account created.');
    }

    public function update(Request $request, User $user)
    {
        if ($user->id === auth()->id()) {
            return $this->respond($request, 'Use your profile settings to edit your own account.', false);
        }

        $data = $this->validateData($request, $user);

        if ($request->filled('password')) {
            $request->validate(['password' => ['string', 'min:8']]);
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return $this->respond($request, 'User account updated.');
    }

    protected function validateData(Request $request, ?User $user = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'unique:users,email,'.$user?->id],
            'phone' => ['nullable', 'string', 'max:30'],
            'role' => ['required', Rule::in(['admin', 'staff'])],
            'role_id' => ['nullable', 'integer', 'exists:roles,id'],
            'extra_modules' => ['nullable', 'array'],
            'extra_modules.*' => ['required', 'string', Rule::in(User::assignableModules())],
        ]);

        $data['extra_modules'] = $data['extra_modules'] ?? [];
        $data['role_id'] = $data['role_id'] ?? null;

        return $data;
    }

    protected function respond(Request $request, string $message, bool $success = true)
    {
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => $success, 'message' => $message]);
        }

        return back()->with($success ? 'success' : 'error', $message);
    }

    public function destroy(Request $request, User $user)
    {
        if ($user->id === auth()->id()) {
            return $this->respond($request, 'You cannot delete your own account.', false);
        }

        $user->delete();

        return $this->respond($request, 'User account deleted.');
    }
}
