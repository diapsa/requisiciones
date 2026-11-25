import { Link, usePage } from '@inertiajs/react';
import {
    Building2,
    CarFront,
    ChartColumn,
    ChevronRight,
    CircleDollarSign,
    ClipboardCheck,
    ClipboardList,
    FolderKanban,
    House,
    Landmark,
    MapPin,
    ReceiptText,
    Tags,
    Users,
    Wallet,
} from 'lucide-react';
import { useState } from 'react';
import { SidebarLogo } from '@/components/app-logo';
import { NavFooter } from '@/components/nav-footer';
import { NavMain, NavMainItem } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import {
    Collapsible,
    CollapsibleContent,
    CollapsibleTrigger,
} from '@/components/ui/collapsible';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarGroup,
    SidebarGroupLabel,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
    useSidebar,
} from '@/components/ui/sidebar';
import { useCurrentUrl } from '@/hooks/use-current-url';
import { dashboard } from '@/routes';
import { index as bankAccountsIndex } from '@/routes/bank-accounts';
import { index as businessUnitsIndex } from '@/routes/business-units';
import { index as departmentsIndex } from '@/routes/departments';
import { index as expenseRatesIndex } from '@/routes/expense-rates';
import { index as expenseTypesIndex } from '@/routes/expense-types';
import { index as projectsIndex } from '@/routes/projects';
import { index as usersIndex } from '@/routes/users';
import { index as vehiclesIndex } from '@/routes/vehicles';
import type { NavItem } from '@/types';

const mainNavItems: NavItem[] = [
    {
        title: 'Panel',
        href: dashboard(),
        icon: House,
    },
];

const upcomingNavItems = [
    { title: 'Requisiciones', icon: ClipboardList },
    { title: 'Autorizaciones', icon: ClipboardCheck },
    { title: 'Comprobaciones', icon: ReceiptText },
    { title: 'Depósitos y saldos', icon: Wallet },
    { title: 'Reportes', icon: ChartColumn },
];

const catalogNavItems: NavItem[] = [
    {
        title: 'Departamentos',
        href: departmentsIndex(),
        icon: Building2,
    },
    {
        title: 'Unidades de negocio',
        href: businessUnitsIndex(),
        icon: MapPin,
    },
    {
        title: 'Proyectos',
        href: projectsIndex(),
        icon: FolderKanban,
    },
    {
        title: 'Vehículos',
        href: vehiclesIndex(),
        icon: CarFront,
    },
    {
        title: 'Tipos de gasto',
        href: expenseTypesIndex(),
        icon: Tags,
    },
    {
        title: 'Tarifas de gasto',
        href: expenseRatesIndex(),
        icon: CircleDollarSign,
    },
    {
        title: 'Cuentas bancarias',
        href: bankAccountsIndex(),
        icon: Landmark,
    },
];

const usersNavItem: NavItem = {
    title: 'Usuarios',
    href: usersIndex(),
    icon: Users,
};

const footerNavItems: NavItem[] = [];

export function AppSidebar() {
    const { auth } = usePage().props;
    const canManageCatalogs = auth.permissions.includes('manage catalogs');
    const canManageUsers = auth.permissions.includes('manage users');

    return (
        <Sidebar collapsible="icon" variant="inset">
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" asChild>
                            <Link
                                href={dashboard()}
                                prefetch
                                aria-label="DAP, gestión de requisiciones. Ir al panel"
                                className="h-auto group-data-[collapsible=icon]:justify-center group-data-[collapsible=icon]:p-0!"
                            >
                                <SidebarLogo />
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent>
                <NavMain items={mainNavItems} label="General" />
                <SidebarGroup className="px-2 py-0">
                    <SidebarGroupLabel>Operación</SidebarGroupLabel>
                    <SidebarMenu>
                        {upcomingNavItems.map(({ title, icon: Icon }) => (
                            <SidebarMenuItem key={title}>
                                <SidebarMenuButton
                                    asChild
                                    tooltip={`${title} · Próximamente`}
                                    className="cursor-default text-sidebar-foreground/60 hover:bg-transparent hover:text-sidebar-foreground/60"
                                >
                                    <span>
                                        <Icon aria-hidden="true" />
                                        <span>{title}</span>
                                    </span>
                                </SidebarMenuButton>
                            </SidebarMenuItem>
                        ))}
                    </SidebarMenu>
                </SidebarGroup>
                {canManageCatalogs && <NavCatalogs />}
                {canManageUsers && (
                    <NavMain items={[usersNavItem]} label="Administración" />
                )}
            </SidebarContent>

            <SidebarFooter>
                <NavFooter items={footerNavItems} className="mt-auto" />
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}

/**
 * Catalogs get their own section, separate from daily operation and from user administration.
 * Its title folds the list; with the sidebar reduced to icons, every catalog icon stays visible.
 */
function NavCatalogs() {
    const { isCurrentUrl } = useCurrentUrl();
    const { isMobile, state } = useSidebar();
    const hasActiveCatalog = catalogNavItems.some((item) =>
        isCurrentUrl(item.href),
    );
    const [catalogsOpen, setCatalogsOpen] = useState(hasActiveCatalog);
    const isIconSidebar = !isMobile && state === 'collapsed';

    return (
        <Collapsible
            asChild
            open={catalogsOpen || isIconSidebar}
            onOpenChange={setCatalogsOpen}
        >
            <SidebarGroup className="px-2 py-0">
                <SidebarGroupLabel asChild>
                    <CollapsibleTrigger className="group/catalogs-trigger w-full hover:bg-sidebar-accent hover:text-sidebar-accent-foreground">
                        Catálogos
                        <ChevronRight className="ml-auto transition-transform group-data-[state=open]/catalogs-trigger:rotate-90" />
                    </CollapsibleTrigger>
                </SidebarGroupLabel>
                <CollapsibleContent>
                    <SidebarMenu>
                        {catalogNavItems.map((item) => (
                            <NavMainItem key={item.title} item={item} />
                        ))}
                    </SidebarMenu>
                </CollapsibleContent>
            </SidebarGroup>
        </Collapsible>
    );
}
