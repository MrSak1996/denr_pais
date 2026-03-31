<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { BookOpen, Folder, LayoutGrid, Users, ListCheck } from 'lucide-vue-next';
import { computed } from 'vue';
import AppLogo from './AppLogo.vue';

const page = usePage();
const auth = computed(() => page.props.auth);

// ✅ Role-based dashboard link
const dashboardHref = computed(() => {
    const roleId = auth.value.user?.role_id;

    switch (roleId) {
        case 1:
            return '/applications/pending_application';
        case 2:
            return '/rps-chief-dashboard';
        case 3:
            return '/cenro-dashboard';
        case 4:
            return '/penro-technical-dashboard';
        case 5:
            return '/penro-rps-chief-dashboard';
        case 6:
            return '/penro-tsd-chief-dashboard';
        case 7:
            return '/penro-dashboard';
        case 8:
            return '/rts-dashboard';
        case 9:
            return '/fus-dashboard';
        case 10:
            return '/lpdd-chief-dashboard';
        case 11:
            return '/ardts-dashboard';
        case 12:
            return '/regional-executive-dashboard';
        default:
            return '/dashboard';
    }
});

// ✅ Dynamic navigation items (same as header)
const mainNavItems = computed<NavItem[]>(() => [
    {
        title: 'Dashboard',
        href: dashboardHref.value, // 👈 dynamic now
        icon: Users,
    },
    {
        title: 'PRC Monitoring',
        href: '/monitoring/index',
        icon: ListCheck,
    },
    {
        title: 'Quarterly Reports',
        href: '/reports/index',
        icon: ListCheck,
    },
]);

const footerNavItems: NavItem[] = [
    {
        title: 'Github Repo',
        href: 'https://github.com/laravel/vue-starter-kit',
        icon: Folder,
    },
    {
        title: 'Documentation',
        href: 'https://laravel.com/docs/starter-kits#vue',
        icon: BookOpen,
    },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="sidebar">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="route('dashboard')">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
