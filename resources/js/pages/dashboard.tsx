import { PlaceholderPattern } from '@/components/ui/placeholder-pattern';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';
import { FaBoxes, FaShoppingCart, FaTags, FaCopyright, FaTruck, FaUsers, FaChartLine, FaChartBar } from 'react-icons/fa';
import { LineChart, Line, BarChart, Bar, XAxis, YAxis, CartesianGrid, Tooltip, ResponsiveContainer } from 'recharts';

const chartData = [
    { name: 'Jan', value: 400 },
    { name: 'Feb', value: 300 },
    { name: 'Mar', value: 600 },
    { name: 'Apr', value: 800 },
    { name: 'May', value: 500 }
];

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

export default function Dashboard() {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Dashboard" />
            <div className="flex h-full flex-1 flex-col gap-6 p-6 dark:bg-gray-900">
                {/* Key Metrics Section */}
                <div className="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4">
                    <div className="bg-gradient-to-r from-blue-500 to-blue-600 rounded-xl border border-blue-400 p-6 shadow-lg transition-all hover:shadow-xl">
                        <div className="flex items-center gap-4">
                            <FaBoxes className="h-8 w-8 text-white" />
                            <div>
                                <h3 className="text-white text-sm font-medium">Total Inventories</h3>
                                <p className="text-3xl font-bold text-white mt-2">1,234</p>
                            </div>
                        </div>
                    </div>
                    <div className="bg-gradient-to-r from-purple-500 to-purple-600 rounded-xl border border-purple-400 p-6 shadow-lg transition-all hover:shadow-xl">
                        <div className="flex items-center gap-4">
                            <FaShoppingCart className="h-8 w-8 text-white" />
                            <div>
                                <h3 className="text-white text-sm font-medium">Total Products</h3>
                                <p className="text-3xl font-bold text-white mt-2">5,678</p>
                            </div>
                        </div>
                    </div>
                    <div className="bg-gradient-to-r from-green-500 to-green-600 rounded-xl border border-green-400 p-6 shadow-lg transition-all hover:shadow-xl">
                        <div className="flex items-center gap-4">
                            <FaTags className="h-8 w-8 text-white" />
                            <div>
                                <h3 className="text-white text-sm font-medium">Categories</h3>
                                <p className="text-3xl font-bold text-white mt-2">45</p>
                            </div>
                        </div>
                    </div>
                    <div className="bg-gradient-to-r from-red-500 to-red-600 rounded-xl border border-red-400 p-6 shadow-lg transition-all hover:shadow-xl">
                        <div className="flex items-center gap-4">
                            <FaCopyright className="h-8 w-8 text-white" />
                            <div>
                                <h3 className="text-white text-sm font-medium">Brands</h3>
                                <p className="text-3xl font-bold text-white mt-2">23</p>
                            </div>
                        </div>
                    </div>
                </div>

                {/* Suppliers and Customers Section */}
                <div className="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div className="bg-gradient-to-r from-yellow-500 to-yellow-600 rounded-xl border border-yellow-400 p-6 shadow-lg transition-all hover:shadow-xl">
                        <div className="flex items-center gap-4">
                            <FaTruck className="h-8 w-8 text-white" />
                            <div>
                                <h3 className="text-white text-sm font-medium">Suppliers</h3>
                                <p className="text-3xl font-bold text-white mt-2">89</p>
                            </div>
                        </div>
                    </div>
                    <div className="bg-gradient-to-r from-pink-500 to-pink-600 rounded-xl border border-pink-400 p-6 shadow-lg transition-all hover:shadow-xl">
                        <div className="flex items-center gap-4">
                            <FaUsers className="h-8 w-8 text-white" />
                            <div>
                                <h3 className="text-white text-sm font-medium">Customers</h3>
                                <p className="text-3xl font-bold text-white mt-2">1,234</p>
                            </div>
                        </div>
                    </div>
                </div>

                {/* Analytics Charts Section */}
                <div className="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 shadow-lg">
                    <h3 className="text-gray-600 dark:text-gray-400 mb-6 text-sm font-medium">Analytics</h3>
                    <div className="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <div className="h-64">
                            <ResponsiveContainer width="100%" height="100%">
                                <LineChart data={chartData}>
                                    <CartesianGrid strokeDasharray="3 3" />
                                    <XAxis dataKey="name" />
                                    <YAxis />
                                    <Tooltip />
                                    <Line type="monotone" dataKey="value" stroke="#8884d8" strokeWidth={2} animationDuration={2000} />
                                </LineChart>
                            </ResponsiveContainer>
                        </div>
                        <div className="h-64">
                            <ResponsiveContainer width="100%" height="100%">
                                <BarChart data={chartData}>
                                    <CartesianGrid strokeDasharray="3 3" />
                                    <XAxis dataKey="name" />
                                    <YAxis />
                                    <Tooltip />
                                    <Bar dataKey="value" fill="#82ca9d" animationDuration={2000} />
                                </BarChart>
                            </ResponsiveContainer>
                        </div>
                    </div>
                </div>

                {/* Recently Added Products Section */}
                <div className="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 shadow-lg">
                    <h3 className="text-gray-600 dark:text-gray-400 mb-6 text-sm font-medium">Recently Added Products</h3>
                    <div className="space-y-6">
                        {[1, 2, 3].map((item) => (
                            <div key={item} className="flex items-center justify-between hover:bg-gray-50 dark:hover:bg-gray-700 p-4 rounded-lg transition-colors">
                                <div className="flex items-center gap-4">
                                    <div className="bg-gradient-to-r from-indigo-500 to-indigo-600 h-12 w-12 rounded-xl flex items-center justify-center">
                                        <FaShoppingCart className="h-6 w-6 text-white" />
                                    </div>
                                    <div>
                                        <p className="font-medium text-gray-900 dark:text-white">Product Name {item}</p>
                                        <p className="text-gray-500 dark:text-gray-400 text-sm">Category {item}</p>
                                    </div>
                                </div>
                                <p className="text-gray-500 dark:text-gray-400 text-sm">2 days ago</p>
                            </div>
                        ))}
                    </div>
                </div>

                {/* Agents Section */}
                <div className="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 shadow-lg">
                    <h3 className="text-gray-600 dark:text-gray-400 mb-6 text-sm font-medium">Agents</h3>
                    <div className="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                        {[1, 2, 3, 4, 5, 6].map((item) => (
                            <div key={item} className="flex items-center gap-4 hover:bg-gray-50 dark:hover:bg-gray-700 p-4 rounded-lg transition-colors">
                                <div className="bg-gradient-to-r from-teal-500 to-teal-600 h-14 w-14 rounded-full flex items-center justify-center">
                                    <FaUsers className="h-6 w-6 text-white" />
                                </div>
                                <div>
                                    <p className="font-medium text-gray-900 dark:text-white">Agent Name {item}</p>
                                    <p className="text-gray-500 dark:text-gray-400 text-sm">Region {item}</p>
                                </div>
                            </div>
                        ))}
                    </div>
                </div>


            </div>
        </AppLayout>
    );
}
