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
    }

    public function rules()
    {
        return [
            'form.name' => 'required|string|max:255',
            'form.email' => $this->isEditing ? 'required|email|unique:users,email,' . $this->user->id : 'required|email|unique:users,email',
            'form.username' => $this->isEditing ? 'required|unique:users,username,' . $this->user->id : 'required|unique:users,username',
            'form.password' => $this->isEditing ? '' : 'required|min:8',
            'form.user_type' => 'required',
            'selectedRoles' => 'array',
        ];
    }

    public function messages()
    {
        return [
            'form.name.required' => 'Please enter the user\'s name.',
            'form.name.string' => 'Name should only contain letters and spaces.',
            'form.name.max' => 'Name is too long.',

            'form.email.required' => 'Please enter the user\'s email address.',
            'form.email.email' => 'That doesn’t look like a valid email. Please check it again.',
            'form.email.unique' => 'This email is already being used by another user.',

            'form.username.required' => 'Please enter a username.',
            'form.username.unique' => 'This username is already taken. Please choose another one.',

            'form.password.required' => 'Please enter a password.',
            'form.password.min' => 'Password must be at least 8 characters long.',

            'form.user_type.required' => 'Please select the user\'s type.',

            'selectedRoles.array' => 'Please select valid role(s) for this user.',
        ];
    }

    public function create()
    {
        $this->resetForm();
        $this->selectedRoles = [];
        $this->isEditing = false;
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();

        // Convert all role IDs to integers
        $this->selectedRoles = array_map('intval', $this->selectedRoles);

        if ($this->isEditing) {
            $this->user->update($this->form);
            $this->user->syncRoles($this->selectedRoles);
            flash()->success('User updated successfully!');
        } else {
            $user = User::create($this->form);
            $user->assignRole($this->selectedRoles);
            flash()->success('User created successfully!');
        }

        return redirect()->route('users');
    }

    private function resetForm()
    {
        $this->form = [
            'name' => '',
            'email' => '',
            'username' => '',
            'user_type' => '',
            'password' => '',
        ];
        $this->selectedRoles = [];
        $this->user = null;
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
            ->paginate(10)
            ->through(function ($user) {
                // Fetch the latest login activity for each user
                $lastLogin = Activity::where('log_name', 'user-login')->where('causer_id', $user->id)->latest()->first();

                $user->last_logged_in = $lastLogin ? $lastLogin->created_at : null;

                return $user;
            });
    }

    public function cancel() 
    {
        $this->resetForm();
    }
};

?>

<div>
    <x-users-form 
        :is-editing="false"
        :roles="$roles"
    />
</div>