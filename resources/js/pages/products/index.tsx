import { Head, router } from "@inertiajs/react";
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
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from "@/components/ui/dialog";

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Manage Products',
        href: '/products',
    },
];

interface Product {
    id: number;
    name: string;
    status: string;
}

export default function ProductsIndex({ products }) {
    const [search, setSearch] = useState("");
    const [status, setStatus] = useState("all");
    const [isOpen, setIsOpen] = useState(false);
    const [isDeleteOpen, setIsDeleteOpen] = useState(false);
    const [isEditOpen, setIsEditOpen] = useState(false);
    const [selectedProduct, setSelectedProduct] = useState<Product | null>(null);
    const [form, setForm] = useState({
        name: '',
        status: ''
    });

    // Filter products dynamically
    const filteredProducts = products.filter((product) => {
        const matchesSearch = product.name.toLowerCase().includes(search.toLowerCase());
        const matchesStatus = status === "all" || product.status.toLowerCase() === status;

        return matchesSearch && matchesStatus;
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        router.post('/products', form);
        setIsOpen(false);
    };

    const handleEdit = (e: React.FormEvent) => {
        e.preventDefault();
        router.put(`/products/${selectedProduct?.id}`, form);
        setIsEditOpen(false);
    };

    const handleDelete = () => {
        router.delete(`/products/${selectedProduct?.id}`);
        setIsDeleteOpen(false);
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Manage Products" />
            <ProductsLayout>
                <div className="space-y-6">
                    <HeadingSmall title="Products Lists" description="See all products here" />

                    <div className="p-6 space-y-6">
                        {/* Filters */}
                        <div className="flex flex-wrap gap-4 justify-between">
                            <Input
                                type="text"
                                placeholder="Search products..."
                                value={search}
                                onChange={(e) => setSearch(e.target.value)}
                                className="w-1/3"
                            />
                            <Dialog open={isOpen} onOpenChange={setIsOpen}>
                                <DialogTrigger asChild>
                                    <Button>Add Product</Button>
                                </DialogTrigger>
                                <DialogContent className="sm:max-w-[900px]">
                                    <DialogHeader>
                                        <DialogTitle>Add New Product</DialogTitle>
                                    </DialogHeader>
                                    <form onSubmit={handleSubmit} className="space-y-4">
                                        <Input
                                            placeholder="Product Name"
                                            value={form.name}
                                            onChange={(e) => setForm({ ...form, name: e.target.value })}
                                        />
                                        <Select
                                            value={form.status}
                                            onValueChange={(value) => setForm({ ...form, status: value })}
                                        >
                                            <SelectTrigger>
                                                <SelectValue placeholder="Select status" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem value="active">Active</SelectItem>
                                                <SelectItem value="inactive">Inactive</SelectItem>
                                            </SelectContent>
                                        </Select>
                                        <DialogFooter>
                                            <Button type="submit">Save</Button>
                                        </DialogFooter>
                                    </form>
                                </DialogContent>
                            </Dialog>
                        </div>

                        {/* Products Table */}
                        <div className="border rounded-xl overflow-hidden shadow">
                            <Table>
                                <TableHeader>
                                    <TableRow className="bg-gray-100">
                                        <TableHead className="font-semibold">ID</TableHead>
                                        <TableHead className="font-semibold">Name</TableHead>
                                        <TableHead className="font-semibold">Action</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {filteredProducts.length > 0 ? (
                                        filteredProducts.map((product) => (
                                            <TableRow key={product.id}>
                                                <TableCell>{product.id}</TableCell>
                                                <TableCell>{product.name}</TableCell>
                                                <TableCell>
                                                    <div className="flex gap-2">
                                                        <Dialog open={isEditOpen} onOpenChange={setIsEditOpen}>
                                                            <DialogTrigger asChild>
                                                                <Button
                                                                    variant="outline"
                                                                    onClick={() => {
                                                                        setSelectedProduct(product);
                                                                        setForm({
                                                                            name: product.name,
                                                                            status: product.status
                                                                        });
                                                                    }}
                                                                >
                                                                    Edit
                                                                </Button>
                                                            </DialogTrigger>
                                                            <DialogContent>
                                                                <DialogHeader>
                                                                    <DialogTitle>Edit Product</DialogTitle>
                                                                </DialogHeader>
                                                                <form onSubmit={handleEdit} className="space-y-4">
                                                                    <Input
                                                                        placeholder="Product Name"
                                                                        value={form.name}
                                                                        onChange={(e) => setForm({ ...form, name: e.target.value })}
                                                                    />
                                                                    <Select
                                                                        value={form.status}
                                                                        onValueChange={(value) => setForm({ ...form, status: value })}
                                                                    >
                                                                        <SelectTrigger>
                                                                            <SelectValue placeholder="Select status" />
                                                                        </SelectTrigger>
                                                                        <SelectContent>
                                                                            <SelectItem value="active">Active</SelectItem>
                                                                            <SelectItem value="inactive">Inactive</SelectItem>
                                                                        </SelectContent>
                                                                    </Select>
                                                                    <DialogFooter>
                                                                        <Button type="submit">Update</Button>
                                                                    </DialogFooter>
                                                                </form>
                                                            </DialogContent>
                                                        </Dialog>

                                                        <Dialog open={isDeleteOpen} onOpenChange={setIsDeleteOpen}>
                                                            <DialogTrigger asChild>
                                                                <Button
                                                                    variant="destructive"
                                                                    onClick={() => setSelectedProduct(product)}
                                                                >
                                                                    Delete
                                                                </Button>
                                                            </DialogTrigger>
                                                            <DialogContent>
                                                                <DialogHeader>
                                                                    <DialogTitle>Delete Product</DialogTitle>
                                                                    <DialogDescription>
                                                                        Are you sure you want to delete this product?
                                                                    </DialogDescription>
                                                                </DialogHeader>
                                                                <DialogFooter>
                                                                    <Button variant="outline" onClick={() => setIsDeleteOpen(false)}>
                                                                        Cancel
                                                                    </Button>
                                                                    <Button variant="destructive" onClick={handleDelete}>
                                                                        Delete
                                                                    </Button>
                                                                </DialogFooter>
                                                            </DialogContent>
                                                        </Dialog>
                                                    </div>
                                                </TableCell>
                                            </TableRow>
                                        ))
                                    ) : (
                                        <TableRow>
                                            <TableCell colSpan="4" className="text-center py-4">
                                                No products found.
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
