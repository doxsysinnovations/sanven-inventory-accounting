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
import ProductsLayout from "@/layouts/products/layout";
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
        title: 'Manage Units',
        href: '/units',
    },
];

export default function UnitsIndex({ units }) {
    const [search, setSearch] = useState("");
    const [status, setStatus] = useState("all");
    const [isOpen, setIsOpen] = useState(false);
    const [isDeleteDialogOpen, setIsDeleteDialogOpen] = useState(false);
    const [unitToDelete, setUnitToDelete] = useState(null);
    const [editingUnit, setEditingUnit] = useState(null);

    const { data, setData, post, put, processing, errors, reset, delete: destroy } = useForm({
        name: "",
    });

    const handleSubmit = (e) => {
        e.preventDefault();
        if (editingUnit) {
            put(route('units.update', editingUnit.id), {
                onSuccess: () => {
                    setIsOpen(false);
                    reset();
                    setEditingUnit(null);
                    toast.success("Unit updated successfully");
                }
            });
        } else {
            post(route('units.store'), {
                onSuccess: () => {
                    setIsOpen(false);
                    reset();
                    toast.success("Unit created successfully");
                }
            });
        }
    };

    const handleEdit = (unit) => {
        setEditingUnit(unit);
        setData('name', unit.name);
        setIsOpen(true);
    };

    const handleDelete = (unit) => {
        setUnitToDelete(unit);
        setIsDeleteDialogOpen(true);
    };

    const confirmDelete = () => {
        destroy(route('units.destroy', unitToDelete.id), {
            onSuccess: () => {
                setIsDeleteDialogOpen(false);
                setUnitToDelete(null);
                toast.success("Unit deleted successfully");
            }
        });
    };

    // Filter units dynamically
    const filteredUnits = units.filter((unit) => {
        const matchesSearch = unit.name.toLowerCase().includes(search.toLowerCase());
        const matchesStatus = status === "all" || unit.status.toLowerCase() === status;

        return matchesSearch && matchesStatus;
    });

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Manage Units" />
            <ProductsLayout>
                <ToastContainer />
                <div className="space-y-6">
                    <HeadingSmall title="Units Lists" description="See all units here" />

                    <div className="p-6 space-y-6">
                        {/* Filters */}
                        <div className="flex flex-wrap items-center justify-between gap-4">
                            <Input
                                type="text"
                                placeholder="Search units..."
                                value={search}
                                onChange={(e) => setSearch(e.target.value)}
                                className="w-1/3"
                            />
                            <Dialog open={isOpen} onOpenChange={setIsOpen}>
                                <DialogTrigger asChild>
                                    <Button onClick={() => {
                                        setEditingUnit(null);
                                        reset();
                                    }}>
                                        Add Unit
                                    </Button>
                                </DialogTrigger>
                                <DialogContent>
                                    <DialogHeader>
                                        <DialogTitle>
                                            {editingUnit ? 'Edit Unit' : 'Add New Unit'}
                                        </DialogTitle>
                                    </DialogHeader>
                                    <form onSubmit={handleSubmit} className="space-y-4">
                                        <Input
                                            placeholder="Unit name"
                                            value={data.name}
                                            onChange={e => setData('name', e.target.value)}
                                        />
                                        {errors.name && (
                                            <div className="text-red-500 text-sm">{errors.name}</div>
                                        )}
                                        <Button type="submit" disabled={processing}>
                                            {editingUnit ? 'Update' : 'Create'}
                                        </Button>
                                    </form>
                                </DialogContent>
                            </Dialog>
                        </div>

                        {/* Delete Confirmation Dialog */}
                        <Dialog open={isDeleteDialogOpen} onOpenChange={setIsDeleteDialogOpen}>
                            <DialogContent>
                                <DialogHeader>
                                    <DialogTitle>Delete Unit</DialogTitle>
                                    <DialogDescription>
                                        Are you sure you want to delete this unit? This action cannot be undone.
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

                        {/* Units Table */}
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
                                    {filteredUnits.length > 0 ? (
                                        filteredUnits.map((unit) => (
                                            <TableRow key={unit.id}>
                                                <TableCell>{unit.id}</TableCell>
                                                <TableCell>{unit.name}</TableCell>
                                                <TableCell className="text-right">
                                                    <div className="flex gap-2 justify-end">
                                                        <Button
                                                            variant="outline"
                                                            size="sm"
                                                            onClick={() => handleEdit(unit)}
                                                        >
                                                            Edit
                                                        </Button>
                                                        <Button
                                                            variant="destructive"
                                                            size="sm"
                                                            onClick={() => handleDelete(unit)}
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
                                                No units found.
                                            </TableCell>
                                        </TableRow>
                                    )}
                                </TableBody>
                            </Table>
                        </div>
                    </div>
                </div>
            </ProductsLayout>
        </AppLayout>
    );
}
