
<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Models\Agent;
use Livewire\Attributes\Title;

new class extends Component {
    use WithPagination;

    public $search = '';
    public $showModal = false;
    public $agent;
    public $isEditing = false;
    public $agentToDelete;
    public $name = '';
    public $email = '';
    public $phone = '';
    public $address = '';
    public $is_active = true;
    public $perPage = 5;

    // Info Modal properties
    public $locationIds = [];
    public $activeTab = 'basic';

    //Location related properties
    public $selectedLocations = [];
    public $allLocations = [];

    public function mount(Agent $agent)
    {
        $this->isEditing = true;
        $this->resetValidation();
        $this->agent = $agent;
        $this->name = $agent->name;
        $this->email = $agent->email;
        $this->phone = $agent->phone;
        $this->address = $agent->address;
        $this->is_active = $agent->is_active;
        
        $this->selectedLocations = $agent->locations()->wherePivotNull('deleted_at')->pluck('locations.id')->toArray();
        $this->allLocations = \App\Models\Location::where('is_active', true)->pluck('name', 'id')->toArray();
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'email' => $this->isEditing ? 'required|email|unique:agents,email,' . $this->agent->id : 'required|email|unique:agents,email',
            'phone' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'is_active' => 'boolean',
            'selectedLocations' => 'array'
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Please enter the agent’s name.',
            'name.string' => 'Name should only contain letters and spaces.',
            'name.max' => 'Name is too long.',

            'email.required' => 'Please enter the agent’s email address.',
            'email.email' => 'That doesn’t look like a valid email. Please check it again.',
            'email.unique' => 'This email is already being used by another agent.',

            'phone.string' => 'Please enter a valid phone number.',
            'phone.max' => 'The phone number is too long.',

            'address.string' => 'Please enter a valid address.',
            'address.max' => 'The address is too long. Please shorten it.',

            'is_active.boolean' => 'Please choose if the agent is active or not.',
        ];
    }

    public function updatedSelectedLocations($value)

    {
        logger('Selected locations updated:', [

            'new_value' => $value,
            'current_selections' => $this->selectedLocations

        ]);

    }

    public function save()
    {
        $this->validate();

        $locationIds = is_array($this->selectedLocations) ? $this->selectedLocations : [];

        if ($this->isEditing) {
            $this->agent->update([
                'name' => $this->name,
                'email' => $this->email,
                'phone' => $this->phone,
                'address' => $this->address,
                'is_active' => $this->is_active,
            ]);
            
            $existing = $this->agent->locations()->withTrashed()->get();

            foreach ($locationIds as $id) {
                $pivot = $existing->firstWhere('id', $id);

                if ($pivot) {
                    $this->agent->locations()->updateExistingPivot($id, ['deleted_at' => null]);
                } else {
                    $this->agent->locations()->attach($id);
                }
            }

            foreach ($existing as $location) {
                if (!in_array($location->id, $locationIds)) {
                    $this->agent->locations()->updateExistingPivot($location->id, ['deleted_at' => now()]);
                }
            }

            flash()->success('Agent updated successfully!');
        } else {
            $agent = Agent::create([
                'name' => $this->name,
                'email' => $this->email,
                'phone' => $this->phone,
                'address' => $this->address,
                'is_active' => $this->is_active,
            ]);
            
            $agent->locations()->attach($locationIds);
            flash()->success('Agent created successfully!');
        }

        return redirect()->route('agents');
    }

    private function resetForm()
    {
        $this->name = '';
        $this->email = '';
        $this->phone = '';
        $this->address = '';
        $this->is_active = true;
        $this->agent = null;
        $this->resetValidation();

        $this->selectedLocations = [];
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

    public function cancel() 
    {
        if($this->isEditing) {
            return redirect()->route('agents');
        }
    }
};

?>

<div>
    <x-agents-form 
        :is-editing="true"
        :locations="$allLocations"
    />
</div>