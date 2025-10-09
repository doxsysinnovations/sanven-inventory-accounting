<?php

use Livewire\Volt\Component;

new class extends Component {
    public $agents = [
        [
            'id' => 1,
            'agent_name' => 'John Smith',
            'customer_name' => 'City Pharmacy',
            'customer_type' => 'Pharmacy',
            'invoice_number' => 'INV-2023-001',
            'date' => '2023-01-15',
            'due_date' => '2023-02-15',
            'total_amount' => 1250.50,
            'status' => 'Paid',
            'days_overdue' => 0,
        ],
        [
            'id' => 2,
            'agent_name' => 'Sarah Johnson',
            'customer_name' => 'General Hospital',
            'customer_type' => 'Hospital',
            'invoice_number' => 'INV-2023-002',
            'date' => '2023-01-18',
            'due_date' => '2023-02-18',
            'total_amount' => 875.25,
            'status' => 'Pending',
            'days_overdue' => 12,
        ],
        [
            'id' => 3,
            'agent_name' => 'Michael Chen',
            'customer_name' => 'Quick Drugstore',
            'customer_type' => 'Drugstore',
            'invoice_number' => 'INV-2023-003',
            'date' => '2023-01-20',
            'due_date' => '2023-02-20',
            'total_amount' => 2200.00,
            'status' => 'Unpaid',
            'days_overdue' => 45,
        ],
        // ... (97 more records will be generated)
    ];

    public function mount()
    {
        $customerTypes = ['Pharmacy', 'Hospital', 'Drugstore'];
        $pharmacyNames = ['City', 'Metro', 'Central', 'Family', 'Wellness', 'Care', 'Vital', 'Life', 'Community', 'Premium'];
        $hospitalNames = ['General', 'Memorial', 'Regional', 'City', 'University', 'Children\'s', 'Saint', 'Mercy', 'Parkview', 'Valley'];
        $drugstoreNames = ['Quick', 'Express', 'Neighborhood', 'Discount', 'Corner', '24/7', 'Value', 'Save', 'Prime', 'Care'];

        // Generate the remaining 97 records
        for ($i = 4; $i <= 100; $i++) {
            $statuses = ['Paid', 'Pending', 'Unpaid'];
            $status = $statuses[array_rand($statuses)];

            $date = now()->subDays(rand(10, 90))->format('Y-m-d');
            $dueDate = date('Y-m-d', strtotime($date . ' +' . rand(15, 30) . ' days'));

            $daysOverdue = ($status !== 'Paid') ? max(0, now()->diffInDays($dueDate)) : 0;

            // Generate customer data
            $customerType = $customerTypes[array_rand($customerTypes)];
            $customerName = '';

            switch($customerType) {
                case 'Pharmacy':
                    $customerName = $pharmacyNames[array_rand($pharmacyNames)] . ' Pharmacy';
                    break;
                case 'Hospital':
                    $customerName = $hospitalNames[array_rand($hospitalNames)] . ' Hospital';
                    break;
                case 'Drugstore':
                    $customerName = $drugstoreNames[array_rand($drugstoreNames)] . ' Drugstore';
                    break;
            }

            $this->agents[] = [
                'id' => $i,
                'agent_name' => $this->generateRandomName(),
                'customer_name' => $customerName,
                'customer_type' => $customerType,
                'invoice_number' => 'INV-2023-' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'date' => $date,
                'due_date' => $dueDate,
                'total_amount' => rand(500, 5000) + (rand(0, 99) / 100),
                'status' => $status,
                'days_overdue' => $daysOverdue,
            ];
        }
    }

    private function generateRandomName()
    {
        $firstNames = ['James', 'Mary', 'Robert', 'Patricia', 'David', 'Jennifer', 'William', 'Linda', 'Richard', 'Elizabeth'];
        $lastNames = ['Smith', 'Johnson', 'Williams', 'Brown', 'Jones', 'Garcia', 'Miller', 'Davis', 'Rodriguez', 'Martinez'];

        return $firstNames[array_rand($firstNames)] . ' ' . $lastNames[array_rand($lastNames)];
    }
}; ?>

<div class="bg-gray-900 text-gray-100 min-h-screen p-6">
    <div class="max-w-7xl mx-auto">
        <h1 class="text-3xl font-bold mb-6">Agent Aging Report with Customers</h1>

        <div class="bg-gray-800 rounded-lg shadow-lg overflow-hidden">
            <div class="p-4 border-b border-gray-700 flex justify-between items-center">
                <div class="flex space-x-4">
                    <input
                        type="text"
                        placeholder="Search..."
                        class="bg-gray-700 text-white px-4 py-2 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                    <select class="bg-gray-700 text-white px-4 py-2 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option>All Statuses</option>
                        <option>Paid</option>
                        <option>Pending</option>
                        <option>Unpaid</option>
                    </select>
                    <select class="bg-gray-700 text-white px-4 py-2 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option>All Customer Types</option>
                        <option>Pharmacy</option>
                        <option>Hospital</option>
                        <option>Drugstore</option>
                    </select>
                </div>
                <button class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md transition">
                    Export Report
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-700">
                    <thead class="bg-gray-750">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                                Agent
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                                Customer
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                                Type
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                                Invoice #
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                                Date
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                                Due Date
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                                Amount
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                                Status
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                                Days Overdue
                            </th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-300 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-gray-800 divide-y divide-gray-700">
                        @foreach($agents as $agent)
                        <tr class="hover:bg-gray-750 transition">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10 rounded-full bg-gray-700 flex items-center justify-center">
                                        <span class="text-gray-300">{{ substr($agent['agent_name'], 0, 1) }}</span>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-white">{{ $agent['agent_name'] }}</div>
                                        <div class="text-sm text-gray-400">ID: {{ $agent['id'] }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-white">{{ $agent['customer_name'] }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $typeColors = [
                                        'Pharmacy' => 'bg-purple-800 text-purple-100',
                                        'Hospital' => 'bg-blue-800 text-blue-100',
                                        'Drugstore' => 'bg-indigo-800 text-indigo-100'
                                    ];
                                @endphp
                                <span class="px-2 py-1 text-xs rounded-full {{ $typeColors[$agent['customer_type']] }}">
                                    {{ $agent['customer_type'] }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-blue-400 font-mono">
                                {{ $agent['invoice_number'] }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">
                                {{ date('M d, Y', strtotime($agent['date'])) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">
                                {{ date('M d, Y', strtotime($agent['due_date'])) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-300">
                                ${{ number_format($agent['total_amount'], 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $statusColors = [
                                        'Paid' => 'bg-green-800 text-green-100',
                                        'Pending' => 'bg-yellow-800 text-yellow-100',
                                        'Unpaid' => 'bg-red-800 text-red-100'
                                    ];
                                @endphp
                                <span class="px-2 py-1 text-xs rounded-full {{ $statusColors[$agent['status']] }}">
                                    {{ $agent['status'] }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">
                                @if($agent['days_overdue'] > 0)
                                    <span class="text-red-400">{{ $agent['days_overdue'] }} days</span>
                                @else
                                    <span class="text-green-400">On time</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <button class="text-blue-400 hover:text-blue-300 mr-3">View</button>
                                <button class="text-gray-400 hover:text-gray-300">More</button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="px-6 py-4 border-t border-gray-700 flex items-center justify-between">
                <div class="text-sm text-gray-400">
                    Showing <span class="font-medium">1</span> to <span class="font-medium">10</span> of <span class="font-medium">100</span> results
                </div>
                <div class="flex space-x-2">
                    <button class="px-3 py-1 rounded-md bg-gray-700 text-gray-300 hover:bg-gray-600">
                        Previous
                    </button>
                    <button class="px-3 py-1 rounded-md bg-blue-600 text-white">
                        1
                    </button>
                    <button class="px-3 py-1 rounded-md bg-gray-700 text-gray-300 hover:bg-gray-600">
                        2
                    </button>
                    <button class="px-3 py-1 rounded-md bg-gray-700 text-gray-300 hover:bg-gray-600">
                        3
                    </button>
                    <button class="px-3 py-1 rounded-md bg-gray-700 text-gray-300 hover:bg-gray-600">
                        Next
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
