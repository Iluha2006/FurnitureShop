import { toast } from 'sonner';
import type { ReactNode } from 'react';
import type { FlashToast } from '@/types/ui';

export type NotifyType = FlashToast['type'];

export type NotifyOptions = {
    description?: ReactNode;
    icon?: ReactNode;
};

/**
 * Show a storefront notification through the global Sonner toaster.
 *
 * @param message Text shown in the toast body.
 * @param type Notification kind, defaults to `success`.
 * @param options Extra Sonner options, e.g. a `description` or an `icon`.
 */
export function notify(
    message: string,
    type: NotifyType = 'success',
    options: NotifyOptions = {},
): string | number {
    return toast[type](message, options);
}
