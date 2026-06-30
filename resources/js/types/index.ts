import type { LucideIcon } from 'lucide-vue-next';

export interface Auth {
    user: User;
}

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface NavItem {
    title: string;
    href: string;
    icon?: LucideIcon;
    isActive?: boolean;
}

export interface SharedData {
    name: string;
    quote: { message: string; author: string };
    auth: Auth;
    flash: { success: string | null; error: string | null };
    notifications?: {
        unread: number;
        items: { id: number; title: string; body: string | null; link: string | null; is_read: boolean; created_at: string }[];
    };
    ziggy: {
        location: string;
        url: string;
        port: null | number;
        defaults: Record<string, unknown>;
        routes: Record<string, string>;
    };
}

export interface User {
    id: number;
    name: string;
    username?: string | null;
    role: 'admin' | 'client' | 'provider';
    phone?: string | null;
    status?: 'pending' | 'active' | 'suspended';
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
}

export interface Paginated<T> {
    data: T[];
    links: { url: string | null; label: string; active: boolean }[];
    total: number;
    current_page?: number;
    last_page?: number;
}

export type BreadcrumbItemType = BreadcrumbItem;
