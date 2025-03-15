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
import StatusSwitch from "@/components/status-switch";
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
import { Switch } from "@/components/ui/switch";


const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Manage Categories',
        href: '/categories',
    },
];

export default function CategoriesIndex({ categories }) {
    const [search, setSearch] = useState("");
    const [status, setStatus] = useState("all");
    const [isOpen, setIsOpen] = useState(false);
    const [isDeleteDialogOpen, setIsDeleteDialogOpen] = useState(false);
    const [categoryToDelete, setCategoryToDelete] = useState(null);
    const [editingCategory, setEditingCategory] = useState(null);

    const { data, setData, post, put, processing, errors, reset, delete: destroy } = useForm({
        name: "", description: "",
    });

    const handleSubmit = (e) => {
        e.preventDefault();
        if (editingCategory) {
            put(route('categories.update', editingCategory.id), {
                onSuccess: () => {
                    setIsOpen(false);
                    reset();
                    setEditingCategory(null);
                    toast.success("Category updated successfully");
                }
            });
        } else {
            post(route('categories.store'), {
                onSuccess: () => {
                    setIsOpen(false);
                    reset();
                    toast.success("Category created successfully");
                }
            });
        }
    };

    const handleEdit = (category) => {
        setEditingCategory(category);
        setData('name', category.name);
        setData('description', category.description);
        setIsOpen(true);
    };

    const handleDelete = (category) => {
        setCategoryToDelete(category);
        setIsDeleteDialogOpen(true);
    };

    const confirmDelete = () => {
        destroy(route('categories.destroy', categoryToDelete.id), {
            onSuccess: () => {
                setIsDeleteDialogOpen(false);
                setCategoryToDelete(null);
                toast.success("Category deleted successfully");
            }
        });
    };

    // Filter categories dynamically
    const filteredCategories = categories.filter((category) => {
        const matchesSearch = category.name.toLowerCase().includes(search.toLowerCase());
        const matchesStatus = status === "all" || category.status.toLowerCase() === status;

        return matchesSearch && matchesStatus;
    });

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Manage Categories" />
            <ProductsLayout>
                <ToastContainer />
                <div className="space-y-6">
                    <HeadingSmall title="Categories Lists" description="See all categories here" />

                    <div className="p-6 space-y-6">
                        {/* Filters */}
                        <div className="flex flex-wrap items-center justify-between gap-4">
                            <Input
                                type="text"
                                placeholder="Search categories..."
                                value={search}
                                onChange={(e) => setSearch(e.target.value)}
                                className="w-1/3"
                            />
                            <Dialog open={isOpen} onOpenChange={setIsOpen}>
                                <DialogTrigger asChild>
                                    <Button onClick={() => {
                                        setEditingCategory(null);
                                        reset();
                                    }}>
                                        Add Category
                                    </Button>
                                </DialogTrigger>
                                <DialogContent>
                                    <DialogHeader>
                                        <DialogTitle>
                                            {editingCategory ? 'Edit Category' : 'Add New Category'}
                                        </DialogTitle>
                                    </DialogHeader>
                                    <form onSubmit={handleSubmit} className="space-y-4">
                                        <Input
                                            placeholder="Category name"
                                            value={data.name}
                                            onChange={e => setData('name', e.target.value)}
                                        />
                                        {errors.name && (
                                            <div className="text-red-500 text-sm">{errors.name}</div>
                                        )}
                                        <Input
                                            placeholder="Description"
                                            value={data.description}
                                            onChange={e => setData('description', e.target.value)}
                                        />
                                        {errors.description && (
                                            <div className="text-red-500 text-sm">{errors.description}</div>
                                        )}
                                        <Button type="submit" disabled={processing}>
                                            {editingCategory ? 'Update' : 'Create'}
                                        </Button>
                                    </form>
                                </DialogContent>
                            </Dialog>
                        </div>

                        {/* Delete Confirmation Dialog */}
                        <Dialog open={isDeleteDialogOpen} onOpenChange={setIsDeleteDialogOpen}>
                            <DialogContent>
                                <DialogHeader>
                                    <DialogTitle>Delete Category</DialogTitle>
                                    <DialogDescription>
                                        Are you sure you want to delete this category? This action cannot be undone.
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

                        {/* Categories Table */}
                        <div className="border rounded-xl overflow-hidden shadow">
                            <Table>
                                <TableHeader>
                                    <TableRow className="bg-gray-100">
                                        <TableHead className="font-semibold">ID</TableHead>
                                        <TableHead className="font-semibold">Name</TableHead>
                                        <TableHead className="font-semibold">Description</TableHead>
                                        <TableHead className="font-semibold">Status</TableHead>
                                        <TableHead className="font-semibold text-center w-[10%]">Action</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {filteredCategories.length > 0 ? (
                                        filteredCategories.map((category) => (
                                            <TableRow key={category.id}>
                                                <TableCell>{category.id}</TableCell>
                                                <TableCell>{category.name}</TableCell>
                                                <TableCell>{category.description}</TableCell>
                                                <TableCell>
                                                    <StatusSwitch model="Category" recordId={category.id} initialStatus={category.is_active} />
                                                </TableCell>
                                                <TableCell>
                                                    <div className="flex gap-2">
                                                        <Button
                                                            variant="outline"
                                                            size="sm"
                                                            onClick={() => handleEdit(category)}
                                                        >
                                                            Edit
                                                        </Button>
                                                        <Button
                                                            variant="destructive"
                                                            size="sm"
                                                            onClick={() => handleDelete(category)}
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
                                                No categories found.
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
