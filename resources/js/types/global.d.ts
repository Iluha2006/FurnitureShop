import type { Auth } from '@/types/auth';
import type { CartQuantities } from '@/types/cart';
import type { Team } from '@/types/teams';

declare module 'react' {
    interface InputHTMLAttributes<T> {
        passwordrules?: string;
    }
}

declare module '@inertiajs/core' {
    export interface InertiaConfig {
        sharedPageProps: {
            name: string;
            auth: Auth;
            sidebarOpen: boolean;
            cartCount: number;
            cartQuantities: CartQuantities;
            currentTeam: Team | null;
            teams: Team[];
            [key: string]: unknown;
        };
    }
}
