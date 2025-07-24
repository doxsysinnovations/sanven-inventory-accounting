<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Models\Agent;
use Livewire\Attributes\Title;

new class extends Component {
    use WithPagination;

    public $search = '';
    public $agent;
    public $name = '';
    public $email = '';
    public $phone = '';
    public $address = '';
    public $is_active = true;
    public $agentInfo;
    public $activeTab = 'basic';

    //Location related properties
    public $selectedLocations = [];
    public $allLocations = [];

    public function mount(Agent $id)
    {
        $this->agentInfo = $id;
        $this->activeTab = 'basic';
        $this->allLocations = \App\Models\Location::where('is_active', true)->pluck('name', 'id')->toArray();
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
            ->paginate(10);
    }
};

?>

<div>
    <x-profile-layout 
        title="Agent Profile"
        modelInstance="agent"
        :modelInfo="$agentInfo"
        editRoute="agents.edit"
        :activeTab="$activeTab"
        :tabs="[
            ['key' => 'basic', 'label' => 'Basic Info'],
            ['key' => 'locations', 'label' => 'Locations'],
        ]"
        :headerDetails="[
            ['label' => 'Email', 'value' => $agentInfo->email],
            ['label' => 'Address', 'value' => $agentInfo->address ?? 'Not provided.'],
            ['label' => 'Phone Number', 'value' => $agentInfo->phone ?? 'Not provided.' ],
        ]"
        :tabContents="[
            'basic' => [
                ['label' => 'Agent Name', 'value' => $agentInfo->name],
                ['label' => 'Created At', 'value' => $agentInfo->created_at->format('M d, Y h:i A')],
                ['label' => 'Agent ID', 'value' => $agentInfo->id],
                ['label' => 'Last Updated', 'value' => $agentInfo->updated_at->format('M d, Y h:i A')],
                ['label' => 'Status', 'value' => $agentInfo->is_active ? 'Active' : 'Inactive']
            ]
        ]"
        :locationsConfig="[
            'locations' => $agentInfo->locations,
            'title' => 'Assigned Locations',
            'emptyTitle' => 'No locations assigned',
            'emptyDescription' => 'This agent is not assigned to any locations yet.',
            'showStatus' => true,
            'gridCols' => 'grid-cols-1 sm:grid-cols-2 md:grid-cols-3'
        ]"
        :withStatus="true"
        :status="$agentInfo->is_active ? 'active' : 'inactive'"
    />
</div>