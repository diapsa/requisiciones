import { Link, usePage } from '@inertiajs/react';
import {
    Building2,
    CarFront,
    ChevronRight,
    CircleDollarSign,
    Folder,
    FolderKanban,
    Landmark,
    LayoutGrid,
    MapPin,
    Tags,
} from 'lucide-react';
import { useState } from 'react';
import { SidebarLogo } from '@/components/app-logo';
import { NavFooter } from '@/components/nav-footer';
import { NavMain } from '@/components/nav-main';
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
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
    SidebarMenuSub,
    SidebarMenuSubButton,
    SidebarMenuSubItem,
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
import { index as vehiclesIndex } from '@/routes/vehicles';
import type { NavItem } from '@/types';

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
        icon: LayoutGrid,
    },
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

const footerNavItems: NavItem[] = [];

export function AppSidebar() {
    const { auth } = usePage().props;
    const canManageCatalogs = auth.permissions.includes('manage catalogs');

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
                <NavMain items={mainNavItems} />
                {canManageCatalogs && <NavCatalogs />}
            </SidebarContent>

            <SidebarFooter>
                <NavFooter items={footerNavItems} className="mt-auto" />
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}

function NavCatalogs() {
    const { isCurrentUrl } = useCurrentUrl();
    const { isMobile, state, setOpen, setOpenMobile } = useSidebar();
    const hasActiveCatalog = catalogNavItems.some((item) =>
        isCurrentUrl(item.href),
    );
    const [catalogsOpen, setCatalogsOpen] = useState(hasActiveCatalog);
    const isIconSidebar = !isMobile && state === 'collapsed';

    function handleOpenChange(open: boolean) {
        setCatalogsOpen(open);

        if (open && isIconSidebar) {
            setOpen(true);
        }
    }

    return (
        <SidebarGroup className="px-2 py-0">
            <SidebarMenu>
                <Collapsible
                    asChild
                    open={catalogsOpen && !isIconSidebar}
                    onOpenChange={handleOpenChange}
                >
                    <SidebarMenuItem>
                        <CollapsibleTrigger asChild>
                            <SidebarMenuButton
                                type="button"
                                tooltip="Catálogos"
                                isActive={hasActiveCatalog && isIconSidebar}
                                className="group/catalogs-trigger"
                            >
                                <Folder />
                                <span>Catálogos</span>
                                <ChevronRight className="ml-auto transition-transform group-data-[collapsible=icon]:hidden group-data-[state=open]/catalogs-trigger:rotate-90" />
                            </SidebarMenuButton>
                        </CollapsibleTrigger>
                        <CollapsibleContent asChild>
                            <SidebarMenuSub>
                                {catalogNavItems.map((item) => {
                                    const isActive = isCurrentUrl(item.href);

                                    return (
                                        <SidebarMenuSubItem key={item.title}>
                                            <SidebarMenuSubButton
                                                asChild
                                                isActive={isActive}
                                            >
                                                <Link
                                                    href={item.href}
                                                    aria-current={
                                                        isActive
                                                            ? 'page'
                                                            : undefined
                                                    }
                                                    onSuccess={() =>
                                                        setOpenMobile(false)
                                                    }
                                                    prefetch
                                                >
                                                    {item.icon && <item.icon />}
                                                    <span>{item.title}</span>
                                                </Link>
                                            </SidebarMenuSubButton>
                                        </SidebarMenuSubItem>
                                    );
                                })}
                            </SidebarMenuSub>
                        </CollapsibleContent>
                    </SidebarMenuItem>
                </Collapsible>
            </SidebarMenu>
        </SidebarGroup>
    );
}
