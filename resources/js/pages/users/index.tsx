import { Head } from "@inertiajs/react";
import AppLayout from "@/layouts/app-layout";
import { Input } from "@/components/ui/input";
import {
    Select,
    SelectTrigger,
    SelectValue,
    SelectContent,
    SelectItem,
} from "@/components/ui/select";
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from "@/components/ui/table";
import { Button } from "@/components/ui/button";
import { useState } from "react";

export default function UsersIndex({ users, roles }) {
    const [search, setSearch] = useState("");
    const [status, setStatus] = useState("all");
    const [role, setRole] = useState("all");

    // Filter users dynamically
    const filteredUsers = users.filter((user) => {
        const matchesSearch = user.name.toLowerCase().includes(search.toLowerCase()) ||
            user.email.toLowerCase().includes(search.toLowerCase());

        const matchesStatus = status === "all" || user.status.toLowerCase() === status;
        const matchesRole = role === "all" || user.role.toLowerCase() === role;

        return matchesSearch && matchesStatus && matchesRole;
    });

    return (
        <AppLayout breadcrumbs={[{ title: "Manage Users", href: "/users" }]}>
            <Head title="Manage Users" />

            <div className="p-6 space-y-6">
                {/* Filters */}
                <div className="flex flex-wrap gap-4">
                    <Input
                        type="text"
                        placeholder="Search users..."
                        value={search}
                        onChange={(e) => setSearch(e.target.value)}
                        className="w-1/3"
                    />

                    <Select value={status} onValueChange={setStatus}>
                        <SelectTrigger className="w-1/4">
                            <SelectValue placeholder="Select Status" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">All Status</SelectItem>
                            <SelectItem value="active">Active</SelectItem>
                            <SelectItem value="inactive">Inactive</SelectItem>
                        </SelectContent>
                    </Select>

                    <Select value={role} onValueChange={setRole}>
                        <SelectTrigger className="w-1/4">
                            <SelectValue placeholder="Select Role" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">All Roles</SelectItem>
                            {roles.map((role) => (
                                <SelectItem key={role.id} value={role.name.toLowerCase()}>
                                    {role.name}
                                </SelectItem>
                            ))}
                        </SelectContent>
                    </Select>
                </div>

                {/* User Table */}
                <div className="border rounded-xl overflow-hidden shadow">
                    <Table>
                        <TableHeader>
                            <TableRow className="bg-gray-100">
                                <TableHead className="font-semibold">ID</TableHead>
                                <TableHead className="font-semibold">Name</TableHead>
                                <TableHead className="font-semibold">Email</TableHead>
                                <TableHead className="font-semibold">Status</TableHead>
                                <TableHead className="font-semibold">Role</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {filteredUsers.length > 0 ? (
                                filteredUsers.map((user) => (
                                    <TableRow key={user.id}>
                                        <TableCell>{user.id}</TableCell>
                                        <TableCell>{user.name}</TableCell>
                                        <TableCell>{user.email}</TableCell>
                                        <TableCell
                                            className={user.status === "Active" ? "text-green-500" : "text-red-500"}
                                        >
                                            {user.status}
                                        </TableCell>
                                        <TableCell>{user.role}</TableCell>
                                    </TableRow>
                                ))
                            ) : (
                                <TableRow>
                                    <TableCell colSpan="5" className="text-center py-4">
                                        No users found.
                                    </TableCell>
                                </TableRow>
                            )}
                        </TableBody>
                    </Table>
                </div>
            </div>
        </AppLayout>
    );
}
