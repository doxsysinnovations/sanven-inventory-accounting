import { useState } from "react";
import { Switch } from "@/components/ui/switch";
import { router } from '@inertiajs/react';
import { toast } from "react-toastify";

interface StatusSwitchProps {
    model: string;
    recordId: number;
    initialStatus: boolean;
}

const StatusSwitch: React.FC<StatusSwitchProps> = ({ model, recordId, initialStatus }) => {
    const [isActive, setIsActive] = useState<boolean>(initialStatus);
    const [loading, setLoading] = useState<boolean>(false);

    const toggleStatus = async () => {
        if (loading) return; // Prevent multiple clicks
        setLoading(true);

        try {
            // Send the current state as part of the request to ensure proper toggling
            await router.post(`/toggle-status/${model}/${recordId}`, {
                current_status: isActive
            }, {
                onSuccess: (response: { is_active: boolean }) => {
                    const newStatus = response.is_active;
                    setIsActive(newStatus);
                    toast.success(`${model} status updated successfully`);
                },
                onError: () => {
                    // Revert the switch state on error
                    setIsActive(isActive);
                    toast.error("Failed to update status");
                },
                onFinish: () => {
                    setLoading(false);
                }
            });
        } catch (error) {
            console.error("Error updating status", error);
            // Revert the switch state on error
            setIsActive(isActive);
            toast.error("Failed to update status");
            setLoading(false);
        }
    };

    return (
        <Switch
            checked={isActive}
            onCheckedChange={toggleStatus}
            disabled={loading}
            className="data-[state=checked]:bg-green-500 data-[state=unchecked]:bg-gray-500"
        />
    );
};

export default StatusSwitch;
