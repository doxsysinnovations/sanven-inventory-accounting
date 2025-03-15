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
        title: 'Manage Roles',
        href: '/roles',
    },
];

export default function RolesIndex({ roles, permissions }) {
    const [search, setSearch] = useState("");
    const [status, setStatus] = useState("all");
    const [isOpen, setIsOpen] = useState(false);
    const [isDeleteDialogOpen, setIsDeleteDialogOpen] = useState(false);
    const [roleToDelete, setRoleToDelete] = useState(null);
    const [editingRole, setEditingRole] = useState(null);

    const { data, setData, post, put, processing, errors, reset, delete: destroy } = useForm({
        name: "",
        permissions: [],
        giveAllPermissions: false,
    });

    const handlePermissionChange = (permissionId) => {
        const updatedPermissions = data.permissions.includes(permissionId)
            ? data.permissions.filter(id => id !== permissionId)
            : [...data.permissions, permissionId];
        setData('permissions', updatedPermissions);
    };

    const handleGiveAllPermissionsChange = (e) => {
        const giveAllPermissions = e.target.checked;
        setData('giveAllPermissions', giveAllPermissions);
        if (giveAllPermissions) {
            setData('permissions', permissions.map(permission => permission.id));
        } else {
            setData('permissions', []);
        }
    };

    const handleSubmit = (e) => {
        e.preventDefault();
        if (editingRole) {
            put(route('roles.update', editingRole.id), {
                onSuccess: () => {
                    setIsOpen(false);
                    reset();
                    setEditingRole(null);
                    toast.success("Role updated successfully");
                }
            });
        } else {
            post(route('roles.store'), {
                onSuccess: () => {
                    setIsOpen(false);
                    reset();
                    toast.success("Role created successfully");
                }
            });
        }
    };

    const handleEdit = (role) => {
        setEditingRole(role);
        setData('name', role.name);
        setData('permissions', role.permissions.map(permission => permission.id));
        setIsOpen(true);
    };

    const handleDelete = (role) => {
        setRoleToDelete(role);
        setIsDeleteDialogOpen(true);
    };

    const confirmDelete = () => {
        destroy(route('roles.destroy', roleToDelete.id), {
            onSuccess: () => {
                setIsDeleteDialogOpen(false);
                setRoleToDelete(null);
                toast.success("Role deleted successfully");
            }
        });
    };

    // Group permissions by their prefix (before the first dot)
    const groupedPermissions = permissions.reduce((groups, permission) => {
        const [group] = permission.name.split('.');
        if (!groups[group]) {
            groups[group] = [];
        }
        groups[group].push(permission);
        return groups;
    }, {});

    // Filter roles dynamically
    const filteredRoles = roles.filter((role) => {
        const matchesSearch = role.name.toLowerCase().includes(search.toLowerCase());
        const matchesStatus = status === "all" || role.status.toLowerCase() === status;

        return matchesSearch && matchesStatus;
    });

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Manage Roles" />
            <UsersLayout>
                <ToastContainer />
                <div className="space-y-6">
                    <HeadingSmall title="Roles Lists" description="See all roles here" />

                    <div className="p-6 space-y-6">
                        {/* Filters */}
                        <div className="flex flex-wrap items-center justify-between gap-4">
                            <Input
                                type="text"
                                placeholder="Search roles..."
                                value={search}
                                onChange={(e) => setSearch(e.target.value)}
                                className="w-1/3"
                            />
                            <Dialog open={isOpen} onOpenChange={setIsOpen}>
                                <DialogTrigger asChild>
                                    <Button onClick={() => {
                                        setEditingRole(null);
                                        reset();
                                    }}>
                                        Add Role
                                    </Button>
                                </DialogTrigger>
                                <DialogContent className="max-w-4xl">
                                    <DialogHeader>
                                        <DialogTitle>
                                            {editingRole ? 'Edit Role' : 'Add New Role'}
                                        </DialogTitle>
                                    </DialogHeader>
                                    <form onSubmit={handleSubmit} className="space-y-4">
                                        <Input
                                            placeholder="Role name"
                                            value={data.name}
                                            onChange={e => setData('name', e.target.value)}
                                        />
                                        {errors.name && (
                                            <div className="text-red-500 text-sm">{errors.name}</div>
                                        )}
                                        <div>
                                            <label className="flex items-center space-x-2">
                                                <input
                                                    type="checkbox"
                                                    checked={data.giveAllPermissions}
                                                    onChange={handleGiveAllPermissionsChange}
                                                    className="rounded border-gray-300"
                                                />
                                                <span>Give All Permissions</span>
                                            </label>
                                        </div>
                                        <div className="grid grid-cols-3 gap-6">
                                            {Object.entries(groupedPermissions).map(([group, perms]) => (
                                                <div key={group} className="space-y-2">
                                                    <h3 className="font-semibold capitalize">{group}</h3>
                                                    {perms.map(permission => (
                                                        <div key={permission.id} className="ml-2">
                                                            <label className="flex items-center space-x-2">
                                                                <input
                                                                    type="checkbox"
                                                                    checked={data.permissions.includes(permission.id)}
                                                                    onChange={() => handlePermissionChange(permission.id)}
                                                                    className="rounded border-gray-300"
                                                                />
                                                                <span>{permission.name.split('.')[1]}</span>
                                                            </label>
                                                        </div>
                                                    ))}
                                                </div>
                                            ))}
                                        </div>
                                        <Button type="submit" disabled={processing}>
                                            {editingRole ? 'Update' : 'Create'}
                                        </Button>
                                    </form>
                                </DialogContent>
                            </Dialog>
                        </div>

                        {/* Delete Confirmation Dialog */}
                        <Dialog open={isDeleteDialogOpen} onOpenChange={setIsDeleteDialogOpen}>
                            <DialogContent>
                                <DialogHeader>
                                    <DialogTitle>Delete Role</DialogTitle>
                                    <DialogDescription>
                                        Are you sure you want to delete this role? This action cannot be undone.
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

                        {/* Roles Table */}
                        <div className="border rounded-xl overflow-hidden shadow">
                            <Table>
                                <TableHeader>
                                    <TableRow className="bg-gray-100">
                                        <TableHead className="font-semibold">ID</TableHead>
                                        <TableHead className="font-semibold">Name</TableHead>
                                        <TableHead className="font-semibold text-center w-[10%]">Action</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {filteredRoles.length > 0 ? (
                                        filteredRoles.map((role) => (
                                            <TableRow key={role.id}>
                                                <TableCell>{role.id}</TableCell>
                                                <TableCell>{role.name}</TableCell>
                                                <TableCell className="text-right">
                                                    <div className="flex gap-2 justify-end">
                                                        <Button
                                                            variant="outline"
                                                            size="sm"
                                                            onClick={() => handleEdit(role)}
                                                        >
                                                            Edit
                                                        </Button>
                                                        <Button
                                                            variant="destructive"
                                                            size="sm"
                                                            onClick={() => handleDelete(role)}
                                                        >
                                                            Delete
                                                        </Button>
                                                    </div>
                                                </TableCell>
                                            </TableRow>
                                        ))
                                    ) : (
                                        <TableRow>
                                            <TableCell colSpan="4" className="text-center py-4">
                                                No roles found.
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
