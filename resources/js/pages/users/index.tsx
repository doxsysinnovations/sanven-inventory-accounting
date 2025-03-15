import { Head, useForm } from "@inertiajs/react";
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
import UsersLayout from "@/layouts/users/layout";
import HeadingSmall from "@/components/heading-small";
import { BreadcrumbItem } from "@/types";
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
    DialogDescription,
    DialogFooter,
} from "@/components/ui/dialog";
import { ToastContainer, toast } from 'react-toastify';
import 'react-toastify/dist/ReactToastify.css';
import StatusSwitch from "@/components/status-switch";

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Manage Users',
        href: '/users',
    },
];

export default function UsersIndex({ users, roles }) {
    const [search, setSearch] = useState("");
    const [status, setStatus] = useState("all");
    const [selectedRole, setSelectedRole] = useState("all"); // State for role filter
    const [isActiveFilter, setIsActiveFilter] = useState("all"); // State for is_active filter
    const [isOpen, setIsOpen] = useState(false);
    const [isDeleteDialogOpen, setIsDeleteDialogOpen] = useState(false);
    const [userToDelete, setUserToDelete] = useState(null);
    const [editingUser, setEditingUser] = useState(null);
    const [previewImage, setPreviewImage] = useState(null);

    const { data, setData, post, put, processing, errors, reset, delete: destroy } = useForm({
        name: "",
        email: "",
        role: [],
        picture: null,
    });

    const handleSubmit = (e) => {
        e.preventDefault();
        if (editingUser) {
            put(route('users.update', editingUser.id), {
                onSuccess: () => {
                    setIsOpen(false);
                    reset();
                    setEditingUser(null);
                    setPreviewImage(null);
                    toast.success("User updated successfully");
                }
            });
        } else {
            post(route('users.store'), {
                onSuccess: () => {
                    setIsOpen(false);
                    reset();
                    setPreviewImage(null);
                    toast.success("User created successfully");
                }
            });
        }
    };

    const handleEdit = (user) => {
        setEditingUser(user);
        setData({
            name: user.name,
            email: user.email,
            role: user.roles,
            picture: null
        });
        setPreviewImage(user.picture_url);
        setIsOpen(true);
    };

    const handleDelete = (user) => {
        setUserToDelete(user);
        setIsDeleteDialogOpen(true);
    };

    const confirmDelete = () => {
        destroy(route('users.destroy', userToDelete.id), {
            onSuccess: () => {
                setIsDeleteDialogOpen(false);
                setUserToDelete(null);
                toast.success("User deleted successfully");
            }
        });
    };

    const handleImageChange = (e) => {
        const file = e.target.files[0];
        if (file) {
            setData('picture', file);
            const reader = new FileReader();
            reader.onloadend = () => {
                setPreviewImage(reader.result);
            };
            reader.readAsDataURL(file);
        }
    };

    // Filter users dynamically
    const filteredUsers = users.filter((user) => {
        const matchesSearch = user.name.toLowerCase().includes(search.toLowerCase()) ||
            user.email.toLowerCase().includes(search.toLowerCase());
        const matchesStatus = status === "all" || user.status.toLowerCase() === status;
        const matchesRole = selectedRole === "all" || user.role === selectedRole; // Role filter
        const matchesActiveStatus = isActiveFilter === "all" ||
            (isActiveFilter === "active" && user.is_active) ||
            (isActiveFilter === "inactive" && !user.is_active); // is_active filter

        return matchesSearch && matchesStatus && matchesRole && matchesActiveStatus;
    });

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Manage Users" />
            <UsersLayout>
                <ToastContainer />
                <div className="space-y-6">
                    <HeadingSmall title="Users List" description="Manage system users here" />

                    <div className="p-6 space-y-6">
                        <div className="flex flex-wrap items-center justify-between gap-4">
                            <Input
                                type="text"
                                placeholder="Search users..."
                                value={search}
                                onChange={(e) => setSearch(e.target.value)}
                                className="w-1/3"
                            />
                            <div className="flex gap-4">
                                <Select
                                    value={selectedRole}
                                    onValueChange={(value) => setSelectedRole(value)}
                                >
                                    <SelectTrigger className="w-[180px]">
                                        <SelectValue placeholder="Filter by Role" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="all">All Roles</SelectItem>
                                        {roles.map((role) => (
                                            <SelectItem key={role.id} value={role.name}>
                                                {role.name}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>

                                <Select
                                    value={isActiveFilter}
                                    onValueChange={(value) => setIsActiveFilter(value)}
                                >
                                    <SelectTrigger className="w-[180px]">
                                        <SelectValue placeholder="Filter by Status" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="all">All Statuses</SelectItem>
                                        <SelectItem value="active">Active</SelectItem>
                                        <SelectItem value="inactive">Inactive</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>

                            <Dialog open={isOpen} onOpenChange={setIsOpen}>
                                <DialogTrigger asChild>
                                    <Button onClick={() => {
                                        setEditingUser(null);
                                        setPreviewImage(null);
                                        reset();
                                    }}>
                                        Add User
                                    </Button>
                                </DialogTrigger>
                                <DialogContent className="max-w-md">
                                    <DialogHeader>
                                        <DialogTitle>
                                            {editingUser ? 'Edit User' : 'Add New User'}
                                        </DialogTitle>
                                    </DialogHeader>
                                    <form onSubmit={handleSubmit} className="space-y-4">
                                        <div className="space-y-2">
                                            <label className="block text-sm font-medium">Profile Picture</label>
                                            <div className="flex items-center space-x-4">
                                                {previewImage && (
                                                    <img
                                                        src={previewImage}
                                                        alt="Preview"
                                                        className="w-20 h-20 rounded-full object-cover"
                                                    />
                                                )}
                                                <Input
                                                    type="file"
                                                    accept="image/*"
                                                    onChange={handleImageChange}
                                                />
                                            </div>
                                        </div>

                                        <Input
                                            placeholder="Full name"
                                            value={data.name}
                                            onChange={e => setData('name', e.target.value)}
                                        />
                                        {errors.name && (
                                            <div className="text-red-500 text-sm">{errors.name}</div>
                                        )}

                                        <Input
                                            type="email"
                                            placeholder="Email address"
                                            value={data.email}
                                            onChange={e => setData('email', e.target.value)}
                                        />
                                        {errors.email && (
                                            <div className="text-red-500 text-sm">{errors.email}</div>
                                        )}

                                        <Select
                                            value={data.role}
                                            onValueChange={(value) => setData('role', value)}
                                            multiple
                                        >
                                            <SelectTrigger>
                                                <SelectValue placeholder="Select roles" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                {roles.map((role) => (
                                                    <SelectItem key={role.id} value={role.name}>
                                                        {role.name}
                                                    </SelectItem>
                                                ))}
                                            </SelectContent>
                                        </Select>
                                        {errors.role && (
                                            <div className="text-red-500 text-sm">{errors.role}</div>
                                        )}

                                        <Button type="submit" disabled={processing}>
                                            {editingUser ? 'Update' : 'Create'}
                                        </Button>
                                    </form>
                                </DialogContent>
                            </Dialog>
                        </div>

                        <Dialog open={isDeleteDialogOpen} onOpenChange={setIsDeleteDialogOpen}>
                            <DialogContent>
                                <DialogHeader>
                                    <DialogTitle>Delete User</DialogTitle>
                                    <DialogDescription>
                                        Are you sure you want to delete this user? This action cannot be undone.
                                    </DialogDescription>
                                </DialogHeader>
                                <DialogFooter>
                                    <Button variant="outline" onClick={() => setIsDeleteDialogOpen(false)}>
                                        Cancel
                                    </Button>
                                    <Button variant="destructive" onClick={confirmDelete}>
                                        Delete
                                    </Button>
                                </DialogFooter>
                            </DialogContent>
                        </Dialog>

                        <div className="border rounded-xl overflow-hidden shadow">
                            <Table>
                                <TableHeader>
                                    <TableRow className="bg-gray-100">
                                        <TableHead className="font-semibold">Picture</TableHead>
                                        <TableHead className="font-semibold">Name</TableHead>
                                        <TableHead className="font-semibold">Email</TableHead>
                                        <TableHead className="font-semibold">Role</TableHead>
                                        <TableHead className="font-semibold">Status</TableHead>
                                        <TableHead className="font-semibold text-center w-[10%]">Action</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {filteredUsers.length > 0 ? (
                                        filteredUsers.map((user) => (
                                            <TableRow key={user.id}>
                                                <TableCell>
                                                    <img
                                                        src={user.picture_url || '/default-avatar.png'}
                                                        alt={user.name}
                                                        className="w-10 h-10 rounded-full object-cover"
                                                    />
                                                </TableCell>
                                                <TableCell>{user.name}</TableCell>
                                                <TableCell>{user.email}</TableCell>
                                                <TableCell>
                                                    <span className="px-2 py-1 rounded-full text-xs">
                                                        {user.roles.map((role, index) => (
                                                            <span key={index} className="mr-1">
                                                                {role.name}
                                                                {index < user.roles.length - 1 && ", "}
                                                            </span>
                                                        ))}
                                                    </span>
                                                </TableCell>
                                                <TableCell>
                                                    <TableCell>
                                                        <StatusSwitch model="User" recordId={user.id} initialStatus={user.is_active} />
                                                    </TableCell>
                                                </TableCell>
                                                <TableCell className="text-right">
                                                    <div className="flex gap-2 justify-end">
                                                        <Button
                                                            variant="outline"
                                                            size="sm"
                                                            onClick={() => handleEdit(user)}
                                                        >
                                                            Edit
                                                        </Button>
                                                        <Button
                                                            variant="destructive"
                                                            size="sm"
                                                            onClick={() => handleDelete(user)}
                                                        >
                                                            Delete
                                                        </Button>
                                                    </div>
                                                </TableCell>
                                            </TableRow>
                                        ))
                                    ) : (
                                        <TableRow>
                                            <TableCell colSpan="6" className="text-center py-4">
                                                No users found.
                                            </TableCell>
                                        </TableRow>
                                    )}
                                </TableBody>
                            </Table>
                        </div>
                    </div>
                </div>
            </UsersLayout>
        </AppLayout>
    );
}
