<script setup lang="ts">
import { toUrl, urlIsActive } from '@/lib/utils';
import { edit as editProfile } from '@/routes/profile';
import { show } from '@/routes/two-factor';
import { edit as editPassword } from '@/routes/user-password';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/vue3';

const navItems: NavItem[] = [
    {
        title: 'Profile',
        href: editProfile(),
    },
    {
        title: 'Password',
        href: editPassword(),
    },
    {
        title: 'Two-Factor',
        href: show(),
    },
];

const currentPath = typeof window !== 'undefined' ? window.location.pathname : '';
</script>

<template>
    <div class="py-8">
        <!-- Horizontal Tab Navigation -->
        <div class="mb-8 flex justify-center">
            <nav class="inline-flex rounded-lg bg-muted p-1">
                <Link
                    v-for="item in navItems"
                    :key="toUrl(item.href)"
                    :href="item.href"
                    :class="[
                        'rounded-md px-4 py-2 text-sm font-medium transition-colors',
                        urlIsActive(item.href, currentPath)
                            ? 'bg-background text-foreground shadow-sm'
                            : 'text-muted-foreground hover:text-foreground',
                    ]"
                >
                    {{ item.title }}
                </Link>
            </nav>
        </div>

        <!-- Content -->
        <div class="mx-auto max-w-2xl px-4">
            <slot />
        </div>
    </div>
</template>
