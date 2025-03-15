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
        title: 'Manage Brands',
        href: '/brands',
    },
];

export default function BrandsIndex({ brands }) {
    const [search, setSearch] = useState("");
    const [status, setStatus] = useState("all");
    const [isOpen, setIsOpen] = useState(false);
    const [isDeleteDialogOpen, setIsDeleteDialogOpen] = useState(false);
    const [brandToDelete, setBrandToDelete] = useState(null);
    const [editingBrand, setEditingBrand] = useState(null);

    const { data, setData, post, put, processing, errors, reset, delete: destroy } = useForm({
        name: "",
    });

    const handleSubmit = (e) => {
        e.preventDefault();
        if (editingBrand) {
            put(route('brands.update', editingBrand.id), {
                onSuccess: () => {
                    setIsOpen(false);
                    reset();
                    setEditingBrand(null);
                    toast.success("Brand updated successfully");
                }
            });
        } else {
            post(route('brands.store'), {
                onSuccess: () => {
                    setIsOpen(false);
                    reset();
                    toast.success("Brand created successfully");
                }
            });
        }
    };

    const handleEdit = (brand) => {
        setEditingBrand(brand);
        setData('name', brand.name);
        setIsOpen(true);
    };

    const handleDelete = (brand) => {
        setBrandToDelete(brand);
        setIsDeleteDialogOpen(true);
    };

    const confirmDelete = () => {
        destroy(route('brands.destroy', brandToDelete.id), {
            onSuccess: () => {
                setIsDeleteDialogOpen(false);
                setBrandToDelete(null);
                toast.success("Brand deleted successfully");
            }
        });
    };

    // Filter brands dynamically
    const filteredBrands = brands.filter((brand) => {
        const matchesSearch = brand.name.toLowerCase().includes(search.toLowerCase());
        const matchesStatus = status === "all" || brand.status.toLowerCase() === status;

        return matchesSearch && matchesStatus;
    });

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Manage Brands" />
            <ProductsLayout>
                <ToastContainer />
                <div className="space-y-6">
                    <HeadingSmall title="Brands Lists" description="See all brands here" />

                    <div className="p-6 space-y-6">
                        {/* Filters */}
                        <div className="flex flex-wrap items-center justify-between gap-4">
                            <Input
                                type="text"
                                placeholder="Search brands..."
                                value={search}
                                onChange={(e) => setSearch(e.target.value)}
                                className="w-1/3"
                            />
                            <Dialog open={isOpen} onOpenChange={setIsOpen}>
                                <DialogTrigger asChild>
                                    <Button onClick={() => {
                                        setEditingBrand(null);
                                        reset();
                                    }}>
                                        Add Brand
                                    </Button>
                                </DialogTrigger>
                                <DialogContent>
                                    <DialogHeader>
                                        <DialogTitle>
                                            {editingBrand ? 'Edit Brand' : 'Add New Brand'}
                                        </DialogTitle>
                                    </DialogHeader>
                                    <form onSubmit={handleSubmit} className="space-y-4">
                                        <Input
                                            placeholder="Brand name"
                                            value={data.name}
                                            onChange={e => setData('name', e.target.value)}
                                        />
                                        {errors.name && (
                                            <div className="text-red-500 text-sm">{errors.name}</div>
                                        )}
                                        <Button type="submit" disabled={processing}>
                                            {editingBrand ? 'Update' : 'Create'}
                                        </Button>
                                    </form>
                                </DialogContent>
                            </Dialog>
                        </div>

                        {/* Delete Confirmation Dialog */}
                        <Dialog open={isDeleteDialogOpen} onOpenChange={setIsDeleteDialogOpen}>
                            <DialogContent>
                                <DialogHeader>
                                    <DialogTitle>Delete Brand</DialogTitle>
                                    <DialogDescription>
                                        Are you sure you want to delete this brand? This action cannot be undone.
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

                        {/* Brands Table */}
                        <div className="border rounded-xl overflow-hidden shadow">
                            <Table>
                                <TableHeader>
                                    <TableRow className="bg-gray-100">
                                        <TableHead className="font-semibold">ID</TableHead>
                                        <TableHead className="font-semibold">Name</TableHead>
                                        <TableHead className="font-semibold">Status</TableHead>
                                        <TableHead className="font-semibold text-center w-[10%]">Action</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {filteredBrands.length > 0 ? (
                                        filteredBrands.map((brand) => (
                                            <TableRow key={brand.id}>
                                                <TableCell>{brand.id}</TableCell>
                                                <TableCell>{brand.name}</TableCell>
                                                <TableCell>
                                                    <StatusSwitch model="Brand" recordId={brand.id} initialStatus={brand.is_active} />
                                                </TableCell>
                                                <TableCell className="text-right">
                                                    <div className="flex gap-2 justify-end">
                                                        <Button
                                                            variant="outline"
                                                            size="sm"
                                                            onClick={() => handleEdit(brand)}
                                                        >
                                                            Edit
                                                        </Button>
                                                        <Button
                                                            variant="destructive"
                                                            size="sm"
                                                            onClick={() => handleDelete(brand)}
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
                                                No brands found.
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
