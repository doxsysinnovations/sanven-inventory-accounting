<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Models\User;
use Livewire\Attributes\Title;
use Spatie\Permission\Models\Role;
use Spatie\Activitylog\Models\Activity;

new class extends Component {
    use WithPagination;

    public $search = '';
    public $showModal = false;
    public $user;
    public $isEditing = false;
    public $confirmingDelete = false;
    public $userToDelete;
    public $roles = [];
    public $selectedRoles = [];
    public $selectedUserType = '';
    public $perPage = 10;

    public $form = [
        'name' => '',
        'username' => '',
        'email' => '',
        'user_type' => '',
        'password' => '',
    ];

    public function mount()
    {
        $this->roles = Role::all();
        $this->perPage = session('perPage', 10);
    }

    public function confirmDelete($userId)
    {
        $this->userToDelete = $userId;
        $this->confirmingDelete = true;
    }

    public function delete()
    {
        $user = User::find($this->userToDelete);
        if ($user) {
            $user->roles()->detach();
            $user->delete();
            flash()->success('User deleted successfully!');
        }
        $this->confirmingDelete = false;
        $this->userToDelete = null;
    }

    #[Title('Users')]
    public function with(): array
    {
        return [
            'users' => $this->users,
        ];
    }

    public function getUsersProperty()
    {
        return User::query()
            ->with('roles')
            ->where('name', 'like', '%' . $this->search . '%')
            ->when($this->selectedUserType, function ($query) {
                return $query->where('user_type', $this->selectedUserType);
            })
            ->paginate($this->perPage)
            ->through(function ($user) {
                // Fetch the latest login activity for each user
                $lastLogin = Activity::where('log_name', 'user-login')->where('causer_id', $user->id)->latest()->first();

                $user->last_logged_in = $lastLogin ? $lastLogin->created_at : null;

                return $user;
            });
    }
};

?>

<div>
    <x-view-layout
        title="All Users"
        :items="$users"
        searchPlaceholder="Search Users..."
        message="No users available."
        :withRoleFilter="true"
        :perPage="$perPage"
        createButtonLabel="Add User"
        createButtonAbility="users.create"
        createButtonRoute="users.create"
    >
        <x-slot:emptyIcon>
            <svg class="w-48 h-48 mb-2 text-gray-300 dark:text-gray-600" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 24 24">
                <path fill-rule="evenodd" d="M12 6a3.5 3.5 0 1 0 0 7 3.5 3.5 0 0 0 0-7Zm-1.5 8a4 4 0 0 0-4 4 2 2 0 0 0 2 2h7a2 2 0 0 0 2-2 4 4 0 0 0-4-4h-3Zm6.82-3.096a5.51 5.51 0 0 0-2.797-6.293 3.5 3.5 0 1 1 2.796 6.292ZM19.5 18h.5a2 2 0 0 0 2-2 4 4 0 0 0-4-4h-1.1a5.503 5.503 0 0 1-.471.762A5.998 5.998 0 0 1 19.5 18ZM4 7.5a3.5 3.5 0 0 1 5.477-2.889 5.5 5.5 0 0 0-2.796 6.293A3.501 3.501 0 0 1 4 7.5ZM7.1 12H6a4 4 0 0 0-4 4 2 2 0 0 0 2 2h.5a5.998 5.998 0 0 1 3.071-5.238A5.505 5.505 0 0 1 7.1 12Z" clip-rule="evenodd"/>
            </svg>
        </x-slot:emptyIcon>
        <x-list-table
            :headers="['Name', 'Username', 'Email', 'Verified At', 'User Type', 'Roles', 'Status', 'Last Login', 'Actions']"
            :rows="$users->map(fn($user) => [
                $user->name,
                $user->username,
                $user->email,
                $user->email_verified_at,
                $user_type_label = match ($user->user_type) {
                    'student' => 'Student',
                    'admin' => 'Admin',
                    'superadmin' => 'Super Admin',
                    'faculty' => 'Faculty',
                    default => ucwords(str_replace(['_', '-'], ' ', $user->user_type)),
                },
                $user->roles->pluck('name')->map(function ($role) {
                    return match ($role) {
                        'student' => 'Student',
                        'admin' => 'Admin',
                        'superadmin' => 'Super Admin',
                        'faculty' => 'Faculty',
                        default => ucwords(str_replace(['_', '-'], ' ', $role)),
                    };
                })->implode(' '),
                auth()->user()->can('users.disable-enable') ? ($user->is_active ? 'active' : 'inactive') : '',
                $user->last_logged_in ? $user->last_logged_in->diffForHumans() : 'Never logged in.',
                '__model' => $user,
            ])"
            editAbility="users.edit"
            editParameter="user"
            editRoute="users.edit"
            deleteAbility="users.delete"
            deleteAction="confirmDelete"
        />
    </x-view-layout>

    @if ($confirmingDelete)
        <x-delete-modal 
            title="Delete User"
            message="Are you sure you want to delete this user? This action cannot be undone."
            onCancel="$set('confirmingDelete', false)"
        />
    @endif
</div>