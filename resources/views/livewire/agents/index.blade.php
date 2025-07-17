<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Models\Agent;
use Livewire\Attributes\Title;

new class extends Component {
    use WithPagination;

    public $search = '';
    public $agent;
    public $confirmingDelete = false;
    public $agentToDelete;
    public $name = '';
    public $email = '';
    public $phone = '';
    public $address = '';
    public $is_active = true;
    public $perPage = 5;

    //Location related properties
    public $allLocations = [];

    public function mount()
    {
        $this->allLocations = \App\Models\Location::where('is_active', true)->pluck('name', 'id')->toArray();
        $this->perPage = session('perPage', 5);
    }

    public function updatedPerPage($value)
    {
        session(['perPage' => $value]);
        $this->resetPage();
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'email' => $this->isEditing ? 'required|email|unique:agents,email,' . $this->agent->id : 'required|email|unique:agents,email',
            'phone' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'is_active' => 'boolean'
        ];
    }

    public function confirmDelete($agentId)
    {
        $this->agentToDelete = $agentId;
        $this->confirmingDelete = true;
    }

    public function delete()
    {
        $agent = Agent::find($this->agentToDelete);
        if ($agent) {
            $agent->delete();
            flash()->success('Agent deleted successfully!');
        }
        $this->confirmingDelete = false;
        $this->agentToDelete = null;
    }

    #[Title('Agents')]
    public function with(): array
    {
        return [
            'agents' => $this->agents,
        ];
    }

    public function getAgentsProperty()
    {
        return Agent::query()
            ->where('name', 'like', '%' . $this->search . '%')
            ->orWhere('email', 'like', '%' . $this->search . '%')
            ->paginate($this->perPage);
    }
};

?>

<div>
    <x-view-layout
        title="Agents"
        :items="$agents"
        :perPage="$perPage"
        searchPlaceholder="Search Agents..."
        message="No exisiting agents."
        createButtonLabel="Add Agents"
        createButtonAbility="agents.create"
        createButtonRoute="agents.create"
    >
        <x-slot:emptyIcon>
            <svg class="w-48 h-48 mb-2 text-gray-300 dark:text-gray-600" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 24 24">
                <path fill-rule="evenodd" d="M8 4a4 4 0 1 0 0 8 4 4 0 0 0 0-8Zm-2 9a4 4 0 0 0-4 4v1a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2v-1a4 4 0 0 0-4-4H6Zm7.25-2.095c.478-.86.75-1.85.75-2.905a5.973 5.973 0 0 0-.75-2.906 4 4 0 1 1 0 5.811ZM15.466 20c.34-.588.535-1.271.535-2v-1a5.978 5.978 0 0 0-1.528-4H18a4 4 0 0 1 4 4v1a2 2 0 0 1-2 2h-4.535Z" clip-rule="evenodd"/>
            </svg>
        </x-slot:emptyIcon>
        <x-list-table
            :headers="['Name', 'Email', 'Phone', 'Location/s', 'Status', 'Actions']"
            :rows="$agents->map(fn($agent) => [
                $agent->name,
                $agent->email,
                !empty($agent->phone) ? $agent->phone : 'No phone number available.',
                $agent->locations->isNotEmpty() ? $agent->locations()->wherePivotNull('deleted_at')->pluck('name')->join(', ') : 'No location/s available.',
                $agent->is_active ? 'active' : 'inactive',
                '__model' => $agent
            ])"
            viewAbility="agents.view"
            viewRoute="agents.view"
            editAbility="agents.edit"
            editParameter="agent"
            editRoute="agents.edit"
            deleteAbility="agents.delete"
            deleteAction="confirmDelete"
        />
    </x-view-layout>

    @if ($confirmingDelete)
        <x-delete-modal 
            title="Delete Agent"
            message="Are you sure you want to delete this agent? This action cannot be undone."
            onCancel="$set('confirmingDelete', false)"
        />
    @endif
</div>