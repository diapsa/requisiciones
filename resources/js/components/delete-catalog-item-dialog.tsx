import { Form } from '@inertiajs/react';
import { Trash2 } from 'lucide-react';
import { useState } from 'react';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import {
    Tooltip,
    TooltipContent,
    TooltipTrigger,
} from '@/components/ui/tooltip';
import type { RouteFormDefinition } from '@/wayfinder';

/**
 * Delete button for a catalog row. When the record is in use it is disabled and a tooltip explains why.
 */
export function DeleteCatalogItemDialog({
    itemName,
    description,
    form,
    blockedReason,
}: {
    itemName: string;
    description: string;
    form: RouteFormDefinition<'post'>;
    blockedReason?: string;
}) {
    const [open, setOpen] = useState(false);

    if (blockedReason) {
        return (
            <Tooltip>
                <TooltipTrigger asChild>
                    <span tabIndex={0}>
                        <Button variant="ghost" size="icon" disabled>
                            <Trash2 />
                            <span className="sr-only">Eliminar {itemName}</span>
                        </Button>
                    </span>
                </TooltipTrigger>
                <TooltipContent>{blockedReason}</TooltipContent>
            </Tooltip>
        );
    }

    return (
        <Dialog open={open} onOpenChange={setOpen}>
            <DialogTrigger asChild>
                <Button
                    variant="ghost"
                    size="icon"
                    className="text-destructive hover:text-destructive"
                >
                    <Trash2 />
                    <span className="sr-only">Eliminar {itemName}</span>
                </Button>
            </DialogTrigger>

            <DialogContent>
                <DialogTitle>¿Eliminar «{itemName}»?</DialogTitle>
                <DialogDescription>{description}</DialogDescription>

                <Form
                    {...form}
                    options={{ preserveScroll: true }}
                    onSuccess={() => setOpen(false)}
                >
                    {({ processing }) => (
                        <DialogFooter className="gap-2">
                            <DialogClose asChild>
                                <Button type="button" variant="secondary">
                                    Cancelar
                                </Button>
                            </DialogClose>
                            <Button
                                type="submit"
                                variant="destructive"
                                disabled={processing}
                            >
                                Eliminar
                            </Button>
                        </DialogFooter>
                    )}
                </Form>
            </DialogContent>
        </Dialog>
    );
}
