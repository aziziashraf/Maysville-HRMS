<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserMenuAccess;
use Illuminate\Http\Request;

class EmployeeMenuAccessController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show which sidebar items a staff member is assigned.
     */
    public function edit(User $user)
    {
        return view('employee.menu_access', [
            'employee' => $user,
            'items' => UserMenuAccess::items(),
            'assigned' => $this->effectiveState($user),
        ]);
    }

    /**
     * Save the assignment. Every item in the registry gets a row, so the
     * result is explicit rather than relying on defaults from here on.
     */
    public function update(Request $request, User $user)
    {
        $checked = array_keys($request->input('menu', []));

        foreach (array_keys(UserMenuAccess::items()) as $key) {
            UserMenuAccess::updateOrCreate(
                ['user_id' => $user->id, 'menu_key' => $key],
                ['is_visible' => in_array($key, $checked, true)]
            );
        }

        $user->forgetMenuAccessMap();

        return redirect()
            ->route('employee_menu_access.edit', $user)
            ->with('success', 'Menu access updated for ' . $user->name . '.');
    }

    /**
     * Restore this staff member to the configured defaults by dropping their
     * saved rows.
     */
    public function reset(User $user)
    {
        UserMenuAccess::where('user_id', $user->id)->delete();
        $user->forgetMenuAccessMap();

        return redirect()
            ->route('employee_menu_access.edit', $user)
            ->with('success', 'Menu access reset to defaults for ' . $user->name . '.');
    }

    /**
     * What each item currently resolves to for this staff member, along with
     * why — so an admin can see when a role permission, not their choice here,
     * is what is hiding something.
     *
     * @return array<string, array{visible: bool, saved: bool, blocked_by_role: bool}>
     */
    private function effectiveState(User $user): array
    {
        $saved = $user->menuAccessMap();
        $state = [];

        foreach (UserMenuAccess::items() as $key => $item) {
            $blockedByRole = !empty($item['ability']) && $user->cannot($item['ability']);

            $state[$key] = [
                'visible' => $saved[$key] ?? (bool) ($item['default'] ?? true),
                'saved' => array_key_exists($key, $saved),
                'blocked_by_role' => $blockedByRole,
            ];
        }

        return $state;
    }
}
